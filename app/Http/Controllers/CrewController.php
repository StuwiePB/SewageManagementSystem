<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use Illuminate\Http\Request;

class CrewController extends Controller
{
    /**
     * Show form to create new crew member.
     */
    public function create()
    {
        return view('admin.crew-create');
    }

    /**
     * Display crew management page.
     */
    public function index(Request $request)
    {
        $query = Crew::query();

        // Filter by role if provided
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or employee ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $crews = $query->orderBy('name')->get();
        
        // Get counts by role
        $roleCounts = [
            'Work Leader' => Crew::where('role', 'Work Leader')->where('status', 'Active')->count(),
            'Senior Technician' => Crew::where('role', 'Senior Technician')->where('status', 'Active')->count(),
            'Technician' => Crew::where('role', 'Technician')->where('status', 'Active')->count(),
            'Equipment Operator' => Crew::where('role', 'Equipment Operator')->where('status', 'Active')->count(),
            'Safety Officer' => Crew::where('role', 'Safety Officer')->where('status', 'Active')->count(),
            'Maintenance Worker' => Crew::where('role', 'Maintenance Worker')->where('status', 'Active')->count(),
        ];

        return view('admin.crew-management', compact('crews', 'roleCounts'));
    }

    /**
     * Store a new crew member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|unique:crews,employee_id',
            'role' => 'required|in:Work Leader,Senior Technician,Technician,Equipment Operator,Safety Officer,Maintenance Worker',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:Active,On Leave,Inactive',
            'hire_date' => 'nullable|date',
            'specialization' => 'nullable|string',
        ]);

        $crew = Crew::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Crew member added successfully',
            'crew' => $crew
        ]);
    }

    /**
     * Update crew member.
     */
    public function update(Request $request, Crew $crew)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|unique:crews,employee_id,' . $crew->id,
            'role' => 'required|in:Work Leader,Senior Technician,Technician,Equipment Operator,Safety Officer,Maintenance Worker',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:Active,On Leave,Inactive',
            'hire_date' => 'nullable|date',
            'specialization' => 'nullable|string',
        ]);

        $crew->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Crew member updated successfully',
            'crew' => $crew->fresh()
        ]);
    }

    /**
     * Delete crew member.
     */
    public function destroy(Crew $crew)
    {
        $crew->delete();

        return response()->json([
            'success' => true,
            'message' => 'Crew member deleted successfully'
        ]);
    }
}
