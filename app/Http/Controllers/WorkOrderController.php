<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Report;
use App\Models\Crew;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of work orders
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with(['crew', 'report']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('crew_id')) {
            $query->where('crew_id', $request->crew_id);
        }

        $workOrders = $query->orderBy('created_at', 'desc')->paginate(20);
        $crews = Crew::orderBy('name')->get();

        return view('operations.work-orders.index', compact('workOrders', 'crews'));
    }

    /**
     * Show the form for creating a new work order
     */
    public function create()
    {
        $reports = Report::where('status', 'new')->orderBy('created_at', 'desc')->get();
        $crews = Crew::where('status', 'available')->orderBy('name')->get();
        $districts = config('brunei.districts', []);
        $mukims = config('brunei.mukims', []);

        return view('operations.work-orders.create', compact('reports', 'crews', 'districts', 'mukims'));
    }

    /**
     * Store a newly created work order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_id' => 'nullable|exists:reports,id',
            'crew_id' => 'nullable|exists:crews,id',
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
        $validated['status'] = $request->crew_id ? 'assigned' : 'pending';
        
        if ($request->crew_id) {
            $validated['assigned_at'] = now();
        }

        // Copy district/mukim from report when linking
        if ($request->report_id) {
            $report = \App\Models\Report::find($request->report_id);
            if ($report) {
                $validated['district'] = $validated['district'] ?? $report->district;
                $validated['mukim'] = $validated['mukim'] ?? $report->mukim;
            }
        }

        // Validate coordinates are within Brunei Darussalam if provided
        $bounds = config('brunei.bounds', ['lat_min' => 4.0, 'lat_max' => 5.2, 'lng_min' => 114.0, 'lng_max' => 115.5]);
        if ($request->filled('latitude') || $request->filled('longitude')) {
            $lat = (float) ($validated['latitude'] ?? 0);
            $lng = (float) ($validated['longitude'] ?? 0);
            if ($lat < $bounds['lat_min'] || $lat > $bounds['lat_max'] || $lng < $bounds['lng_min'] || $lng > $bounds['lng_max']) {
                return back()->withInput()->withErrors(['latitude' => 'Coordinates must be within Brunei Darussalam.']);
            }
        }

        $workOrder = WorkOrder::create($validated);

        // Update report status if linked
        if ($workOrder->report_id) {
            $workOrder->report->update(['status' => 'in_progress']);
        }

        // Update crew status if assigned
        if ($workOrder->crew_id) {
            $workOrder->crew->update(['status' => 'on_site']);
        }

        return redirect()->route('operations.work-orders.index')
            ->with('success', 'Work order created successfully.');
    }

    /**
     * Update work order status
     */
    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
            'crew_id' => 'nullable|exists:crews,id',
        ]);

        $oldStatus = $workOrder->status;
        $workOrder->update($validated);

        // Handle status transitions
        if ($validated['status'] === 'assigned' && $oldStatus !== 'assigned') {
            $workOrder->update(['assigned_at' => now()]);
            if ($workOrder->crew_id) {
                $workOrder->crew->update(['status' => 'on_site']);
            }
        }

        if ($validated['status'] === 'in_progress' && $oldStatus !== 'in_progress') {
            $workOrder->update(['started_at' => now()]);
        }

        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $workOrder->update(['completed_at' => now()]);
            if ($workOrder->crew_id) {
                $workOrder->crew->update(['status' => 'available']);
            }
            if ($workOrder->report_id) {
                $workOrder->report->update(['status' => 'resolved']);
            }
        }

        if ($validated['status'] === 'cancelled') {
            if ($workOrder->crew_id) {
                $workOrder->crew->update(['status' => 'available']);
            }
        }

        return redirect()->back()->with('success', 'Work order status updated.');
    }

    /**
     * Show the specified work order
     */
    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['crew', 'report']);
        return view('operations.work-orders.show', compact('workOrder'));
    }
}
