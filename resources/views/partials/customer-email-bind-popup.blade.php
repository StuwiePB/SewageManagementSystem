@php
    $bindUser = auth()->user();
@endphp
@if($bindUser && $bindUser->needsEmailBinding())
    <div
        id="email-bind-popup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="email-bind-popup-title"
        style="display: flex; position: fixed; inset: 0; z-index: 10060; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box;"
    >
        <div
            style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.55); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
        ></div>
        <div
            style="position: relative; z-index: 1; width: 100%; max-width: 320px; border-radius: 16px; padding: 22px 20px 18px; background: rgba(66, 106, 120, 0.92); border: 0.7px solid rgba(255, 255, 255, 0.24); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45); box-sizing: border-box; font-family: Poppins, sans-serif;"
        >
            <p
                id="email-bind-popup-title"
                style="margin: 0 0 18px; color: #fff; font-size: 14px; font-weight: 600; line-height: 1.45; text-align: center;"
            >
                {{ __('For email notification pls bind ur email to ur account') }}
            </p>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a
                    href="{{ route('customer.profilesettings', ['name' => $bindUser->profileSlug()]) }}"
                    class="press-btn delayed-nav"
                    style="display: flex; align-items: center; justify-content: center; height: 42px; border-radius: 12px; background: #04BCFF; color: #0a1628; font-size: 13px; font-weight: 700; text-decoration: none;"
                >
                    {{ __('Bind email') }}
                </a>
                <button
                    type="button"
                    id="email-bind-popup-later"
                    class="press-btn"
                    style="height: 40px; border-radius: 12px; border: 0.7px solid rgba(255, 255, 255, 0.25); background: rgba(255, 255, 255, 0.08); color: rgba(255, 255, 255, 0.85); font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif; cursor: pointer;"
                >
                    {{ __('Later') }}
                </button>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            (function () {
                var popup = document.getElementById('email-bind-popup');
                var laterBtn = document.getElementById('email-bind-popup-later');
                var dismissUrl = @json(route('customer.email-bind-prompt.dismiss'));
                var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                if (!popup) return;
                if (laterBtn) {
                    laterBtn.addEventListener('click', function () {
                        popup.style.display = 'none';
                        fetch(dismissUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            credentials: 'same-origin',
                        }).catch(function () {});
                    });
                }
            })();
        </script>
    @endpush
@endif
