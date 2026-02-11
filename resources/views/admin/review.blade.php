<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Review - Incident Management</title>
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
        
        .incident-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: var(--primary-blue);
            border-bottom: 3px solid var(--accent-teal);
            padding-bottom: 0.5rem;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        
        .incident-image {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        
        .ai-info-card {
            background-color: #f8f9fa;
            border-left: 4px solid var(--secondary-blue);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .confidence-bar {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        
        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--success-green), var(--accent-teal));
            transition: width 0.3s ease;
        }
        
        .badge-custom {
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
        
        .badge-uncertain {
            background-color: rgba(255, 190, 11, 0.1);
            color: #b38a00;
        }
        
        .evidence-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            margin: 0.25rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .evidence-badge.true {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-green);
        }
        
        .evidence-badge.false {
            background-color: rgba(108, 117, 125, 0.1);
            color: var(--text-light);
        }
        
        .btn-confirm {
            background-color: var(--success-green);
            border-color: var(--success-green);
            color: white;
        }
        
        .btn-confirm:hover {
            background-color: #238a7d;
            border-color: #238a7d;
            color: white;
        }
        
        .btn-reject {
            background-color: var(--alert-red);
            border-color: var(--alert-red);
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #c92a3a;
            border-color: #c92a3a;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--secondary-blue);
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-shield-alt me-2"></i>
                Incident Management System - Admin Review
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('incidents.create') }}">Upload Incident</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.incidents.review') }}">Review</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <h2 class="section-title">Incidents Requiring Review</h2>
        <p class="text-muted mb-4">Review incidents that need manual verification after AI analysis.</p>
        
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
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $incident->created_at->format('M d, Y H:i') }}
                                    @if($incident->user)
                                        | <i class="fas fa-user me-1"></i>{{ $incident->user->name }}
                                    @endif
                                </small>
                            </p>
                            
                            @if(Storage::disk('local')->exists($incident->photo_path))
                                <img src="{{ route('incidents.image', ['incident' => $incident->id]) }}" 
                                     alt="Incident Photo" 
                                     class="incident-image"
                                     onerror="this.onerror=null; this.outerHTML='<div class=\'alert alert-warning\'>Image not found</div>';">
                            @else
                                <div class="alert alert-warning">Image not found</div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="ai-info-card">
                                <h6 class="mb-3">
                                    <i class="fas fa-robot me-2"></i>AI Analysis Results
                                </h6>
                                
                                <div class="mb-3">
                                    <strong>Label:</strong>
                                    @php
                                        $labelClass = match($incident->ai_label) {
                                            'SEWAGE' => 'badge-sewage',
                                            'NOT_SEWAGE' => 'badge-not-sewage',
                                            'UNCERTAIN' => 'badge-uncertain',
                                            default => 'badge-not-sewage'
                                        };
                                    @endphp
                                    <span class="badge badge-custom {{ $labelClass }}">
                                        {{ $incident->ai_label ?? 'N/A' }}
                                    </span>
                                </div>
                                
                                @if($incident->has_person && $incident->person_confidence)
                                    <div class="mb-3 alert alert-warning">
                                        <strong>Person Detected:</strong> {{ number_format($incident->person_confidence * 100, 1) }}% confidence
                                        @if($incident->person_reason)
                                            <br><small>{{ $incident->person_reason }}</small>
                                        @endif
                                    </div>
                                @endif
                                
                                @if($incident->proof)
                                    <div class="mb-3">
                                        <strong>Proof Details:</strong>
                                        <div class="mt-2 small">
                                            <div><strong>Source Object:</strong> {{ ucfirst($incident->proof['source_object'] ?? 'none') }}</div>
                                            @if($incident->proof['source_object'] !== 'none')
                                                <div><strong>Location:</strong> {{ ucfirst($incident->proof['source_location'] ?? 'unknown') }}</div>
                                                <div><strong>Discharge:</strong> {{ ucfirst($incident->proof['discharge_description'] ?? 'none') }}</div>
                                                <div><strong>Water Color:</strong> {{ ucfirst($incident->proof['water_color'] ?? 'unknown') }}</div>
                                                <div>
                                                    <strong>Indicators:</strong>
                                                    @if($incident->proof['foam_present']) <span class="badge bg-info">Foam</span> @endif
                                                    @if($incident->proof['debris_present']) <span class="badge bg-warning">Debris</span> @endif
                                                </div>
                                            @else
                                                <div class="text-danger"><strong>No source object detected</strong></div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                @if($incident->evidence_count !== null)
                                    <div class="mb-3">
                                        <strong>Evidence Count:</strong> {{ $incident->evidence_count }}/4
                                        @if($incident->evidence)
                                            <div class="mt-2">
                                                <small class="d-block mb-1"><strong>Evidence Details:</strong></small>
                                                <span class="evidence-badge {{ $incident->evidence['source_visible'] ?? false ? 'true' : 'false' }}">
                                                    Source Visible: {{ $incident->evidence['source_visible'] ?? false ? 'Yes' : 'No' }}
                                                </span>
                                                <span class="evidence-badge {{ $incident->evidence['active_discharge'] ?? false ? 'true' : 'false' }}">
                                                    Active Discharge: {{ $incident->evidence['active_discharge'] ?? false ? 'Yes' : 'No' }}
                                                </span>
                                                <span class="evidence-badge {{ $incident->evidence['contamination_cues'] ?? false ? 'true' : 'false' }}">
                                                    Contamination: {{ $incident->evidence['contamination_cues'] ?? false ? 'Yes' : 'No' }}
                                                </span>
                                                <span class="evidence-badge {{ $incident->evidence['sewer_context'] ?? false ? 'true' : 'false' }}">
                                                    Sewer Context: {{ $incident->evidence['sewer_context'] ?? false ? 'Yes' : 'No' }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                
                                <div class="mb-3">
                                    <strong>Confidence:</strong>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ number_format(($incident->ai_confidence ?? 0) * 100, 1) }}%</span>
                                        <div class="confidence-bar flex-grow-1">
                                            <div class="confidence-fill" 
                                                 style="width: {{ ($incident->ai_confidence ?? 0) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Severity:</strong>
                                    <span class="badge bg-warning text-dark ms-2">
                                        {{ ucfirst($incident->ai_severity ?? 'N/A') }}
                                    </span>
                                </div>
                                
                                @if($incident->ai_reasons)
                                    <div class="mb-3">
                                        <strong>AI Reasons:</strong>
                                        <ul class="mt-2 mb-0">
                                            @foreach($incident->ai_reasons as $reason)
                                                <li>{{ $reason }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-confirm" 
                                        onclick="reviewIncident({{ $incident->id }}, 'confirm_sewage')">
                                    <i class="fas fa-check me-2"></i>Confirm Sewage
                                </button>
                                <button class="btn btn-reject" 
                                        onclick="reviewIncident({{ $incident->id }}, 'reject_sewage')">
                                    <i class="fas fa-times me-2"></i>Not Sewage
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
        
        <!-- Alert Container -->
        <div id="alertContainer"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function reviewIncident(incidentId, action) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            if (!confirm(`Are you sure you want to ${action === 'confirm_sewage' ? 'confirm this as sewage' : 'mark this as not sewage'}?`)) {
                return;
            }
            
            fetch(`/admin/incidents/${incidentId}/review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ action: action })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the incident card
                    const card = document.getElementById(`incident-${incidentId}`);
                    if (card) {
                        card.style.transition = 'opacity 0.3s';
                        card.style.opacity = '0';
                        setTimeout(() => card.remove(), 300);
                    }
                    
                    showAlert('Incident review status updated successfully', 'success');
                    
                    // Check if no more incidents
                    setTimeout(() => {
                        if (document.querySelectorAll('.incident-card').length === 1) {
                            location.reload();
                        }
                    }, 500);
                } else {
                    showAlert(data.message || 'Failed to update incident', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Network error. Please try again.', 'danger');
            });
        }
        
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            
            setTimeout(() => alert.remove(), 5000);
        }
    </script>
</body>
</html>
