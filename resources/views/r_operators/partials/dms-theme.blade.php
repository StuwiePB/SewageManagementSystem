<style>
    :root {
        --bg-primary: #1a1f2e;
        --bg-secondary: #1e2433;
        --text-primary: #ffffff;
        --text-secondary: #9ca3af;
        --accent-blue: #4a90e2;
        --accent-red: #e74c3c;
        --accent-green: #2ecc71;
        --border-subtle: rgba(74, 144, 226, 0.2);
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; }
    .layout { display: flex; min-height: 100vh; }
    .sidebar { width: 280px; background: var(--bg-secondary); border-right: 1px solid var(--border-subtle); display: flex; flex-direction: column; min-height: 100vh; }
    .sidebar-header { padding: 2rem 1.5rem; text-align: center; border-bottom: 1px solid var(--border-subtle); }
    .sidebar-header i { font-size: 2.5rem; color: var(--accent-blue); margin-bottom: 0.75rem; display: block; }
    .sidebar-header h2 { font-size: 1.25rem; font-weight: 700; }
    .sidebar-header p { font-size: 0.875rem; color: var(--text-secondary); }
    .sidebar .layout-inner { flex: 1; display: flex; flex-direction: column; min-height: 0; }
    .sidebar .nav { flex: 1; padding: 1.5rem 0; }
    .nav a { display: flex; align-items: center; gap: 0.875rem; padding: 0.875rem 1.5rem; color: var(--text-secondary); font-weight: 500; font-size: 0.9375rem; text-decoration: none; border-left: 3px solid transparent; transition: all 0.2s; }
    .nav a:hover { background: rgba(74, 144, 226, 0.1); color: var(--text-primary); }
    .nav a.active { background: rgba(74, 144, 226, 0.18); color: var(--accent-blue); border-left-color: var(--accent-blue); }
    .nav a i { width: 20px; text-align: center; }
    .nav-group { margin-bottom: 0.25rem; }
    .nav-group .nav-parent { border-left: 3px solid transparent; }
    .nav-group .nav-sub { padding-left: 2.75rem; font-size: 0.875rem; }
    .nav-group .nav-sub i { font-size: 0.8rem; opacity: 0.85; }
    .sidebar-footer { padding: 1.5rem; border-top: 1px solid var(--border-subtle); }
    .sidebar-footer .btn-logout { display: flex; align-items: center; gap: 0.875rem; width: 100%; padding: 0.875rem 1.5rem; background: none; border: none; color: var(--text-secondary); font-family: inherit; cursor: pointer; }
    .main { flex: 1; padding: 2rem; overflow-y: auto; }
    .topbar { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
    .topbar h1 { font-size: 1.75rem; font-weight: 800; }
    .topbar p { color: var(--text-secondary); margin-top: 0.35rem; font-size: 0.9rem; }
    .card { background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; }
    .card-title { font-size: 1rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--accent-blue); }
    .section-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    .form-label { display: block; margin-bottom: 0.35rem; font-size: 0.8rem; color: var(--text-secondary); font-weight: 500; }
    .form-control { width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid var(--border-subtle); background: var(--bg-primary); color: var(--text-primary); font-size: 0.875rem; }
    .form-control:focus { outline: none; border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.25); }
    .dms-datetime-picker,
    .dms-date-picker {
        cursor: pointer;
        padding-right: 2.25rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%234a90e2' viewBox='0 0 448 512'%3E%3Cpath d='M152 24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H64C28.7 64 0 92.7 0 128v16 48V448c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V192 144 128c0-35.3-28.7-64-64-64H328V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H152V24zM48 192H400V448c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.65rem center;
        background-size: 1rem;
    }
    .form-control.low-confidence { border-color: var(--accent-red); box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.25); }
    .field-hint { font-size: 0.7rem; color: var(--accent-red); margin-top: 0.2rem; }
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1.1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; text-decoration: none; }
    .btn-primary { background: var(--accent-blue); color: #fff; }
    .btn-secondary { background: rgba(74, 144, 226, 0.15); color: var(--accent-blue); }
    .btn-ghost { background: transparent; color: var(--text-secondary); border: 1px solid var(--border-subtle); }
    .entry-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; }
    .entry-card { padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-subtle); background: var(--bg-secondary); cursor: pointer; transition: border-color 0.2s, transform 0.2s; text-align: left; color: inherit; font: inherit; width: 100%; }
    .entry-card:hover { border-color: var(--accent-blue); transform: translateY(-2px); }
    .entry-card.recommended { border-color: var(--accent-blue); }
    .entry-card .badge-rec { display: inline-block; font-size: 0.65rem; background: var(--accent-blue); color: #fff; padding: 0.15rem 0.5rem; border-radius: 4px; margin-bottom: 0.5rem; }
    .ocr-loading { text-align: center; padding: 3rem 1rem; color: var(--text-secondary); }
    .ocr-loading .spinner { width: 40px; height: 40px; border: 3px solid var(--border-subtle); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; }
    .checkbox-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; }
    .routing-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    .routing-table th, .routing-table td { padding: 0.4rem; border-bottom: 1px solid var(--border-subtle); }
    .routing-table input { font-size: 0.75rem; padding: 0.35rem; }
    .archive-banner { background: rgba(74, 144, 226, 0.12); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--text-secondary); }
    .dms-toast { position: fixed; bottom: 1.5rem; right: 1.5rem; background: var(--accent-green); color: #0d1a12; padding: 0.75rem 1.25rem; border-radius: 8px; font-weight: 600; opacity: 0; transform: translateY(10px); transition: all 0.3s; z-index: 9999; pointer-events: none; }
    .dms-toast.show { opacity: 1; transform: translateY(0); }
    .error-text { color: var(--accent-red); font-size: 0.75rem; margin-top: 0.2rem; }
    .form-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-subtle); }
    textarea.form-control { min-height: 80px; resize: vertical; }
    .photo-upload-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 768px) { .layout { flex-direction: column; } .sidebar { width: 100%; min-height: auto; } .photo-upload-grid { grid-template-columns: 1fr; } }
</style>
