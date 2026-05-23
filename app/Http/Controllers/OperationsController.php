<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersDmsArchiveByDateTime;
use App\Models\DmsPaperReport;
use App\Models\OperationsReport;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperationsController extends Controller
{
    use FiltersDmsArchiveByDateTime;
    public function dashboard()
    {
        $activeIncidents = OperationsReport::whereIn('status', ['pending', 'in_progress'])->count();
        $previousActiveIncidents = OperationsReport::whereIn('status', ['pending', 'in_progress'])
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

        $reportsSentTodayQuery = OperationsReport::query()
            ->whereDate('created_at', today());

        if (Schema::hasColumn('operations_reports', 'customer_report_id')) {
            $reportsSentTodayQuery->whereNotNull('customer_report_id');
        } else {
            // Legacy schema fallback: count today's reports when linkage column is unavailable.
            $reportsSentTodayQuery->whereNotNull('report_number');
        }

        $reportsSentToday = $reportsSentTodayQuery->count();

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

    public function oldReports(Request $request)
    {
        $query = DmsPaperReport::query()->with('archiveWorkOrders');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('archive_number', 'like', "%{$search}%")
                    ->orWhere('dds_file_reference', 'like', "%{$search}%")
                    ->orWhere('service_request_reference', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        $dateColumn = match ($request->input('date_on', 'incident')) {
            'investigated' => 'investigated_at',
            'digitized' => 'created_at',
            default => 'incident_at',
        };

        $this->applyDateTimeRangeFilter($query, $request, $dateColumn);

        $paperReports = $query->orderByDesc($dateColumn)->paginate(20)->withQueryString();

        return view('r_operators.old-reports.index', compact('paperReports'));
    }

    public function map()
    {
        $reports = OperationsReport::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('status', ['pending', 'in_progress'])
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

        $weather = app(\App\Services\BruneiWeatherService::class)->buildMapWidgetPayload();
        $bruneiGisLayers = app(\App\Services\BruneiDrainageRiskService::class)->mapLayerPayload($weather);

        return view('r_operators.map', compact('reports', 'workOrders', 'mapReports', 'mapWorkOrders', 'bruneiGisLayers', 'weather'));
    }
}
