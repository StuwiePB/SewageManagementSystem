@extends('r_admin.layout')

@section('title', 'Edit Work Order')

@section('content')

<div class="header">
    <div>
        <h1>Edit Work Order</h1>
        <p>{{ $workOrder->work_order_number }}</p>
    </div>
    <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="btn btn-secondary">Back to Work Order</a>
</div>

<div class="card" style="max-width: 700px;">
    <form action="{{ route('admin.work-orders.update', $workOrder) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label" for="type">Type *</label>
            <input type="text" name="type" id="type" value="{{ old('type', $workOrder->type) }}" placeholder="e.g., Blockage Removal, Maintenance" required class="form-control">
            @error('type')
                <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="priority">Priority *</label>
            <select name="priority" id="priority" required class="form-control">
                <option value="low" {{ old('priority', $workOrder->priority) == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority', $workOrder->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority', $workOrder->priority) == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority', $workOrder->priority) == 'critical' ? 'selected' : '' }}>Critical</option>
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
                        <option value="{{ $slug }}" {{ old('district', $workOrder->district) == $slug ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="mukim">Mukim</label>
                <select name="mukim" id="mukim" class="form-control">
                    <option value="">Select mukim...</option>
                    @foreach($mukims ?? [] as $dSlug => $mukimList)
                        @foreach($mukimList as $mSlug => $mName)
                            <option value="{{ $mSlug }}" data-district="{{ $dSlug }}" {{ old('mukim', $workOrder->mukim) == $mSlug && old('district', $workOrder->district) == $dSlug ? 'selected' : '' }}>{{ $mName }} ({{ $districts[$dSlug] ?? $dSlug }})</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="location_address">Location / Street address *</label>
            <input type="text" name="location_address" id="location_address" value="{{ old('location_address', $workOrder->location_address) }}" required placeholder="e.g. Jalan Gadong" class="form-control">
            @error('location_address')
                <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="latitude">Latitude</label>
                <input type="number" step="any" name="latitude" id="latitude" value="{{ old('latitude', $workOrder->latitude) }}" placeholder="e.g., 4.9031" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="longitude">Longitude</label>
                <input type="number" step="any" name="longitude" id="longitude" value="{{ old('longitude', $workOrder->longitude) }}" placeholder="e.g., 114.9398" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description / Situation details</label>
            <textarea name="description" id="description" rows="4" placeholder="Explain details of the situation at site..." class="form-control" style="resize: vertical;">{{ old('description', $workOrder->description) }}</textarea>
            @error('description')
                <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Notes</label>
            <textarea name="notes" id="notes" rows="2" class="form-control" style="resize: vertical;">{{ old('notes', $workOrder->notes) }}</textarea>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn-submit">Save changes</button>
            <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="btn btn-secondary">Cancel</a>
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
