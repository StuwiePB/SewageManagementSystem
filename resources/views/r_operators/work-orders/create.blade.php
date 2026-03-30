@extends('r_operators.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Create Work Order</h1>
        <p>Create a new work order</p>
    </div>
    <a href="{{ route('operations.work-orders.index') }}" class="btn btn-secondary">Back to Work Orders</a>
</div>

<div class="card" style="max-width:700px;">
    <form action="{{ route('operations.work-orders.store') }}" method="POST">
        @csrf

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Link to Report (Optional)</label>
            <select name="report_id" style="
                width:100%;
                padding:10px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="" data-district="" data-mukim="" data-address="">Select a report (optional)</option>
                @foreach($reports as $report)
                    <option value="{{ $report->id }}" data-district="{{ $report->district ?? '' }}" data-mukim="{{ $report->mukim ?? '' }}" data-address="{{ e($report->location_address) }}" {{ old('report_id') == $report->id ? 'selected' : '' }}>
                        {{ $report->report_number }} - {{ $report->location_address }}
                    </option>
                @endforeach
            </select>
            <p style="margin:4px 0 0; font-size:12px; color:var(--text-secondary);">Only shows reports with "new" status</p>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Type *</label>
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
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Priority *</label>
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
                        <option value="{{ $slug }}" {{ old('district') == $slug ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('district')
                    <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Mukim (whereabouts)</label>
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
                            <option value="{{ $mSlug }}" data-district="{{ $dSlug }}" {{ old('mukim') == $mSlug && old('district') == $dSlug ? 'selected' : '' }}>{{ $mName }} ({{ $districts[$dSlug] ?? $dSlug }})</option>
                        @endforeach
                    @endforeach
                </select>
                @error('mukim')
                    <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Location / Street address *</label>
            <input type="text" name="location_address" value="{{ old('location_address') }}" required placeholder="e.g. Jalan Gadong, Kampung Sengkurong" style="
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
                <input type="number" step="any" name="latitude" value="{{ old('latitude') }}" placeholder="e.g., 4.9031" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Longitude</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude') }}" placeholder="e.g., 114.9398" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Description</label>
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
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Notes</label>
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
            <button type="submit" class="btn btn-primary">Create Work Order</button>
            <a href="{{ route('operations.work-orders.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
(function() {
    var districtSelect = document.getElementById('district');
    var mukimSelect = document.getElementById('mukim');
    var reportSelect = document.querySelector('select[name="report_id"]');
    var addressInput = document.querySelector('input[name="location_address"]');

    if (districtSelect && mukimSelect) {
        districtSelect.addEventListener('change', function() {
            var district = this.value;
            for (var i = 0; i < mukimSelect.options.length; i++) {
                var opt = mukimSelect.options[i];
                opt.style.display = (!district || opt.getAttribute('data-district') === district || opt.value === '') ? '' : 'none';
                opt.disabled = district && opt.getAttribute('data-district') && opt.getAttribute('data-district') !== district;
            }
            mukimSelect.value = '';
        });
        districtSelect.dispatchEvent(new Event('change'));
    }

    if (reportSelect && districtSelect && mukimSelect && addressInput) {
        reportSelect.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            if (opt.value && opt.dataset.district) {
                districtSelect.value = opt.dataset.district || '';
                districtSelect.dispatchEvent(new Event('change'));
                setTimeout(function() {
                    mukimSelect.value = opt.dataset.mukim || '';
                }, 0);
                if (opt.dataset.address) addressInput.value = opt.dataset.address;
            }
        });
    }
})();
</script>

@endsection
