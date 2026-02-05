@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Create Work Order</h1>
        <p>Create a new work order</p>
    </div>
    <a href="{{ route('operations.work-orders.index') }}" style="color:#6b7280; text-decoration:none;">← Back to Work Orders</a>
</div>

<div class="card" style="max-width:700px;">
    <form action="{{ route('operations.work-orders.store') }}" method="POST">
        @csrf

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Link to Report (Optional)</label>
            <select name="report_id" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="">Select a report (optional)</option>
                @foreach($reports as $report)
                    <option value="{{ $report->id }}" {{ old('report_id') == $report->id ? 'selected' : '' }}>
                        {{ $report->report_number }} - {{ $report->location_address }}
                    </option>
                @endforeach
            </select>
            <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">Only shows reports with "new" status</p>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Assign Crew (Optional)</label>
            <select name="crew_id" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="">Unassigned</option>
                @foreach($crews as $crew)
                    <option value="{{ $crew->id }}" {{ old('crew_id') == $crew->id ? 'selected' : '' }}>
                        {{ $crew->name }} ({{ ucfirst(str_replace('_', ' ', $crew->status)) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Type *</label>
            <input type="text" name="type" value="{{ old('type') }}" placeholder="e.g., Blockage Removal, Maintenance, Emergency Response" required style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
            @error('type')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Priority *</label>
            <select name="priority" required style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @error('priority')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Location Address *</label>
            <input type="text" name="location_address" value="{{ old('location_address') }}" required style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
            @error('location_address')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:20px;">
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Latitude</label>
                <input type="number" step="any" name="latitude" value="{{ old('latitude') }}" placeholder="e.g., 40.7128" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Longitude</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude') }}" placeholder="e.g., -74.0060" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Description</label>
            <textarea name="description" rows="3" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
                resize:vertical;
            ">{{ old('description') }}</textarea>
            @error('description')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Notes</label>
            <textarea name="notes" rows="2" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
                resize:vertical;
            ">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" style="
                background:#0056A6;
                color:white;
                padding:10px 24px;
                border:none;
                border-radius:8px;
                font-weight:500;
                cursor:pointer;
            ">Create Work Order</button>
            <a href="{{ route('operations.work-orders.index') }}" style="
                background:#f3f4f6;
                color:#374151;
                padding:10px 24px;
                border-radius:8px;
                text-decoration:none;
                font-weight:500;
                display:inline-block;
            ">Cancel</a>
        </div>
    </form>
</div>

@endsection
