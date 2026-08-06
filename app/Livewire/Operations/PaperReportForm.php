<?php

namespace App\Livewire\Operations;

use App\Livewire\Concerns\HasDmsPaperReportForm;
use App\Models\DmsPaperReport;
use App\Services\AuditLogger;
use App\Services\Dms\DmsFormOcrService;
use App\Support\DmsFormDateTime;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('r_operators.layouts.livewire')]
class PaperReportForm extends Component
{
    use HasDmsPaperReportForm;
    use WithFileUploads;

    /** choose | upload | processing | form */
    public string $step = 'choose';

    public string $entryMode = '';

    public $scanUpload;

    public ?string $scannedImagePath = null;

    public ?string $ocrRawText = null;

    public function mount(): void
    {
        $this->initPaperFormDefaults();
    }

    public function selectOcr(): void
    {
        $this->entryMode = 'ocr';
        $this->step = 'upload';
    }

    public function selectManual(): void
    {
        $this->entryMode = 'manual';
        $this->fieldConfidence = [];
        $this->step = 'form';
    }

    public function back(): void
    {
        if ($this->step === 'form' && $this->entryMode === 'ocr') {
            $this->step = 'upload';

            return;
        }

        $this->redirect(route('operations.old-reports.index'), navigate: true);
    }

    public function processOcr(): void
    {
        $this->validate([
            'scanUpload' => 'required|image|max:15360',
        ]);

        $stored = $this->scanUpload->store('dms-paper-scans', 'local');
        $this->scannedImagePath = $stored;

        $result = app(DmsFormOcrService::class)->extractFromImage($stored);
        $this->ocrRawText = $result['raw_text'];
        $this->applyOcrFields($result['fields'], $result['confidence']);

        $this->step = 'form';
    }

    public function save(): void
    {
        $this->validate($this->paperReportValidationRules());

        $lowConfidence = [];
        foreach ($this->fieldConfidence as $field => $score) {
            if ($score < (float) config('dms_forms.ocr_confidence_threshold', 0.65)) {
                $lowConfidence[] = $field;
            }
        }

        $report = DmsPaperReport::create([
            'archive_number' => DmsPaperReport::generateArchiveNumber(),
            'source' => $this->entryMode === 'ocr' ? 'ocr' : 'manual',
            'scanned_image_path' => $this->scannedImagePath,
            'ocr_raw_text' => $this->ocrRawText,
            'low_confidence_fields' => $lowConfidence ?: null,
            'digitized_by' => Auth::id(),
            'dds_file_reference' => $this->dds_file_reference,
            'service_request_reference' => $this->service_request_reference,
            'contact_name' => $this->contact_name,
            'incident_at' => DmsFormDateTime::parse($this->incident_datetime),
            'investigated_at' => DmsFormDateTime::parse($this->investigated_datetime),
            'form_data' => $this->buildPaperFormPayload(),
        ]);

        AuditLogger::log(
            'old_report.created',
            sprintf('Digitized old report %s (%s)', $report->archive_number, $this->entryMode === 'ocr' ? 'OCR upload' : 'manual entry'),
            $report,
            AuditLogger::AREA_OPERATIONS,
            [
                'archive_number' => $report->archive_number,
                'dds_file_reference' => $report->dds_file_reference,
                'source' => $report->source,
            ],
        );

        session()->flash('success', 'Archived paper report saved successfully.');

        $this->redirect(route('operations.old-reports.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.operations.paper-report-form');
    }
}
