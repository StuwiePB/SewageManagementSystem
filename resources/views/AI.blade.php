<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Incident Upload - AI Analysis</title>
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
            max-width: 900px;
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
        
        .form-label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
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
        
        .photo-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s;
            min-height: 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        
        .photo-upload-area:hover {
            border-color: var(--secondary-blue);
            background-color: rgba(0, 119, 204, 0.02);
        }
        
        .photo-upload-area.dragover {
            border-color: var(--accent-teal);
            background-color: rgba(0, 168, 150, 0.05);
        }
        
        .photo-upload-area i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary-blue);
        }
        
        .photo-preview {
            margin-top: 1rem;
            display: none;
        }
        
        .photo-preview img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .spinner-border {
            display: none;
        }
        
        .uploading .spinner-border {
            display: inline-block;
        }
        
        .uploading .btn-primary {
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-shield-alt me-2"></i>
                Incident Management System
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
                        <a class="nav-link active" href="{{ route('incidents.create') }}">Upload Incident</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('incidents.index') }}">View Incidents</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <div class="incident-card">
            <h2 class="section-title">Upload Incident Report</h2>
            <p class="text-muted mb-4">Upload an image of the incident for AI analysis and review.</p>
            
            <form id="incidentForm" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label class="form-label">Incident Photo *</label>
                    <div class="photo-upload-area" id="photoUploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p class="mb-2">Click to upload or drag and drop</p>
                        <small>Maximum file size: 10MB. Supported formats: JPG, PNG, GIF</small>
                    </div>
                    <input type="file" id="photoInput" name="photo" accept="image/*" class="d-none" required>
                    <div class="photo-preview" id="photoPreview">
                        <img id="previewImage" src="" alt="Preview">
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removePhoto">
                            <i class="fas fa-times me-1"></i> Remove Photo
                        </button>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <i class="fas fa-upload me-2"></i> Upload Incident
                    </button>
                </div>
            </form>
            
            <!-- Success/Error Messages -->
            <div id="alertContainer"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const photoUploadArea = document.getElementById('photoUploadArea');
        const photoInput = document.getElementById('photoInput');
        const photoPreview = document.getElementById('photoPreview');
        const previewImage = document.getElementById('previewImage');
        const removePhotoBtn = document.getElementById('removePhoto');
        const incidentForm = document.getElementById('incidentForm');
        const alertContainer = document.getElementById('alertContainer');

        // Click to upload
        photoUploadArea.addEventListener('click', () => {
            photoInput.click();
        });

        // Drag and drop
        photoUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            photoUploadArea.classList.add('dragover');
        });

        photoUploadArea.addEventListener('dragleave', () => {
            photoUploadArea.classList.remove('dragover');
        });

        photoUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            photoUploadArea.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                photoInput.files = e.dataTransfer.files;
                handleFileSelect(photoInput.files[0]);
            }
        });

        // File input change
        photoInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        // Handle file selection
        function handleFileSelect(file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showAlert('Please select an image file.', 'danger');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                showAlert('File size must be less than 10MB.', 'danger');
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                photoPreview.style.display = 'block';
                photoUploadArea.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        // Remove photo
        removePhotoBtn.addEventListener('click', () => {
            photoInput.value = '';
            photoPreview.style.display = 'none';
            photoUploadArea.style.display = 'flex';
            previewImage.src = '';
        });

        // Form submission
        incidentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!photoInput.files.length) {
                showAlert('Please select an image to upload.', 'danger');
                return;
            }

            const formData = new FormData(incidentForm);
            
            // Get CSRF token from meta tag and ensure it's included
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // Ensure CSRF token is in FormData (FormData should pick it up from @csrf, but explicit is safer)
            if (!formData.has('_token')) {
                formData.append('_token', csrfToken);
            }
            
            // Show loading state
            incidentForm.classList.add('uploading');
            clearAlerts();

            try {
                const response = await fetch('{{ route("incidents.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    if (response.status === 419) {
                        showAlert('Session expired. Please refresh the page and try again.', 'danger');
                    } else {
                        showAlert('Server error. Please try again.', 'danger');
                    }
                    return;
                }

                const data = await response.json();

                if (response.ok) {
                    showAlert('Incident uploaded successfully! ID: ' + data.incident.id, 'success');
                    incidentForm.reset();
                    photoPreview.style.display = 'none';
                    photoUploadArea.style.display = 'flex';
                } else {
                    // Handle validation errors
                    let errorMsg = data.message || 'An error occurred while uploading the incident.';
                    
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join('<br>');
                        errorMsg = errorList || errorMsg;
                    }
                    
                    showAlert(errorMsg, 'danger');
                }
            } catch (error) {
                showAlert('Network error. Please try again.', 'danger');
                console.error('Error:', error);
            } finally {
                incidentForm.classList.remove('uploading');
            }
        });

        // Show alert
        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Clear alerts
        function clearAlerts() {
            alertContainer.innerHTML = '';
        }
    </script>
</body>
</html>
