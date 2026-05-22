<div class="archive-banner">
    <i class="fas fa-archive"></i> Digitizing historical OM/DDS paper records. Verify all fields before saving.
</div>

<div class="card">
    <h3 class="card-title">References</h3>
    <div class="section-grid">
        <div>
            <label class="form-label">DDS File Reference *</label>
            <input type="text" wire:model="dds_file_reference" class="form-control @if($this->isLowConfidence('dds_file_reference')) low-confidence @endif">
            @if($this->isLowConfidence('dds_file_reference'))<p class="field-hint">Low OCR confidence — please verify</p>@endif
            @error('dds_file_reference')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Service Request Reference (Ticket) *</label>
            <input type="text" wire:model="service_request_reference" class="form-control @if($this->isLowConfidence('service_request_reference')) low-confidence @endif">
            @if($this->isLowConfidence('service_request_reference'))<p class="field-hint">Low OCR confidence — please verify</p>@endif
            @error('service_request_reference')<p class="error-text">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Customer &amp; Complaint Details</h3>
    <div class="section-grid">
        <div>
            <label class="form-label">Contact Name *</label>
            <input type="text" wire:model="contact_name" class="form-control @if($this->isLowConfidence('contact_name')) low-confidence @endif">
            @error('contact_name')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Phone (H)</label>
            <input type="tel" wire:model="phone_home" class="form-control @if($this->isLowConfidence('phone_home')) low-confidence @endif">
        </div>
        <div>
            <label class="form-label">Phone (M)</label>
            <input type="tel" wire:model="phone_mobile" class="form-control @if($this->isLowConfidence('phone_mobile')) low-confidence @endif">
        </div>
        <div>
            <label class="form-label">House Location *</label>
            <input type="text" wire:model="house_location" class="form-control @if($this->isLowConfidence('house_location')) low-confidence @endif">
            @error('house_location')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Incident Date &amp; Time</label>
            <input type="text" wire:model="incident_datetime" class="form-control dms-datetime-picker @if($this->isLowConfidence('incident_datetime')) low-confidence @endif" placeholder="Select date & time" autocomplete="off">
            @error('incident_datetime')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Incident Location *</label>
            <input type="text" wire:model="incident_location" class="form-control @if($this->isLowConfidence('incident_location')) low-confidence @endif">
            @error('incident_location')<p class="error-text">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Service Request Assessment</h3>
    <div class="section-grid">
        <div>
            <label class="form-label">Problem Code *</label>
            <input type="text" wire:model="problem_code" class="form-control @if($this->isLowConfidence('problem_code')) low-confidence @endif">
            @error('problem_code')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Mukim *</label>
            <input type="text" wire:model="mukim" class="form-control @if($this->isLowConfidence('mukim')) low-confidence @endif" list="mukim-list">
            <datalist id="mukim-list">
                @foreach(collect(config('brunei.mukims', []))->flatten(1) as $name)
                    <option value="{{ $name }}">
                @endforeach
            </datalist>
            @error('mukim')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Assigned Crew *</label>
            <input type="text" wire:model="assigned_crew" class="form-control @if($this->isLowConfidence('assigned_crew')) low-confidence @endif">
            @error('assigned_crew')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div style="grid-column: 1 / -1;">
            <label class="form-label">Work Details *</label>
            <textarea wire:model="work_details" class="form-control @if($this->isLowConfidence('work_details')) low-confidence @endif"></textarea>
            @error('work_details')<p class="error-text">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Service Group</h3>
    <div class="section-grid">
        <div>
            <label class="form-label">Group Type *</label>
            <select wire:model.live="service_group_type" class="form-control">
                <option value="jkr">JKR</option>
                <option value="other_govt">Other Govt Dept</option>
            </select>
        </div>
        @if($service_group_type === 'jkr')
            <div>
                <label class="form-label">JKR Service *</label>
                <select wire:model="service_group_jkr" class="form-control">
                    <option value="">Select...</option>
                    @foreach(config('dms_forms.service_group_jkr') as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div>
                <label class="form-label">Other Govt Dept *</label>
                <select wire:model="service_group_other" class="form-control">
                    <option value="">Select...</option>
                    @foreach(config('dms_forms.service_group_other_govt') as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <h3 class="card-title">Term Contract</h3>
    <div class="section-grid">
        <div>
            <label class="form-label">Catchment *</label>
            <input type="text" wire:model="catchment" class="form-control">
            @error('catchment')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Contractor *</label>
            <input type="text" wire:model="contractor" class="form-control">
            @error('contractor')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">In Term Contract *</label>
            <select wire:model="in_term_contract" class="form-control">
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
            @error('in_term_contract')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Last Maintained Date</label>
            <input type="text" wire:model="last_maintained_date" class="form-control dms-date-picker" placeholder="Select date" autocomplete="off">
        </div>
        <div>
            <label class="form-label">Next Maintain Date</label>
            <input type="text" wire:model="next_maintain_date" class="form-control dms-date-picker" placeholder="Select date" autocomplete="off">
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Suggestions &amp; Approval</h3>
    <div class="section-grid">
        <div style="grid-column: 1 / -1;">
            <label class="form-label">Inspection &amp; Suggestions (CTA/STA/TA/TTA/Supervisor)</label>
            <textarea wire:model="inspection_suggestions" class="form-control"></textarea>
        </div>
        <div>
            <label class="form-label">Review (CTA/STA)</label>
            <input type="text" wire:model="review_cta_sta" class="form-control">
        </div>
        <div>
            <label class="form-label">Support (Drainage Maintenance Superintendent)</label>
            <input type="text" wire:model="support_superintendent" class="form-control">
        </div>
        <div>
            <label class="form-label">Approval (Executive Engineer) *</label>
            <select wire:model.live="approval_decision" class="form-control">
                <option value="">Select...</option>
                @foreach(config('dms_forms.approval_decisions') as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('approval_decision')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        @if($approval_decision === 'not_approve')
            <div style="grid-column: 1 / -1;">
                <label class="form-label">Reason *</label>
                <textarea wire:model="approval_reason" class="form-control"></textarea>
                @error('approval_reason')<p class="error-text">{{ $message }}</p>@enderror
            </div>
        @endif
    </div>
</div>

<div class="card">
    <h3 class="card-title">Routing Slip</h3>
    <div class="table-wrap">
        <table class="routing-table">
            <thead>
                <tr>
                    <th>Route</th><th>Activity</th><th>Tick</th><th>Date &amp; Time In</th><th>Date &amp; Time Out</th><th>TPOR</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($routing_slip as $index => $row)
                    <tr wire:key="route-{{ $index }}">
                        <td><input type="text" wire:model="routing_slip.{{ $index }}.route" class="form-control"></td>
                        <td><input type="text" wire:model="routing_slip.{{ $index }}.activity" class="form-control"></td>
                        <td><input type="text" wire:model="routing_slip.{{ $index }}.tick" class="form-control"></td>
                        <td><input type="text" wire:model="routing_slip.{{ $index }}.datetime_in" class="form-control dms-datetime-picker" placeholder="Date & time" autocomplete="off"></td>
                        <td><input type="text" wire:model="routing_slip.{{ $index }}.datetime_out" class="form-control dms-datetime-picker" placeholder="Date & time" autocomplete="off"></td>
                        <td><input type="text" wire:model="routing_slip.{{ $index }}.tpor" class="form-control"></td>
                        <td>
                            @if(count($routing_slip) > 1)
                                <button type="button" class="btn-ghost" wire:click="removeRoutingRow({{ $index }})"><i class="fas fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <button type="button" class="btn btn-secondary" style="margin-top:0.75rem;" wire:click="addRoutingRow"><i class="fas fa-plus"></i> Add Row</button>
</div>

<div class="card">
    <h3 class="card-title">Page 2 — Site &amp; Investigation</h3>
    <div class="section-grid">
        <div style="grid-column: 1 / -1;">
            <label class="form-label">Site Sketch</label>
            <textarea wire:model="site_sketch" class="form-control" placeholder="Describe or reference site sketch from paper form"></textarea>
        </div>
        <div>
            <label class="form-label">Notes</label>
            <textarea wire:model="notes" class="form-control"></textarea>
        </div>
        <div>
            <label class="form-label">Comments</label>
            <textarea wire:model="comments" class="form-control"></textarea>
        </div>
    </div>
    <h4 style="margin:1rem 0 0.5rem; font-size:0.9rem; color:var(--text-secondary);">Sketch By Complainant</h4>
    <div class="section-grid">
        <div><label class="form-label">Signature</label><input type="text" wire:model="sketch_signature" class="form-control"></div>
        <div><label class="form-label">Name</label><input type="text" wire:model="sketch_name" class="form-control"></div>
        <div><label class="form-label">Date</label><input type="text" wire:model="sketch_date" class="form-control dms-date-picker" placeholder="Select date" autocomplete="off"></div>
    </div>
    <h4 style="margin:1rem 0 0.5rem; font-size:0.9rem; color:var(--text-secondary);">Investigated By STA/TA/TTA/Supervisor</h4>
    <div class="section-grid">
        <div><label class="form-label">Name</label><input type="text" wire:model="investigated_name" class="form-control"></div>
        <div>
            <label class="form-label">Investigated Date &amp; Time</label>
            <input type="text" wire:model="investigated_datetime" class="form-control dms-datetime-picker" placeholder="Select date & time" autocomplete="off">
            @error('investigated_datetime')<p class="error-text">{{ $message }}</p>@enderror
        </div>
    </div>
    <h4 style="margin:1rem 0 0.5rem; font-size:0.9rem; color:var(--text-secondary);">Keadaan Kawasan</h4>
    <div class="checkbox-grid">
        @foreach(config('dms_forms.keadaan_kawasan') as $key => $label)
            <label class="checkbox-item"><input type="checkbox" wire:model="keadaan_kawasan.{{ $key }}"> {{ $label }}</label>
        @endforeach
    </div>
    <h4 style="margin:1rem 0 0.5rem; font-size:0.9rem; color:var(--text-secondary);">Jenis Masalah Yang Dihadapi</h4>
    <div class="checkbox-grid">
        @foreach(config('dms_forms.jenis_masalah') as $key => $label)
            <label class="checkbox-item"><input type="checkbox" wire:model="jenis_masalah.{{ $key }}"> {{ $label }}</label>
        @endforeach
    </div>
    <h4 style="margin:1rem 0 0.5rem; font-size:0.9rem; color:var(--text-secondary);">Kategori Masalah</h4>
    <div class="checkbox-grid">
        @foreach(config('dms_forms.kategori_masalah') as $key => $label)
            <label class="checkbox-item"><input type="checkbox" wire:model="kategori_masalah.{{ $key }}"> {{ $label }}</label>
        @endforeach
    </div>
</div>
