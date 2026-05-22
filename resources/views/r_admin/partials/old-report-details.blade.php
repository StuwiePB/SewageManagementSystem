@php
    $contact = $form['contact'] ?? [];
    $serviceGroup = $form['service_group'] ?? [];
    $termContract = $form['term_contract'] ?? [];
    $approval = $form['suggestions_approval'] ?? [];
    $pageTwo = $form['page_two'] ?? [];
    $sketch = $pageTwo['sketch_by_complainant'] ?? [];
    $investigated = $pageTwo['investigated'] ?? [];
    $jkrLabels = config('dms_forms.service_group_jkr', []);
    $otherLabels = config('dms_forms.service_group_other_govt', []);
    $approvalLabels = config('dms_forms.approval_decisions', []);
    $keadaanLabels = config('dms_forms.keadaan_kawasan', []);
    $jenisLabels = config('dms_forms.jenis_masalah', []);
    $kategoriLabels = config('dms_forms.kategori_masalah', []);
    $formatList = fn (array $keys, array $labels) => collect($keys)->map(fn ($k) => $labels[$k] ?? $k)->join(', ') ?: '—';
    $serviceGroupLabel = ($serviceGroup['type'] ?? '') === 'other_govt'
        ? ($otherLabels[$serviceGroup['other_govt'] ?? ''] ?? '—')
        : ($jkrLabels[$serviceGroup['jkr'] ?? ''] ?? '—');
@endphp

<section class="card">
    <h3 class="section-head" style="margin-bottom: 1rem;">References</h3>
    <div class="detail-row"><p class="info-label">DDS File Reference</p><p class="info-value">{{ $paperReport->dds_file_reference ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Service Request Reference (Ticket)</p><p class="info-value">{{ $paperReport->service_request_reference ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Archive Number</p><p class="info-value">{{ $paperReport->archive_number }}</p></div>
    <div class="detail-row"><p class="info-label">Source</p><p class="info-value">{{ strtoupper($paperReport->source) }}</p></div>
    <div class="detail-row"><p class="info-label">Digitized by</p><p class="info-value">{{ $paperReport->digitizedBy?->name ?? '—' }} @if($paperReport->created_at)({{ $paperReport->created_at->format('d M Y, H:i') }})@endif</p></div>
</section>

<section class="card">
    <h3 class="section-head" style="margin-bottom: 1rem;">Customer &amp; Complaint</h3>
    <div class="detail-row"><p class="info-label">Contact Name</p><p class="info-value">{{ $paperReport->contact_name ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Phone (H)</p><p class="info-value">{{ $contact['phone_home'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Phone (M)</p><p class="info-value">{{ $contact['phone_mobile'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">House Location</p><p class="info-value">{{ $form['house_location'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Incident Date &amp; Time</p><p class="info-value">{{ $paperReport->incident_at?->format('d M Y, H:i') ?? ($contact['incident_datetime'] ?? '—') }}</p></div>
    <div class="detail-row"><p class="info-label">Incident Location</p><p class="info-value">{{ $form['incident_location'] ?? '—' }}</p></div>
</section>

<section class="card">
    <h3 class="section-head" style="margin-bottom: 1rem;">Service Request Assessment</h3>
    <div class="detail-row"><p class="info-label">Problem Code</p><p class="info-value">{{ $form['problem_code'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Mukim</p><p class="info-value">{{ $form['mukim'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Assigned Crew</p><p class="info-value">{{ $form['assigned_crew'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Work Details</p><p class="info-value">{{ $form['work_details'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Service Group</p><p class="info-value">{{ $serviceGroupLabel }}</p></div>
</section>

<section class="card">
    <h3 class="section-head" style="margin-bottom: 1rem;">Term Contract</h3>
    <div class="detail-row"><p class="info-label">Catchment</p><p class="info-value">{{ $termContract['catchment'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Contractor</p><p class="info-value">{{ $termContract['contractor'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">In Term Contract</p><p class="info-value">{{ isset($termContract['in_term_contract']) ? ucfirst($termContract['in_term_contract']) : '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Last Maintained</p><p class="info-value">{{ $termContract['last_maintained_date'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Next Maintain</p><p class="info-value">{{ $termContract['next_maintain_date'] ?? '—' }}</p></div>
</section>

<section class="card">
    <h3 class="section-head" style="margin-bottom: 1rem;">Suggestions &amp; Approval</h3>
    <div class="detail-row"><p class="info-label">Inspection &amp; Suggestions</p><p class="info-value">{{ $approval['inspection_suggestions'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Review (CTA/STA)</p><p class="info-value">{{ $approval['review_cta_sta'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Support (Superintendent)</p><p class="info-value">{{ $approval['support_superintendent'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Approval</p><p class="info-value">{{ $approvalLabels[$approval['approval_decision'] ?? ''] ?? '—' }}</p></div>
    @if(($approval['approval_decision'] ?? '') === 'not_approve')
        <div class="detail-row"><p class="info-label">Reason</p><p class="info-value">{{ $approval['approval_reason'] ?? '—' }}</p></div>
    @endif
</section>

@if(!empty($form['routing_slip']))
<section class="card">
    <h3 class="section-head" style="margin-bottom: 1rem;">Routing Slip</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Route</th><th>Activity</th><th>Tick</th><th>Date &amp; Time In</th><th>Date &amp; Time Out</th><th>TPOR</th></tr></thead>
            <tbody>
                @foreach($form['routing_slip'] as $row)
                    <tr>
                        <td>{{ $row['route'] ?? '—' }}</td>
                        <td>{{ $row['activity'] ?? '—' }}</td>
                        <td>{{ $row['tick'] ?? '—' }}</td>
                        <td>{{ $row['datetime_in'] ?? '—' }}</td>
                        <td>{{ $row['datetime_out'] ?? '—' }}</td>
                        <td>{{ $row['tpor'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

<section class="card">
    <h3 class="section-head" style="margin-bottom: 1rem;">Page 2 — Site &amp; Investigation</h3>
    <div class="detail-row"><p class="info-label">Site Sketch</p><p class="info-value">{{ $pageTwo['site_sketch'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Notes</p><p class="info-value">{{ $pageTwo['notes'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Comments</p><p class="info-value">{{ $pageTwo['comments'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Sketch — Signature / Name / Date</p><p class="info-value">{{ ($sketch['signature'] ?? '—') }} / {{ ($sketch['name'] ?? '—') }} / {{ ($sketch['date'] ?? '—') }}</p></div>
    <div class="detail-row"><p class="info-label">Investigated By</p><p class="info-value">{{ $investigated['name'] ?? '—' }}</p></div>
    <div class="detail-row"><p class="info-label">Investigated Date &amp; Time</p><p class="info-value">{{ $paperReport->investigated_at?->format('d M Y, H:i') ?? ($investigated['datetime'] ?? '—') }}</p></div>
    <div class="detail-row"><p class="info-label">Keadaan Kawasan</p><p class="info-value">{{ $formatList($pageTwo['keadaan_kawasan'] ?? [], $keadaanLabels) }}</p></div>
    <div class="detail-row"><p class="info-label">Jenis Masalah</p><p class="info-value">{{ $formatList($pageTwo['jenis_masalah'] ?? [], $jenisLabels) }}</p></div>
    <div class="detail-row"><p class="info-label">Kategori Masalah</p><p class="info-value">{{ $formatList($pageTwo['kategori_masalah'] ?? [], $kategoriLabels) }}</p></div>
</section>
