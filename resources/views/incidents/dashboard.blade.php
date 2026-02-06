<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidents Dashboard - Risk Priority</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0056a6;
            --secondary-blue: #0077cc;
            --accent-teal: #00a896;
            --alert-red: #e63946;
            --warning-yellow: #ffbe0b;
            --success-green: #2a9d8f;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --text-light: #6c757d;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f8ff;
            color: var(--dark-gray);
            line-height: 1.6;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            box-shadow: 0 4px 12px rgba(0, 87, 166, 0.15);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .priority-high .stats-number {
            color: var(--alert-red);
        }
        
        .priority-medium .stats-number {
            color: var(--warning-yellow);
        }
        
        .priority-low .stats-number {
            color: var(--success-green);
        }
        
        .incident-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dee2e6;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .incident-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .incident-card.priority-high {
            border-left-color: var(--alert-red);
        }
        
        .incident-card.priority-medium {
            border-left-color: var(--warning-yellow);
        }
        
        .incident-card.priority-low {
            border-left-color: var(--success-green);
        }
        
        .priority-badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        .priority-badge.high {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--alert-red);
        }
        
        .priority-badge.medium {
            background-color: rgba(255, 190, 11, 0.1);
            color: #b38a00;
        }
        
        .priority-badge.low {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-green);
        }
        
        .risk-score {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .risk-score.high {
            color: var(--alert-red);
        }
        
        .risk-score.medium {
            color: var(--warning-yellow);
        }
        
        .risk-score.low {
            color: var(--success-green);
        }
        
        .section-title {
            color: var(--primary-blue);
            border-bottom: 3px solid var(--accent-teal);
            padding-bottom: 0.5rem;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        
        .thumbnail-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-chart-line me-2"></i>
                Incidents Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('incidents.create') }}">Upload Incident</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.incidents.review') }}">Review</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('incidents.dashboard') }}">Dashboard</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <h2 class="section-title">Incident Risk Priority Dashboard</h2>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card priority-high">
                    <div class="stats-number">{{ $highPriority->count() }}</div>
                    <div class="stats-label">High Priority Incidents</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card priority-medium">
                    <div class="stats-number">{{ $mediumPriority->count() }}</div>
                    <div class="stats-label">Medium Priority Incidents</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card priority-low">
                    <div class="stats-number">{{ $lowPriority->count() }}</div>
                    <div class="stats-label">Low Priority Incidents</div>
                </div>
            </div>
        </div>

        <!-- Incidents List (sorted by risk score) -->
        <h3 class="mt-4 mb-3">All Incidents (Sorted by Risk Score)</h3>
        
        @if($incidents->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No incidents with risk scores yet. Upload incidents to see them here.
            </div>
        @else
            @foreach($incidents as $incident)
                @php
                    $priority = \App\Http\Controllers\IncidentController::getPriorityLevel($incident->risk_score);
                    $priorityClass = strtolower($priority);
                @endphp
                <div class="incident-card priority-{{ $priorityClass }}">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            @if(Storage::disk('local')->exists($incident->photo_path))
                                <img src="{{ route('incidents.image', ['incident' => $incident->id]) }}" 
                                     alt="Incident #{{ $incident->id }}" 
                                     class="thumbnail-img"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'thumbnail-img bg-light d-flex align-items-center justify-content-center\'><i class=\'fas fa-image text-muted\'></i></div>';">
                            @else
                                <div class="thumbnail-img bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image text-muted"></i>
                                    <small class="d-block mt-1 text-muted">Not found</small>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-3">
                            <h5 class="mb-1">Incident #{{ $incident->id }}</h5>
                            <p class="text-muted mb-0 small">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $incident->created_at->format('M d, Y H:i') }}
                            </p>
                            @if($incident->user)
                                <p class="text-muted mb-0 small">
                                    <i class="fas fa-user me-1"></i>{{ $incident->user->name }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="col-md-2">
                            <div class="mb-2">
                                <strong>Risk Score:</strong>
                                <div class="risk-score {{ $priorityClass }}">{{ $incident->risk_score ?? 'N/A' }}</div>
                            </div>
                            <span class="priority-badge {{ $priorityClass }}">
                                {{ $priority }} PRIORITY
                            </span>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-1">
                                <strong>Status:</strong>
                                @php
                                    $statusBadgeClass = match($incident->review_status) {
                                        'NOT_SEWAGE', 'NOT_SEWAGE_CONFIRMED' => 'bg-success',
                                        'NEEDS_REVIEW', 'UNCERTAIN' => 'bg-warning text-dark',
                                        'SEWAGE', 'SEWAGE_CONFIRMED' => 'bg-danger',
                                        'PENDING_AI' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                    $statusDisplay = match($incident->review_status) {
                                        'NOT_SEWAGE' => 'NOT SEWAGE',
                                        'NOT_SEWAGE_CONFIRMED' => 'NOT SEWAGE CONFIRMED',
                                        'NEEDS_REVIEW' => 'NEEDS REVIEW',
                                        'SEWAGE_CONFIRMED' => 'SEWAGE CONFIRMED',
                                        default => $incident->review_status
                                    };
                                @endphp
                                <span class="badge {{ $statusBadgeClass }} ms-2">{{ $statusDisplay }}</span>
                            </div>
                            @if($incident->ai_label)
                                <div class="mb-1">
                                    <strong>AI Label:</strong>
                                    @php
                                        $labelBadgeClass = match($incident->ai_label) {
                                            'SEWAGE' => 'bg-danger',
                                            'NOT_SEWAGE' => 'bg-success',
                                            'UNCERTAIN' => 'bg-warning text-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $labelBadgeClass }} ms-2">
                                        {{ $incident->ai_label }}
                                    </span>
                                </div>
                            @endif
                            @if($incident->sewage_score !== null)
                                <div class="mb-1">
                                    <strong>Sewage Score:</strong>
                                    <span class="ms-2">{{ number_format($incident->sewage_score * 100, 1) }}%</span>
                                </div>
                            @endif
                            @if($incident->sewage_indicators && count($incident->sewage_indicators) > 0)
                                <div class="mb-1">
                                    <strong>Indicators:</strong>
                                    <span class="ms-2">{{ implode(', ', array_slice($incident->sewage_indicators, 0, 3)) }}{{ count($incident->sewage_indicators) > 3 ? '...' : '' }}</span>
                                </div>
                            @endif
                            @if($incident->person_detected)
                                <div class="mb-0">
                                    <strong>Person Detected:</strong>
                                    <span class="badge bg-info ms-2">YES</span>
                                </div>
                            @endif
                            @if($incident->ai_confidence)
                                <div class="mb-0">
                                    <strong>Confidence:</strong>
                                    <span class="ms-2">{{ number_format($incident->ai_confidence * 100, 1) }}%</span>
                                </div>
                            @endif
                            @if($incident->analysis_error)
                                <div class="mb-0 mt-2">
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Error: {{ Str::limit($incident->analysis_error, 50) }}
                                    </small>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-2 text-end">
                            <a href="{{ route('admin.incidents.review') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Review
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
