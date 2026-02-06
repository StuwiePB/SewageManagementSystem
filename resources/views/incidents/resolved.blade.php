<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resolved Incidents</title>
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
        
        .sewage-confirmed .stats-number {
            color: var(--alert-red);
        }
        
        .not-sewage .stats-number {
            color: var(--success-green);
        }
        
        .incident-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dee2e6;
        }
        
        .incident-card.sewage-confirmed {
            border-left-color: var(--alert-red);
        }
        
        .incident-card.not-sewage {
            border-left-color: var(--success-green);
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
        
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .badge-sewage {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--alert-red);
        }
        
        .badge-not-sewage {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-green);
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
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-check-circle me-2"></i>
                Resolved Incidents
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
                        <a class="nav-link" href="{{ route('incidents.create') }}">Upload</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.incidents.review') }}">Review</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('incidents.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('incidents.resolved') }}">Resolved</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <h2 class="section-title">Resolved Incidents</h2>
        <p class="text-muted mb-4">All incidents that have been reviewed and resolved.</p>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="stats-card sewage-confirmed">
                    <div class="stats-number">{{ $sewageConfirmed->count() }}</div>
                    <div class="stats-label">Confirmed Sewage Incidents</div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="stats-card not-sewage">
                    <div class="stats-number">{{ $notSewage->count() }}</div>
                    <div class="stats-label">False Alarms (Not Sewage)</div>
                </div>
            </div>
        </div>

        <!-- Resolved Incidents List -->
        @if($incidents->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No resolved incidents yet. Review incidents to resolve them.
            </div>
        @else
            @foreach($incidents as $incident)
                @php
                    $statusClass = match($incident->review_status) {
                        'SEWAGE_CONFIRMED' => 'sewage-confirmed',
                        'NOT_SEWAGE_CONFIRMED' => 'not-sewage',
                        default => 'not-sewage'
                    };
                    $badgeClass = match($incident->review_status) {
                        'SEWAGE_CONFIRMED' => 'badge-sewage',
                        'NOT_SEWAGE_CONFIRMED' => 'badge-not-sewage',
                        default => 'badge-not-sewage'
                    };
                @endphp
                <div class="incident-card {{ $statusClass }}">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            @if(Storage::disk('local')->exists($incident->photo_path))
                                <img src="{{ route('incidents.image', ['incident' => $incident->id]) }}" 
                                     alt="Incident #{{ $incident->id }}" 
                                     class="thumbnail-img">
                            @else
                                <div class="thumbnail-img bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-3">
                            <h5 class="mb-1">Incident #{{ $incident->id }}</h5>
                            <p class="text-muted mb-0 small">
                                <i class="fas fa-calendar me-1"></i>
                                Created: {{ $incident->created_at->format('M d, Y H:i') }}
                            </p>
                            <p class="text-muted mb-0 small">
                                <i class="fas fa-check-circle me-1"></i>
                                Resolved: {{ $incident->updated_at->format('M d, Y H:i') }}
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
                                <div class="fs-4 fw-bold">{{ $incident->risk_score ?? 'N/A' }}</div>
                            </div>
                            @if($incident->risk_score)
                                <span class="priority-badge {{ strtolower(\App\Http\Controllers\IncidentController::getPriorityLevel($incident->risk_score)) }}">
                                    {{ \App\Http\Controllers\IncidentController::getPriorityLevel($incident->risk_score) }} PRIORITY
                                </span>
                            @endif
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-2">
                                <strong>Final Status:</strong>
                                <span class="badge-status {{ $badgeClass }} ms-2">
                                    {{ match($incident->review_status) {
                                        'SEWAGE_CONFIRMED' => 'SEWAGE CONFIRMED',
                                        'NOT_SEWAGE_CONFIRMED' => 'NOT SEWAGE',
                                        default => $incident->review_status
                                    } }}
                                </span>
                            </div>
                            @if($incident->ai_label)
                                <div class="mb-1">
                                    <strong>AI Label:</strong>
                                    <span class="badge {{ $incident->ai_label === 'SEWAGE' ? 'bg-danger' : 'bg-success' }} ms-2">
                                        {{ $incident->ai_label }}
                                    </span>
                                </div>
                            @endif
                            @if($incident->ai_confidence)
                                <div class="mb-0">
                                    <strong>Confidence:</strong>
                                    <span class="ms-2">{{ number_format($incident->ai_confidence * 100, 1) }}%</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-2 text-end">
                            <a href="{{ route('incidents.dashboard') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>View Details
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
