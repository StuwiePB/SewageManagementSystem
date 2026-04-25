@extends('r_operators.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Create Work Order</h1>
        <p>Create a new work order</p>
    </div>
    <a href="{{ route('operations.work-orders.index') }}" class="btn btn-secondary">Back to Work Orders</a>
</div>

@if(!empty($prefillReport))
    <div class="card card-mb" style="max-width:700px;">
        <h3 class="section-head" style="margin:0 0 8px;">From report {{ $prefillReport->report_number }}</h3>
        <p class="section-desc" style="margin:0 0 12px;">Details below are pre-filled from this operations report. You can edit before creating the work order.</p>
        @if($prefillReport->customerReport && $prefillReport->customerReport->photo_path)
            <p style="font-size:13px; color:var(--text-secondary); margin:0 0 8px;">Reporter photo (customer submission)</p>
            <a href="{{ \Illuminate\Support\Facades\Storage::url($prefillReport->customerReport->photo_path) }}" target="_blank" rel="noopener" class="link-primary" style="display:inline-block;">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($prefillReport->customerReport->photo_path) }}" alt="Reporter photo" style="max-width:100%; max-height:280px; border-radius:8px; border:1px solid var(--border-subtle);">
            </a>
        @endif
    </div>
@endif

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
                <option value="" data-district="" data-mukim="" data-address="" data-latitude="" data-longitude="">Select a report (optional)</option>
                @foreach($reports as $report)
                    <option value="{{ $report->id }}"
                        data-district="{{ $report->district ?? '' }}"
                        data-mukim="{{ $report->mukim ?? '' }}"
                        data-address="{{ e($report->location_address) }}"
                        data-latitude="{{ $report->latitude !== null ? e($report->latitude) : '' }}"
                        data-longitude="{{ $report->longitude !== null ? e($report->longitude) : '' }}"
                        {{ (string) old('report_id', $workOrderDefaults['report_id'] ?? '') === (string) $report->id ? 'selected' : '' }}>
                        {{ $report->report_number }} - {{ $report->location_address }}
                    </option>
                @endforeach
            </select>
            <p style="margin:4px 0 0; font-size:12px; color:var(--text-secondary);">Pending reports listed; opening from the Reports table can include other statuses if no work order exists yet.</p>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Type *</label>
            <input type="text" name="type" value="{{ old('type', $workOrderDefaults['type'] ?? '') }}" placeholder="e.g., Blockage Removal, Maintenance, Emergency Response" required style="
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
                <option value="low" {{ old('priority', $workOrderDefaults['priority'] ?? 'medium') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority', $workOrderDefaults['priority'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority', $workOrderDefaults['priority'] ?? 'medium') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority', $workOrderDefaults['priority'] ?? 'medium') == 'critical' ? 'selected' : '' }}>Critical</option>
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
                        <option value="{{ $slug }}" {{ old('district', $workOrderDefaults['district'] ?? '') == $slug ? 'selected' : '' }}>{{ $name }}</option>
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
                            <option value="{{ $mSlug }}" data-district="{{ $dSlug }}" {{ old('mukim', $workOrderDefaults['mukim'] ?? '') == $mSlug && old('district', $workOrderDefaults['district'] ?? '') == $dSlug ? 'selected' : '' }}>{{ $mName }} ({{ $districts[$dSlug] ?? $dSlug }})</option>
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
            <input type="text" name="location_address" value="{{ old('location_address', $workOrderDefaults['location_address'] ?? '') }}" required placeholder="e.g. Jalan Gadong, Kampung Sengkurong" style="
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
                <input type="number" step="any" name="latitude" value="{{ old('latitude', $workOrderDefaults['latitude'] ?? '') }}" placeholder="e.g., 4.9031" style="
                    width:100%;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; color:var(--text-primary);">Longitude</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude', $workOrderDefaults['longitude'] ?? '') }}" placeholder="e.g., 114.9398" style="
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
            ">{{ old('description', $workOrderDefaults['description'] ?? '') }}</textarea>
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
            ">{{ old('notes', $workOrderDefaults['notes'] ?? '') }}</textarea>
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
    var latInput = document.querySelector('input[name="latitude"]');
    var lngInput = document.querySelector('input[name="longitude"]');

    if (districtSelect && mukimSelect) {
        districtSelect.addEventListener('change', function() {
            var district = this.value;
            for (var i = 0; i < mukimSelect.options.length; i++) {
                var opt = mukimSelect.options[i];
                opt.style.display = (!district || opt.getAttribute('data-district') === district || opt.value === '') ? '' : 'none';
                opt.disabled = !!(district && opt.getAttribute('data-district') && opt.getAttribute('data-district') !== district);
            }
            var sel = mukimSelect.options[mukimSelect.selectedIndex];
            if (district && sel && sel.value && sel.getAttribute('data-district') !== district) {
                mukimSelect.value = '';
            }
        });
        districtSelect.dispatchEvent(new Event('change'));
    }

    function applyReportOption(opt) {
        if (!opt || !opt.value) return;
        if (districtSelect && opt.dataset.district) {
            districtSelect.value = opt.dataset.district || '';
            districtSelect.dispatchEvent(new Event('change'));
            setTimeout(function() {
                if (mukimSelect) mukimSelect.value = opt.dataset.mukim || '';
            }, 0);
        }
        if (addressInput && opt.dataset.address) addressInput.value = opt.dataset.address;
        if (latInput && opt.dataset.latitude) latInput.value = opt.dataset.latitude;
        if (lngInput && opt.dataset.longitude) lngInput.value = opt.dataset.longitude;
    }

    if (reportSelect && districtSelect && mukimSelect && addressInput) {
        reportSelect.addEventListener('change', function() {
            applyReportOption(this.options[this.selectedIndex]);
        });
        if (reportSelect.value) {
            applyReportOption(reportSelect.options[reportSelect.selectedIndex]);
        }
    }
})();
</script>

@endsection
