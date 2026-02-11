<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Worker - Admin Control</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f0f1a; color: #e2e8f0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 280px; background: linear-gradient(180deg, #1a1a2e 0%, #16162a 100%); border-right: 1px solid rgba(139, 92, 246, 0.1); display: flex; flex-direction: column; }
        .sidebar-header { padding: 2rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(139, 92, 246, 0.15); }
        .sidebar-header i { font-size: 2.5rem; color: #8b5cf6; margin-bottom: 0.75rem; display: block; }
        .sidebar-header h2 { font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 0.25rem; }
        .sidebar-header p { font-size: 0.875rem; color: #94a3b8; }
        .sidebar-nav { flex: 1; padding: 1.5rem 0; }
        .nav-item { display: flex; align-items: center; gap: 0.875rem; padding: 0.875rem 1.5rem; color: #e2e8f0; font-weight: 500; font-size: 0.9375rem; transition: all 0.2s; cursor: pointer; border-left: 3px solid transparent; text-decoration: none; }
        .nav-item:hover { background: rgba(139, 92, 246, 0.1); color: #fff; }
        .nav-item.active { background: #8b5cf6; color: #fff; border-left-color: #a78bfa; }
        .nav-item i { width: 20px; text-align: center; }
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid rgba(139, 92, 246, 0.15); }
        .sidebar-footer .nav-item { padding: 0.75rem 1rem; }
        
        /* Main Content */
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .header { margin-bottom: 2rem; }
        .header h1 { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem; }
        .header p { color: #94a3b8; font-size: 0.9375rem; }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: #8b5cf6; font-weight: 600; margin-bottom: 1rem; text-decoration: none; transition: all 0.2s; }
        .back-link:hover { color: #a78bfa; }
        
        /* Form Card */
        .form-card { background: #1e1e2f; border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; padding: 2rem; max-width: 800px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { color: #e2e8f0; font-weight: 600; font-size: 0.875rem; }
        .form-group label .required { color: #f87171; margin-left: 0.25rem; }
        .form-group input, .form-group select, .form-group textarea { background: #0f0f1a; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; padding: 0.75rem 1rem; color: #e2e8f0; font-size: 0.9375rem; outline: none; transition: all 0.2s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1); }
        .form-group textarea { resize: vertical; min-height: 100px; font-family: inherit; }
        .form-group .hint { color: #94a3b8; font-size: 0.8125rem; margin-top: 0.25rem; }
        
        /* Buttons */
        .form-actions { display: flex; gap: 1rem; margin-top: 2rem; }
        .btn { padding: 0.875rem 2rem; border-radius: 8px; font-weight: 600; font-size: 0.9375rem; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .btn-primary { background: linear-gradient(135deg, #8b5cf6, #a78bfa); color: #fff; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5); }
        .btn-secondary { background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3); }
        .btn-secondary:hover { background: rgba(139, 92, 246, 0.25); }
        
        /* Alert Messages */
        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; display: none; }
        .alert.show { display: block; }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; }
        
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .sidebar { width: 240px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-shield-halved"></i>
            <h2>Admin Control</h2>
            <p>Super Administrator</p>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="{{ route('admin.crew-management') }}" class="nav-item active">
                <i class="fas fa-users-cog"></i>
                <span>Crew Management</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="{{ route('login') }}" class="nav-item">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <a href="{{ route('admin.crew-management') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Crew Management</span>
        </a>

        <div class="header">
            <h1>Add New Worker</h1>
            <p>Fill in the details to add a new crew member to the team</p>
        </div>

        <!-- Alert Messages -->
        <div id="successAlert" class="alert alert-success">
            <i class="fas fa-check-circle"></i> Worker added successfully!
        </div>
        <div id="errorAlert" class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <span id="errorMessage">An error occurred. Please try again.</span>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <form id="addWorkerForm">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Employee ID <span class="required">*</span></label>
                        <input type="text" name="employee_id" id="employee_id" placeholder="e.g., WL-003, T-004" required>
                        <span class="hint">Unique identifier for the worker</span>
                    </div>

                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" id="name" placeholder="e.g., Ahmad Hassan" required>
                    </div>

                    <div class="form-group">
                        <label>Role <span class="required">*</span></label>
                        <select name="role" id="role" required>
                            <option value="">Select Role</option>
                            <option value="Work Leader">Work Leader</option>
                            <option value="Senior Technician">Senior Technician</option>
                            <option value="Technician">Technician</option>
                            <option value="Equipment Operator">Equipment Operator</option>
                            <option value="Safety Officer">Safety Officer</option>
                            <option value="Maintenance Worker">Maintenance Worker</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status <span class="required">*</span></label>
                        <select name="status" id="status" required>
                            <option value="Active">Active</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" id="phone" placeholder="+673 712 3456">
                        <span class="hint">Optional: Contact number</span>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="email" placeholder="worker@sewage.gov.bn">
                        <span class="hint">Optional: Email address</span>
                    </div>

                    <div class="form-group">
                        <label>Hire Date</label>
                        <input type="date" name="hire_date" id="hire_date">
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                    </div>

                    <div class="form-group full-width">
                        <label>Specialization / Expertise</label>
                        <textarea name="specialization" id="specialization" placeholder="e.g., Heavy machinery operation, excavator certified"></textarea>
                        <span class="hint">Optional: Areas of expertise or special skills</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Worker</span>
                    </button>
                    <a href="{{ route('admin.crew-management') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('addWorkerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('{{ route("admin.crew.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    // Show success message
                    document.getElementById('successAlert').classList.add('show');
                    document.getElementById('errorAlert').classList.remove('show');
                    
                    // Reset form
                    this.reset();

                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.crew-management") }}';
                    }, 2000);
                } else {
                    // Show error message
                    const errorMsg = result.message || 'Failed to add worker. Please check the form.';
                    document.getElementById('errorMessage').textContent = errorMsg;
                    document.getElementById('errorAlert').classList.add('show');
                    document.getElementById('successAlert').classList.remove('show');
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('errorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('errorAlert').classList.add('show');
                document.getElementById('successAlert').classList.remove('show');
            }
        });
    </script>
</body>
</html>
