@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Edit Crew</h1>
        <p>Update crew information</p>
    </div>
    <a href="{{ route('operations.crews.index') }}" style="color:#6b7280; text-decoration:none;">← Back to Crews</a>
</div>

<div class="card" style="max-width:600px;">
    <form action="{{ route('operations.crews.update', $crew) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Name *</label>
            <input type="text" name="name" value="{{ old('name', $crew->name) }}" required style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
            @error('name')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Contact Phone</label>
            <input type="text" name="contact_phone" value="{{ old('contact_phone', $crew->contact_phone) }}" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
            @error('contact_phone')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Contact Email</label>
            <input type="email" name="contact_email" value="{{ old('contact_email', $crew->contact_email) }}" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
            @error('contact_email')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Status *</label>
            <select name="status" required style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="available" {{ old('status', $crew->status) == 'available' ? 'selected' : '' }}>Available</option>
                <option value="on_site" {{ old('status', $crew->status) == 'on_site' ? 'selected' : '' }}>On Site</option>
                <option value="off_duty" {{ old('status', $crew->status) == 'off_duty' ? 'selected' : '' }}>Off Duty</option>
            </select>
            @error('status')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Specialization</label>
            <input type="text" name="specialization" value="{{ old('specialization', $crew->specialization) }}" placeholder="e.g., Blockage Removal, Maintenance" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
            @error('specialization')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:#374151;">Notes</label>
            <textarea name="notes" rows="3" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
                resize:vertical;
            ">{{ old('notes', $crew->notes) }}</textarea>
            @error('notes')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
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
            ">Update Crew</button>
            <a href="{{ route('operations.crews.index') }}" style="
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
