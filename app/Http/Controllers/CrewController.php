<?php

namespace App\Http\Controllers;

use App\Models\Crew;
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
        return view('operations.crews.index', compact('crews'));
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
        $crew->load('activeWorkOrders.report');
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
        // Check if crew has active work orders
        if ($crew->activeWorkOrders()->count() > 0) {
            return redirect()->route('operations.crews.index')
                ->with('error', 'Cannot delete crew with active work orders.');
        }

        $crew->delete();

        return redirect()->route('operations.crews.index')
            ->with('success', 'Crew deleted successfully.');
    }
}
