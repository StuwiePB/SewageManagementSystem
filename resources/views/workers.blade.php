<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workers Portal - Sewage Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 200px);
            min-height: 100vh;
            padding: 20px 16px 40px;
            color: #134e4a;
        }

        .workers-app {
            --worker-primary: #0d9488;
            --worker-primary-dark: #0f766e;
            --worker-surface: #f0fdfa;
            --worker-card: #ffffff;
            --worker-border: #99f6e4;
            --worker-muted: #5eead4;
            --worker-text: #134e4a;
            --worker-text-muted: #0f766e;
            --worker-shadow: 0 4px 20px rgba(13, 148, 136, 0.08);
            --worker-radius: 16px;
            --worker-radius-sm: 12px;
            max-width: 480px;
            margin: 0 auto;
        }

        .workers-app .topbar {
            margin-bottom: 24px;
            padding-top: 8px;
        }

        .workers-app .topbar h1 {
            font-size: 1.5rem;
            color: var(--worker-text);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .workers-app .topbar p {
            font-size: 0.8125rem;
            color: var(--worker-text-muted);
            margin: 2px 0 0;
            opacity: 0.9;
        }

        /* Main nav — 3 big buttons */
        .workers-nav {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 28px;
        }

        .workers-nav-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 24px;
            border-radius: var(--worker-radius-sm);
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            color: var(--worker-text);
            background: var(--worker-card);
            box-shadow: var(--worker-shadow);
            border: 1px solid var(--worker-border);
        }

        .workers-nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(13, 148, 136, 0.12);
        }

        .workers-nav-btn.active {
            background: linear-gradient(135deg, var(--worker-primary) 0%, var(--worker-primary-dark) 100%);
            color: #fff;
            border-color: var(--worker-primary-dark);
        }

        .workers-nav-btn svg {
            width: 22px;
            height: 22px;
            margin-right: 10px;
            opacity: 0.9;
        }

        .workers-nav-btn.active svg {
            opacity: 1;
        }

        /* Panels */
        .workers-panel {
            display: none;
            animation: workersFade 0.25s ease;
        }

        .workers-panel.is-active {
            display: block;
        }

        @keyframes workersFade {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .workers-panel-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--worker-text);
            margin: 0 0 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--worker-border);
        }

        /* Work Order panel */
        .workers-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .workers-filter {
            padding: 8px 14px;
            border-radius: 20px;
            border: 1px solid var(--worker-border);
            background: var(--worker-card);
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--worker-text-muted);
            cursor: pointer;
        }

        .workers-filter.active {
            background: var(--worker-primary);
            color: #fff;
            border-color: var(--worker-primary);
        }

        .workers-create-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--worker-radius-sm);
            background: var(--worker-primary);
            color: #fff;
            border: none;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            margin-bottom: 16px;
            box-shadow: 0 2px 10px rgba(13, 148, 136, 0.3);
            text-decoration: none;
        }

        .workers-create-btn:hover {
            background: var(--worker-primary-dark);
        }

        .workers-table-wrap {
            overflow-x: auto;
            border-radius: var(--worker-radius-sm);
            border: 1px solid var(--worker-border);
            background: var(--worker-card);
        }

        .workers-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .workers-table th {
            text-align: left;
            padding: 12px 10px;
            color: var(--worker-text-muted);
            font-weight: 600;
            background: var(--worker-surface);
            border-bottom: 1px solid var(--worker-border);
        }

        .workers-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0fdfa;
            color: var(--worker-text);
        }

        .workers-table tr:last-child td {
            border-bottom: none;
        }

        .workers-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .workers-badge--high { background: #fee2e2; color: #991b1b; }
        .workers-badge--medium { background: #fef3c7; color: #854d0e; }
        .workers-badge--low { background: #d1fae5; color: #065f46; }
        .workers-badge--pending { background: #e0e7ff; color: #3730a3; }
        .workers-badge--assigned { background: #dbeafe; color: #1e40af; }
        .workers-badge--in-progress { background: #fef3c7; color: #92400e; }
        .workers-badge--completed { background: #d1fae5; color: #065f46; }
        .workers-badge--cancelled { background: #f3f4f6; color: #6b7280; }

        .workers-action-link {
            color: var(--worker-primary);
            font-weight: 600;
            text-decoration: none;
            font-size: 0.8125rem;
        }

        /* Crew Management — cards */
        .workers-crew-grid {
            display: grid;
            gap: 16px;
        }

        .workers-crew-card {
            background: var(--worker-card);
            border-radius: var(--worker-radius-sm);
            padding: 16px;
            border: 1px solid var(--worker-border);
            box-shadow: var(--worker-shadow);
        }

        .workers-crew-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }

        .workers-crew-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--worker-muted) 0%, var(--worker-primary) 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .workers-crew-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .workers-crew-name {
            font-weight: 700;
            color: var(--worker-text);
            font-size: 1rem;
            margin: 0 0 2px;
        }

        .workers-crew-role {
            font-size: 0.8125rem;
            color: var(--worker-text-muted);
            margin: 0;
        }

        .workers-crew-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 8px;
        }

        .workers-crew-status--available { background: #d1fae5; color: #065f46; }
        .workers-crew-status--off { background: #f3f4f6; color: #6b7280; }

        .workers-crew-details {
            font-size: 0.8125rem;
            color: var(--worker-text-muted);
            margin-top: 10px;
            line-height: 1.5;
        }

        .workers-crew-details p {
            margin: 4px 0;
        }

        .workers-crew-details a {
            color: var(--worker-primary);
            text-decoration: none;
        }

        /* Medical Leave panel */
        .workers-form-group {
            margin-bottom: 20px;
        }

        .workers-form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--worker-text);
            margin-bottom: 8px;
        }

        .workers-form-group textarea {
            width: 100%;
            min-height: 120px;
            padding: 14px;
            border-radius: var(--worker-radius-sm);
            border: 1px solid var(--worker-border);
            font-family: inherit;
            font-size: 0.9375rem;
            resize: vertical;
            box-sizing: border-box;
        }

        .workers-form-group textarea:focus {
            outline: none;
            border-color: var(--worker-primary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }

        .workers-upload-zone {
            border: 2px dashed var(--worker-border);
            border-radius: var(--worker-radius-sm);
            padding: 32px 20px;
            text-align: center;
            background: var(--worker-surface);
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
        }

        .workers-upload-zone:hover,
        .workers-upload-zone.dragover {
            border-color: var(--worker-primary);
            background: rgba(13, 148, 136, 0.06);
        }

        .workers-upload-zone input {
            display: none;
        }

        .workers-upload-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 12px;
            background: var(--worker-muted);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--worker-primary-dark);
        }

        .workers-upload-icon svg {
            width: 28px;
            height: 28px;
        }

        .workers-upload-text {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--worker-text);
            margin: 0 0 4px;
        }

        .workers-upload-hint {
            font-size: 0.8125rem;
            color: var(--worker-text-muted);
            margin: 0;
        }

        .workers-submit {
            width: 100%;
            padding: 14px;
            border-radius: var(--worker-radius-sm);
            background: linear-gradient(135deg, var(--worker-primary) 0%, var(--worker-primary-dark) 100%);
            color: #fff;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35);
        }

        .workers-submit:hover {
            opacity: 0.95;
        }
    </style>
</head>
<body>

<div class="workers-app">
    <div class="topbar">
        <div>
            <h1>Workers</h1>
            <p>Mobile access portal</p>
        </div>
    </div>

    <nav class="workers-nav" role="tablist">
        <button type="button" class="workers-nav-btn active" data-panel="work-order" role="tab" aria-selected="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            Work Order
        </button>
        <button type="button" class="workers-nav-btn" data-panel="crew" role="tab" aria-selected="false">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            Crew Management
        </button>
        <button type="button" class="workers-nav-btn" data-panel="medical" role="tab" aria-selected="false">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            Medical Leave
        </button>
    </nav>

    {{-- Panel: Work Order --}}
    <section id="panel-work-order" class="workers-panel is-active" role="tabpanel" aria-labelledby="work-order">
        <h2 class="workers-panel-title">Work Orders</h2>
        <div class="workers-filters">
            <button type="button" class="workers-filter active">All Statuses</button>
            <button type="button" class="workers-filter">All Priorities</button>
        </div>
        <a href="{{ route('operations.work-orders.create') }}" class="workers-create-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create Work Order
        </a>
        <div class="workers-table-wrap">
            <table class="workers-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Crew</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Dummy data for presentation --}}
                    <tr>
                        <td>WO-1042</td>
                        <td>Maple St</td>
                        <td>Blockage</td>
                        <td><span class="workers-badge workers-badge--high">High</span></td>
                        <td>Crew A</td>
                        <td><span class="workers-badge workers-badge--pending">Pending</span></td>
                        <td><a href="{{ route('workers.work-order.show', 1) }}" class="workers-action-link">View</a></td>
                    </tr>
                    <tr>
                        <td>WO-1041</td>
                        <td>Oak Ave</td>
                        <td>Inspection</td>
                        <td><span class="workers-badge workers-badge--medium">Medium</span></td>
                        <td>Crew B</td>
                        <td><span class="workers-badge workers-badge--in-progress">In Progress</span></td>
                        <td><a href="{{ route('workers.work-order.show', 1) }}" class="workers-action-link">View</a></td>
                    </tr>
                    <tr>
                        <td>WO-1040</td>
                        <td>Pine Rd</td>
                        <td>Repair</td>
                        <td><span class="workers-badge workers-badge--low">Low</span></td>
                        <td>Crew A</td>
                        <td><span class="workers-badge workers-badge--completed">Completed</span></td>
                        <td><a href="{{ route('workers.work-order.show', 1) }}" class="workers-action-link">View</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Panel: Crew Management --}}
    <section id="panel-crew" class="workers-panel" role="tabpanel" aria-labelledby="crew" hidden>
        <h2 class="workers-panel-title">Crew Management</h2>
        <div class="workers-crew-grid">
            <article class="workers-crew-card">
                <div class="workers-crew-header">
                    <div class="workers-crew-avatar">BL</div>
                    <div>
                        <h3 class="workers-crew-name">Brianna Lee</h3>
                        <p class="workers-crew-role">Maintenance Specialist</p>
                    </div>
                </div>
                <span class="workers-crew-status workers-crew-status--available">Available</span>
                <div class="workers-crew-details">
                    <p>📞 (555) 201-3401</p>
                    <p><a href="mailto:brianna.lee@example.com">brianna.lee@example.com</a></p>
                    <p>Pump maintenance, valve repair</p>
                </div>
            </article>
            <article class="workers-crew-card">
                <div class="workers-crew-header">
                    <div class="workers-crew-avatar">CM</div>
                    <div>
                        <h3 class="workers-crew-name">Carlos Mendes</h3>
                        <p class="workers-crew-role">Emergency Responder</p>
                    </div>
                </div>
                <span class="workers-crew-status workers-crew-status--available">Available</span>
                <div class="workers-crew-details">
                    <p>📞 (555) 201-3402</p>
                    <p><a href="mailto:carlos.m@example.com">carlos.m@example.com</a></p>
                    <p>Overflow containment, hazard response</p>
                </div>
            </article>
            <article class="workers-crew-card">
                <div class="workers-crew-header">
                    <div class="workers-crew-avatar">DG</div>
                    <div>
                        <h3 class="workers-crew-name">Dana Gupta</h3>
                        <p class="workers-crew-role">Inspector</p>
                    </div>
                </div>
                <span class="workers-crew-status workers-crew-status--off">Off-duty</span>
                <div class="workers-crew-details">
                    <p>📞 (555) 201-3403</p>
                    <p><a href="mailto:dana.g@example.com">dana.g@example.com</a></p>
                    <p>Site inspection, reporting</p>
                </div>
            </article>
            <article class="workers-crew-card">
                <div class="workers-crew-header">
                    <div class="workers-crew-avatar">AR</div>
                    <div>
                        <h3 class="workers-crew-name">Alex Rivera</h3>
                        <p class="workers-crew-role">Field Technician</p>
                    </div>
                </div>
                <span class="workers-crew-status workers-crew-status--available">Available</span>
                <div class="workers-crew-details">
                    <p>📞 (555) 201-3404</p>
                    <p><a href="mailto:alex.r@example.com">alex.r@example.com</a></p>
                    <p>Blockage removal, CCTV inspection</p>
                </div>
            </article>
        </div>
    </section>

    {{-- Panel: Medical Leave --}}
    <section id="panel-medical" class="workers-panel" role="tabpanel" aria-labelledby="medical" hidden>
        <h2 class="workers-panel-title">Medical Leave</h2>
        <form class="workers-medical-form" action="#" method="post" enctype="multipart/form-data">
            @csrf
            <div class="workers-form-group">
                <label for="medical-details">Details</label>
                <textarea id="medical-details" name="details" placeholder="Describe your medical situation and reason for leave…" maxlength="2000"></textarea>
            </div>
            <div class="workers-form-group">
                <label>Take picture for proof</label>
                <label class="workers-upload-zone" for="medical-proof" id="upload-zone">
                    <input type="file" id="medical-proof" name="proof" accept="image/*" capture="environment">
                    <div class="workers-upload-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7" /></svg>
                    </div>
                    <p class="workers-upload-text">Tap to take photo or upload</p>
                    <p class="workers-upload-hint">Image or document</p>
                </label>
            </div>
            <button type="submit" class="workers-submit">Submit request</button>
        </form>
    </section>
</div>

<script>
(function () {
    var app = document.querySelector('.workers-app');
    if (!app) return;

    var navBtns = app.querySelectorAll('.workers-nav-btn');
    var panels = app.querySelectorAll('.workers-panel');

    function switchPanel(panelId) {
        navBtns.forEach(function (btn) {
            var isActive = btn.getAttribute('data-panel') === panelId;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive);
        });
        panels.forEach(function (panel) {
            var isActive = panel.id === 'panel-' + panelId;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });
    }

    navBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            switchPanel(btn.getAttribute('data-panel'));
        });
    });

    // Drag and drop for upload zone
    var zone = app.querySelector('#upload-zone');
    var input = app.querySelector('#medical-proof');
    if (zone && input) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (ev) {
            zone.addEventListener(ev, function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.toggle('dragover', ev === 'dragenter' || ev === 'dragover');
            });
        });
        zone.addEventListener('drop', function (e) {
            var files = e.dataTransfer.files;
            if (files.length) input.files = files;
        });
    }
})();
</script>

</body>
</html>
