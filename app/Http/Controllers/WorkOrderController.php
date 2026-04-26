<?php

namespace App\Http\Controllers;

use App\Models\OperationsReport;
use App\Models\WorkOrder;
use App\Models\WorkOrderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class WorkOrderController extends Controller
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

        return view('r_operators.work-orders.index', compact('workOrders'));
    }

    public function create(Request $request)
    {
        $reports = OperationsReport::where('status', 'pending')->orderBy('created_at', 'desc')->get();

        $prefillReport = null;
        if ($request->filled('report')) {
            $candidate = OperationsReport::with('customerReport')
                ->whereKey($request->integer('report'))
                ->first();
            if ($candidate && ! $candidate->workOrder) {
                $prefillReport = $candidate;
                if (! $reports->contains('id', $candidate->id)) {
                    $reports = $reports->prepend($candidate)->values();
                }
            }
        }

        $districts = config('brunei.districts', []);
        $mukims = config('brunei.mukims', []);

        $workOrderDefaults = [
            'report_id' => '',
            'type' => '',
            'priority' => 'medium',
            'location_address' => '',
            'district' => '',
            'mukim' => '',
            'latitude' => '',
            'longitude' => '',
            'description' => '',
            'notes' => '',
        ];

        if ($prefillReport) {
            $workOrderDefaults['report_id'] = (string) $prefillReport->id;
            $workOrderDefaults['type'] = ucfirst(str_replace('_', ' ', (string) $prefillReport->issue_type));
            $workOrderDefaults['priority'] = $prefillReport->severity === 'urgent' ? 'high' : 'medium';
            $workOrderDefaults['location_address'] = (string) $prefillReport->location_address;
            $workOrderDefaults['district'] = (string) ($prefillReport->district ?? '');
            $workOrderDefaults['mukim'] = (string) ($prefillReport->mukim ?? '');
            $workOrderDefaults['latitude'] = $prefillReport->latitude !== null ? (string) $prefillReport->latitude : '';
            $workOrderDefaults['longitude'] = $prefillReport->longitude !== null ? (string) $prefillReport->longitude : '';
            $workOrderDefaults['description'] = (string) ($prefillReport->description ?? '');
        }

        return view('r_operators.work-orders.create', compact(
            'reports',
            'districts',
            'mukims',
            'prefillReport',
            'workOrderDefaults'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_id' => [
                'nullable',
                'exists:operations_reports,id',
                Rule::unique('work_orders', 'report_id'),
            ],
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

        return redirect()->route('operations.work-orders.index')
            ->with('success', 'Work order created successfully.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,on_site,in_progress,completed',
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

        return view('r_operators.work-orders.show', compact('workOrder'));
    }

    public function pdf(WorkOrder $workOrder)
    {
        $workOrder->load(['report', 'photos']);

        return view('r_operators.work-orders.pdf', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder)
    {
        $workOrder->load('report');
        $districts = config('brunei.districts', []);
        $mukims = config('brunei.mukims', []);

        return view('r_operators.work-orders.edit', compact('workOrder', 'districts', 'mukims'));
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

        return redirect()->route('operations.work-orders.show', $workOrder)
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

    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->load(['photos', 'report.customerReport']);

        $opsReport = $workOrder->report;
        $customerReport = $opsReport?->customerReport;
        $workOrderNumber = $workOrder->work_order_number;
        $photoPaths = $workOrder->photos->pluck('path')->filter()->values()->all();

        try {
            DB::transaction(function () use ($workOrder, $opsReport, $customerReport) {
                $workOrder->delete();

                if ($opsReport) {
                    $opsReport->delete();
                }

                if ($customerReport) {
                    $customerReport->delete();
                }
            });

            foreach ($photoPaths as $path) {
                Storage::disk('public')->delete($path);
            }
        } catch (Throwable $e) {
            Log::warning('Failed to delete work order and linked reports.', [
                'work_order_id' => $workOrder->id,
                'operations_report_id' => $opsReport?->id,
                'customer_report_id' => $customerReport?->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('operations.work-orders.index')
                ->with('error', 'Unable to delete this work order right now. Please try again.');
        }

        return redirect()
            ->route('operations.work-orders.index')
            ->with('success', 'Deleted work order '.$workOrderNumber.' and linked report records.');
    }

    public function submitForApproval(WorkOrder $workOrder)
    {
        return redirect()->back()->with('error', 'This status flow is no longer available.');
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
