@extends('r_admin.layout')

@section('title', 'Old Report '.$paperReport->archive_number)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('admin.old-reports.index') }}" class="back-link">← Old Reports</a>
        <h1 style="margin-top: 0.75rem;">{{ $paperReport->archive_number }}</h1>
        <p>{{ $paperReport->dds_file_reference ?? 'OM/DDS paper report' }}</p>
    </div>
</div>

@include('r_admin.partials.dms-archive-view-only-notice')

@if($scannedImageUrl)
    <section class="card">
        <h3 class="section-head" style="margin-bottom: 1rem;">Scanned Document</h3>
        <a href="{{ $scannedImageUrl }}" target="_blank" rel="noopener" class="link-primary">Open full image</a>
        <div style="margin-top: 1rem;">
            <img src="{{ $scannedImageUrl }}" alt="Scanned form" style="max-width: 100%; max-height: 480px; border-radius: 8px; border: 1px solid rgba(106, 150, 255, 0.2);">
        </div>
    </section>
@endif

@include('r_admin.partials.old-report-details', ['paperReport' => $paperReport, 'form' => $form])

@if($paperReport->archiveWorkOrders->isNotEmpty())
    <section class="card">
        <h3 class="section-head" style="margin-bottom: 1rem;">Linked Old Work Orders</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Work Order #</th><th>Status</th><th>Created</th><th></th></tr></thead>
                <tbody>
                    @foreach($paperReport->archiveWorkOrders as $wo)
                        <tr>
                            <td>{{ $wo->work_order_number }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $wo->status)) }}</td>
                            <td>{{ $wo->record_created_at?->format('d M Y, H:i') ?? '—' }}</td>
                            <td><a href="{{ route('admin.old-work-orders.show', $wo) }}" class="link-primary">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection
