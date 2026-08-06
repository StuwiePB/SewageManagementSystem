<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Incident Upload - AI Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg-primary: #1A1D2B; --bg-secondary: #272B3C; --text-primary: #FFFFFF; --text-secondary: #B0B0B0; --accent-blue: #6A96FF; --accent-red: #FF5B5B; --accent-green: #56FF8B; --accent-purple: #A86AFF; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary); line-height: 1.6; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Inter', sans-serif; font-weight: 600; color: var(--text-primary); }
        .navbar { background: var(--bg-secondary); border-bottom: 1px solid rgba(106, 150, 255, 0.15); }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; color: var(--text-primary) !important; }
        .nav-link { color: var(--text-secondary) !important; font-weight: 500; }
        .nav-link:hover, .nav-link.active { color: var(--accent-blue) !important; }
        .navbar-toggler { border-color: rgba(106, 150, 255, 0.3); }
        .navbar-toggler-icon { filter: invert(1); }
        .main-container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .incident-card { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.15); border-radius: 12px; padding: 2rem; margin-bottom: 2rem; }
        .section-title { color: var(--accent-blue); border-bottom: 3px solid var(--accent-blue); padding-bottom: 0.5rem; display: inline-block; margin-bottom: 1.5rem; }
        .form-label { font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; }
        .photo-upload-area { border: 2px dashed rgba(106, 150, 255, 0.3); border-radius: 8px; padding: 2rem; text-align: center; color: var(--text-secondary); cursor: pointer; transition: all 0.3s; min-height: 250px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-primary); }
        .photo-upload-area:hover { border-color: var(--accent-blue); background: rgba(106, 150, 255, 0.05); }
        .photo-upload-area.dragover { border-color: var(--accent-green); background: rgba(86, 255, 139, 0.05); }
        .photo-upload-area i { font-size: 3rem; margin-bottom: 1rem; color: var(--accent-blue); }
        .photo-preview { margin-top: 1rem; display: none; }
        .photo-preview img { max-width: 100%; max-height: 400px; border-radius: 8px; border: 1px solid rgba(106, 150, 255, 0.2); }
        .uploading .spinner-border { display: inline-block; }
        .uploading .btn-primary { pointer-events: none; opacity: 0.6; }
        .btn-primary { background: var(--accent-blue); color: white; border: none; }
        .btn-primary:hover { background: var(--accent-blue); opacity: 0.9; color: white; }
        .btn-outline-danger { border-color: var(--accent-red); color: var(--accent-red); }
        .btn-outline-danger:hover { background: rgba(255, 91, 91, 0.2); color: var(--accent-red); }
        .text-muted { color: var(--text-secondary) !important; }
        .alert-success { background: rgba(86, 255, 139, 0.15); color: var(--accent-green); border-color: transparent; }
        .alert-danger { background: rgba(255, 91, 91, 0.15); color: var(--accent-red); border-color: transparent; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}"><i class="fas fa-shield-alt me-2"></i>Drainage Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="{{ route('incidents.create') }}">Upload</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('ai.incidents.dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="incident-card">
            <h2 class="section-title">Upload Incident Report</h2>
            <p class="text-muted mb-4">Upload an image of the incident. The AI will check whether it is drainage-related or not.</p>
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
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removePhoto"><i class="fas fa-times me-1"></i> Remove Photo</button>
                    </div>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <i class="fas fa-upload me-2"></i> Upload
                    </button>
                </div>
            </form>
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

        photoUploadArea.addEventListener('click', () => photoInput.click());
        photoUploadArea.addEventListener('dragover', (e) => { e.preventDefault(); photoUploadArea.classList.add('dragover'); });
        photoUploadArea.addEventListener('dragleave', () => photoUploadArea.classList.remove('dragover'));
        photoUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            photoUploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) { photoInput.files = e.dataTransfer.files; handleFileSelect(photoInput.files[0]); }
        });
        photoInput.addEventListener('change', (e) => { if (e.target.files.length > 0) handleFileSelect(e.target.files[0]); });

        function handleFileSelect(file) {
            if (!file.type.startsWith('image/')) { showAlert('Please select an image file.', 'danger'); return; }
            if (file.size > 10 * 1024 * 1024) { showAlert('File size must be less than 10MB.', 'danger'); return; }
            const reader = new FileReader();
            reader.onload = (e) => { previewImage.src = e.target.result; photoPreview.style.display = 'block'; photoUploadArea.style.display = 'none'; };
            reader.readAsDataURL(file);
        }

        removePhotoBtn.addEventListener('click', () => {
            photoInput.value = ''; photoPreview.style.display = 'none'; photoUploadArea.style.display = 'flex'; previewImage.src = '';
        });

        incidentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!photoInput.files.length) { showAlert('Please select an image to upload.', 'danger'); return; }
            const formData = new FormData(incidentForm);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            if (!formData.has('_token')) formData.append('_token', csrfToken);
            incidentForm.classList.add('uploading');
            alertContainer.innerHTML = '';
            try {
                const response = await fetch('{{ route("incidents.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
                });
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    showAlert(response.status === 419 ? 'Session expired. Please refresh and try again.' : 'Server error. Please try again.', 'danger');
                    return;
                }
                const data = await response.json();
                if (response.ok) {
                    showAlert('Incident uploaded successfully! ID: ' + data.incident.id + '. AI is checking drainage relevance.', 'success');
                    incidentForm.reset();
                    photoPreview.style.display = 'none';
                    photoUploadArea.style.display = 'flex';
                } else {
                    let errorMsg = data.message || 'An error occurred.';
                    if (data.errors) errorMsg = Object.values(data.errors).flat().join('<br>') || errorMsg;
                    showAlert(errorMsg, 'danger');
                }
            } catch (err) {
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                incidentForm.classList.remove('uploading');
            }
        });

        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-' + type + ' alert-dismissible fade show';
            alert.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            alertContainer.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }
    </script>
</body>
</html>
