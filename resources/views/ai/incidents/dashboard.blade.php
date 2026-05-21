<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidents Dashboard - Drainage Relevance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg-primary: var(--text-primary); --bg-secondary: var(--brudms-secondary); --text-primary: #FFFFFF; --text-secondary: #B0B0B0; --accent-blue: var(--brudms-primary); --accent-red: #FF5B5B; --accent-green: #56FF8B; --accent-purple: #A86AFF; --warning-yellow: #ffbe0b; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary); line-height: 1.6; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Inter', sans-serif; font-weight: 600; color: var(--text-primary); }
        .navbar { background: var(--bg-secondary); border-bottom: 1px solid rgba(106, 150, 255, 0.15); }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; color: var(--text-primary) !important; }
        .nav-link { color: var(--text-secondary) !important; font-weight: 500; }
        .nav-link:hover, .nav-link.active { color: var(--accent-blue) !important; }
        .navbar-toggler { border-color: rgba(106, 150, 255, 0.3); }
        .navbar-toggler-icon { filter: invert(1); }
        .main-container { max-width: 1400px; margin: 2rem auto; padding: 0 1rem; }
        .stats-card { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.15); border-radius: 12px; padding: 1.5rem; text-align: center; }
        .stats-number { font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; }
        .stats-label { color: var(--text-secondary); font-size: 0.9rem; }
        .priority-high .stats-number { color: var(--accent-red); }
        .priority-medium .stats-number { color: var(--warning-yellow); }
        .priority-low .stats-number { color: var(--accent-green); }
        .incident-card { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.15); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid rgba(106, 150, 255, 0.3); transition: transform 0.2s, box-shadow 0.2s; }
        .incident-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); }
        .incident-card.priority-high { border-left-color: var(--accent-red); }
        .incident-card.priority-medium { border-left-color: var(--warning-yellow); }
        .incident-card.priority-low { border-left-color: var(--accent-green); }
        .priority-badge { padding: 0.4rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: inline-block; }
        .priority-badge.high { background: rgba(255, 91, 91, 0.2); color: var(--accent-red); }
        .priority-badge.medium { background: rgba(255, 190, 11, 0.2); color: var(--warning-yellow); }
        .priority-badge.low { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
        .risk-score { font-size: 1.5rem; font-weight: 700; }
        .risk-score.high { color: var(--accent-red); }
        .risk-score.medium { color: var(--warning-yellow); }
        .risk-score.low { color: var(--accent-green); }
        .section-title { color: var(--accent-blue); border-bottom: 3px solid var(--accent-blue); padding-bottom: 0.5rem; display: inline-block; margin-bottom: 1.5rem; }
        .thumbnail-img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; }
        .text-muted { color: var(--text-secondary) !important; }
        .btn-outline-primary { border-color: var(--accent-blue); color: var(--accent-blue); }
        .btn-outline-primary:hover { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); border-color: var(--accent-blue); }
        .alert-info { background: rgba(106, 150, 255, 0.15); color: var(--accent-blue); border-color: transparent; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('ai.incidents.dashboard') }}"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('ai.incidents.dashboard') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('incidents.create') }}">Upload</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.incidents.review') }}">Review</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ route('ai.incidents.dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <h2 class="section-title">Drainage Relevance Dashboard</h2>
        <p class="text-muted mb-4">AI checks whether each incident is related to drainage or not.</p>
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="stats-card priority-high">
                    <div class="stats-number">{{ $highPriority->count() }}</div>
                    <div class="stats-label">Likely drainage-related</div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="stats-card priority-low">
                    <div class="stats-number">{{ $lowPriority->count() }}</div>
                    <div class="stats-label">Not drainage-related</div>
                </div>
            </div>
        </div>

        <h3 class="mt-4 mb-3">All Incidents (by drainage relevance)</h3>
        @if($incidents->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No incidents yet. Upload images to see AI drainage relevance results here.
            </div>
        @else
            @foreach($incidents as $incident)
                @php
                    $priority = \App\Http\Controllers\IncidentController::getPriorityLevel($incident->risk_score);
                    $priorityClass = strtolower($priority);
                    if ($priorityClass === 'unknown') {
                        $priorityClass = 'medium';
                    }
                @endphp
                <div class="incident-card priority-{{ $priorityClass }}">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            @if(Storage::disk('local')->exists($incident->photo_path))
                                <img src="{{ route('incidents.image', ['incident' => $incident->id]) }}" alt="Incident #{{ $incident->id }}" class="thumbnail-img" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'thumbnail-img bg-light d-flex align-items-center justify-content-center\'><i class=\'fas fa-image text-muted\'></i></div>';">
                            @else
                                <div class="thumbnail-img bg-light d-flex align-items-center justify-content-center"><i class="fas fa-image text-muted"></i></div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-1">Incident #{{ $incident->id }}</h5>
                            <p class="text-muted mb-0 small"><i class="fas fa-calendar me-1"></i>{{ $incident->created_at->format('M d, Y H:i') }}</p>
                            @if($incident->user)<p class="text-muted mb-0 small"><i class="fas fa-user me-1"></i>{{ $incident->user->name }}</p>@endif
                        </div>
                        <div class="col-md-2">
                            @php
                                $relevanceDisplay = $incident->risk_score === null ? 'N/A' : $incident->risk_score . '%';
                            @endphp
                            <div class="mb-2"><strong>Drainage relevance:</strong><div class="risk-score {{ $priorityClass }}">{{ $relevanceDisplay }}</div></div>
                            <span class="priority-badge {{ $priorityClass }}">{{ $priority }} PRIORITY</span>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-1"><strong>Status:</strong>
                                @php
                                    $statusBadgeClass = match($incident->review_status) {
                                        'NOT_SEWAGE', 'NOT_SEWAGE_CONFIRMED' => 'bg-success',
                                        'NEEDS_REVIEW' => 'bg-warning text-dark',
                                        'SEWAGE_CONFIRMED' => 'bg-danger',
                                        'PENDING_AI' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                    $statusDisplay = match($incident->review_status) {
                                        'NOT_SEWAGE' => 'Not drainage-related',
                                        'NOT_SEWAGE_CONFIRMED' => 'Not drainage-related (confirmed)',
                                        'NEEDS_REVIEW' => 'Needs review',
                                        'SEWAGE_CONFIRMED' => 'Drainage-related (confirmed)',
                                        'PENDING_AI' => 'Pending AI',
                                        default => $incident->review_status
                                    };
                                @endphp
                                <span class="badge {{ $statusBadgeClass }} ms-2">{{ $statusDisplay }}</span>
                            </div>
                            @if($incident->ai_label)
                                @php
                                    $aiLabelDisplay = match($incident->ai_label) {
                                        'SEWAGE' => 'Drainage-related',
                                        'NOT_SEWAGE' => 'Not drainage-related',
                                        'NEEDS_REVIEW' => 'Needs review',
                                        default => $incident->ai_label
                                    };
                                @endphp
                                <div class="mb-1"><strong>AI result:</strong><span class="badge {{ $incident->ai_label === 'SEWAGE' ? 'bg-danger' : 'bg-success' }} ms-2">{{ $aiLabelDisplay }}</span></div>
                            @endif
                            @if($incident->sewage_score !== null)<div class="mb-1"><strong>Relevance score:</strong><span class="ms-2">{{ number_format($incident->sewage_score * 100, 1) }}%</span></div>@endif
                            @if($incident->person_detected)<div class="mb-0"><strong>Person Detected:</strong><span class="badge bg-info ms-2">YES</span></div>@endif
                            @if($incident->analysis_error)<div class="mb-0 mt-2"><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>{{ Str::limit($incident->analysis_error, 50) }}</small></div>@endif
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('admin.incidents.review') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>Review</a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
