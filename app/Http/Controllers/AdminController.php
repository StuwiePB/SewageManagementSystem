<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\Incident;
use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkOrder;
use App\Services\Reports\CustomerReportDrainageScan;
use App\Services\Reports\CustomerReportOperationsSync;
use App\Support\AccountEmail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display admin dashboard with statistics.
     * Total customer complaints use Report; other cards use WorkOrder data where noted.
     */
    public function dashboard()
    {
        $totalReports = Report::count();

        // Work-order–driven stats (connected to work orders for the dashboard cards)
        $reportsToday = WorkOrder::whereDate('created_at', Carbon::today())->count();
        $resolvedReports = WorkOrder::where('status', 'completed')->count();
        $workInProgress = WorkOrder::whereIn('status', [
            'pending', 'assigned', 'in_progress', 'on_the_way', 'on_site', 'pending_approval',
        ])->count();
        $cancelledWorkOrders = WorkOrder::where('status', 'cancelled')->count();

        $months = [];
        $reportsData = [];
        $maintenanceData = [];
        $resolvedData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            $reportsData[] = WorkOrder::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count();
            $maintenanceData[] = round($reportsData[count($reportsData) - 1] * 0.7);
            $resolvedData[] = WorkOrder::where('status', 'completed')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->count();
        }

        $totalWorkOrders = WorkOrder::whereNot('status', 'cancelled')->count();
        $totalForPercentage = max($totalWorkOrders, 1);
        $resolvedPercentage = round(($resolvedReports / $totalForPercentage) * 100);
        $inProgressPercentage = round(($workInProgress / $totalForPercentage) * 100);
        $pendingPercentage = max(0, 100 - $resolvedPercentage - $inProgressPercentage);

        // User counts by role (use users.role column to avoid Spatie role guard errors)
        $civilianUsers = User::where('role', User::ROLE_CUSTOMER)->count();
        $adminUsers = User::where('role', User::ROLE_ADMIN)->count();
        $operationsUsers = User::where('role', User::ROLE_OPERATOR)->count();
        $crewLeaders = User::where('role', User::ROLE_CREW_LEADER)->count();

        // Recent activities: last 7 days only (entire week)
        $activities = collect();
        $weekAgo = Carbon::now()->subWeek();

        foreach (Incident::with('user')->where('created_at', '>=', $weekAgo)->latest('created_at')->take(8)->get() as $incident) {
            $activities->push([
                'type' => 'civilian_report',
                'title' => 'Civilian reported incident',
                'description' => 'Incident #'.$incident->id.' submitted'.($incident->user ? ' by '.$incident->user->name : ''),
                'time' => $incident->created_at,
                'link' => route('admin.incidents.review'),
            ]);
        }

        foreach (Incident::where('updated_at', '>=', $weekAgo)->where(function ($q) {
            $q->where('ai_generated', true)->orWhere('review_status', 'NOT_SEWAGE_CONFIRMED');
        })->latest('updated_at')->take(5)->get() as $incident) {
            $activities->push([
                'type' => 'ai_detection',
                'title' => 'AI detected unnecessary image',
                'description' => $incident->ai_generated
                    ? 'Incident #'.$incident->id.' – image flagged as AI-generated'
                    : 'Incident #'.$incident->id.' – not sewage-related',
                'time' => $incident->updated_at,
                'link' => route('admin.incidents.review'),
            ]);
        }

        foreach (WorkOrder::with(['crew', 'report'])->whereNotNull('crew_id')->whereNotNull('assigned_at')->where('assigned_at', '>=', $weekAgo)->latest('assigned_at')->take(5)->get() as $wo) {
            $crewName = $wo->crew ? $wo->crew->name : 'Crew';
            $location = $wo->location_address ?: ($wo->report ? $wo->report->location_address : 'site');
            $activities->push([
                'type' => 'crew_dispatched',
                'title' => 'Operations sent crew',
                'description' => $crewName.' assigned to '.Str::limit($location, 40),
                'time' => $wo->assigned_at,
                'link' => route('admin.work-orders.index'),
            ]);
        }

        foreach (WorkOrder::with('report')->where('created_at', '>=', $weekAgo)->latest('created_at')->take(8)->get() as $wo) {
            $location = $wo->location_address ?: ($wo->report ? $wo->report->location_address : 'Work order');
            $activities->push([
                'type' => 'work_order_created',
                'title' => 'Work order created',
                'description' => $wo->work_order_number.' – '.Str::limit($location, 35),
                'time' => $wo->created_at,
                'link' => route('admin.work-orders.show', $wo),
            ]);
        }

        $recentActivities = $activities->sortByDesc('time')->take(12)->values()->all();

        return view('r_admin.dashboard', compact(
            'totalReports',
            'reportsToday',
            'resolvedReports',
            'workInProgress',
            'cancelledWorkOrders',
            'months',
            'reportsData',
            'maintenanceData',
            'resolvedData',
            'resolvedPercentage',
            'inProgressPercentage',
            'pendingPercentage',
            'civilianUsers',
            'adminUsers',
            'operationsUsers',
            'crewLeaders',
            'recentActivities'
        ));
    }

    /**
     * All customer-submitted complaints ({@see Report}); card layout aligned with customer history UI.
     */
    public function customerReports(Request $request): View
    {
        $query = Report::query()->with('user')->latest('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference_code', 'like', '%'.$s.'%')
                    ->orWhere('address', 'like', '%'.$s.'%')
                    ->orWhere('reporter_name', 'like', '%'.$s.'%')
                    ->orWhere('description', 'like', '%'.$s.'%')
                    ->orWhere('phone', 'like', '%'.$s.'%')
                    ->orWhereHas('user', function ($uq) use ($s) {
                        $uq->where('name', 'like', '%'.$s.'%')
                            ->orWhere('email', 'like', '%'.$s.'%');
                    });
            });
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            $allowed = ['pending', 'in_progress', 'under_review', 'resolved'];
            if (in_array($status, $allowed, true)) {
                $query->where('status', $status);
            }
        }

        $reports = $query->paginate(15)->withQueryString();

        $unscannedWithPhotoCount = Report::query()
            ->whereNotNull('photo_path')
            ->whereNull('drainage_ai_verdict')
            ->count();

        return view('r_admin.customer-reports.index', compact('reports', 'unscannedWithPhotoCount'));
    }

    /** JSON: IDs of customer reports that have a photo but no AI drainage verdict yet. */
    public function customerReportsUnscannedIds(): JsonResponse
    {
        $ids = Report::query()
            ->whereNotNull('photo_path')
            ->whereNull('drainage_ai_verdict')
            ->orderBy('id')
            ->pluck('id');

        return response()->json(['ids' => $ids]);
    }

    /** Run Google Vision + drainage classifier for one customer report; persists {@see Report::drainage_ai_verdict}. */
    public function customerReportScanDrainage(Report $report, CustomerReportDrainageScan $scanner): JsonResponse
    {
        if (! $report->photo_path) {
            return response()->json(['ok' => false, 'message' => 'No photo on this report.'], 422);
        }
        if ($report->drainage_ai_verdict !== null) {
            return response()->json(['ok' => false, 'message' => 'Already scanned.'], 422);
        }

        try {
            $verdict = $scanner->scanAndPersist($report);
        } catch (\Throwable $e) {
            Log::error('Customer report drainage scan failed', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['ok' => false, 'message' => 'Scan failed.'], 500);
        }

        return response()->json(['ok' => true, 'verdict' => $verdict]);
    }

    public function customerReportShow(Report $report): View
    {
        $report->load(['user', 'operationsReport']);

        return view('r_admin.customer-reports.show', compact('report'));
    }

    public function customerReportSendToOperations(Request $request, Report $report): RedirectResponse
    {
        $already = $report->operationsReport()->exists();
        CustomerReportOperationsSync::syncFromCustomerReport($report);

        $message = $already
            ? 'Report was already linked to operations.'
            : 'Report sent to operations.';

        return redirect()
            ->back()
            ->with('success', $message);
    }

    public function customerReportDestroy(Report $report): RedirectResponse
    {
        if ($report->photo_path) {
            Storage::disk('public')->delete($report->photo_path);
        }
        $report->delete();

        return redirect()
            ->route('admin.customer-reports.index')
            ->with('success', 'Customer report deleted.');
    }

    /** GIS Map: interactive map view of incidents (reports) and work orders. */
    public function gisMap(): View
    {
        $reports = OperationsReport::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('status', ['new', 'in_progress'])
            ->get();

        $workOrders = WorkOrder::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->with('crew')
            ->get();

        $mapReports = $reports->map(fn ($r) => [
            'lat' => (float) $r->latitude,
            'lng' => (float) $r->longitude,
            'number' => $r->report_number,
            'address' => $r->location_address,
        ])->values()->all();

        $mapWorkOrders = $workOrders->map(fn ($w) => [
            'lat' => (float) $w->latitude,
            'lng' => (float) $w->longitude,
            'number' => $w->work_order_number,
            'address' => $w->location_address,
            'crew' => $w->crew?->name,
        ])->values()->all();

        return view('r_admin.gis-map', compact('reports', 'workOrders', 'mapReports', 'mapWorkOrders'));
    }

    /**
     * List admin and super_admin accounts, and operator accounts (Spatie role and/or users.role column).
     */
    public function staffDirectory(): View
    {
        $adminIds = User::role([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->pluck('id');
        $adminIdsColumn = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->pluck('id');
        $adminUsers = User::query()
            ->whereIn('id', $adminIds->merge($adminIdsColumn)->unique())
            ->orderBy('name')
            ->get();

        $operationIds = User::role(User::ROLE_OPERATOR)->pluck('id');
        $operationIdsColumn = User::where('role', User::ROLE_OPERATOR)->pluck('id');
        $operationUsers = User::query()
            ->whereIn('id', $operationIds->merge($operationIdsColumn)->unique())
            ->orderBy('name')
            ->with('crew')
            ->get();

        return view('r_admin.staff.index', compact('adminUsers', 'operationUsers'));
    }

    public function createStaffUser(Request $request): View
    {
        $role = $request->query('role', User::ROLE_ADMIN);
        if ($role === 'operation') {
            $role = User::ROLE_OPERATOR;
        }
        if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_OPERATOR], true)) {
            $role = User::ROLE_ADMIN;
        }

        $crews = Crew::orderBy('name')->get();

        return view('r_admin.staff.create-user', [
            'defaultRole' => $role,
            'crews' => $crews,
        ]);
    }

    public function storeStaffUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_OPERATOR])],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'username')],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if (! is_string($value)) {
                        return;
                    }
                    $role = $request->input('role');
                    if ($role === User::ROLE_ADMIN && ! AccountEmail::endsWith($value, AccountEmail::ADMIN_SUFFIX)) {
                        $fail('Email must use '.AccountEmail::ADMIN_SUFFIX.' for Admin accounts.');
                    }
                    if ($role === User::ROLE_OPERATOR && ! AccountEmail::endsWith($value, AccountEmail::OPERATION_SUFFIX)) {
                        $fail('Email must use '.AccountEmail::OPERATION_SUFFIX.' for Operator accounts.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'crew_id' => ['nullable', 'integer', 'exists:crews,id'],
        ]);

        $role = $validated['role'];
        $crewId = $role === User::ROLE_OPERATOR ? ($validated['crew_id'] ?? null) : null;

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'email_verified_at' => now(),
            'crew_id' => $crewId,
        ]);
        $user->assignRole($role);

        $msg = $role === User::ROLE_ADMIN
            ? 'Admin user created. They can sign in with their email and password.'
            : 'Operator user created. They can sign in with their email and password.';
        if ($role === User::ROLE_OPERATOR && $crewId) {
            $msg .= ' They are set as crew leader and can use the Workers page to take attendance.';
        }

        return redirect()->route('admin.staff.index')->with('success', $msg);
    }

    /** Create new worker (field worker for crews) */
    public function createWorker(): View
    {
        return view('r_admin.workers.create');
    }

    public function storeWorker(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'role' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'in:available,on_site,off_duty'],
            'skills' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        Worker::create(array_merge($validated, [
            'status' => $validated['status'] ?? 'available',
            'crew_id' => null,
        ]));

        return redirect()->route('admin.dashboard')->with('success', 'Worker created. You can assign them to a crew in Operations.');
    }
}
