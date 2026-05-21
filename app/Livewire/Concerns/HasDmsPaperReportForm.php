<?php

namespace App\Livewire\Concerns;

trait HasDmsPaperReportForm
{
    public string $dds_file_reference = '';

    public string $service_request_reference = '';

    public string $contact_name = '';

    public string $phone_home = '';

    public string $phone_mobile = '';

    public string $house_location = '';

    public string $incident_datetime = '';

    public string $incident_location = '';

    public string $problem_code = '';

    public string $mukim = '';

    public string $assigned_crew = '';

    public string $work_details = '';

    public string $service_group_type = 'jkr';

    public string $service_group_jkr = '';

    public string $service_group_other = '';

    public string $catchment = '';

    public string $contractor = '';

    public string $in_term_contract = '';

    public string $last_maintained_date = '';

    public string $next_maintain_date = '';

    public string $inspection_suggestions = '';

    public string $review_cta_sta = '';

    public string $support_superintendent = '';

    public string $approval_decision = '';

    public string $approval_reason = '';

    /** @var array<int, array{route: string, activity: string, tick: string, datetime_in: string, datetime_out: string, tpor: string}> */
    public array $routing_slip = [];

    public string $site_sketch = '';

    public string $notes = '';

    public string $comments = '';

    public string $sketch_signature = '';

    public string $sketch_name = '';

    public string $sketch_date = '';

    public string $investigated_name = '';

    public string $investigated_datetime = '';

    /** @var array<string, bool> */
    public array $keadaan_kawasan = [];

    /** @var array<string, bool> */
    public array $jenis_masalah = [];

    /** @var array<string, bool> */
    public array $kategori_masalah = [];

    /** @var array<string, float> */
    public array $fieldConfidence = [];

    protected function initPaperFormDefaults(): void
    {
        if ($this->routing_slip === []) {
            $this->routing_slip = [
                ['route' => '', 'activity' => '', 'tick' => '', 'datetime_in' => '', 'datetime_out' => '', 'tpor' => ''],
            ];
        }

        foreach (array_keys(config('dms_forms.keadaan_kawasan', [])) as $key) {
            $this->keadaan_kawasan[$key] ??= false;
        }
        foreach (array_keys(config('dms_forms.jenis_masalah', [])) as $key) {
            $this->jenis_masalah[$key] ??= false;
        }
        foreach (array_keys(config('dms_forms.kategori_masalah', [])) as $key) {
            $this->kategori_masalah[$key] ??= false;
        }
    }

    protected function paperReportValidationRules(): array
    {
        return [
            'dds_file_reference' => 'required|string|max:120',
            'service_request_reference' => 'required|string|max:120',
            'contact_name' => 'required|string|max:255',
            'phone_home' => 'nullable|string|max:40',
            'phone_mobile' => 'nullable|string|max:40',
            'house_location' => 'required|string|max:500',
            'incident_datetime' => 'nullable|date',
            'investigated_datetime' => 'nullable|date',
            'incident_location' => 'required|string|max:500',
            'problem_code' => 'required|string|max:80',
            'mukim' => 'required|string|max:120',
            'assigned_crew' => 'required|string|max:120',
            'work_details' => 'required|string|max:2000',
            'service_group_type' => 'required|in:jkr,other_govt',
            'catchment' => 'required|string|max:120',
            'contractor' => 'required|string|max:120',
            'in_term_contract' => 'required|in:yes,no',
            'approval_decision' => 'required|in:approve,not_approve',
            'approval_reason' => 'required_if:approval_decision,not_approve|nullable|string|max:1000',
        ];
    }

    protected function applyOcrFields(array $fields, array $confidence): void
    {
        foreach ($fields as $key => $value) {
            if (property_exists($this, $key) && is_string($this->{$key})) {
                $this->{$key} = $value;
            }
        }
        $this->fieldConfidence = $confidence;
    }

    public function isLowConfidence(string $field): bool
    {
        if (! isset($this->fieldConfidence[$field])) {
            return false;
        }

        return $this->fieldConfidence[$field] < (float) config('dms_forms.ocr_confidence_threshold', 0.65);
    }

    public function addRoutingRow(): void
    {
        $this->routing_slip[] = ['route' => '', 'activity' => '', 'tick' => '', 'datetime_in' => '', 'datetime_out' => '', 'tpor' => ''];
    }

    public function removeRoutingRow(int $index): void
    {
        if (count($this->routing_slip) <= 1) {
            return;
        }
        unset($this->routing_slip[$index]);
        $this->routing_slip = array_values($this->routing_slip);
    }

    protected function buildPaperFormPayload(): array
    {
        return [
            'house_location' => $this->house_location,
            'incident_location' => $this->incident_location,
            'problem_code' => $this->problem_code,
            'mukim' => $this->mukim,
            'assigned_crew' => $this->assigned_crew,
            'work_details' => $this->work_details,
            'contact' => [
                'phone_home' => $this->phone_home,
                'phone_mobile' => $this->phone_mobile,
                'incident_datetime' => $this->incident_datetime,
            ],
            'service_group' => [
                'type' => $this->service_group_type,
                'jkr' => $this->service_group_jkr,
                'other_govt' => $this->service_group_other,
            ],
            'term_contract' => [
                'catchment' => $this->catchment,
                'contractor' => $this->contractor,
                'in_term_contract' => $this->in_term_contract,
                'last_maintained_date' => $this->last_maintained_date,
                'next_maintain_date' => $this->next_maintain_date,
            ],
            'suggestions_approval' => [
                'inspection_suggestions' => $this->inspection_suggestions,
                'review_cta_sta' => $this->review_cta_sta,
                'support_superintendent' => $this->support_superintendent,
                'approval_decision' => $this->approval_decision,
                'approval_reason' => $this->approval_reason,
            ],
            'routing_slip' => $this->routing_slip,
            'page_two' => [
                'site_sketch' => $this->site_sketch,
                'notes' => $this->notes,
                'comments' => $this->comments,
                'sketch_by_complainant' => [
                    'signature' => $this->sketch_signature,
                    'name' => $this->sketch_name,
                    'date' => $this->sketch_date,
                ],
                'investigated' => [
                    'name' => $this->investigated_name,
                    'datetime' => $this->investigated_datetime,
                ],
                'keadaan_kawasan' => array_keys(array_filter($this->keadaan_kawasan)),
                'jenis_masalah' => array_keys(array_filter($this->jenis_masalah)),
                'kategori_masalah' => array_keys(array_filter($this->kategori_masalah)),
            ],
        ];
    }
}
