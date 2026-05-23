<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bind Email OTP - BruDMS</title>
    <link rel="preload" as="image" href="{{ asset('images/crdboard.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; }
        .glass-box {
            width: 100%;
            max-width: min(70rem, 85vw);
            min-height: 44rem;
            padding: clamp(0.5rem, 1.5vw, 0.75rem);
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-radius: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.02);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            display: flex;
            gap: clamp(0.5rem, 1.5vw, 0.75rem);
        }
        .glass-box-left {
            flex: 1;
            min-width: 0;
            padding: 0.5rem;
            box-sizing: border-box;
            overflow: hidden;
            border-radius: 0.75rem;
            position: relative;
        }
        .glass-box-right {
            flex: 1;
            min-width: 0;
            position: relative;
            border-radius: 0.75rem;
        }
        .image-quote {
            display: none;
            position: absolute;
            bottom: 1.5rem;
            left: 1.5rem;
            width: calc(100% - 3rem);
            transition: transform 0.4s ease-out;
            max-width: 28rem;
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-style: italic;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.95);
            text-align: left;
            line-height: 1.5;
            z-index: 2;
            padding: 0;
        }
        .code-beta-hint {
            margin: 0.55rem 0 0;
            font-family: 'Poppins', sans-serif;
            font-size: 0.72rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.55);
            text-align: center;
            letter-spacing: 0.04em;
        }
        .code-input-row {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.42rem;
            width: 100%;
        }
        .code-digit {
            width: 100%;
            aspect-ratio: 0.86 / 1;
            border-radius: 0.65rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            text-align: center;
            box-sizing: border-box;
        }
        .code-digit:focus {
            border-color: rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.12);
            outline: none;
            box-shadow: none;
        }
        .email-invalid-msg {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: all 0.25s ease;
            margin: -0.95rem 0 0 0;
        }
        .email-invalid-msg.visible {
            max-height: 3rem;
            opacity: 1;
            margin: 0.4rem 0 0 0;
        }
        .email-invalid-msg span {
            display: block;
            color: #ff9ca5;
            font-family: 'Poppins', sans-serif;
            font-size: 0.72rem;
            text-align: center;
            line-height: 1.3;
        }
        #code-invalid-msg {
            display: flex;
            justify-content: center;
        }
        @media (max-width: 768px) {
            .glass-box {
                max-width: 82% !important;
                width: 82% !important;
                --mobile-glass-min-height: 60vh;
                min-height: var(--mobile-glass-min-height) !important;
                height: auto !important;
                flex-direction: column !important;
            }
            .glass-box-left {
                flex: 0 0 auto !important;
                width: auto !important;
                min-height: 12vh !important;
                height: 12vh !important;
                padding: 0 !important;
            }
            .glass-box-right {
                flex: 1 1 auto !important;
                min-height: 0 !important;
            }
            .glass-box-left img[alt=""] {
                min-height: 0 !important;
            }
            .panel-logo {
                height: 2.25rem !important;
            }
            .create-account-header {
                top: 17% !important;
            }
            .create-account-text {
                font-size: 1.28rem !important;
                padding: 0 0.5rem;
                text-align: center;
                white-space: nowrap !important;
            }
            .tagline {
                font-size: 0.54rem !important;
                padding: 0 0.5rem;
            }
            .signup-form-wrap.login-form-wrap {
                top: 32% !important;
                max-width: 16.5rem !important;
            }
        }
        @media (min-width: 769px) {
            .glass-box-left {
                flex: 0 0 35% !important;
            }
            .glass-box-right {
                flex: 0 0 65% !important;
            }
            .image-quote {
                display: block !important;
            }
            .create-account-header {
                top: 26% !important;
            }
            .signup-form-wrap.login-form-wrap {
                top: 36% !important;
            }
        }
    </style>
