@extends('r_admin.layout')

@section('title', 'Create Work Order')

@section('content')

<div class="header">
    <div>
        <h1>Create Work Order</h1>
        <p>Create a new work order</p>
    </div>
    <a href="{{ route('admin.work-orders.index') }}" class="btn btn-secondary">Back to Work Orders</a>
</div>

<div class="card" style="max-width: 700px;">
    <form action="{{ route('admin.work-orders.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label class="form-label" for="report_id">Link to Report (Optional)</label>
            <select name="report_id" id="report_id" class="form-control">
                <option value="" data-district="" data-mukim="" data-address="">Select a report (optional)</option>
                @foreach($reports as $report)
                    <option value="{{ $report->id }}" data-district="{{ $report->district ?? '' }}" data-mukim="{{ $report->mukim ?? '' }}" data-address="{{ e($report->location_address) }}" {{ old('report_id') == $report->id ? 'selected' : '' }}>
                        {{ $report->report_number }} - {{ $report->location_address }}
                    </option>
                @endforeach
            </select>
            <p class="info-value-small" style="margin: 0.25rem 0 0;">Only shows reports with "new" status</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="type">Type *</label>
            <input type="text" name="type" id="type" value="{{ old('type') }}" placeholder="e.g., Blockage Removal, Maintenance, Emergency Response" required class="form-control">
            @error('type')
                <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="priority">Priority *</label>
            <select name="priority" id="priority" required class="form-control">
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @error('priority')
                <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="district">District (Brunei)</label>
                <select name="district" id="district" class="form-control">
                    <option value="">Select district...</option>
                    @foreach($districts ?? [] as $slug => $name)
                        <option value="{{ $slug }}" {{ old('district') == $slug ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('district')
                    <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="mukim">Mukim (whereabouts)</label>
                <select name="mukim" id="mukim" class="form-control">
                    <option value="">Select mukim...</option>
                    @foreach($mukims ?? [] as $dSlug => $mukimList)
                        @foreach($mukimList as $mSlug => $mName)
                            <option value="{{ $mSlug }}" data-district="{{ $dSlug }}" {{ old('mukim') == $mSlug && old('district') == $dSlug ? 'selected' : '' }}>{{ $mName }} ({{ $districts[$dSlug] ?? $dSlug }})</option>
                        @endforeach
                    @endforeach
                </select>
                @error('mukim')
                    <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="location_address">Location / Street address *</label>
            <input type="text" name="location_address" id="location_address" value="{{ old('location_address') }}" required placeholder="e.g. Jalan Gadong, Kampung Sengkurong" class="form-control">
            @error('location_address')
                <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="latitude">Latitude</label>
                <input type="number" step="any" name="latitude" id="latitude" value="{{ old('latitude') }}" placeholder="e.g., 4.9031" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="longitude">Longitude</label>
                <input type="number" step="any" name="longitude" id="longitude" value="{{ old('longitude') }}" placeholder="e.g., 114.9398" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea name="description" id="description" rows="3" class="form-control" style="resize: vertical;">{{ old('description') }}</textarea>
            @error('description')
                <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Notes</label>
            <textarea name="notes" id="notes" rows="2" class="form-control" style="resize: vertical;">{{ old('notes') }}</textarea>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn-submit">Create Work Order</button>
            <a href="{{ route('admin.work-orders.index') }}" class="btn btn-secondary">Cancel</a>
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
