<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class PublicReportController extends Controller
{
    /**
     * Show the public report form (Brunei Darussalam only).
     */
    public function create()
    {
        $districts = config('brunei.districts', []);
        $mukims = config('brunei.mukims', []);

        return view('submitreport', compact('districts', 'mukims'));
    }

    /**
     * Store a new report from the public form. Location must be within Brunei Darussalam.
     */
    public function store(Request $request)
    {
        $bounds = config('brunei.bounds', [
            'lat_min' => 4.0,
            'lat_max' => 5.2,
            'lng_min' => 114.0,
            'lng_max' => 115.5,
        ]);

        $rules = [
            'issue_type' => 'required|in:blockage,overflow,odor,maintenance,other',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string|max:2000',
            'district' => 'required|string|max:255',
            'mukim' => 'nullable|string|max:255',
            'location_address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_contact' => 'nullable|string|max:100',
        ];

        $validated = $request->validate($rules);

        // Validate coordinates are within Brunei bounds if provided
        if ($request->filled('latitude') || $request->filled('longitude')) {
            $lat = (float) ($validated['latitude'] ?? 0);
            $lng = (float) ($validated['longitude'] ?? 0);
            if ($lat < $bounds['lat_min'] || $lat > $bounds['lat_max'] ||
                $lng < $bounds['lng_min'] || $lng > $bounds['lng_max']) {
                return back()
                    ->withInput()
                    ->withErrors(['latitude' => 'Coordinates must be within Brunei Darussalam.']);
            }
        } else {
            $validated['latitude'] = null;
            $validated['longitude'] = null;
        }

        $validated['report_number'] = Report::generateReportNumber();
        $validated['status'] = 'new';

        $report = Report::create($validated);

        return redirect()
            ->route('submitreport')
            ->with('report_success', true)
            ->with('report_number', $report->report_number);
    }
}
