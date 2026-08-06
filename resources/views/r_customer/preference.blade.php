<x-layouts::customer :title="__('Preferences') . ' – BruDMS'" :bare="true">
        @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    @endpush

    @php
        $user = auth()->user();
        $photoPath = $user->profile_photo_path ?? null;
        $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::url($photoPath) : null;
    @endphp

    {{-- Header: back arrow + Preference + profile unit --}}
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.general', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; transition: transform 0.1s ease;" aria-label="{{ __('Back') }}">
            <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
        </a>
        <span class="cust-title" style="font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;">{{ __('Preferences') }}</span>
    </div>

    {{-- Content: Appearance, Language Select, Anonymous Report --}}
    <div style="position: fixed; top: 12vh; left: 48px; right: 48px; bottom: 20px; z-index: 5; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; padding: 2px;">
        {{-- Appearance --}}
        <div>
            <label style="display: block; color: rgba(255, 255, 255, 0.5); font-size: 12px; font-weight: 600; font-family: Poppins, sans-serif; margin-bottom: 10px; margin-left: 10px;">{{ __('Appearance') }}</label>
            <div class="cust-settings-card" style="min-height: 220px; border-radius: 10px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); display: flex; align-items: flex-start; justify-content: center; gap: 24px; padding: 32px 20px 20px;">
                <div class="appearance-opt" data-theme="dark" style="display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer;">
                    <img src="{{ asset('images/Vectors/preference_darkmode.svg') }}" alt="{{ __('Dark mode') }}" style="height: 120px; width: auto; object-fit: contain;">
                    <span style="font-size: 10px; font-weight: 500; font-family: Poppins, sans-serif; color: rgba(255, 255, 255, 0.6);">{{ __('Dark') }}</span>
                    <div class="appearance-radio press-btn" style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, 0.9); display: flex; align-items: center; justify-content: center; margin-top: -2px;">
                        <div class="appearance-radio-dot {{ ($prefAppearance ?? 'light') === 'dark' ? 'appearance-radio-selected' : '' }}" style="width: 6px; height: 6px; border-radius: 50%; {{ ($prefAppearance ?? 'light') === 'dark' ? 'background: white;' : '' }}"></div>
                    </div>
                </div>
                <div class="appearance-opt" data-theme="light" style="display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer;">
                    <img src="{{ asset('images/Vectors/preference_lightmode.svg') }}" alt="{{ __('Light mode') }}" style="height: 120px; width: auto; object-fit: contain;">
                    <span style="font-size: 10px; font-weight: 500; font-family: Poppins, sans-serif; color: rgba(255, 255, 255, 0.6);">{{ __('Light') }}</span>
                    <div class="appearance-radio press-btn" style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, 0.9); display: flex; align-items: center; justify-content: center; margin-top: -2px;">
                        <div class="appearance-radio-dot {{ ($prefAppearance ?? 'light') === 'light' ? 'appearance-radio-selected' : '' }}" style="width: 6px; height: 6px; border-radius: 50%; {{ ($prefAppearance ?? 'light') === 'light' ? 'background: white;' : '' }}"></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Language Select --}}
        <div>
            <label style="display: block; color: rgba(255, 255, 255, 0.5); font-size: 12px; font-weight: 600; font-family: Poppins, sans-serif; margin-bottom: 10px; margin-left: 10px;">{{ __('Language Select') }}</label>
            <div class="cust-settings-card" style="min-height: 100px; border-radius: 10px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); display: flex; align-items: flex-start; justify-content: center; gap: 24px; padding: 8px 20px 10px;">
                <div class="language-opt" data-lang="ms" style="display: flex; flex-direction: column; align-items: center; gap: 2px; cursor: pointer;">
                    <img src="{{ asset('images/Vectors/preference_bahasamelayu.svg') }}" alt="{{ __('Bahasa Melayu') }}" style="height: 40px; width: auto; object-fit: contain; margin-top: 4px;">
                    <span style="font-size: 10px; font-weight: 500; font-family: Poppins, sans-serif; color: rgba(255, 255, 255, 0.6); margin-top: -4px;">{{ __('Bahasa Melayu') }}</span>
                    <div class="language-radio press-btn" style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, 0.9); display: flex; align-items: center; justify-content: center; margin-top: 4px;">
                        <div class="language-radio-dot {{ ($prefLanguage ?? 'ms') === 'ms' ? 'language-radio-selected' : '' }}" style="width: 6px; height: 6px; border-radius: 50%; {{ ($prefLanguage ?? 'ms') === 'ms' ? 'background: white;' : '' }}"></div>
                    </div>
                </div>
                <div class="language-opt" data-lang="en" style="display: flex; flex-direction: column; align-items: center; gap: 2px; cursor: pointer;">
                    <img src="{{ asset('images/Vectors/preference_englishlanguage.svg') }}" alt="{{ __('English') }}" style="height: 40px; width: auto; object-fit: contain; margin-top: 4px;">
                    <span style="font-size: 10px; font-weight: 500; font-family: Poppins, sans-serif; color: rgba(255, 255, 255, 0.6); margin-top: -4px;">{{ __('English (US)') }}</span>
                    <div class="language-radio press-btn" style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, 0.9); display: flex; align-items: center; justify-content: center; margin-top: 4px;">
                        <div class="language-radio-dot {{ ($prefLanguage ?? 'ms') === 'en' ? 'language-radio-selected' : '' }}" style="width: 6px; height: 6px; border-radius: 50%; {{ ($prefLanguage ?? 'ms') === 'en' ? 'background: white;' : '' }}"></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Anonymous Report --}}
        <div>
            <label style="display: block; color: rgba(255, 255, 255, 0.5); font-size: 12px; font-weight: 600; font-family: Poppins, sans-serif; margin-bottom: 10px; margin-left: 10px;">{{ __('Anonymous Report') }}</label>
            <div class="cust-settings-card" style="min-height: 200px; border-radius: 10px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); display: flex; align-items: flex-start; justify-content: center; gap: 24px; padding: 32px 20px 14px;">
                <div class="anonymous-opt" data-mode="nonanonymous" style="display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer;">
                    <div style="position: relative; height: 120px; width: fit-content; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('images/Vectors/preference_nonanonymous.svg') }}" alt="{{ __('Non-Anonymous') }}" style="height: 120px; width: auto; object-fit: contain; display: block;">
                        <div style="position: absolute; left: 8px; bottom: 8px; display: flex; align-items: center; gap: 4px; pointer-events: none; max-width: calc(100% - 16px); overflow: hidden; text-overflow: ellipsis;">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="" style="width: 10px; height: 10px; border-radius: 9999px; object-fit: cover;" />
                            @elseif(file_exists(public_path('images/default-avatar.png')))
                                <img src="{{ asset('images/default-avatar.png') }}" alt="" style="width: 10px; height: 10px; border-radius: 9999px; object-fit: cover;" />
                            @else
                                <div style="width: 10px; height: 10px; border-radius: 9999px; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; color: white; font-size: 6px; font-weight: 600;">{{ $user->initials() }}</div>
                            @endif
                            <span style="color: white; font-size: 6px; font-weight: 400; font-family: Poppins, sans-serif; white-space: nowrap; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">{{ $user->name ?? 'User' }}</span>
                        </div>
                    </div>
                    <div class="anonymous-radio press-btn" style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, 0.9); display: flex; align-items: center; justify-content: center; margin-top: 8px;">
                        <div class="anonymous-radio-dot {{ ($prefAnonymous ?? 'nonanonymous') === 'nonanonymous' ? 'anonymous-radio-selected' : '' }}" style="width: 6px; height: 6px; border-radius: 50%; {{ ($prefAnonymous ?? 'nonanonymous') === 'nonanonymous' ? 'background: white;' : '' }}"></div>
                    </div>
                </div>
                <div class="anonymous-opt" data-mode="anonymous" style="display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer;">
                    <img src="{{ asset('images/Vectors/preference_anonymous.svg') }}" alt="{{ __('Anonymous') }}" style="height: 120px; width: auto; object-fit: contain;">
                    <div class="anonymous-radio press-btn" style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, 0.9); display: flex; align-items: center; justify-content: center; margin-top: 8px;">
                        <div class="anonymous-radio-dot {{ ($prefAnonymous ?? 'nonanonymous') === 'anonymous' ? 'anonymous-radio-selected' : '' }}" style="width: 6px; height: 6px; border-radius: 50%; {{ ($prefAnonymous ?? 'nonanonymous') === 'anonymous' ? 'background: white;' : '' }}"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        var PREF_URL = '{{ route("customer.preference.update") }}';
        var CSRF = '{{ csrf_token() }}';

        function applyAppearance(val) {
            var theme = val === 'light' ? 'light' : 'dark';
            if (typeof window.__brudmsApplyTheme === 'function') {
                window.__brudmsApplyTheme(theme);
            } else if (window.BrudmsTheme && typeof window.BrudmsTheme.apply === 'function') {
                window.BrudmsTheme.apply(theme);
            }
            document.querySelectorAll('.appearance-opt').forEach(function(opt) {
                var dot = opt.querySelector('.appearance-radio-dot');
                if (opt.dataset.theme === val) {
                    dot.style.background = 'white';
                    dot.classList.add('appearance-radio-selected');
                } else {
                    dot.style.background = '';
                    dot.classList.remove('appearance-radio-selected');
                }
            });
        }
        function applyLanguage(val) {
            document.querySelectorAll('.language-opt').forEach(function(opt) {
                var dot = opt.querySelector('.language-radio-dot');
                if (opt.dataset.lang === val) {
                    dot.style.background = 'white';
                    dot.classList.add('language-radio-selected');
                } else {
                    dot.style.background = '';
                    dot.classList.remove('language-radio-selected');
                }
            });
        }
        function applyAnonymous(val) {
            document.querySelectorAll('.anonymous-opt').forEach(function(opt) {
                var dot = opt.querySelector('.anonymous-radio-dot');
                if (opt.dataset.mode === val) {
                    dot.style.background = 'white';
                    dot.classList.add('anonymous-radio-selected');
                } else {
                    dot.style.background = '';
                    dot.classList.remove('anonymous-radio-selected');
                }
            });
        }

        function savePref(data) {
            return fetch(PREF_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(function (res) { return res.json(); })
                .then(function (payload) {
                    if (payload && payload.appearance) {
                        applyAppearance(payload.appearance);
                        var meta = document.querySelector('meta[name="brudms-theme-default"]');
                        if (meta) {
                            meta.setAttribute('content', payload.appearance);
                        }
                    }
                    return payload;
                })
                .catch(function () {});
        }

        document.querySelectorAll('.appearance-opt').forEach(function(opt) {
            opt.addEventListener('click', function() {
                var val = this.dataset.theme;
                applyAppearance(val);
                savePref({ appearance: val });
            });
        });
        document.querySelectorAll('.language-opt').forEach(function(opt) {
            opt.addEventListener('click', function() {
                var val = this.dataset.lang;
                if (val === (@json($prefLanguage ?? 'ms'))) {
                    return;
                }
                applyLanguage(val);
                savePref({ language: val }).then(function () {
                    // Translated strings are rendered server-side, so a full reload
                    // is needed for the new language to actually show up on the page.
                    window.location.reload();
                });
            });
        });
        document.querySelectorAll('.anonymous-opt').forEach(function(opt) {
            opt.addEventListener('click', function() {
                var val = this.dataset.mode;
                applyAnonymous(val);
                savePref({ anonymous: val });
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            var current = document.documentElement.getAttribute('data-theme')
                || @json($prefAppearance ?? 'light');
            applyAppearance(current === 'light' ? 'light' : 'dark');
        });

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
