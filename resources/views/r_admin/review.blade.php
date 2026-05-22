<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Review - Incident Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg-primary: var(--text-primary); --bg-secondary: var(--brudms-secondary); --text-primary: #FFFFFF; --text-secondary: #B0B0B0; --accent-blue: var(--brudms-primary); --accent-red: #FF5B5B; --accent-green: #56FF8B; --accent-purple: #A86AFF; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary); line-height: 1.6; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Inter', sans-serif; font-weight: 600; color: var(--text-primary); }
        .navbar { background: var(--bg-secondary); border-bottom: 1px solid rgba(106, 150, 255, 0.15); }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; color: var(--text-primary) !important; }
        .nav-link { color: var(--text-secondary) !important; font-weight: 500; }
        .nav-link:hover, .nav-link.active { color: var(--accent-blue) !important; }
        .navbar-toggler { border-color: rgba(106, 150, 255, 0.3); }
        .navbar-toggler-icon { filter: invert(1); }
        .main-container { max-width: 1400px; margin: 2rem auto; padding: 0 1rem; }
        .incident-card { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.15); border-radius: 12px; padding: 2rem; margin-bottom: 2rem; }
        .section-title { color: var(--accent-blue); border-bottom: 3px solid var(--accent-blue); padding-bottom: 0.5rem; display: inline-block; margin-bottom: 1.5rem; }
        .incident-image { max-width: 100%; border-radius: 8px; border: 1px solid rgba(106, 150, 255, 0.2); margin-bottom: 1rem; }
        .ai-info-card { background: var(--bg-primary); border-left: 4px solid var(--accent-blue); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid rgba(106, 150, 255, 0.15); }
        .confidence-bar { height: 8px; background: rgba(106, 150, 255, 0.2); border-radius: 4px; overflow: hidden; margin-top: 0.5rem; }
        .confidence-fill { height: 100%; background: linear-gradient(90deg, var(--accent-green), var(--accent-blue)); transition: width 0.3s ease; }
        .badge-custom { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
        .badge-sewage { background: rgba(255, 91, 91, 0.2); color: var(--accent-red); }
        .badge-not-sewage { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
        .badge-uncertain { background: rgba(255, 190, 11, 0.2); color: #ffbe0b; }
        .evidence-badge { display: inline-block; padding: 0.25rem 0.5rem; margin: 0.25rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500; }
        .evidence-badge.true { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
        .evidence-badge.false { background: rgba(106, 150, 255, 0.15); color: var(--text-secondary); }
        .btn-confirm { background: var(--accent-green); border-color: var(--accent-green); color: var(--text-primary); }
        .btn-confirm:hover { background: var(--accent-green); opacity: 0.9; border-color: var(--accent-green); color: var(--text-primary); }
        .btn-reject { background: var(--accent-red); border-color: var(--accent-red); color: white; }
        .btn-reject:hover { background: var(--accent-red); opacity: 0.9; border-color: var(--accent-red); color: white; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--text-secondary); }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; color: var(--accent-blue); }
        .text-muted { color: var(--text-secondary) !important; }
        .alert-warning { background: rgba(255, 190, 11, 0.15); color: #ffbe0b; border-color: transparent; }
        .review-actions { margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid rgba(106, 150, 255, 0.15); }
        .review-actions .form-label { color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem; }
        .review-actions textarea { width: 100%; padding: 0.75rem; background: var(--bg-primary); border: 1px solid rgba(106, 150, 255, 0.3); border-radius: 8px; color: var(--text-primary); font-size: 0.9375rem; min-height: 90px; resize: vertical; }
        .review-actions textarea::placeholder { color: var(--text-secondary); opacity: 0.7; }
        .review-actions .btn-wrap { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem; }
        .review-actions .btn-send { background: var(--accent-green); color: var(--text-primary); border: none; padding: 0.5rem 1.25rem; border-radius: 8px; font-weight: 500; cursor: pointer; }
        .review-actions .btn-send:hover { opacity: 0.9; color: var(--text-primary); }
        .review-actions .btn-delete { background: rgba(255, 91, 91, 0.2); color: var(--accent-red); border: 1px solid var(--accent-red); padding: 0.5rem 1.25rem; border-radius: 8px; font-weight: 500; cursor: pointer; }
        .review-actions .btn-delete:hover { background: rgba(255, 91, 91, 0.3); color: var(--accent-red); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('ai.incidents.dashboard') }}"><i class="fas fa-shield-alt me-2"></i>Drainage Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('incidents.create') }}">Upload</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('ai.incidents.dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ route('admin.incidents.review') }}">Review</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <h2 class="section-title">Incidents Requiring Review</h2>
        <p class="text-muted mb-4">Review incidents that need manual verification. AI checks whether each is drainage-related or not.</p>

        @if(session('success'))
            <div class="alert alert-success mb-4" style="background: rgba(86, 255, 139, 0.15); color: var(--accent-green); border: none; border-radius: 8px;">{{ session('success') }}</div>
        @endif

        @if($incidents->isEmpty())
            <div class="incident-card">
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h4>No incidents pending review</h4>
                    <p>All incidents have been reviewed or are awaiting AI analysis.</p>
                </div>
            </div>
        @else
            @foreach($incidents as $incident)
                <div class="incident-card" id="incident-{{ $incident->id }}">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Incident #{{ $incident->id }}</h5>
                            <p class="text-muted mb-3">
                                <small>
                                    <i class="fas fa-calendar me-1"></i>{{ $incident->created_at->format('M d, Y H:i') }}
                                    @if($incident->user)| <i class="fas fa-user me-1"></i>{{ $incident->user->name }}@endif
                                </small>
                            </p>
                            @if(Storage::disk('local')->exists($incident->photo_path))
                                <img src="{{ route('incidents.image', ['incident' => $incident->id]) }}" alt="Incident Photo" class="incident-image" onerror="this.onerror=null; this.outerHTML='<div class=\'alert alert-warning\'>Image not found</div>';">
                            @else
                                <div class="alert alert-warning">Image not found</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="ai-info-card">
                                <h6 class="mb-3"><i class="fas fa-robot me-2"></i>AI drainage relevance</h6>
                                <div class="mb-3">
                                    <strong>Result:</strong>
                                    @php
                                        $labelClass = match($incident->ai_label) {
                                            'SEWAGE' => 'badge-sewage',
                                            'NOT_SEWAGE' => 'badge-not-sewage',
                                            'NEEDS_REVIEW' => 'badge-uncertain',
                                            default => 'badge-not-sewage'
                                        };
                                        $labelDisplay = match($incident->ai_label ?? '') {
                                            'SEWAGE' => 'Drainage-related',
                                            'NOT_SEWAGE' => 'Not drainage-related',
                                            'NEEDS_REVIEW' => 'Needs review',
                                            default => $incident->ai_label ?? 'N/A'
                                        };
                                    @endphp
                                    <span class="badge badge-custom {{ $labelClass }}">{{ $labelDisplay }}</span>
                                </div>
                                @if($incident->has_person && $incident->person_confidence)
                                    <div class="mb-3 alert alert-warning">
                                        <strong>Person Detected:</strong> {{ number_format($incident->person_confidence * 100, 1) }}% confidence
                                        @if($incident->person_reason)<br><small>{{ $incident->person_reason }}</small>@endif
                                    </div>
                                @endif
                                @if($incident->evidence && is_array($incident->evidence))
                                    <div class="mb-3">
                                        <strong>Evidence:</strong>
                                        <div class="mt-2 small">
                                            @if(isset($incident->evidence['sewage_score']))
                                                <div>Drainage relevance: {{ number_format(($incident->evidence['sewage_score'] ?? 0) * 100, 1) }}%</div>
                                            @endif
                                            @if(!empty($incident->evidence['sewage_indicators']))
                                                <div>Indicators: {{ implode(', ', $incident->evidence['sewage_indicators']) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <strong>Confidence:</strong>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ number_format(($incident->ai_confidence ?? 0) * 100, 1) }}%</span>
                                        <div class="confidence-bar flex-grow-1">
                                            <div class="confidence-fill" style="width: {{ ($incident->ai_confidence ?? 0) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                @if($incident->ai_reasons)
                                    <div class="mb-3">
                                        <strong>AI reason:</strong>
                                        <ul class="mt-2 mb-0">@foreach($incident->ai_reasons as $reason)<li>{{ $reason }}</li>@endforeach</ul>
                                    </div>
                                @endif
                            </div>
                            <div class="review-actions">
                                <label class="form-label" for="admin-message-{{ $incident->id }}">Admin message / description</label>
                                <textarea id="admin-message-{{ $incident->id }}" class="admin-message-input" placeholder="Optional: add a note or description for this incident…" data-incident-id="{{ $incident->id }}">{{ old('admin_message', $incident->admin_message) }}</textarea>
                                <div class="btn-wrap">
                                    <button type="button" class="btn-send" data-incident-id="{{ $incident->id }}" data-action="send">
                                        <i class="fas fa-paper-plane me-1"></i> Send to operation
                                    </button>
                                    <button type="button" class="btn-delete" data-incident-id="{{ $incident->id }}" data-action="delete">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
        <div id="alertContainer"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-send, .btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var incidentId = this.getAttribute('data-incident-id');
                var action = this.getAttribute('data-action');
                var textarea = document.querySelector('#admin-message-' + incidentId);
                var adminMessage = textarea ? textarea.value.trim() : '';
                var card = document.getElementById('incident-' + incidentId);
                var alertContainer = document.getElementById('alertContainer');
                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                var url = action === 'send'
                    ? '{{ url("admin/incidents") }}/' + incidentId + '/send-to-operations'
                    : '{{ url("admin/incidents") }}/' + incidentId + '/delete';
                var method = 'POST';
                var body = JSON.stringify({ admin_message: adminMessage, _token: csrfToken });

                if (action === 'delete' && !confirm('Are you sure you want to delete this incident? This cannot be undone.')) return;

                btn.disabled = true;
                fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: body
                })
                .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
                .then(function(result) {
                    if (result.ok) {
                        if (card) card.remove();
                        window.location.reload();
                    } else {
                        alertContainer.innerHTML = '<div class="alert alert-danger mt-2">' + (result.data.message || 'Something went wrong.') + '</div>';
                        btn.disabled = false;
                    }
                })
                .catch(function() {
                    alertContainer.innerHTML = '<div class="alert alert-danger mt-2">Network error. Please try again.</div>';
                    btn.disabled = false;
                });
            });
        });
    </script>
</body>
</html>
