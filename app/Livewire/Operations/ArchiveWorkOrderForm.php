<?php

namespace App\Livewire\Operations;

use App\Models\DmsArchiveWorkOrder;
use App\Models\DmsArchiveWorkOrderPhoto;
use App\Models\DmsPaperReport;
use App\Services\AuditLogger;
use App\Support\DmsFormDateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('r_operators.layouts.livewire')]
class ArchiveWorkOrderForm extends Component
{
    use WithFileUploads;

    public string $work_order_number = '';

    public string $priority = 'medium';

    public string $assigned_crew = '';

    public string $work_type = 'maintenance';

    public string $estimated_completion_date = '';

    public string $location_address = '';

    public string $mukim = '';

    public string $problem_category = '';

    public string $description = '';

    public string $site_notes = '';

    public string $status = 'pending';

    public string $record_created_at = '';

    public string $record_started_at = '';

    public string $record_completed_at = '';

    public ?int $dms_paper_report_id = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $photos_before = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $photos_after = [];

    public function mount(): void
    {
        $this->work_order_number = DmsArchiveWorkOrder::generateWorkOrderNumber();
        $this->record_created_at = now()->format('Y-m-d H:i');

        if (request()->filled('report')) {
            $this->dms_paper_report_id = (int) request('report');
            $paperReport = DmsPaperReport::find($this->dms_paper_report_id);
            if ($paperReport?->incident_at) {
                $this->record_created_at = $paperReport->incident_at->format('Y-m-d H:i');
            }
        }
    }

    public function back(): void
    {
        $this->redirect(route('operations.old-work-orders.index'), navigate: true);
    }

    public function save(): void
    {
        $this->validate([
            'work_order_number' => [
                'required',
                'string',
                'max:64',
                Rule::unique('dms_archive_work_orders', 'work_order_number'),
            ],
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_crew' => 'required|string|max:120',
            'work_type' => 'required|in:maintenance,repair,inspection,emergency',
            'estimated_completion_date' => 'nullable|date',
            'location_address' => 'required|string|max:500',
            'mukim' => 'required|string|max:120',
            'problem_category' => 'required|string|max:120',
            'description' => 'required|string|max:3000',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'record_created_at' => 'required|date',
            'record_started_at' => 'nullable|date',
            'record_completed_at' => 'nullable|date',
            'dms_paper_report_id' => 'required|exists:dms_paper_reports,id',
            'photos_before.*' => 'nullable|image|max:10240',
            'photos_after.*' => 'nullable|image|max:10240',
        ]);

        $workOrder = DmsArchiveWorkOrder::create([
            'work_order_number' => $this->work_order_number,
            'priority' => $this->priority,
            'assigned_crew' => $this->assigned_crew,
            'work_type' => $this->work_type,
            'estimated_completion_date' => DmsFormDateTime::parse($this->estimated_completion_date),
            'location_address' => $this->location_address,
            'mukim' => $this->mukim,
            'problem_category' => $this->problem_category,
            'description' => $this->description,
            'site_notes' => $this->site_notes,
            'status' => $this->status,
            'record_created_at' => DmsFormDateTime::parse($this->record_created_at),
            'record_started_at' => DmsFormDateTime::parse($this->record_started_at),
            'record_completed_at' => DmsFormDateTime::parse($this->record_completed_at),
            'dms_paper_report_id' => $this->dms_paper_report_id,
            'digitized_by' => Auth::id(),
        ]);

        foreach ($this->photos_before as $file) {
            if (! $file) {
                continue;
            }
            $path = $file->store('dms-archive-work-orders/'.$workOrder->id.'/before', 'public');
            DmsArchiveWorkOrderPhoto::create([
                'dms_archive_work_order_id' => $workOrder->id,
                'path' => $path,
                'photo_type' => 'before',
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        foreach ($this->photos_after as $file) {
            if (! $file) {
                continue;
            }
            $path = $file->store('dms-archive-work-orders/'.$workOrder->id.'/after', 'public');
            DmsArchiveWorkOrderPhoto::create([
                'dms_archive_work_order_id' => $workOrder->id,
                'path' => $path,
                'photo_type' => 'after',
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        AuditLogger::log(
            'old_work_order.created',
            sprintf('Created old work order %s', $workOrder->work_order_number),
            $workOrder,
            AuditLogger::AREA_OPERATIONS,
            [
                'work_order_number' => $workOrder->work_order_number,
                'priority' => $workOrder->priority,
                'status' => $workOrder->status,
                'mukim' => $workOrder->mukim,
            ],
        );

        session()->flash('success', 'Archive work order saved successfully.');

        $this->redirect(route('operations.old-work-orders.index'), navigate: true);
    }

    public function render()
    {
        $paperReports = DmsPaperReport::orderByDesc('created_at')->limit(200)->get();

        return view('livewire.operations.archive-work-order-form', [
            'paperReports' => $paperReports,
            'mukims' => collect(config('brunei.mukims', []))->flatten(1),
        ]);
    }
}
