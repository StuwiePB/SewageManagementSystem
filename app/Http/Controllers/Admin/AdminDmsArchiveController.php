<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\FiltersDmsArchiveByDateTime;
use App\Http\Controllers\Controller;
use App\Models\DmsArchiveWorkOrder;
use App\Models\DmsPaperReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminDmsArchiveController extends Controller
{
    use FiltersDmsArchiveByDateTime;

    public function oldReportsIndex(Request $request): View
    {
        $query = DmsPaperReport::query()->with('digitizedBy');

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

        return view('r_admin.old-reports.index', compact('paperReports'));
    }

    public function oldReportShow(DmsPaperReport $paperReport): View
    {
        $paperReport->load(['digitizedBy', 'archiveWorkOrders']);

        $scannedImageUrl = null;
        if ($paperReport->scanned_image_path && Storage::disk('local')->exists($paperReport->scanned_image_path)) {
            $scannedImageUrl = route('admin.old-reports.scan', $paperReport);
        }

        return view('r_admin.old-reports.show', [
            'paperReport' => $paperReport,
            'form' => $paperReport->form_data ?? [],
            'scannedImageUrl' => $scannedImageUrl,
        ]);
    }

    public function oldReportScan(DmsPaperReport $paperReport)
    {
        if (! $paperReport->scanned_image_path || ! Storage::disk('local')->exists($paperReport->scanned_image_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($paperReport->scanned_image_path);
    }

    public function oldWorkOrdersIndex(Request $request): View
    {
        $query = DmsArchiveWorkOrder::with(['paperReport', 'digitizedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
                    ->orWhere('location_address', 'like', "%{$search}%")
                    ->orWhere('assigned_crew', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $this->applyWorkOrderDateFilter($query, $request);

        $dateColumn = match ($request->input('date_on', 'created')) {
            'started' => 'record_started_at',
            'completed' => 'record_completed_at',
            default => 'record_created_at',
        };

        $archiveWorkOrders = $query->orderByDesc($dateColumn)->paginate(20)->withQueryString();

        return view('r_admin.old-work-orders.index', compact('archiveWorkOrders'));
    }

    public function oldWorkOrderShow(DmsArchiveWorkOrder $archiveWorkOrder): View
    {
        $archiveWorkOrder->load(['paperReport', 'digitizedBy', 'photos']);

        return view('r_admin.old-work-orders.show', compact('archiveWorkOrder'));
    }
}
