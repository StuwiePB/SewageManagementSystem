<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DMS Ops</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('r_operators.partials.dms-theme')
    @include('r_operators.partials.datetime-picker-assets')
    @livewireStyles
    @stack('styles')
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-hard-hat"></i>
            <h2>DMS Ops</h2>
            <p>Drainage Management System</p>
        </div>
        <div class="layout-inner">
            @include('r_operators.partials.sidebar-nav')
            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fas fa-arrow-right-from-bracket"></i><span>Sign out</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    <main class="main">
        {{ $slot }}
    </main>
</div>
<div id="dms-toast" class="dms-toast" role="status" aria-live="polite"></div>
@livewireScripts
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('dms-toast', ({ message }) => {
            const el = document.getElementById('dms-toast');
            if (!el) return;
            el.textContent = message;
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 4000);
        });
    });
</script>
@include('r_operators.partials.datetime-picker-init')
@stack('scripts')
</body>
</html>
