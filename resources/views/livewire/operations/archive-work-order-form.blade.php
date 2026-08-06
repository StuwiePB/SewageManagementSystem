<div>
    <header class="topbar">
        <div>
            <h1>Add Work Order</h1>
            <p>Manual entry for historical work order records</p>
        </div>
        <button type="button" class="btn btn-secondary" wire:click="back">
            <i class="fas fa-arrow-left"></i> Back to Old Work Orders
        </button>
    </header>

    <div class="archive-banner">
        <i class="fas fa-archive"></i> Digitizing old paper work orders — not for new live assignments.
    </div>

    <form wire:submit="save">
        <div class="card">
            <h3 class="card-title">Work Order Details</h3>
            <div class="section-grid">
                <div>
                    <label class="form-label">Work Order ID *</label>
                    <input type="text" wire:model="work_order_number" class="form-control" placeholder="e.g. WO-1998-00123 from paper form">
                    <p style="margin:0.25rem 0 0; font-size:0.75rem; color:var(--text-secondary);">Pre-filled for convenience — edit to match the original paper record.</p>
                    @error('work_order_number')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Priority *</label>
                    <select wire:model="priority" class="form-control">
                        @foreach(config('dms_forms.priorities') as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priority')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Assigned Crew/Technician *</label>
                    <input type="text" wire:model="assigned_crew" class="form-control">
                    @error('assigned_crew')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Work Type *</label>
                    <select wire:model="work_type" class="form-control">
                        @foreach(config('dms_forms.work_types') as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('work_type')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Estimated Completion (Date &amp; Time)</label>
                    <input type="text" wire:model="estimated_completion_date" class="form-control dms-datetime-picker" placeholder="Select date & time" autocomplete="off">
                </div>
                <div>
                    <label class="form-label">Status *</label>
                    <select wire:model="status" class="form-control">
                        @foreach(config('dms_forms.archive_work_order_statuses') as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Location &amp; Problem</h3>
            <div class="section-grid">
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Location/Address *</label>
                    <input type="text" wire:model="location_address" class="form-control">
                    @error('location_address')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Mukim *</label>
                    <input type="text" wire:model="mukim" class="form-control" list="wo-mukim-list">
                    <datalist id="wo-mukim-list">
                        @foreach($mukims as $name)
                            <option value="{{ $name }}">
                        @endforeach
                    </datalist>
                    @error('mukim')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Problem Category *</label>
                    <input type="text" wire:model="problem_category" class="form-control" placeholder="e.g. Blockage, Culvert repair">
                    @error('problem_category')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Description *</label>
                    <textarea wire:model="description" class="form-control"></textarea>
                    @error('description')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Site Notes</label>
                    <textarea wire:model="site_notes" class="form-control"></textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Dates &amp; Link</h3>
            <div class="section-grid">
                <div>
                    <label class="form-label">Date &amp; Time Created *</label>
                    <input type="text" wire:model="record_created_at" class="form-control dms-datetime-picker" placeholder="Select date & time" autocomplete="off">
                    @error('record_created_at')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Date &amp; Time Started</label>
                    <input type="text" wire:model="record_started_at" class="form-control dms-datetime-picker" placeholder="Select date & time" autocomplete="off">
                </div>
                <div>
                    <label class="form-label">Date &amp; Time Completed</label>
                    <input type="text" wire:model="record_completed_at" class="form-control dms-datetime-picker" placeholder="Select date & time" autocomplete="off">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Related Report ID (optional)</label>
                    <select wire:model="dms_paper_report_id" class="form-control">
                        <option value="">— None —</option>
                        @foreach($paperReports as $report)
                            <option value="{{ $report->id }}">{{ $report->archive_number }} — {{ $report->dds_file_reference ?? 'No DDS ref' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Photo Attachments</h3>
            <div class="photo-upload-grid">
                <div>
                    <label class="form-label">Before (site photos)</label>
                    <input type="file" wire:model="photos_before" accept="image/*" multiple class="form-control">
                    <div wire:loading wire:target="photos_before" class="field-hint" style="color:var(--text-secondary);">Uploading…</div>
                </div>
                <div>
                    <label class="form-label">After (completion photos)</label>
                    <input type="file" wire:model="photos_after" accept="image/*" multiple class="form-control">
                    <div wire:loading wire:target="photos_after" class="field-hint" style="color:var(--text-secondary);">Uploading…</div>
                </div>
            </div>
            @error('photos_before.*')<p class="error-text">{{ $message }}</p>@enderror
            @error('photos_after.*')<p class="error-text">{{ $message }}</p>@enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save"><i class="fas fa-save"></i> Save Work Order</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
            <button type="button" class="btn btn-secondary" wire:click="back">Cancel</button>
        </div>
    </form>
</div>

@script
<script>
    initDmsDatePickers($el);
</script>
@endscript
