<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewage Issue Reporting – Brunei Darussalam</title>
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
        
        .hero-section {
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7)), 
                        url('https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            padding: 4rem 0;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
        }
        
        .hero-title {
            color: var(--primary-blue);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 2rem;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .report-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2.5rem;
            border: none;
            transition: transform 0.3s ease;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
        }
        
        .section-title {
            color: var(--primary-blue);
            border-bottom: 3px solid var(--accent-teal);
            padding-bottom: 0.5rem;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 0.25rem rgba(0, 119, 204, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 119, 204, 0.25);
        }
        
        .btn-secondary {
            background-color: var(--accent-teal);
            border-color: var(--accent-teal);
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: #008f7a;
            border-color: #008f7a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 168, 150, 0.25);
        }
        
        .map-container {
            height: 300px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            background-color: #e9ecef;
            position: relative;
        }
        
        .map-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-light);
        }
        
        .map-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary-blue);
        }
        
        .status-card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--accent-teal);
            margin-bottom: 1.5rem;
        }
        
        .status-card.resolved {
            border-left-color: var(--success-green);
        }
        
        .status-card.in-progress {
            border-left-color: var(--warning-yellow);
        }
        
        .status-card.received {
            border-left-color: var(--secondary-blue);
        }
        
        .status-badge {
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .badge-received {
            background-color: rgba(0, 119, 204, 0.1);
            color: var(--secondary-blue);
        }
        
        .badge-in-progress {
            background-color: rgba(255, 190, 11, 0.1);
            color: #b38a00;
        }
        
        .badge-resolved {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-green);
        }
        
        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(0, 119, 204, 0.1);
            color: var(--secondary-blue);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 1.5rem;
            background-color: white;
            border-radius: 10px;
            height: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-title {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .info-box {
            background-color: rgba(0, 168, 150, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            border-left: 4px solid var(--accent-teal);
            margin-top: 2rem;
        }
        
        .footer {
            background-color: var(--dark-gray);
            color: white;
            padding: 2.5rem 0;
            margin-top: 4rem;
        }
        
        .footer-title {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .copyright {
            text-align: center;
            padding-top: 1.5rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        
        .progress-tracker {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 2rem;
            counter-reset: step;
        }
        
        .progress-tracker::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            z-index: 1;
        }
        
        .progress-step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        
        .progress-step::before {
            counter-increment: step;
            content: counter(step);
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: var(--text-light);
            display: block;
            margin: 0 auto 0.5rem;
            font-weight: 600;
        }
        
        .progress-step.active::before {
            background-color: var(--accent-teal);
            color: white;
        }
        
        .progress-step.completed::before {
            background-color: var(--success-green);
            color: white;
        }
        
        .step-title {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .progress-step.active .step-title {
            color: var(--accent-teal);
            font-weight: 600;
        }
        
        .progress-step.completed .step-title {
            color: var(--success-green);
        }
        
        .report-summary {
            background-color: rgba(0, 119, 204, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 4px solid var(--secondary-blue);
        }
        
        .photo-preview {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .photo-preview:hover {
            border-color: var(--secondary-blue);
            background-color: rgba(0, 119, 204, 0.02);
        }
        
        .photo-preview i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-blue);
        }
        
        /* AI Chatbot section (React-style) */
        #chatbot .chatbot-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        #chatbot .chatbot-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: #fff;
            padding: 1rem 1.25rem;
        }
        #chatbot .chatbot-header h3 { font-size: 1.1rem; margin: 0 0 0.2rem 0; font-weight: 600; }
        #chatbot .chatbot-header p { margin: 0; font-size: 0.85rem; opacity: 0.9; }
        #chatbot .chatbot-messages {
            height: 24rem;
            overflow-y: auto;
            padding: 1rem 1.25rem;
            background: #f9fafb;
        }
        #chatbot .chat-msg { margin-bottom: 0.75rem; display: flex; }
        #chatbot .chat-msg.user { justify-content: flex-end; }
        #chatbot .chat-msg.bot { justify-content: flex-start; }
        #chatbot .chat-bubble {
            max-width: 75%;
            padding: 0.6rem 0.9rem;
            border-radius: 1.25rem;
            font-size: 0.95rem;
        }
        #chatbot .chat-bubble.user {
            background: var(--secondary-blue);
            color: #fff;
            border-bottom-right-radius: 0.35rem;
        }
        #chatbot .chat-bubble.bot {
            background: #fff;
            color: #1f2937;
            border-bottom-left-radius: 0.35rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        #chatbot .chatbot-footer {
            border-top: 1px solid #e5e7eb;
            background: #fff;
            padding: 0.75rem 1rem 1rem;
        }
        #chatbot .chatbot-footer .form-control {
            border-radius: 0.5rem;
            padding: 0.6rem 0.85rem;
        }
        #chatbot .typing-dots span {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #9ca3af;
            margin: 0 2px;
            animation: typingBounce 1s infinite;
        }
        #chatbot .typing-dots span:nth-child(2) { animation-delay: 0.15s; }
        #chatbot .typing-dots span:nth-child(3) { animation-delay: 0.3s; }
        @keyframes typingBounce {
            0%, 100% { transform: translateY(0); opacity: 0.6; }
            50% { transform: translateY(-4px); opacity: 1; }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 2.5rem 0;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .report-card {
                padding: 1.5rem;
            }
            
            .progress-tracker {
                font-size: 0.8rem;
            }
            
            .progress-step::before {
                width: 25px;
                height: 25px;
                line-height: 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-water me-2"></i>
                Aqua Guard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#report">Report Issue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#status">Check Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#information">Information</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#chatbot">AI Assistant</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('operations.dashboard') }}">Operations</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">Report Sewage Issues in Our Community</h1>
            <p class="hero-subtitle">
                Help us keep our sewage system running smoothly. Report blockages, overflows, or maintenance issues quickly and easily. 
                Your reports help us respond faster and protect our environment.
            </p>
            <a href="{{ route('submitreport') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus-circle me-2"></i> Report a New Issue
            </a>
            <a href="{{ route('checkreportstatus') }}" class="btn btn-secondary btn-lg ms-2">
                <i class="fas fa-search me-2"></i> Check Report Status
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-container">
        <div class="container">
            <!-- AI Chatbot Section -->
            <section id="chatbot" class="mb-5">
                <h2 class="section-title">
                    <i class="fas fa-comments me-2"></i>Ask Our AI Assistant
                </h2>
                <div class="chatbot-card">
                    <div class="chatbot-header">
                        <h3>Sewage System AI Assistant</h3>
                        <p>Get instant answers about sewage issues, reporting, and more</p>
                    </div>
                    <div id="chatbotMessages" class="chatbot-messages">
                        <div class="chat-msg bot">
                            <div class="chat-bubble bot">Hello! I'm here to help you with sewage-related questions. How can I assist you today?</div>
                        </div>
                    </div>
                    <div class="chatbot-footer">
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" id="chatbotInput" class="form-control flex-grow-1" placeholder="Ask about reporting, status checks, emergencies..." />
                            <button type="button" id="chatbotSend" class="btn btn-primary" style="background-color: var(--primary-blue); border-color: var(--primary-blue);">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <p class="text-muted small mb-0">Try asking: "How do I report an issue?" or "What's the emergency number?"</p>
                    </div>
                </div>
            </section>

            <!-- Information Section -->
            <section id="information" class="mb-5">
                <h2 class="section-title">How It Works</h2>
                
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-flag"></i>
                            </div>
                            <h4 class="feature-title">1. Report Issue</h4>
                            <p>Use our simple form to report sewage issues in Brunei Darussalam. Provide details, district, mukim, and location.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h4 class="feature-title">2. We Assess & Prioritize</h4>
                            <p>Our team reviews reports, assesses severity, and prioritizes based on urgency and public safety.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h4 class="feature-title">3. Dispatch & Resolve</h4>
                            <p>Maintenance crews are dispatched to resolve issues. We work efficiently to minimize disruptions.</p>
                        </div>
                    </div>
                </div>
                
                <div class="info-box">
                    <h4><i class="fas fa-lightbulb me-2"></i> What to Report</h4>
                    <ul class="mb-0">
                        <li><strong>Sewage blockages</strong> - Water backing up from drains or toilets</li>
                        <li><strong>Sewage overflows</strong> - Sewage coming from manholes or drains onto streets</li>
                        <li><strong>Strong sewage odors</strong> - Persistent bad smells from drains or sewer areas</li>
                        <li><strong>Damaged sewer infrastructure</strong> - Broken manhole covers, exposed pipes</li>
                        <li><strong>Illegal dumping into sewers</strong> - Witnessed dumping of hazardous materials</li>
                    </ul>
                </div>
            </section>
            
            <!-- Contact Section -->
            <section id="contact" class="mb-5">
                <h2 class="section-title">Contact Public Works</h2>
                
                <div class="report-card">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h5><i class="fas fa-phone-alt me-2"></i> Emergency Contact</h5>
                            <p>For sewage emergencies that pose immediate health or environmental risks:</p>
                            <div class="alert alert-warning">
                                <h6 class="alert-heading mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Emergency Hotline</h6>
                                <h4 class="mb-0">(555) 123-EMER (3637)</h4>
                                <p class="mb-0 small">Available 24/7 for urgent sewage emergencies</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <h5><i class="fas fa-envelope me-2"></i> General Inquiries</h5>
                            <p>For non-emergency questions about the sewage system:</p>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-inbox me-2"></i> Email: Public Works, Brunei Darussalam</li>
                                <li class="mb-2"><i class="fas fa-phone me-2"></i> Phone: (555) 123-4567 (Mon-Fri, 8AM-5PM)</li>
                                <li><i class="fas fa-map-marker-alt me-2"></i> Address: 123 Public Works Blvd, City, State 12345</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">Sewage Monitoring System</h5>
                    <p>A public service portal for reporting and tracking sewage system issues in Brunei Darussalam. Together, we can maintain a clean and functional sewage infrastructure.</p>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <div class="footer-links">
                        <a href="#report">Report Issue</a>
                        <a href="#status">Check Status</a>
                        <a href="#information">Information</a>
                        <a href="#contact">Contact</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Resources</h5>
                    <div class="footer-links">
                        <a href="#">Sewage System Maintenance Tips</a>
                        <a href="#">Environmental Impact Information</a>
                        <a href="#">Public Works Department</a>
                        <a href="#">Public Services</a>
                    </div>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h5 class="footer-title">Stay Informed</h5>
                    <p>Subscribe to receive updates about sewage system maintenance and alerts in your area.</p>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Your email address">
                        <button class="btn btn-primary" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p class="mb-0">&copy; {{ date('Y') }} Public Works Department, Brunei Darussalam. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission handler (only on pages that have reportForm)
        var reportForm = document.getElementById('reportForm');
        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var summary = document.getElementById('reportSummary');
                if (summary) { summary.style.display = 'block'; summary.scrollIntoView({ behavior: 'smooth' }); }
                var steps = document.querySelectorAll('.progress-step');
                steps.forEach(function(step) { step.classList.remove('active', 'completed'); });
                if (steps[0]) steps[0].classList.add('completed');
                if (steps[1]) steps[1].classList.add('completed');
                if (steps[2]) steps[2].classList.add('active');
            });
        }

        var photoUpload = document.getElementById('photoUpload');
        var photoInput = document.getElementById('photoInput');
        if (photoUpload && photoInput) {
            photoUpload.addEventListener('click', function() { photoInput.click(); });
            photoInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    photoUpload.innerHTML = '<i class="fas fa-check-circle text-success"></i><p>Photo uploaded: ' + e.target.files[0].name + '</p><small>Click to change photo</small>';
                }
            });
        }

        var trackButton = document.getElementById('trackButton');
        if (trackButton) {
            trackButton.addEventListener('click', function() {
                var trackingNumber = (document.getElementById('trackingNumber') || {}).value || '';
                if (trackingNumber.trim() === '') { alert('Please enter a report tracking number'); return; }
                var statusDisplay = document.getElementById('statusDisplay');
                if (statusDisplay) { statusDisplay.style.display = 'block'; statusDisplay.scrollIntoView({ behavior: 'smooth' }); }
            });
        }

        var formInputs = document.querySelectorAll('#reportForm input, #reportForm select, #reportForm textarea');
        formInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                var first = document.querySelectorAll('.progress-step')[0];
                if (first) first.classList.add('completed');
            });
        });
        
        // Address field special handling for location step
        var addressEl = document.getElementById('address');
        if (addressEl) {
            addressEl.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    var steps = document.querySelectorAll('.progress-step');
                    if (steps[1]) steps[1].classList.add('completed');
                }
            });
        }

        // AI Chatbot (React-style inline assistant)
        (function() {
            var messagesEl = document.getElementById('chatbotMessages');
            var inputEl = document.getElementById('chatbotInput');
            var sendBtn = document.getElementById('chatbotSend');
            if (!messagesEl || !inputEl || !sendBtn) return;

            function appendMessage(text, role) {
                var row = document.createElement('div');
                row.className = 'chat-msg ' + role;
                var bubble = document.createElement('div');
                bubble.className = 'chat-bubble ' + role;
                bubble.textContent = text;
                row.appendChild(bubble);
                messagesEl.appendChild(row);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            function showTyping() {
                var row = document.createElement('div');
                row.className = 'chat-msg bot';
                row.id = 'chatbotTyping';
                var bubble = document.createElement('div');
                bubble.className = 'chat-bubble bot';
                bubble.innerHTML = '<span class="typing-dots"><span></span><span></span><span></span></span>';
                row.appendChild(bubble);
                messagesEl.appendChild(row);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            function hideTyping() {
                var el = document.getElementById('chatbotTyping');
                if (el) el.remove();
            }

            function sendMessage() {
                var text = inputEl.value.trim();
                if (!text) return;
                
                appendMessage(text, 'user');
                inputEl.value = '';
                sendBtn.disabled = true;
                showTyping();

                // Call OpenAI API via Laravel route
                fetch('{{ route("ai.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    hideTyping();
                    if (data.reply) {
                        appendMessage(data.reply, 'bot');
                    } else if (data.error) {
                        appendMessage('Sorry, ' + data.error + ' Please check your OpenAI API key configuration.', 'bot');
                    } else {
                        appendMessage('Sorry, I could not generate a response. Please try again.', 'bot');
                    }
                })
                .catch(function(error) {
                    hideTyping();
                    appendMessage('Sorry, I could not reach the AI service. Please check your internet connection and try again.', 'bot');
                    console.error('Chatbot error:', error);
                })
                .finally(function() {
                    sendBtn.disabled = false;
                });
            }

            sendBtn.addEventListener('click', sendMessage);
            inputEl.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); sendMessage(); }
            });
        })();
    </script>
</body>
</html>