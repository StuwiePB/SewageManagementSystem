<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Crew;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsController extends Controller
{
    /**
     * Display the operations dashboard
     */
    public function dashboard()
    {
        // Summary statistics
        $activeIncidents = Report::whereIn('status', ['new', 'in_progress'])->count();
        $previousActiveIncidents = Report::whereIn('status', ['new', 'in_progress'])
            ->whereDate('created_at', '<', now()->subDay())
            ->count();
        $incidentChange = $previousActiveIncidents > 0 
            ? round((($activeIncidents - $previousActiveIncidents) / $previousActiveIncidents) * 100, 1) . '%'
            : '';

        $crewsAvailable = Crew::where('status', 'available')->count();
        $crewsOnSite = Crew::where('status', 'on_site')->count();
        $crewsStatus = "{$crewsOnSite} on-site";

        // Recent incidents (last 5)
        $recentIncidents = Report::orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($report) {
                return [
                    'title' => ucfirst($report->issue_type) . ' - ' . $report->location_address,
                    'status' => ucfirst(str_replace('_', ' ', $report->status)),
                    'time' => $report->created_at->diffForHumans(),
                ];
            });

        // Pending work orders
        $workOrders = WorkOrder::whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->with(['crew', 'report'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->work_order_number,
                    'location' => $order->location_address,
                    'type' => $order->type,
                    'priority' => ucfirst($order->priority),
                    'crew' => $order->crew ? $order->crew->name : 'Unassigned',
                    'status' => ucfirst(str_replace('_', ' ', $order->status)),
                ];
            });

        // Stats for chart - incidents by day for last 7 days
        $incidentsByDay = Report::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = $incidentsByDay->pluck('date')->map(fn($date) => date('M d', strtotime($date)))->toArray();
        $chartData = $incidentsByDay->pluck('count')->toArray();

        // Fill in missing days with 0
        $last7Days = collect(range(6, 0))->map(fn($days) => now()->subDays($days)->format('Y-m-d'));
        $chartLabels = $last7Days->map(fn($date) => date('M d', strtotime($date)))->toArray();
        $chartData = $last7Days->map(function($date) use ($incidentsByDay) {
            $found = $incidentsByDay->firstWhere('date', $date);
            return $found ? $found->count : 0;
        })->toArray();

        return view('operations.dashboard', compact(
            'activeIncidents',
            'incidentChange',
            'crewsAvailable',
            'crewsOnSite',
            'crewsStatus',
            'recentIncidents',
            'workOrders',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * Display the reports page with filters
     */
    public function reports(Request $request)
    {
        $query = Report::query();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                  ->orWhere('location_address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $reports = $query->with('workOrder.crew')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('operations.reports', compact('reports'));
    }

    /**
     * Display the map page
     */
    public function map()
    {
        // Get all active reports and work orders with coordinates
        $reports = Report::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('status', ['new', 'in_progress'])
            ->get();

        $workOrders = WorkOrder::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->with('crew')
            ->get();

        return view('operations.map', compact('reports', 'workOrders'));
    }
}
