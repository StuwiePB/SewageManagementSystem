<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CrewController extends Controller
{
    /**
     * Display a listing of crews
     */
    public function index()
    {
        $crews = Crew::orderBy('name')->get();
        $unassignedCount = Worker::whereNull('crew_id')->count();
        return view('operations.crews.index', compact('crews', 'unassignedCount'));
    }

    /**
     * Display unassigned workers page
     */
    public function unassigned()
    {
        $unassignedWorkers = Worker::whereNull('crew_id')
            ->orderBy('name')
            ->get();
        $crews = Crew::orderBy('name')->get();
        
        return view('operations.crews.unassigned', compact('unassignedWorkers', 'crews'));
    }

    /**
     * Show the form for creating a new crew
     */
    public function create()
    {
        return view('operations.crews.create');
    }

    /**
     * Store a newly created crew
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:available,on_site,off_duty',
            'specialization' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Crew::create($validated);

        return redirect()->route('operations.crews.index')
            ->with('success', 'Crew created successfully.');
    }

    /**
     * Display the specified crew
     */
    public function show(Crew $crew)
    {
        $crew->load(['activeWorkOrders.report', 'workers']);
        return view('operations.crews.show', compact('crew'));
    }

    /**
     * Show the form for editing the specified crew
     */
    public function edit(Crew $crew)
    {
        return view('operations.crews.edit', compact('crew'));
    }

    /**
     * Update the specified crew
     */
    public function update(Request $request, Crew $crew)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:available,on_site,off_duty',
            'specialization' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $crew->update($validated);

        return redirect()->route('operations.crews.index')
            ->with('success', 'Crew updated successfully.');
    }

    /**
     * Remove the specified crew
     */
    public function destroy(Crew $crew)
    {
        // Unassign all work orders from this crew
        $crew->workOrders()->update(['crew_id' => null]);
        
        // Unassign all workers from this crew
        $crew->workers()->update(['crew_id' => null]);
        
        // Update crew status to available if they were on-site (for work orders)
        $crew->workOrders()->where('status', 'in_progress')->get()->each(function ($workOrder) {
            if ($workOrder->crew_id === null) {
                // Work order is now unassigned, but keep its status
            }
        });

        $crewName = $crew->name;
        $crew->delete();

        return redirect()->route('operations.crews.index')
            ->with('success', "{$crewName} deleted successfully. Work orders and workers have been unassigned.");
    }

    /**
     * Assign a worker to a crew (ops action)
     */
    public function assignWorker(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'crew_id' => 'required|exists:crews,id',
        ]);

        $worker = Worker::findOrFail($validated['worker_id']);
        $crew = Crew::findOrFail($validated['crew_id']);
        
        $worker->update([
            'crew_id' => $crew->id,
            'status' => $worker->status ?? 'available',
        ]);

        return redirect()->route('operations.crews.unassigned')
            ->with('success', "{$worker->name} assigned to {$crew->name}.");
    }
}
