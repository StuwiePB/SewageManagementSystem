<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order {{ $workOrder->work_order_number }} - Workers Portal</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            padding: 20px 16px 40px;
            color: #1f2937;
        }

        .wo-container {
            max-width: 480px;
            margin: 0 auto;
        }

        .wo-header {
            margin-bottom: 24px;
        }

        .wo-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            margin-bottom: 12px;
            font-weight: 500;
        }

        .wo-back:hover {
            color: #0d9488;
        }

        .wo-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .wo-section {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .wo-section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .wo-info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (min-width: 640px) {
            .wo-info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .wo-field {
            margin-bottom: 16px;
        }

        .wo-field:last-child {
            margin-bottom: 0;
        }

        .wo-label {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .wo-value {
            font-size: 0.9375rem;
            color: #1f2937;
            font-weight: 500;
            margin-top: 4px;
        }

        .wo-value-text {
            font-size: 0.875rem;
            color: #374151;
            line-height: 1.6;
        }

        .wo-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 4px;
        }

        .wo-badge--critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .wo-badge--high {
            background: #fed7aa;
            color: #9a3412;
        }

        .wo-badge--medium {
            background: #fef3c7;
            color: #854d0e;
        }

        .wo-badge--low {
            background: #d1fae5;
            color: #065f46;
        }

        .wo-location {
            margin-top: 4px;
        }

        .wo-location-line {
            font-size: 0.9375rem;
            color: #1f2937;
            margin: 2px 0;
        }

        .wo-location-meta {
            font-size: 0.8125rem;
            color: #6b7280;
            margin: 2px 0;
        }

        .wo-location-coords {
            font-size: 0.75rem;
            color: #9ca3af;
            margin: 2px 0;
        }

        .wo-map-container {
            width: 100%;
            height: 220px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            margin-top: 12px;
            overflow: hidden;
        }
        #wo-map { width: 100%; height: 100%; min-height: 220px; }

        .wo-map-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: #f3f4f6;
            border-radius: 20px;
            color: #374151;
            text-decoration: none;
            font-size: 0.8125rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .wo-map-link:hover {
            background: #e5e7eb;
            color: #0d9488;
        }

        .wo-map-link svg {
            width: 16px;
            height: 16px;
        }

        .wo-timeline {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .wo-timeline-item {
            display: flex;
            flex-direction: column;
        }

        .wo-timeline-value {
            font-size: 0.875rem;
            color: #1f2937;
            margin-top: 4px;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .wo-timeline {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
    </style>
</head>
<body>

<div class="wo-container">
    <div class="wo-header">
        <a href="{{ route('workers') }}" class="wo-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Workers
        </a>
        <h1 class="wo-title">{{ $workOrder->work_order_number }}</h1>
    </div>

    <div class="wo-section">
        <h2 class="wo-section-title">Work Order Information</h2>
        
        <div class="wo-info-grid">
            <div>
                <div class="wo-field">
                    <span class="wo-label">Type</span>
                    <div class="wo-value">{{ $workOrder->type }}</div>
                </div>

                <div class="wo-field">
                    <span class="wo-label">Priority</span>
                    <span class="wo-badge wo-badge--{{ $workOrder->priority }}">
                        {{ ucfirst($workOrder->priority) }}
                    </span>
                </div>

                <div class="wo-field">
                    <span class="wo-label">Location</span>
                    <div class="wo-location">
                        <div class="wo-location-line">{{ $workOrder->location_address }}</div>
                        @if($workOrder->district_display || $workOrder->mukim_display)
                            <div class="wo-location-meta">
                                {{ $workOrder->district_display }}{{ $workOrder->mukim_display ? ' • ' . $workOrder->mukim_display : '' }}
                            </div>
                        @endif
                        @if($workOrder->latitude && $workOrder->longitude)
                            <div class="wo-location-coords">
                                {{ number_format($workOrder->latitude, 4) }}, {{ number_format($workOrder->longitude, 4) }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($workOrder->description)
                <div class="wo-field">
                    <span class="wo-label">Description</span>
                    <div class="wo-value-text">{{ $workOrder->description }}</div>
                </div>
                @endif
            </div>

            <div>
                <div class="wo-field">
                    <span class="wo-label">Map</span>
                    <div class="wo-map-container">
                        @if($workOrder->latitude && $workOrder->longitude)
                            <div id="wo-map"></div>
                            <a href="https://www.google.com/maps?q={{ $workOrder->latitude }},{{ $workOrder->longitude }}" 
                               target="_blank" 
                               rel="noopener"
                               class="wo-map-link"
                               style="display: inline-flex; margin-top: 10px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Open in Google Maps
                            </a>
                        @else
                            <a href="https://www.google.com/maps/search/{{ urlencode($workOrder->location_address ?? '') }}" 
                               target="_blank" 
                               rel="noopener"
                               class="wo-map-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Open in Google Maps for the location
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wo-section">
        <h2 class="wo-section-title">Timeline</h2>
        <div class="wo-timeline">
            <div class="wo-timeline-item">
                <span class="wo-label">Created</span>
                <div class="wo-timeline-value">{{ $workOrder->created_at?->format('M d, Y g:i A') ?? '—' }}</div>
            </div>
            @if($workOrder->assigned_at)
            <div class="wo-timeline-item">
                <span class="wo-label">Assigned</span>
                <div class="wo-timeline-value">{{ $workOrder->assigned_at?->format('M d, Y g:i A') ?? '—' }}</div>
            </div>
            @else
            <div class="wo-timeline-item">
                <span class="wo-label">Assigned</span>
                <div class="wo-timeline-value" style="color: #9ca3af;">Not assigned</div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($workOrder->latitude && $workOrder->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var lat = {{ (float) $workOrder->latitude }};
    var lng = {{ (float) $workOrder->longitude }};
    var map = L.map('wo-map').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    L.marker([lat, lng]).addTo(map)
        .bindPopup('{{ addslashes($workOrder->location_address ?? $workOrder->work_order_number) }}')
        .openPopup();
})();
</script>
@endif

</body>
</html>
