<?php

namespace App\Http\Controllers;

use App\Models\OperationsReport;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsController extends Controller
{
    public function dashboard()
    {
        $activeIncidents = OperationsReport::whereIn('status', ['new', 'in_progress'])->count();
        $previousActiveIncidents = OperationsReport::whereIn('status', ['new', 'in_progress'])
            ->whereDate('created_at', '<', now()->subDay())
            ->count();
        $incidentChange = $previousActiveIncidents > 0
            ? round((($activeIncidents - $previousActiveIncidents) / $previousActiveIncidents) * 100, 1).'%'
            : '';

        $recentIncidents = OperationsReport::orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($report) {
                return [
                    'title' => ucfirst($report->issue_type).' - '.$report->location_address,
                    'status' => ucfirst(str_replace('_', ' ', $report->status)),
                    'time' => $report->created_at->diffForHumans(),
                ];
            });

        $workOrders = WorkOrder::whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->with('report')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->work_order_number,
                    'location' => $order->location_address,
                    'type' => $order->type,
                    'priority' => ucfirst($order->priority),
                    'status' => ucfirst(str_replace('_', ' ', $order->status)),
                ];
            });

        $incidentsByDay = OperationsReport::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $last7Days = collect(range(6, 0))->map(fn ($days) => now()->subDays($days)->format('Y-m-d'));
        $chartLabels = $last7Days->map(fn ($date) => date('M d', strtotime($date)))->toArray();
        $chartData = $last7Days->map(function ($date) use ($incidentsByDay) {
            $found = $incidentsByDay->firstWhere('date', $date);

            return $found ? $found->count : 0;
        })->toArray();

        $reportsSentToday = OperationsReport::query()
            ->whereNotNull('customer_report_id')
            ->whereDate('created_at', today())
            ->count();

        return view('r_operators.dashboard', compact(
            'activeIncidents',
            'incidentChange',
            'recentIncidents',
            'workOrders',
            'chartLabels',
            'chartData',
            'reportsSentToday'
        ));
    }

    public function reports(Request $request)
    {
        $query = OperationsReport::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                    ->orWhere('location_address', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $reports = $query->with('workOrder')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('r_operators.reports', compact('reports'));
    }

    public function map()
    {
        $reports = OperationsReport::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('status', ['new', 'in_progress'])
            ->get();

        $workOrders = WorkOrder::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])
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
        ])->values()->all();

        return view('r_operators.map', compact('reports', 'workOrders', 'mapReports', 'mapWorkOrders'));
    }
}
