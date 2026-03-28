<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationsReport;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public const PERIOD_LAST_7 = 'last_7_days';
    public const PERIOD_THIS_MONTH = 'this_month';
    public const PERIOD_THIS_QUARTER = 'this_quarter';
    public const PERIOD_CUSTOM = 'custom';

    public const TYPE_REPORTS = 'reports';
    public const TYPE_WORK_ORDERS = 'work_orders';
    public const TYPE_GEOGRAPHIC = 'geographic';

    /**
     * Statistics index – pick options
     */
    public function index()
    {
        return view('r_admin.statistics.index');
    }

    /**
     * Build date range from request
     */
    protected function getDateRange(Request $request): array
    {
        $period = $request->get('period', self::PERIOD_THIS_MONTH);
        $start = $request->get('start');
        $end = $request->get('end');

        if ($period === self::PERIOD_CUSTOM && $start && $end) {
            return [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
            ];
        }

        if ($period === self::PERIOD_LAST_7) {
            return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
        }

        if ($period === self::PERIOD_THIS_QUARTER) {
            return [now()->startOfQuarter(), now()->endOfDay()];
        }

        // default: this month
        return [now()->startOfMonth(), now()->endOfDay()];
    }

    /**
     * Display selected statistics
     */
    public function show(Request $request)
    {
        $request->validate([
            'period' => 'required|in:' . implode(',', [self::PERIOD_LAST_7, self::PERIOD_THIS_MONTH, self::PERIOD_THIS_QUARTER, self::PERIOD_CUSTOM]),
            'type' => 'required|in:' . implode(',', [self::TYPE_REPORTS, self::TYPE_WORK_ORDERS, self::TYPE_GEOGRAPHIC]),
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        [$start, $end] = $this->getDateRange($request);
        $type = $request->get('type');

        $stats = $this->computeStats($type, $start, $end);

        return view('r_admin.statistics.show', compact('stats', 'type', 'start', 'end'));
    }

    /**
     * Compute stats by type
     */
    protected function computeStats(string $type, CarbonInterface $start, CarbonInterface $end): array
    {
        $base = [
            'period_label' => $start->format('M d, Y') . ' – ' . $end->format('M d, Y'),
            'generated_at' => now()->format('M d, Y g:i A'),
        ];

        if ($type === self::TYPE_REPORTS) {
            return array_merge($base, $this->reportStats($start, $end));
        }

        if ($type === self::TYPE_WORK_ORDERS) {
            return array_merge($base, $this->workOrderStats($start, $end));
        }

        if ($type === self::TYPE_GEOGRAPHIC) {
            return array_merge($base, $this->geographicStats($start, $end));
        }

        return $base;
    }

    protected function reportStats(CarbonInterface $start, CarbonInterface $end): array
    {
        $query = OperationsReport::whereBetween('created_at', [$start, $end]);

        $total = $query->count();

        $byStatus = OperationsReport::whereBetween('created_at', [$start, $end])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byIssueType = OperationsReport::whereBetween('created_at', [$start, $end])
            ->select('issue_type', DB::raw('count(*) as count'))
            ->groupBy('issue_type')
            ->pluck('count', 'issue_type')
            ->toArray();

        $byDay = OperationsReport::whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'title' => 'Reports Statistics',
            'total' => $total,
            'by_status' => $byStatus,
            'by_issue_type' => $byIssueType,
            'by_day' => $byDay,
        ];
    }

    protected function workOrderStats(CarbonInterface $start, CarbonInterface $end): array
    {
        $query = WorkOrder::whereBetween('created_at', [$start, $end]);

        $total = $query->count();

        $completed = WorkOrder::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->count();

        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        $byStatus = WorkOrder::whereBetween('created_at', [$start, $end])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byPriority = WorkOrder::whereBetween('created_at', [$start, $end])
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $byDay = WorkOrder::whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $pendingBacklog = WorkOrder::whereIn('status', ['pending', 'assigned', 'in_progress', 'on_the_way', 'on_site'])
            ->count();

        return [
            'title' => 'Work Orders Statistics',
            'total' => $total,
            'completed' => $completed,
            'completion_rate' => $completionRate,
            'pending_backlog' => $pendingBacklog,
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'by_day' => $byDay,
        ];
    }

    protected function geographicStats(CarbonInterface $start, CarbonInterface $end): array
    {
        $reportsByDistrict = OperationsReport::whereBetween('created_at', [$start, $end])
            ->whereNotNull('district')
            ->select('district', DB::raw('count(*) as count'))
            ->groupBy('district')
            ->orderByDesc('count')
            ->get();

        $workOrdersByDistrict = WorkOrder::whereBetween('created_at', [$start, $end])
            ->whereNotNull('district')
            ->select('district', DB::raw('count(*) as count'))
            ->groupBy('district')
            ->orderByDesc('count')
            ->get();

        $reportsByMukim = OperationsReport::whereBetween('created_at', [$start, $end])
            ->whereNotNull('district')
            ->whereNotNull('mukim')
            ->select('district', 'mukim', DB::raw('count(*) as count'))
            ->groupBy('district', 'mukim')
            ->orderByDesc('count')
            ->take(20)
            ->get();

        $workOrdersByMukim = WorkOrder::whereBetween('created_at', [$start, $end])
            ->whereNotNull('district')
            ->whereNotNull('mukim')
            ->select('district', 'mukim', DB::raw('count(*) as count'))
            ->groupBy('district', 'mukim')
            ->orderByDesc('count')
            ->take(20)
            ->get();

        return [
            'title' => 'Geographic Statistics (Hotspots)',
            'reports_by_district' => $reportsByDistrict,
            'work_orders_by_district' => $workOrdersByDistrict,
            'reports_by_mukim' => $reportsByMukim,
            'work_orders_by_mukim' => $workOrdersByMukim,
        ];
    }

    /**
     * Export stats as CSV (Excel-compatible)
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'period' => 'required|in:' . implode(',', [self::PERIOD_LAST_7, self::PERIOD_THIS_MONTH, self::PERIOD_THIS_QUARTER, self::PERIOD_CUSTOM]),
            'type' => 'required|in:' . implode(',', [self::TYPE_REPORTS, self::TYPE_WORK_ORDERS, self::TYPE_GEOGRAPHIC]),
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        [$start, $end] = $this->getDateRange($request);
        $type = $request->get('type');
        $stats = $this->computeStats($type, $start, $end);

        $filename = 'sewage-ops-statistics-' . $type . '-' . $start->format('Y-m-d') . '-to-' . $end->format('Y-m-d') . '.csv';

        return response()->streamDownload(
            function () use ($stats, $type) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['SewageOps Statistics', $stats['title'] ?? $type]);
                fputcsv($out, ['Period', $stats['period_label'] ?? '']);
                fputcsv($out, ['Generated', $stats['generated_at'] ?? '']);
                fputcsv($out, []);

                if ($type === self::TYPE_REPORTS) {
                    fputcsv($out, ['Metric', 'Count']);
                    fputcsv($out, ['Total Reports', $stats['total'] ?? 0]);
                    foreach ($stats['by_status'] ?? [] as $status => $count) {
                        fputcsv($out, ['Status: ' . $status, $count]);
                    }
                    foreach ($stats['by_issue_type'] ?? [] as $issue => $count) {
                        fputcsv($out, ['Issue Type: ' . $issue, $count]);
                    }
                    fputcsv($out, []);
                    fputcsv($out, ['Date', 'Count']);
                    foreach ($stats['by_day'] ?? [] as $row) {
                        fputcsv($out, [$row->date ?? $row['date'], $row->count ?? $row['count']]);
                    }
                } elseif ($type === self::TYPE_WORK_ORDERS) {
                    fputcsv($out, ['Metric', 'Count']);
                    fputcsv($out, ['Total Work Orders', $stats['total'] ?? 0]);
                    fputcsv($out, ['Completed', $stats['completed'] ?? 0]);
                    fputcsv($out, ['Completion Rate %', $stats['completion_rate'] ?? 0]);
                    fputcsv($out, ['Pending Backlog', $stats['pending_backlog'] ?? 0]);
                    foreach ($stats['by_status'] ?? [] as $status => $count) {
                        fputcsv($out, ['Status: ' . $status, $count]);
                    }
                    foreach ($stats['by_priority'] ?? [] as $priority => $count) {
                        fputcsv($out, ['Priority: ' . $priority, $count]);
                    }
                    fputcsv($out, []);
                    fputcsv($out, ['Date', 'Count']);
                    foreach ($stats['by_day'] ?? [] as $row) {
                        fputcsv($out, [$row->date ?? $row['date'], $row->count ?? $row['count']]);
                    }
                } else {
                    fputcsv($out, ['Reports by District']);
                    fputcsv($out, ['District', 'Count']);
                    foreach ($stats['reports_by_district'] ?? [] as $row) {
                        $districtName = config("brunei.districts.{$row->district}") ?? $row->district;
                        fputcsv($out, [$districtName, $row->count]);
                    }
                    fputcsv($out, []);
                    fputcsv($out, ['Work Orders by District']);
                    fputcsv($out, ['District', 'Count']);
                    foreach ($stats['work_orders_by_district'] ?? [] as $row) {
                        $districtName = config("brunei.districts.{$row->district}") ?? $row->district;
                        fputcsv($out, [$districtName, $row->count]);
                    }
                }

                fclose($out);
            },
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Export stats as PDF (print-friendly HTML, user can Print → Save as PDF)
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'period' => 'required|in:' . implode(',', [self::PERIOD_LAST_7, self::PERIOD_THIS_MONTH, self::PERIOD_THIS_QUARTER, self::PERIOD_CUSTOM]),
            'type' => 'required|in:' . implode(',', [self::TYPE_REPORTS, self::TYPE_WORK_ORDERS, self::TYPE_GEOGRAPHIC]),
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        [$start, $end] = $this->getDateRange($request);
        $type = $request->get('type');
        $stats = $this->computeStats($type, $start, $end);

        return view('r_admin.statistics.pdf', compact('stats', 'type', 'start', 'end'));
    }
}
