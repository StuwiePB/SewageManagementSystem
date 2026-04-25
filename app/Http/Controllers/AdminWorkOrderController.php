<?php

namespace App\Http\Controllers;

use App\Models\OperationsReport;
use App\Models\WorkOrder;
use App\Models\WorkOrderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminWorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::with('report');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $workOrders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('r_admin.work-orders.index', compact('workOrders'));
    }

    public function create()
    {
        $reports = OperationsReport::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        $districts = config('brunei.districts', []);
        $mukims = config('brunei.mukims', []);

        return view('r_admin.work-orders.create', compact('reports', 'districts', 'mukims'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_id' => 'nullable|exists:operations_reports,id',
            'type' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'location_address' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'mukim' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['work_order_number'] = WorkOrder::generateWorkOrderNumber();
        $validated['status'] = 'pending';

        if ($request->report_id) {
            $report = OperationsReport::find($request->report_id);
            if ($report) {
                $validated['district'] = $validated['district'] ?? $report->district;
                $validated['mukim'] = $validated['mukim'] ?? $report->mukim;
            }
        }

        $bounds = config('brunei.bounds', ['lat_min' => 4.0, 'lat_max' => 5.2, 'lng_min' => 114.0, 'lng_max' => 115.5]);
        if ($request->filled('latitude') || $request->filled('longitude')) {
            $lat = (float) ($validated['latitude'] ?? 0);
            $lng = (float) ($validated['longitude'] ?? 0);
            if ($lat < $bounds['lat_min'] || $lat > $bounds['lat_max'] || $lng < $bounds['lng_min'] || $lng > $bounds['lng_max']) {
                return back()->withInput()->withErrors(['latitude' => 'Coordinates must be within Brunei Darussalam.']);
            }
        }

        $workOrder = WorkOrder::create($validated);

        $this->syncLinkedReportStatuses($workOrder, 'in_progress');

        return redirect()->route('admin.work-orders.index')
            ->with('success', 'Work order created successfully.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,on_the_way,on_site,pending_approval,completed,cancelled',
        ]);

        $oldStatus = $workOrder->status;
        $workOrder->update($validated);

        if ($validated['status'] === 'assigned' && $oldStatus !== 'assigned') {
            $workOrder->update(['assigned_at' => now()]);
        }

        if (in_array($validated['status'], ['in_progress', 'on_site']) && ! $workOrder->started_at) {
            $workOrder->update(['started_at' => now()]);
        }

        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $workOrder->update(['completed_at' => now()]);
        }

        $this->syncLinkedReportStatuses($workOrder, $this->mapWorkOrderStatusToReportStatus($validated['status']));

        return redirect()->back()->with('success', 'Work order status updated.');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['report.customerReport', 'photos']);

        return view('r_admin.work-orders.show', compact('workOrder'));
    }

    public function pdf(WorkOrder $workOrder)
    {
        $workOrder->load(['report', 'photos']);

        return view('r_admin.work-orders.pdf', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder)
    {
        $workOrder->load('report');
        $districts = config('brunei.districts', []);
        $mukims = config('brunei.mukims', []);

        return view('r_admin.work-orders.edit', compact('workOrder', 'districts', 'mukims'));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'location_address' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'mukim' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $bounds = config('brunei.bounds', ['lat_min' => 4.0, 'lat_max' => 5.2, 'lng_min' => 114.0, 'lng_max' => 115.5]);
        if ($request->filled('latitude') || $request->filled('longitude')) {
            $lat = (float) ($validated['latitude'] ?? 0);
            $lng = (float) ($validated['longitude'] ?? 0);
            if ($lat < $bounds['lat_min'] || $lat > $bounds['lat_max'] || $lng < $bounds['lng_min'] || $lng > $bounds['lng_max']) {
                return back()->withInput()->withErrors(['latitude' => 'Coordinates must be within Brunei Darussalam.']);
            }
        }

        $workOrder->update($validated);

        return redirect()->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Work order updated.');
    }

    public function storePhoto(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'photos' => 'required|array|min:2|max:3',
            'photos.*' => 'required|image|max:10240',
        ], [
            'photos.min' => 'Please select at least 2 photos for better evidence.',
            'photos.max' => 'Please select at most 3 photos per upload.',
        ]);

        $uploaded = 0;
        foreach ($request->file('photos') as $file) {
            $path = $file->store('work-order-photos/'.$workOrder->id, 'public');
            WorkOrderPhoto::create([
                'work_order_id' => $workOrder->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
            $uploaded++;
        }

        return redirect()->back()->with('success', $uploaded.' photo(s) added.');
    }

    public function destroyPhoto(WorkOrder $workOrder, WorkOrderPhoto $photo)
    {
        if ($photo->work_order_id !== $workOrder->id) {
            abort(404);
        }
        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return redirect()->back()->with('success', 'Photo removed.');
    }

    public function submitForApproval(WorkOrder $workOrder)
    {
        $workOrder->update(['status' => 'pending_approval']);
        $this->syncLinkedReportStatuses($workOrder, 'in_progress');

        return redirect()->back()->with('success', 'Work order submitted for approval.');
    }

    private function mapWorkOrderStatusToReportStatus(string $workOrderStatus): string
    {
        return $workOrderStatus === 'completed'
            ? 'resolved'
            : 'in_progress';
    }

    private function syncLinkedReportStatuses(WorkOrder $workOrder, string $operationsStatus): void
    {
        if (! $workOrder->report_id) {
            return;
        }

        $report = $workOrder->report()->first();
        if (! $report) {
            return;
        }

        $report->update(['status' => $operationsStatus]);

        if (! Schema::hasColumn('operations_reports', 'customer_report_id') || ! $report->customer_report_id) {
            return;
        }

        $customerStatus = $operationsStatus === 'resolved' ? 'resolved' : 'in_progress';
        \App\Models\Report::query()
            ->whereKey($report->customer_report_id)
            ->update(['status' => $customerStatus]);
    }
}
