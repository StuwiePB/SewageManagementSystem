@extends('r_admin.layout')

@section('title', 'Customer reports')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    @keyframes cust-rep-dots { 0%, 33% { opacity: 0.3; } 66%, 100% { opacity: 1; } }
    .cust-rep-dots { animation: cust-rep-dots 1.2s ease-in-out infinite; }
    .cust-rep-scan-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-self: flex-start;
        min-width: 220px;
    }
    .cust-rep-scan-actions .btn-submit { width: 100%; white-space: nowrap; }
    .cust-rep-list { display: flex; flex-direction: column; gap: 12px; }
    .cust-rep-card {
        font-family: 'Poppins', sans-serif;
        position: relative;
        width: 100%;
        min-height: 105px;
        border-radius: 9px;
        background: rgba(106, 150, 255, 0.08);
        border: 1px solid rgba(106, 150, 255, 0.22);
        display: flex;
        flex-direction: row;
        align-items: stretch;
        padding: 8px;
        gap: 12px;
    }
    .cust-rep-thumb {
        width: 120px;
        min-width: 120px;
        height: 93px;
        border-radius: 5px;
        border: 1px solid rgba(106, 150, 255, 0.25);
        object-fit: cover;
        flex-shrink: 0;
        align-self: center;
    }
    .cust-rep-thumb--empty {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-primary);
        color: var(--text-secondary);
        font-size: 1.5rem;
    }
    .cust-rep-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; justify-content: center; }
    .cust-rep-type { color: var(--text-secondary); font-weight: 700; font-size: 14px; }
    .cust-rep-ref { color: var(--accent-blue); font-size: 11px; font-weight: 600; letter-spacing: 0.02em; }
    .cust-rep-meta-line {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }
    .cust-rep-meta-line img { width: 12px; height: 12px; object-fit: contain; opacity: 0.65; flex-shrink: 0; }
    .cust-rep-meta-line span { font-size: 10px; font-weight: 400; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cust-rep-meta-line .cust-rep-status { font-weight: 600; white-space: nowrap; }
    .cust-rep-reporter { font-size: 10px; color: var(--text-secondary); margin-top: 2px; }
    .cust-rep-card-tools {
        position: absolute;
        top: 6px;
        right: 8px;
        left: 136px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        z-index: 3;
        pointer-events: none;
    }
    .cust-rep-card-tools > * { pointer-events: auto; }
    .cust-rep-ai {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 10px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        flex-shrink: 1;
        min-width: 0;
    }
    .cust-rep-menu-wrap { position: relative; flex-shrink: 0; }
    .cust-rep-menu-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid rgba(106, 150, 255, 0.35);
        background: rgba(26, 29, 43, 0.95);
        color: var(--text-primary);
        cursor: pointer;
        font-size: 1.1rem;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    .cust-rep-menu-btn:hover { background: rgba(106, 150, 255, 0.15); }
    .cust-rep-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        min-width: 210px;
        background: var(--bg-secondary);
        border: 1px solid rgba(106, 150, 255, 0.28);
        border-radius: 10px;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.4);
        padding: 0.35rem 0;
        z-index: 25;
    }
    .cust-rep-menu.is-open { display: block; }
    .cust-rep-menu a,
    .cust-rep-menu button[type="submit"],
    .cust-rep-menu button[type="button"] {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0.55rem 1rem;
        border: none;
        background: none;
        color: var(--text-primary);
        font-size: 0.875rem;
        font-family: inherit;
        cursor: pointer;
        text-decoration: none;
    }
    .cust-rep-menu a:hover,
    .cust-rep-menu button[type="submit"]:hover,
    .cust-rep-menu button[type="button"]:hover { background: rgba(106, 150, 255, 0.12); }
    .cust-rep-menu .cust-rep-menu-danger { color: var(--accent-red); }
    .cust-rep-ai-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .cust-rep-ai--drainage { color: var(--accent-green); }
    .cust-rep-ai--drainage .cust-rep-ai-dot { background: var(--accent-green); box-shadow: 0 0 0 2px rgba(86, 255, 139, 0.25); }
    .cust-rep-ai--review { color: #FFA500; }
    .cust-rep-ai--review .cust-rep-ai-dot { background: #FFA500; box-shadow: 0 0 0 2px rgba(255, 165, 0, 0.25); }
    .cust-rep-ai--none { color: var(--text-secondary); }
    .cust-rep-ai--none .cust-rep-ai-dot { background: rgba(176, 176, 176, 0.35); }
    .cust-rep-ai--not { color: var(--accent-red); }
    .cust-rep-ai--not .cust-rep-ai-dot { background: var(--accent-red); box-shadow: 0 0 0 2px rgba(255, 91, 91, 0.25); }
    .drain-scan-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(10, 12, 20, 0.72);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .drain-scan-overlay.is-open { display: flex; }
    .drain-scan-modal {
        background: var(--bg-secondary);
        border: 1px solid rgba(106, 150, 255, 0.25);
        border-radius: 12px;
        padding: 1.75rem 2rem;
        max-width: 380px;
        width: 100%;
        text-align: center;
    }
    .drain-scan-spinner {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        border: 3px solid rgba(106, 150, 255, 0.2);
        border-top-color: var(--accent-blue);
        border-radius: 50%;
        animation: drain-spin 0.85s linear infinite;
    }
    @keyframes drain-spin { to { transform: rotate(360deg); } }
    .drain-scan-progress { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.35rem; }
    .drain-scan-detail { font-size: 0.875rem; color: var(--text-secondary); }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="alert-success" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert-success" style="margin-bottom: 1.25rem; background: rgba(255, 91, 91, 0.12); color: #ffb4b4; border: 1px solid rgba(255, 91, 91, 0.35);">
        <strong style="display: block; margin-bottom: 0.35rem;">Could not remove report</strong>
        <ul style="margin: 0; padding-left: 1.1rem; font-size: 0.875rem;">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="header">
    <div>
        <h1>Customer reports</h1>
        <p>Every complaint filed through the customer portal (same card style as customer history, without the mobile shell).</p>
    </div>
    <div class="cust-rep-scan-actions">
        <button type="button" class="btn-submit" id="drain-scan-start" {{ ($unscannedWithPhotoCount ?? 0) === 0 ? 'disabled' : '' }}>
            Scan unscanned photos ({{ $unscannedWithPhotoCount ?? 0 }})
        </button>
        <button type="button" class="btn-submit btn-secondary" id="drain-rescan-start" {{ ($reportsWithPhotoCount ?? 0) === 0 ? 'disabled' : '' }}>
            ReScan Photo ({{ $reportsWithPhotoCount ?? 0 }})
        </button>
    </div>
</div>

<section class="card card-mb" aria-label="Filters">
    <form id="customer-reports-filter-form" method="GET" action="{{ route('admin.customer-reports.index') }}" class="search-filter-bar">
        <input id="customer-reports-search" type="search" name="search" value="{{ request('search') }}" placeholder="Reference, address, reporter, phone, email…" class="form-control" style="flex:1; min-width:220px;">
        <select id="customer-reports-sent" name="sent" class="form-control" style="max-width:180px;">
            <option value="unsent" {{ request('sent', 'unsent') === 'unsent' ? 'selected' : '' }}>Unsent only</option>
            <option value="sent" {{ request('sent') === 'sent' ? 'selected' : '' }}>Sent only</option>
            <option value="all" {{ request('sent') === 'all' ? 'selected' : '' }}>All</option>
        </select>
        <select id="customer-reports-status" name="status" class="form-control" style="max-width:200px;">
            <option value="">All statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In progress</option>
            <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under review</option>
            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
        </select>
        <button type="submit" class="btn-submit">Filter</button>
        @if(request()->hasAny(['search', 'status', 'sent']))
            <a href="{{ route('admin.customer-reports.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
</section>

<section class="card" aria-label="Customer reports list">
    @if($reports->isEmpty())
        <div class="cust-rep-card" style="justify-content: center; align-items: center; min-height: 130px; flex-direction: column; gap: 8px;">
            <img src="{{ asset('images/Vectors/myhistory_emptyhistory.svg') }}" alt="" style="width: 70px; height: 70px; object-fit: contain; opacity: 0.85;" />
            <span style="font-family: 'Poppins', sans-serif; font-size: 11px; color: var(--text-secondary);">No customer reports match your filters.</span>
        </div>
    @else
        <div class="cust-rep-list">
            @foreach ($reports as $report)
                @php
                    $photoUrl = $report->photo_path ? \Illuminate\Support\Facades\Storage::url($report->photo_path) : null;
                    $isSent = (bool) $report->operationsReport;
                    $st = $report->status ?? 'pending';
                    $statusText = ($st === 'resolved') ? 'Resolved' : (in_array($st, ['in_progress', 'under_review'], true) ? 'In progress' : 'Active');
                    $statusColor = ($st === 'resolved') ? 'var(--accent-green)' : (in_array($st, ['in_progress', 'under_review'], true) ? '#FFA500' : 'var(--accent-red)');
                    $who = $report->user?->name ?? $report->reporter_name ?? 'Guest';
                @endphp
                <div class="cust-rep-card" style="{{ $isSent ? 'background: rgba(86, 255, 139, 0.11); border-color: rgba(86, 255, 139, 0.35);' : '' }}">
                    @php
                        $aiV = $report->drainage_ai_verdict;
                        $aiClass = match ($aiV) {
                            'drainage' => 'cust-rep-ai--drainage',
                            'not_drainage' => 'cust-rep-ai--not',
                            'needs_review' => 'cust-rep-ai--review',
                            default => 'cust-rep-ai--none',
                        };
                        $aiLabel = match ($aiV) {
                            'drainage' => 'Drainage',
                            'not_drainage' => 'Not drainage',
                            'needs_review' => 'Needs review',
                            default => $report->photo_path ? 'Not scanned' : 'No photo',
                        };
                    @endphp
                    <div class="cust-rep-card-tools">
                        @if($isSent)
                            <div class="cust-rep-ai cust-rep-ai--drainage" title="Already sent to operations">
                                <span class="cust-rep-ai-dot" aria-hidden="true"></span>
                                <span>Sent to operations</span>
                            </div>
                        @else
                            <div class="cust-rep-ai {{ $aiClass }}" title="AI drainage check (Google Vision)">
                                <span class="cust-rep-ai-dot" aria-hidden="true"></span>
                                <span>{{ $aiLabel }}</span>
                            </div>
                        @endif
                        <div class="cust-rep-menu-wrap">
                            <button type="button" class="cust-rep-menu-btn" aria-expanded="false" aria-haspopup="true" title="More actions">⋮</button>
                            <div class="cust-rep-menu" role="menu">
                                <a href="{{ route('admin.customer-reports.show', $report) }}" role="menuitem">Review</a>
                                @unless($isSent)
                                    <form method="post" action="{{ route('admin.customer-reports.send-to-operations', $report) }}">
                                        @csrf
                                        <button type="submit" role="menuitem">Send to operations</button>
                                    </form>
                                @endunless
                                <button type="button" class="cust-rep-menu-danger js-open-delete-report" role="menuitem" data-delete-url="{{ route('admin.customer-reports.destroy', $report) }}">Delete</button>
                            </div>
                        </div>
                    </div>
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="" class="cust-rep-thumb" loading="lazy" />
                    @else
                        <div class="cust-rep-thumb cust-rep-thumb--empty" aria-hidden="true"><i class="fas fa-image"></i></div>
                    @endif
                    <div class="cust-rep-body">
                        <span class="cust-rep-type">{{ $report->problem_type ?? 'Report' }}</span>
                        @if($report->reference_code)
                            <span class="cust-rep-ref">{{ $report->reference_code }}</span>
                        @endif
                        <div class="cust-rep-meta-line">
                            <img src="{{ asset('images/Vectors/all_reportstatus.svg') }}" alt="" />
                            <span class="cust-rep-status" style="color: {{ $statusColor }};">{{ $statusText }}</span>
                        </div>
                        <div class="cust-rep-meta-line">
                            <img src="{{ asset('images/Vectors/all_calempty.svg') }}" alt="" />
                            <span>{{ $report->created_at ? $report->created_at->format('jS M Y') : '—' }}</span>
                        </div>
                        <div class="cust-rep-meta-line">
                            <img src="{{ asset('images/Vectors/all_calcomplete.svg') }}" alt="" />
                            @if($st === 'resolved')
                                <span>{{ $report->updated_at ? $report->updated_at->format('jS M Y') : '—' }}</span>
                            @else
                                <span style="color: var(--text-secondary);">In progress<span class="cust-rep-dots">...</span></span>
                            @endif
                        </div>
                        <div class="cust-rep-meta-line">
                            <img src="{{ asset('images/Vectors/all_location.svg') }}" alt="" />
                            <span title="{{ $report->address ?? '—' }}">{{ \Illuminate\Support\Str::limit($report->address ?? '—', 48) }}</span>
                        </div>
                        <div class="cust-rep-reporter">{{ $who }}{{ $report->phone ? ' · '.$report->phone : '' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($reports->hasPages())
        <div class="pagination-bar">
            <span class="pagination-info">Showing {{ $reports->firstItem() }}–{{ $reports->lastItem() }} of {{ $reports->total() }}</span>
            <div class="pagination-btns">
                @if($reports->onFirstPage())
                    <span class="btn-page disabled"><svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Previous</span>
                @else
                    <a href="{{ $reports->previousPageUrl() }}" class="btn-page"><svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Previous</a>
                @endif
                @if($reports->hasMorePages())
                    <a href="{{ $reports->nextPageUrl() }}" class="btn-page">Next <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></a>
                @else
                    <span class="btn-page disabled">Next <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></span>
                @endif
            </div>
        </div>
    @endif
</section>

@include('r_admin.customer-reports.partials.delete-report-modal')

<div class="drain-scan-overlay" id="drain-scan-overlay" aria-hidden="true">
    <div class="drain-scan-modal" role="dialog" aria-labelledby="drain-scan-title" aria-modal="true">
        <div class="drain-scan-spinner" aria-hidden="true"></div>
        <div class="drain-scan-progress" id="drain-scan-title">Scanning…</div>
        <div class="drain-scan-detail" id="drain-scan-detail">Preparing</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.cust-rep-menu-wrap').forEach(function (wrap) {
        var toggle = wrap.querySelector('.cust-rep-menu-btn');
        var menu = wrap.querySelector('.cust-rep-menu');
        if (!toggle || !menu) return;
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var open = !menu.classList.contains('is-open');
            document.querySelectorAll('.cust-rep-menu.is-open').forEach(function (m) {
                if (m !== menu) m.classList.remove('is-open');
            });
            menu.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        menu.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('click', function (e) { e.stopPropagation(); });
        });
    });
    document.addEventListener('click', function () {
        document.querySelectorAll('.cust-rep-menu.is-open').forEach(function (m) { m.classList.remove('is-open'); });
        document.querySelectorAll('.cust-rep-menu-btn[aria-expanded="true"]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
    });
})();
(function () {
    var filterForm = document.getElementById('customer-reports-filter-form');
    var sentSelect = document.getElementById('customer-reports-sent');
    var statusSelect = document.getElementById('customer-reports-status');
    var searchInput = document.getElementById('customer-reports-search');
    if (filterForm) {
        var submitTimer = null;
        var submitForm = function () {
            if (submitTimer) {
                clearTimeout(submitTimer);
                submitTimer = null;
            }
            filterForm.submit();
        };
        if (sentSelect) sentSelect.addEventListener('change', submitForm);
        if (statusSelect) statusSelect.addEventListener('change', submitForm);
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                if (submitTimer) clearTimeout(submitTimer);
                submitTimer = setTimeout(function () { filterForm.submit(); }, 450);
            });
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitForm();
                }
            });
        }
    }
})();
(function () {
    var overlay = document.getElementById('drain-scan-overlay');
    var titleEl = document.getElementById('drain-scan-title');
    var detailEl = document.getElementById('drain-scan-detail');
    var csrf = @json(csrf_token());
    var scanBase = @json(rtrim(url('/'), '/') . '/admin/customer-reports/');
    var scanButtons = [
        document.getElementById('drain-scan-start'),
        document.getElementById('drain-rescan-start')
    ].filter(Boolean);

    if (!overlay || scanButtons.length === 0) return;

    function setButtonsDisabled(disabled) {
        scanButtons.forEach(function (b) { b.disabled = disabled; });
    }

    function runBulkDrainScan(config) {
        var btn = config.button;
        if (btn.disabled) return;
        setButtonsDisabled(true);
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        titleEl.textContent = config.loadingTitle || 'Scanning…';
        detailEl.textContent = 'Fetching list…';

        fetch(config.idsUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var ids = data.ids || [];
                if (ids.length === 0) {
                    titleEl.textContent = config.emptyTitle || 'Nothing to scan';
                    detailEl.textContent = config.emptyDetail || 'No matching reports.';
                    setTimeout(function () {
                        overlay.classList.remove('is-open');
                        overlay.setAttribute('aria-hidden', 'true');
                        setButtonsDisabled(false);
                    }, 1200);
                    return;
                }
                var total = ids.length;
                var done = 0;

                function scanNext() {
                    if (done >= total) {
                        titleEl.textContent = 'Done';
                        detailEl.textContent = 'Reloading…';
                        window.location.reload();
                        return;
                    }
                    var id = ids[done];
                    titleEl.textContent = (config.progressTitle || 'Scanning') + ' ' + (done + 1) + ' / ' + total;
                    detailEl.textContent = 'Report #' + id;

                    fetch(scanBase + id + '/' + config.scanAction, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: '{}'
                    })
                        .then(function () {
                            done++;
                            scanNext();
                        })
                        .catch(function () {
                            done++;
                            scanNext();
                        });
                }
                scanNext();
            })
            .catch(function () {
                titleEl.textContent = 'Error';
                detailEl.textContent = 'Could not start scan.';
                setButtonsDisabled(false);
                setTimeout(function () {
                    overlay.classList.remove('is-open');
                    overlay.setAttribute('aria-hidden', 'true');
                }, 2000);
            });
    }

    var scanStart = document.getElementById('drain-scan-start');
    if (scanStart) {
        scanStart.addEventListener('click', function () {
            runBulkDrainScan({
                button: scanStart,
                idsUrl: @json(route('admin.customer-reports.unscanned-ids', ['sent' => request('sent', 'unsent')])),
                scanAction: 'scan-drainage',
                emptyDetail: 'All photos with images already have an AI label.'
            });
        });
    }

    var rescanStart = document.getElementById('drain-rescan-start');
    if (rescanStart) {
        rescanStart.addEventListener('click', function () {
            if (!window.confirm('Re-run AI drainage scan on all reports with photos? Existing labels will be replaced.')) {
                return;
            }
            runBulkDrainScan({
                button: rescanStart,
                idsUrl: @json(route('admin.customer-reports.rescan-ids', ['sent' => request('sent', 'unsent')])),
                scanAction: 'rescan-drainage',
                loadingTitle: 'Re-scanning…',
                progressTitle: 'Re-scanning',
                emptyDetail: 'No reports with photos match the current filter.'
            });
        });
    }
})();
</script>
@endpush
