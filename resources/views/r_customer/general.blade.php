<x-layouts::customer :title="__('General') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    @endpush

    {{-- Header: back arrow + General settings --}}
    @php
        $user = auth()->user();
        $photoPath = $user->profile_photo_path ?? null;
        $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::url($photoPath) : null;
        $displayPhone = $user->phone ?? '';
        $digits = preg_replace('/\D+/', '', (string) $displayPhone) ?? '';
        if (str_starts_with($digits, '673') && strlen($digits) >= 10) {
            $local = substr($digits, 3, 7);
            $displayPhone = '+673 '.substr($local, 0, 3).' '.substr($local, 3);
        } elseif ($digits !== '') {
            $displayPhone = '+'.$digits;
        } else {
            $displayPhone = '—';
        }
    @endphp
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; transition: transform 0.1s ease; filter: blur(0.4px);" aria-label="{{ __('Back') }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <span class="cust-title" style="font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;">General settings</span>
    </div>

    {{-- Boxes stacked vertically --}}
    <div style="position: fixed; top: 22vh; left: 28px; right: 28px; bottom: 20px; z-index: 5; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; overflow-x: visible; padding: 2px;">
        {{-- Top box: profile card --}}
        <div class="cust-list-row cust-glass" style="position: relative; border-radius: 12px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); min-height: 88px; flex-shrink: 0; box-sizing: border-box; display: flex; align-items: center; gap: 12px; padding: 12px 16px;">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="" style="width: 48px; height: 48px; border-radius: 9999px; object-fit: cover; flex-shrink: 0;" />
            @elseif(file_exists(public_path('images/default-avatar.png')))
                <img src="{{ asset('images/default-avatar.png') }}" alt="" style="width: 48px; height: 48px; border-radius: 9999px; object-fit: cover; flex-shrink: 0;" />
            @else
                <div style="width: 48px; height: 48px; border-radius: 9999px; background: #3f3f46; display: flex; align-items: center; justify-content: center; color: #e4e4e7; font-size: 14px; font-weight: 600; flex-shrink: 0;">{{ $user->initials() }}</div>
            @endif
            <div style="flex: 1; min-width: 0; margin-top: -12px;">
                <p style="color: white; font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif; margin: 0; line-height: 1.3;">{{ $user->name ?? 'User' }}</p>
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 9px; font-weight: 300; font-family: Poppins, sans-serif; margin: 2px 0 0 0; line-height: 1.3;">{{ $displayPhone }}</p>
            </div>
            <a href="{{ route('customer.profilesettings', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="position: absolute; bottom: 12px; right: 16px; padding: 6px 12px; border-radius: 9999px; border: 0.7px solid rgba(255, 255, 255, 0.21); background: rgba(255, 255, 255, 0.08); color: white; font-size: 11px; font-weight: 500; font-family: Poppins, sans-serif; cursor: pointer; text-decoration: none;">{{ __('Edit Profile') }}</a>
        </div>
        {{-- 3 buttons --}}
        <a href="{{ route('customer.preference', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav cust-list-row cust-glass" style="border-radius: 12px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); height: 50px; flex-shrink: 0; box-sizing: border-box; display: flex; align-items: center; gap: 12px; padding: 0 16px; cursor: pointer; width: 100%; text-align: left; font-family: Poppins, sans-serif; text-decoration: none;">
            <svg class="gen-opt-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            <span style="color: white; font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif;">{{ __('Preference') }}</span>
        </a>
        <a href="{{ route('customer.faq', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav cust-list-row cust-glass" style="border-radius: 12px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); height: 50px; flex-shrink: 0; box-sizing: border-box; display: flex; align-items: center; gap: 12px; padding: 0 16px; cursor: pointer; width: 100%; text-align: left; font-family: Poppins, sans-serif; text-decoration: none;">
            <svg class="gen-opt-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
            <span style="color: white; font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif;">{{ __('Help FAQ?') }}</span>
        </a>
        <a href="{{ route('customer.customersupport', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav cust-list-row cust-glass" style="border-radius: 12px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); height: 50px; flex-shrink: 0; box-sizing: border-box; display: flex; align-items: center; gap: 12px; padding: 0 16px; cursor: pointer; width: 100%; text-align: left; font-family: Poppins, sans-serif; text-decoration: none;">
            <svg class="gen-opt-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/></svg>
            <span style="color: white; font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif;">{{ __('Contact Customer Support') }}</span>
        </a>
        {{-- Logout button --}}
        <form method="POST" action="{{ route('logout') }}" style="width: 100%; flex-shrink: 0;">
            @csrf
            <button type="submit" class="press-btn" style="width: 100%; border-radius: 12px; background: #7f1d1d; border: 0.7px solid #7f1d1d; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); height: 42px; box-sizing: border-box; display: flex; align-items: center; justify-content: center; cursor: pointer; font-family: Poppins, sans-serif;">
                <span style="color: white; font-size: 13px; font-weight: 600;">{{ __('Logout') }}</span>
            </button>
        </form>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.delayed-nav').forEach(function(el) {
            el.style.transition = 'transform 0.1s ease';
            el.addEventListener('click', function(e) {
                e.preventDefault();
                var href = el.getAttribute('href');
                el.style.transform = 'scale(0.95)';
                setTimeout(function() {
                    el.style.transform = 'scale(1)';
                    setTimeout(function() { window.location.href = href; }, 100);
                }, 100);
            });
        });
    </script>
    @endpush
</x-layouts::customer>
