@extends('r_admin.layout')

@section('title', 'Customer report '.$report->reference_code)

@section('content')
@if(session('success'))
    <div class="alert-success" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
@endif

<div class="header">
    <div>
        <a href="{{ route('admin.customer-reports.index') }}" class="back-link" style="margin-bottom: 0.75rem;">← Customer reports</a>
        <h1>Review report</h1>
        <p>{{ $report->reference_code ?? 'Report #'.$report->id }}</p>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
        <form method="post" action="{{ route('admin.customer-reports.send-to-operations', $report) }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn-submit">Send to operations</button>
        </form>
        <form method="post" action="{{ route('admin.customer-reports.destroy', $report) }}" style="display: inline;" onsubmit="return confirm('Delete this customer report permanently?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<div class="grid-2col">
    <section class="card">
        <h3 class="section-head" style="margin-bottom: 1rem;">Details</h3>
        <div class="detail-row">
            <p class="info-label">Reference</p>
            <p class="info-value">{{ $report->reference_code ?? '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Status</p>
            <p class="info-value">{{ ucfirst(str_replace('_', ' ', $report->status ?? 'pending')) }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Problem type</p>
            <p class="info-value">{{ $report->problem_type ?? '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Severity</p>
            <p class="info-value">{{ $report->severity ?? '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Reporter</p>
            <p class="info-value">{{ $report->user?->name ?? $report->reporter_name ?? 'Guest' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Phone</p>
            <p class="info-value">{{ $report->phone ?? '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Address</p>
            <p class="info-value">{{ $report->address ?? '—' }}</p>
        </div>
        @if($report->latitude !== null && $report->longitude !== null)
            <div class="detail-row">
                <p class="info-label">Coordinates</p>
                <p class="info-value">{{ $report->latitude }}, {{ $report->longitude }}</p>
            </div>
        @endif
        <div class="detail-row">
            <p class="info-label">Submitted</p>
            <p class="info-value">{{ $report->created_at?->format('Y-m-d H:i') ?? '—' }}</p>
        </div>
        @if($report->drainage_ai_verdict)
            <div class="detail-row">
                <p class="info-label">AI drainage check</p>
                <p class="info-value">{{ match ($report->drainage_ai_verdict) {
                    'drainage' => 'Drainage-related',
                    'not_drainage' => 'Not drainage-related',
                    'needs_review' => 'Needs review',
                    default => $report->drainage_ai_verdict,
                } }}</p>
            </div>
        @endif
        <div class="detail-row">
            <p class="info-label">Operations</p>
            @if($report->operationsReport)
                <p class="info-value">Linked — report #{{ $report->operationsReport->report_number }}</p>
            @else
                <p class="info-value-muted">Not linked yet</p>
            @endif
        </div>
        @if($report->description)
            <div class="detail-row">
                <p class="info-label">Description</p>
                <p class="info-value" style="white-space: pre-wrap;">{{ $report->description }}</p>
            </div>
        @endif
    </section>

    <section class="card">
        <h3 class="section-head" style="margin-bottom: 1rem;">Photo</h3>
        @php $photoUrl = $report->photo_path ? \Illuminate\Support\Facades\Storage::url($report->photo_path) : null; @endphp
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="Report photo" style="max-width: 100%; border-radius: 8px; border: 1px solid rgba(106, 150, 255, 0.2);" />
        @else
            <p class="info-value-muted">No photo uploaded.</p>
        @endif
    </section>
</div>
@endsection