</head>
<body style="min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; padding: 1rem; box-sizing: border-box;">
    <div style="position: fixed; inset: 0; overflow: hidden; z-index: 0;">
        <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('{{ e(asset('images/crdboard.png')) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
    </div>

    <div class="glass-box" style="position: relative; z-index: 1;">
        <div class="glass-box-left" style="flex: 1; min-width: 0; padding: 0.5rem; box-sizing: border-box; overflow: hidden; border-radius: 0.75rem; position: relative;">
            <div style="position: absolute; inset: 0; border-radius: 0.75rem;">
                <img
                    src="{{ asset('images/left-panel.jpg') }}"
                    alt=""
                    draggable="false"
                    style="
                        width: 100%;
                        height: 100%;
                        min-height: 20rem;
                        object-fit: cover;
                        object-position: center;
                        display: block;
                        border-radius: 0.75rem;
                    "
                />
                <div
                    style="
                        position: absolute;
                        inset: 0;
                        background: rgba(0, 0, 0, 0.53);
                        border-radius: 0.75rem;
                        pointer-events: none;
                    "
                    aria-hidden="true"
                ></div>
                <img
                    class="panel-logo"
                    src="{{ asset('images/logo.png') }}"
                    alt=""
                    draggable="false"
                    style="
                        position: absolute;
                        top: 1rem;
                        left: 1rem;
                        z-index: 2;
                        height: 3.5rem;
                        width: auto;
                        object-fit: contain;
                        border-radius: 0.25rem;
                    "
                />
                <p class="image-quote">
                    &ldquo;120 blockages a year. 35 streets flooded. 18,000 people affected. <span style="color: #3b82f6;">Bru</span>DMS puts you in control, report in seconds, cut response from 48 to 12 hours, prevent 60% of disruptions, save 25% in maintenance, and make crews 40% more efficient. Streets move faster. Floods hit less. You own the flow.&rdquo;<br>
                    &mdash; some netizen i think
                </p>
            </div>
        </div>
        <div class="glass-box-right" style="flex: 1; min-width: 0; position: relative;">
            <div class="create-account-header" style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                <div class="create-account-text" style="font-family: 'Poppins', sans-serif; font-size: 2.1rem; font-weight: 600; color: #fff; white-space: nowrap;">
                    Enter your code
                </div>
                <p class="tagline" style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 0.78rem; font-weight: 400; font-style: italic; color: rgba(255, 255, 255, 0.9); text-align: center;">
                    "Everybody does, enter your email"
                </p>
            </div>
            <div class="signup-form-wrap login-form-wrap" style="position: absolute; top: 31%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 26rem; padding: 0 1rem; box-sizing: border-box;">
                <form id="code-form" class="signup-form" action="#" method="post" onsubmit="return false;" autocomplete="off">
                    <div class="code-input-row" id="code-input-row" aria-label="6 digit code">
                        <input type="text" class="code-digit" id="code-digit-1" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" autocorrect="off" spellcheck="false" data-lpignore="true" data-1p-ignore="true">
                        <input type="text" class="code-digit" id="code-digit-2" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                        <input type="text" class="code-digit" id="code-digit-3" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                        <input type="text" class="code-digit" id="code-digit-4" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                        <input type="text" class="code-digit" id="code-digit-5" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                        <input type="text" class="code-digit" id="code-digit-6" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                    </div>
                    <p class="code-beta-hint">BETA: {{ $betaEmailBindPasscode }}</p>
                    <div class="email-invalid-msg" id="code-invalid-msg" data-msg="Invalid code." aria-live="polite"><span></span></div>
                </form>
            </div>
        </div>
    </div>

    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.profilesettings', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; transition: transform 0.1s ease;" aria-label="{{ __('Back') }}">
            <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
        </a>
        <span style="color: white; font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;">Email Binding</span>
    </div>

    <script>
        (function() {
            var glassBox = document.querySelector('.glass-box');
            var otpAnchor = document.getElementById('code-input-row');

            function adjustMobileGlassBox() {
                if (!glassBox) return;
                if (window.innerWidth > 768) {
                    glassBox.style.removeProperty('--mobile-glass-min-height');
                    return;
                }
                if (!otpAnchor) return;

                var boxRect = glassBox.getBoundingClientRect();
                var anchorRect = otpAnchor.getBoundingClientRect();
                var desiredGap = 35;
                var relativeBottom = anchorRect.bottom - boxRect.top;
                var desiredMinHeight = Math.max(420, Math.ceil(relativeBottom + desiredGap));
                glassBox.style.setProperty('--mobile-glass-min-height', desiredMinHeight + 'px');
            }

            function queueAdjust() {
                window.requestAnimationFrame(adjustMobileGlassBox);
            }

            window.addEventListener('resize', queueAdjust);
            document.addEventListener('input', queueAdjust, true);
            document.addEventListener('blur', queueAdjust, true);
            document.addEventListener('click', function() {
                window.setTimeout(queueAdjust, 30);
            }, true);

            var observer = new MutationObserver(queueAdjust);
            observer.observe(document.body, { subtree: true, attributes: true, attributeFilter: ['class', 'style'] });

            queueAdjust();
        })();

        (function() {
            var BIND_OTP_CODE = @json($betaEmailBindPasscode);
            var codeDigits = Array.prototype.slice.call(document.querySelectorAll('.code-digit'));
            var successRedirect = '{{ route('customer.profilesettings', ['name' => $user->profileSlug()]) }}';
            var bindEmailEndpoint = '{{ route('customer.profile.bind-email') }}';
            var csrfToken = '{{ csrf_token() }}';
            var pendingEmail = (new URLSearchParams(window.location.search).get('email') || '').trim().toLowerCase();
            var codeInvalidEl = document.getElementById('code-invalid-msg');
            var codeInvalidSpan = codeInvalidEl ? codeInvalidEl.querySelector('span') : null;
            var codeInvalidText = codeInvalidEl ? codeInvalidEl.getAttribute('data-msg') : '';
            var isSubmitting = false;

            function currentCode() {
                return codeDigits.map(function(input) {
                    return (input.value || '').replace(/\D/g, '').slice(0, 1);
                }).join('');
            }

            function clearCodeError() {
                if (codeInvalidSpan) { codeInvalidSpan.textContent = ''; }
                if (codeInvalidEl) { codeInvalidEl.classList.remove('visible'); }
            }

            function showCodeError() {
                if (codeInvalidSpan) { codeInvalidSpan.textContent = codeInvalidText; }
                if (codeInvalidEl) { codeInvalidEl.classList.add('visible'); }
            }

            function handleOtpCheck() {
                if (codeDigits.length !== 6) return;
                var code = currentCode();
                if (code.length < 6) return;
                if (isSubmitting) return;
                if (code === BIND_OTP_CODE) {
                    clearCodeError();
                    if (!pendingEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(pendingEmail)) {
                        showCodeError();
                        return;
                    }
                    isSubmitting = true;
                    fetch(bindEmailEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ email: pendingEmail })
                    }).then(function(response) {
                        return response.json().catch(function() { return {}; }).then(function(data) {
                            return { ok: response.ok, data: data };
                        });
                    }).then(function(result) {
                        if (!result.ok || !result.data.ok) {
                            showCodeError();
                            isSubmitting = false;
                            return;
                        }
                        window.location.href = successRedirect;
                    }).catch(function() {
                        showCodeError();
                        isSubmitting = false;
                    });
                    return;
                }
                showCodeError();
            }

            codeDigits.forEach(function(input, index, arr) {
                input.addEventListener('input', function(e) {
                    var raw = (e.target.value || '').replace(/\D/g, '');
                    e.target.value = raw.slice(-1);
                    clearCodeError();
                    if (e.target.value && index < arr.length - 1) {
                        arr[index + 1].focus();
                    }
                    handleOtpCheck();
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        arr[index - 1].focus();
                    }
                });
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    var text = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, arr.length);
                    if (!text) return;
                    text.split('').forEach(function(ch, i) { arr[i].value = ch; });
                    arr[Math.min(text.length, arr.length) - 1].focus();
                    clearCodeError();
                    handleOtpCheck();
                });
            });

            if (codeDigits[0]) {
                codeDigits[0].focus();
            }
        })();

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
</body>
</html>
