<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display admin dashboard with statistics.
     */
    public function dashboard()
    {
        // Total reports from customers
        $totalReports = Incident::count();

        // Reports submitted today
        $reportsToday = Incident::whereDate('created_at', Carbon::today())->count();

        // Resolved incidents (SEWAGE_CONFIRMED or NOT_SEWAGE_CONFIRMED)
        $resolvedReports = Incident::whereIn('review_status', ['SEWAGE_CONFIRMED', 'NOT_SEWAGE_CONFIRMED'])->count();

        // Work in progress (PENDING_AI, NEEDS_REVIEW, or any other non-resolved status)
        $workInProgress = Incident::whereNotIn('review_status', ['SEWAGE_CONFIRMED', 'NOT_SEWAGE_CONFIRMED'])->count();

        // Get incidents for charts
        $incidents = Incident::orderBy('created_at', 'desc')->limit(100)->get();

        // Monthly data for Activity Trend chart (last 6 months)
        $months = [];
        $reportsData = [];
        $maintenanceData = [];
        $resolvedData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M');
            $months[] = $monthName;

            // Reports in that month
            $monthlyReports = Incident::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $reportsData[] = $monthlyReports;

            // Simulated maintenance data (could be from a maintenance_tasks table in future)
            $maintenanceData[] = round($monthlyReports * 0.7);

            // Resolved in that month
            $monthlyResolved = Incident::whereIn('review_status', ['SEWAGE_CONFIRMED', 'NOT_SEWAGE_CONFIRMED'])
                ->whereYear('updated_at', $date->year)
                ->whereMonth('updated_at', $date->month)
                ->count();
            $resolvedData[] = $monthlyResolved;
        }

        // Status distribution percentages
        $totalForPercentage = max($totalReports, 1); // Avoid division by zero
        $resolvedPercentage = round(($resolvedReports / $totalForPercentage) * 100);
        $inProgressPercentage = round(($workInProgress / $totalForPercentage) * 100);
        $pendingPercentage = max(0, 100 - $resolvedPercentage - $inProgressPercentage);

        return view('admin.dashboard', compact(
            'totalReports',
            'reportsToday',
            'resolvedReports',
            'workInProgress',
            'months',
            'reportsData',
            'maintenanceData',
            'resolvedData',
            'resolvedPercentage',
            'inProgressPercentage',
            'pendingPercentage'
        ));
    }
}
