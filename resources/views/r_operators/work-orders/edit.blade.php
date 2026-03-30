@extends('r_operators.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Edit Work Order</h1>
        <p>{{ $workOrder->work_order_number }}</p>
    </div>
    <a href="{{ route('operations.work-orders.show', $workOrder) }}" class="btn btn-secondary">Back to Work Order</a>
</div>

<div class="card" style="max-width:700px;">
    <form action="{{ route('operations.work-orders.update', $workOrder) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Type *</label>
            <input type="text" name="type" value="{{ old('type', $workOrder->type) }}" placeholder="e.g., Blockage Removal, Maintenance" required style="
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
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Priority *</label>
            <select name="priority" required style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="low" {{ old('priority', $workOrder->priority) == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority', $workOrder->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority', $workOrder->priority) == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority', $workOrder->priority) == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @error('priority')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:20px;">
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">District (Brunei)</label>
                <select name="district" id="district" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
                    <option value="">Select district...</option>
                    @foreach($districts ?? [] as $slug => $name)
                        <option value="{{ $slug }}" {{ old('district', $workOrder->district) == $slug ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Mukim</label>
                <select name="mukim" id="mukim" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
                    <option value="">Select mukim...</option>
                    @foreach($mukims ?? [] as $dSlug => $mukimList)
                        @foreach($mukimList as $mSlug => $mName)
                            <option value="{{ $mSlug }}" data-district="{{ $dSlug }}" {{ old('mukim', $workOrder->mukim) == $mSlug && old('district', $workOrder->district) == $dSlug ? 'selected' : '' }}>{{ $mName }} ({{ $districts[$dSlug] ?? $dSlug }})</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Location / Street address *</label>
            <input type="text" name="location_address" value="{{ old('location_address', $workOrder->location_address) }}" required placeholder="e.g. Jalan Gadong" style="
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
                <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Latitude</label>
                <input type="number" step="any" name="latitude" value="{{ old('latitude', $workOrder->latitude) }}" placeholder="e.g., 4.9031" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Longitude</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude', $workOrder->longitude) }}" placeholder="e.g., 114.9398" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Description / Situation details</label>
            <textarea name="description" rows="4" placeholder="Explain details of the situation at site..." style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
                resize:vertical;
            ">{{ old('description', $workOrder->description) }}</textarea>
            @error('description')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Notes</label>
            <textarea name="notes" rows="2" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
                resize:vertical;
            ">{{ old('notes', $workOrder->notes) }}</textarea>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('operations.work-orders.show', $workOrder) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
(function() {
    var districtSelect = document.getElementById('district');
    var mukimSelect = document.getElementById('mukim');
    if (districtSelect && mukimSelect) {
        districtSelect.addEventListener('change', function() {
            var district = this.value;
            for (var i = 0; i < mukimSelect.options.length; i++) {
                var opt = mukimSelect.options[i];
                opt.style.display = (!district || opt.getAttribute('data-district') === district || opt.value === '') ? '' : 'none';
                opt.disabled = district && opt.getAttribute('data-district') && opt.getAttribute('data-district') !== district;
            }
            if (!district) mukimSelect.value = '';
        });
        districtSelect.dispatchEvent(new Event('change'));
    }
})();
</script>

@endsection
