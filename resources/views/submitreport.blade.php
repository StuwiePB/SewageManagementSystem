<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipal Sewage Issue Reporting Portal</title>
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
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-water me-2"></i>
                Sewage Monitoring System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('submitreport') }}">Report Issue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <div class="container">
            <!-- Report Issue Section -->
            <section id="report" class="mb-5">
                <h2 class="section-title">Report a Sewage Issue</h2>
                
                <!-- Progress Tracker -->
                <div class="progress-tracker">
                    <div class="progress-step active">
                        <div class="step-title">Details</div>
                    </div>
                    <div class="progress-step">
                        <div class="step-title">Location</div>
                    </div>
                    <div class="progress-step">
                        <div class="step-title">Confirmation</div>
                    </div>
                </div>
                
                <div class="report-card">
                    <form id="reportForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="issueType" class="form-label">Type of Issue *</label>
                                <select class="form-select" id="issueType" required>
                                    <option value="">Select issue type</option>
                                    <option value="blockage">Sewage Blockage</option>
                                    <option value="overflow">Sewage Overflow</option>
                                    <option value="odor">Strong Sewage Odor</option>
                                    <option value="maintenance">Maintenance Required</option>
                                    <option value="other">Other Issue</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="severity" class="form-label">Severity Level *</label>
                                <select class="form-select" id="severity" required>
                                    <option value="">Select severity</option>
                                    <option value="low">Low - Minor Issue</option>
                                    <option value="medium">Medium - Significant Issue</option>
                                    <option value="high">High - Serious Issue</option>
                                    <option value="critical">Critical - Emergency</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description of the Issue *</label>
                            <textarea class="form-control" id="description" rows="4" placeholder="Please provide details about the sewage issue, including when you first noticed it and any other relevant information." required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address *</label>
                                <input type="text" class="form-control" id="address" placeholder="Street address or nearest intersection" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="landmark" class="form-label">Nearest Landmark</label>
                                <input type="text" class="form-control" id="landmark" placeholder="E.g., near the park, next to library">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Location on Map</label>
                            <div class="map-container">
                                <div class="map-placeholder">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <p>Interactive map would appear here</p>
                                    <small>Click to pinpoint the exact location of the issue</small>
                                </div>
                            </div>
                            <div class="form-text mt-2">
                                <i class="fas fa-info-circle me-1"></i> You can also use the "Use My Location" button for automatic detection.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Photo Evidence (Optional)</label>
                            <div class="photo-preview" id="photoUpload">
                                <i class="fas fa-camera"></i>
                                <p>Click to upload a photo of the issue</p>
                                <small>Maximum file size: 5MB. Supported formats: JPG, PNG</small>
                            </div>
                            <input type="file" id="photoInput" accept="image/*" style="display: none;">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name (Optional)</label>
                                <input type="text" class="form-control" id="name" placeholder="For follow-up if needed">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact" class="form-label">Contact Information (Optional)</label>
                                <input type="text" class="form-control" id="contact" placeholder="Email or phone number">
                                <div class="form-text">We'll only contact you for follow-up questions about your report.</div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">Clear Form</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Submit Report
                            </button>
                        </div>
                    </form>
                    
                    <!-- Report Summary (Will appear after submission) -->
                    <div class="report-summary" id="reportSummary" style="display: none;">
                        <h5><i class="fas fa-check-circle me-2 text-success"></i> Report Submitted Successfully!</h5>
                        <p class="mb-1">Your report has been received and assigned a reference number: <strong>SMS-2023-0856</strong></p>
                        <p class="mb-0">You can use this number to track the status of your report. Our team will review it and take appropriate action.</p>
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
                    <p>A public service portal for reporting and tracking sewage system issues in our municipality. Together, we can maintain a clean and functional sewage infrastructure.</p>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <div class="footer-links">
                        <a href="submitreport.php">Report Issue</a>
                        <a href="publicpage.php">Home</a>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p class="mb-0">&copy; 2023 Municipal Public Works Department. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission handler
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show success message
            document.getElementById('reportSummary').style.display = 'block';
            
            // Scroll to the summary
            document.getElementById('reportSummary').scrollIntoView({ behavior: 'smooth' });
            
            // Reset progress tracker
            const steps = document.querySelectorAll('.progress-step');
            steps.forEach(step => {
                step.classList.remove('active', 'completed');
            });
            
            // Mark first two as completed, last as active
            steps[0].classList.add('completed');
            steps[1].classList.add('completed');
            steps[2].classList.add('active');
            
            // You would normally send form data to a server here
            console.log('Form submitted successfully');
        });
        
        // Photo upload functionality
        document.getElementById('photoUpload').addEventListener('click', function() {
            document.getElementById('photoInput').click();
        });
        
        document.getElementById('photoInput').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                document.getElementById('photoUpload').innerHTML = `
                    <i class="fas fa-check-circle text-success"></i>
                    <p>Photo uploaded: ${fileName}</p>
                    <small>Click to change photo</small>
                `;
            }
        });
        
        // Update progress steps when interacting with form
        const formInputs = document.querySelectorAll('#reportForm input, #reportForm select, #reportForm textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Mark first step as completed when user starts filling form
                document.querySelectorAll('.progress-step')[0].classList.add('completed');
            });
        });
        
        // Address field special handling for location step
        document.getElementById('address').addEventListener('input', function() {
            if (this.value.trim() !== '') {
                document.querySelectorAll('.progress-step')[1].classList.add('completed');
            }
        });
    </script>
</body>
</html>