<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>BruDMS</title>
        @include('partials.favicon')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
        <style>
            @keyframes glass-box-enter {
                from {
                    opacity: 0;
                    transform: translateY(36px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            @keyframes glass-box-enter-mobile {
                from {
                    opacity: 0;
                    transform: translateY(12px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .glass-box {
                animation: glass-box-enter 0.32s ease-out forwards;
            }
            img {
                -webkit-user-drag: none;
                user-select: none;
                -webkit-user-select: none;
            }
            @media (max-width: 768px) {
                #bg-desktop { display: none !important; }
                #bg-mobile { display: block !important; }
            }
            @media (max-width: 768px) {
                .glass-box {
                    flex-direction: column !important;
                    max-width: 82% !important;
                    width: 82% !important;
                    min-height: 60vh !important;
                    animation: glass-box-enter 0.5s ease-out forwards !important;
                }
                .right-panel-signup {
                    transition: transform 0.4s ease-out, opacity 0.15s ease-out !important;
                }
                .right-panel-login.visible {
                    transition-duration: 0.2s !important;
                }
                .right-panel-login.exit {
                    transition: transform 0.4s ease-out, opacity 0.15s ease-out !important;
                }
                .glass-box-left {
                    flex: 0 0 auto !important;
                    min-height: 12vh !important;
                    height: 12vh !important;
                }
                .glass-box-right {
                    flex: 1 1 auto !important;
                    min-height: 0 !important;
                }
                .glass-box-left img[alt=""] {
                    min-height: 0 !important;
                }
                .powered-by {
                    font-size: 0.58rem !important;
                }
                .panel-logo {
                    height: 2.25rem !important;
                }
                .lang-toggle-wrap {
                    top: calc(-12vh + 0.4rem) !important;
                    bottom: auto !important;
                    margin-bottom: 0 !important;
                    right: 0.5rem !important;
                }
                .lang-toggle a {
                    padding: 0.12rem 0.32rem !important;
                    font-size: 0.48rem !important;
                }
                .create-account-header {
                    top: 11% !important;
                }
                #right-panel-login .create-account-header {
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
                .signup-form {
                    gap: 0.6rem !important;
                    max-width: 100% !important;
                }
                .glass-input {
                    padding: 0.5rem 0.7rem !important;
                    font-size: 0.82rem !important;
                }
                .login-prompt {
                    margin-top: 0.5rem !important;
                    font-size: 0.52rem !important;
                }
                .signup-form-wrap {
                    top: 23% !important;
                    max-width: 16.5rem !important;
                }
                .signup-form-wrap.login-form-wrap {
                    top: 32% !important;
                }
                .signup-form {
                    max-width: 16.5rem !important;
                }
                .glass-input {
                    font-size: 0.75rem !important;
                }
                .password-toggle {
                    width: 1.15rem !important;
                    height: 1.15rem !important;
                    right: 0.65rem !important;
                }
                .password-toggle svg {
                    width: 0.95rem !important;
                    height: 0.95rem !important;
                }
                .btn-create-account {
                    font-size: 0.88rem !important;
                    padding: 0.6rem 1rem !important;
                    max-width: 12rem !important;
                    margin-top: 1.2rem !important;
                }
                .password-strength:not(.visible) {
                    margin-top: -0.6rem !important;
                    margin-bottom: 0 !important;
                }
                .email-invalid-msg:not(.visible) {
                    margin-top: -0.6rem !important;
                    margin-bottom: 0 !important;
                }
                .password-strength.visible,
                .email-invalid-msg.visible,
                .password-match-msg.visible {
                    margin-top: 0.25rem !important;
                }
                .email-invalid-msg.visible {
                    margin-top: -0.75rem !important;
                }
                .password-strength.visible {
                    margin-top: -0.75rem !important;
                }
                .password-match-msg.visible {
                    margin-top: -0.25rem !important;
                }
                .password-strength span,
                .email-invalid-msg span,
                .password-match-msg span {
                    font-size: 0.62rem !important;
                }
            }
            @media (min-width: 769px) {
                .glass-box {
                    position: relative;
                }
                .glass-box-left {
                    flex: 0 0 35% !important;
                }
                .glass-box-right {
                    flex: 0 0 65% !important;
                }
                .image-quote {
                    display: block !important;
                }
                #right-panel-login .create-account-header {
                    top: 26% !important;
                }
                #right-panel-login .signup-form-wrap.login-form-wrap {
                    top: 36% !important;
                }
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
            .lang-toggle {
                display: inline-flex;
                border-radius: 9999px;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.2);
                background: rgba(255, 255, 255, 0.06);
            }
            .lang-toggle a {
                padding: 0.35rem 0.85rem;
                font-size: 0.8rem;
                font-weight: 700;
                font-family: 'Poppins', sans-serif;
                text-decoration: none;
                color: rgba(255, 255, 255, 0.65);
                transition: background 0.2s, color 0.2s;
            }
            .lang-toggle a:first-child {
                border-right: 1px solid rgba(255, 255, 255, 0.15);
            }
            .lang-toggle a.active {
                background: rgba(255, 255, 255, 0.2);
                color: rgba(255, 255, 255, 0.95);
            }
            .lang-toggle a:not(.active):hover {
                background: rgba(255, 255, 255, 0.08);
                color: rgba(255, 255, 255, 0.85);
            }
            .signup-form {
                display: flex;
                flex-direction: column;
                gap: 0.95rem;
                width: 100%;
                max-width: 26rem;
            }
            .glass-input {
                width: 100%;
                padding: 0.75rem 0.9rem 0.75rem 1.3rem;
                border-radius: 9999px;
                border: 1px solid rgba(255, 255, 255, 0.15);
                background: rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                color: #fff;
                font-family: 'Poppins', sans-serif;
                font-size: 0.95rem;
                font-weight: 600;
                box-sizing: border-box;
                outline: none;
                transition: border-color 0.2s, background 0.2s;
            }
            .glass-input::placeholder {
                color: rgba(255, 255, 255, 0.5);
            }
            .glass-input:focus {
                border-color: rgba(255, 255, 255, 0.3);
                background: rgba(255, 255, 255, 0.1);
            }
            .glass-input:-webkit-autofill,
            .glass-input:-webkit-autofill:hover,
            .glass-input:-webkit-autofill:focus,
            .glass-input:-webkit-autofill:active {
                background: rgba(255, 255, 255, 0.08) !important;
                -webkit-backdrop-filter: blur(8px) !important;
                backdrop-filter: blur(8px) !important;
                -webkit-text-fill-color: #fff !important;
                caret-color: #fff;
                border: 1px solid rgba(255, 255, 255, 0.15) !important;
                transition: background-color 5000s ease-in-out 0s;
            }
            .password-wrap {
                position: relative;
                width: 100%;
            }
            .password-wrap .glass-input {
                padding-right: 2.75rem;
            }
            .password-toggle {
                position: absolute;
                right: 0.8rem;
                top: 50%;
                transform: translateY(-50%);
                width: 1.5rem;
                height: 1.5rem;
                padding: 0;
                border: none;
                background: none;
                cursor: pointer;
                color: rgba(255, 255, 255, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.25rem;
                transition: color 0.2s;
            }
            .password-toggle:hover {
                color: #fff;
            }
            .password-toggle svg {
                width: 1.25rem;
                height: 1.25rem;
            }
            .password-toggle .icon-hide { display: none; }
            .password-toggle .icon-show { display: block; }
            .password-toggle[aria-pressed="true"] .icon-hide { display: block; }
            .password-toggle[aria-pressed="true"] .icon-show { display: none; }
            .login-prompt {
                margin: 0.65rem 0 0;
                font-family: 'Poppins', sans-serif;
                font-weight: 400;
                font-size: 0.72rem;
                color: #fff;
                text-align: right;
            }
            .login-link {
                color: cyan;
                text-decoration: none;
                display: inline-block;
                transition: transform 0.15s ease;
                -webkit-tap-highlight-color: transparent;
                outline: none;
            }
            .login-link:focus {
                outline: none;
            }
            .login-link:active {
                outline: none;
                transform: scale(0.92);
            }
            .btn-create-account {
                display: block;
                width: 100%;
                max-width: 17rem;
                margin: 1.85rem auto 0;
                padding: 0.75rem 1.25rem;
                background: #2563eb;
                color: #fff;
                font-family: 'Poppins', sans-serif;
                font-size: 1.2rem;
                font-weight: 600;
                letter-spacing: 0.02em;
                border: none;
                border-radius: 9999px;
                cursor: pointer;
                text-align: center;
                transition: background 0.2s, transform 0.15s;
            }
            .btn-create-account:hover {
                background: #1d4ed8;
            }
.btn-create-account:active {
                transform: scale(0.98);
            }
            .btn-create-account:disabled,
            .btn-create-account.btn-disabled {
                background: rgba(255, 255, 255, 0.2);
                color: rgba(255, 255, 255, 0.5);
                cursor: not-allowed;
                pointer-events: none;
            }
            .password-match-msg,
            .password-strength,
            .email-invalid-msg {
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                margin: 0;
                padding: 0;
                min-height: 0;
                transition: max-height 0.35s ease-out, opacity 0.25s ease-out, margin 0.35s ease-out;
            }
            .password-strength:not(.visible) {
                margin-top: -0.95rem;
                margin-bottom: 0;
            }
            .password-match-msg.visible,
            .password-strength.visible,
            .email-invalid-msg.visible {
                max-height: 3rem;
                opacity: 1;
            }
            .email-invalid-msg:not(.visible) {
                margin-top: -0.95rem;
                margin-bottom: 0;
            }
            .email-invalid-msg.visible {
                margin: -0.95rem 0 0 0;
            }
            .password-strength.visible {
                margin: -0.95rem 0 0 0;
            }
            .password-match-msg span,
            .password-strength span,
            .email-invalid-msg span {
                display: block;
                font-size: 0.72rem;
                font-family: 'Poppins', sans-serif;
                padding: 0.35rem 0 0;
                padding-left: 1rem;
            }
            .password-match-msg span,
            .email-invalid-msg span {
                color: rgba(255, 120, 120, 0.95);
            }
            .password-strength.weak span { color: rgba(255, 120, 120, 0.95); }
            .password-strength.moderate span { color: #e4a853; }
            .password-strength.strong span { color: #6ee7b7; }
            .right-panel-signup {
                position: absolute;
                inset: 0;
                transition: transform 0.35s ease-out, opacity 0.35s ease-out;
            }
            .right-panel-signup.exit {
                transform: translateX(-80px);
                opacity: 0;
                pointer-events: none;
            }
            .right-panel-signup.hidden {
                visibility: hidden;
                pointer-events: none;
                opacity: 0;
                transform: translateX(48px);
            }
            .lang-toggle-wrap {
                z-index: 10;
            }
            .right-panel-login {
                position: absolute;
                inset: 0;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateX(48px);
                transition: none;
            }
            .right-panel-login.visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(0);
                transition: transform 0.28s ease-out, opacity 0.28s ease-out, visibility 0s;
            }
            .right-panel-login.exit {
                transform: translateX(-80px);
                opacity: 0;
                pointer-events: none;
                transition: transform 0.35s ease-out, opacity 0.35s ease-out;
            }
        </style>
    </head>
    <body style="min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; padding: 1rem; box-sizing: border-box;">
        <div id="bg-desktop" style="position: fixed; inset: 0; z-index: -1; background-image: url('/images/crdboard.svg'); background-size: cover; background-position: center; background-repeat: no-repeat;" aria-hidden="true"></div>
        <div id="bg-mobile" style="position: fixed; inset: 0; z-index: -1; overflow: hidden; display: none;" aria-hidden="true">
            <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('/images/crdboard.svg'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
        </div>
        <div
            class="glass-box"
            style="
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
            "
        >
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
                <div class="lang-toggle-wrap" style="position: absolute; top: 0.75rem; right: 1rem;">
                    <div class="lang-toggle" role="group" aria-label="Language">
                        <a href="{{ route('locale', 'ms') }}" class="{{ app()->getLocale() === 'ms' ? 'active' : '' }}">BM</a>
                        <a href="{{ route('locale', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                    </div>
                </div>
                <!-- Right side content goes here -->
                <div id="right-panel-signup" class="right-panel-signup{{ request()->is('login') ? ' hidden' : '' }}">
                    <div class="create-account-header" style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                        <div class="create-account-text" style="font-family: 'Poppins', sans-serif; font-size: 2.1rem; font-weight: 600; color: #fff; white-space: nowrap;">
                            {{ __('create_account') }}
                        </div>
                        <p class="tagline" style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 0.78rem; font-weight: 400; font-style: italic; color: rgba(255, 255, 255, 0.9); text-align: center;">
                            &ldquo;Join <span style="color: #3b82f6;">Bru</span>Flow, report what you know&rdquo;
                        </p>
                    </div>
                    <div class="signup-form-wrap" style="position: absolute; top: 31%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 26rem; padding: 0 1rem; box-sizing: border-box;">
                        @if ($errors->any())
                            <div class="form-errors" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; font-size: 0.85rem;">
                                @foreach ($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                            </div>
                        @endif
                        <form id="signup-form" class="signup-form" action="{{ route('register.store') }}" method="post">
                            @csrf
                            <input type="text" class="glass-input" name="name" placeholder="{{ __('username') }}" autocomplete="name" id="username" value="{{ old('name') }}">
                            <input type="email" class="glass-input" name="email" placeholder="you@example.com" autocomplete="email" id="email" value="{{ old('email') }}">
                            <div class="email-invalid-msg" id="email-invalid-msg" data-msg="{{ __('validation_email_invalid') }}" aria-live="polite"><span></span></div>
                            <div class="password-wrap">
                                <input type="password" class="glass-input" name="password" placeholder="{{ __('password') }}" autocomplete="new-password" id="password">
                                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false" data-target="password">
                                    <span class="icon-show" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                                    <span class="icon-hide" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></span>
                                </button>
                            </div>
                            <div class="password-strength" id="password-strength" data-weak="{{ __('password_strength_weak') }}" data-moderate="{{ __('password_strength_moderate') }}" data-strong="{{ __('password_strength_strong') }}" aria-live="polite"><span></span></div>
                            <div class="password-wrap">
                                <input type="password" class="glass-input" name="password_confirmation" placeholder="{{ __('confirm_password') }}" autocomplete="new-password" id="password_confirmation">
                                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false" data-target="password_confirmation">
                                    <span class="icon-show" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                                    <span class="icon-hide" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></span>
                                </button>
                            </div>
                        </form>
                        <div class="password-match-msg" id="password-match-msg" data-msg="{{ __('validation_password_match') }}" aria-live="polite"><span></span></div>
                        <p class="login-prompt"><a href="{{ route('login') }}" class="login-link" id="show-login">{{ __('already_have_account') }}</a></p>
                        <button type="submit" form="signup-form" class="btn-create-account btn-disabled" id="btn-create-account" disabled>{{ __('create_account_btn') }}</button>
                    </div>
                </div>
                <div id="right-panel-login" class="right-panel-login{{ request()->is('login') ? ' visible' : '' }}">
                    <div class="create-account-header" style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                        <div class="create-account-text" style="font-family: 'Poppins', sans-serif; font-size: 2.1rem; font-weight: 600; color: #fff; white-space: nowrap;">
                            {{ __('log_in') }}
                        </div>
                        <p class="tagline" style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 0.78rem; font-weight: 400; font-style: italic; color: rgba(255, 255, 255, 0.9); text-align: center;">
                            &ldquo;Take control. Stop the flood&rdquo;
                        </p>
                    </div>
                    <div class="signup-form-wrap login-form-wrap" style="position: absolute; top: 31%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 26rem; padding: 0 1rem; box-sizing: border-box;">
                        @if ($errors->any())
                            <div class="form-errors" style="margin-top: -1.5rem; margin-bottom: 0.75rem; padding: 0.4rem 0.6rem; border-radius: 0.5rem; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; font-size: 0.65rem; font-family: 'Poppins', sans-serif;">
                                @foreach ($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                            </div>
                        @endif
                        <form id="login-form" class="signup-form" action="{{ route('login.store') }}" method="post">
                            @csrf
                            <input type="email" class="glass-input" name="email" placeholder="{{ __('email') }}" autocomplete="email" id="login-email" value="{{ old('email') }}">
                            <div class="email-invalid-msg" id="login-email-invalid-msg" data-msg="{{ __('validation_email_invalid') }}" aria-live="polite"><span></span></div>
                            <div class="password-wrap">
                                <input type="password" class="glass-input" name="password" placeholder="{{ __('password') }}" autocomplete="current-password" id="login-password">
                                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false" data-target="login-password">
                                    <span class="icon-show" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                                    <span class="icon-hide" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></span>
                                </button>
                            </div>
                        </form>
                        <p class="login-prompt"><a href="{{ route('home') }}" class="login-link signup-back-link" id="show-signup">{{ __('create_account_prompt') }}</a></p>
                        <button type="submit" form="login-form" class="btn-create-account btn-disabled" id="btn-login" disabled>{{ __('log_in_btn') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="powered-by" style="position: fixed; bottom: 1rem; left: 50%; transform: translateX(-50%); text-align: center; font-family: 'Arial Nova', Arial, sans-serif; font-size: 0.72rem; font-weight: 200; color: rgba(255, 255, 255, 0.25); white-space: nowrap; z-index: 1;">
            {{ __('powered_by') }}
        </div>
        <script>
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
            document.querySelectorAll('.password-toggle').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var input = document.getElementById(this.getAttribute('data-target'));
                    if (!input) return;
                    var isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    this.setAttribute('aria-pressed', isHidden);
                    this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                });
            });

            (function() {
                var signupPanel = document.getElementById('right-panel-signup');
                var loginPanel = document.getElementById('right-panel-login');
                var showLogin = document.getElementById('show-login');
                var showSignup = document.getElementById('show-signup');

                function goToLogin() {
                    showLogin.addEventListener('click', function(e) {
                        e.preventDefault();
                        signupPanel.classList.add('exit');
                        signupPanel.addEventListener('transitionend', function onExit(e) {
                            if (e.propertyName !== 'opacity') return;
                            signupPanel.removeEventListener('transitionend', onExit);
                            signupPanel.classList.add('hidden');
                            signupPanel.classList.remove('exit');
                            loginPanel.classList.add('visible');
                            window.history.pushState({}, '', '{{ route("login") }}');
                        });
                    });
                }

                function goToSignup() {
                    showSignup.addEventListener('click', function(e) {
                        e.preventDefault();
                        loginPanel.classList.add('exit');
                        loginPanel.addEventListener('transitionend', function onExit(e) {
                            if (e.propertyName !== 'opacity') return;
                            loginPanel.removeEventListener('transitionend', onExit);
                            loginPanel.classList.remove('exit');
                            loginPanel.classList.remove('visible');
                            signupPanel.classList.remove('hidden');
                            window.history.pushState({}, '', '{{ route("home") }}');
                        });
                    });
                }

                goToLogin();
                goToSignup();
            })();

            (function() {
                var btn = document.getElementById('btn-create-account');
                var msgEl = document.getElementById('password-match-msg');
                var msgSpan = msgEl ? msgEl.querySelector('span') : null;
                var msgText = msgEl ? msgEl.getAttribute('data-msg') : '';
                var emailInvalidEl = document.getElementById('email-invalid-msg');
                var emailInvalidSpan = emailInvalidEl ? emailInvalidEl.querySelector('span') : null;
                var emailInvalidText = emailInvalidEl ? emailInvalidEl.getAttribute('data-msg') : '';
                var strengthEl = document.getElementById('password-strength');
                var strengthSpan = strengthEl ? strengthEl.querySelector('span') : null;
                var inputs = ['username', 'email', 'password', 'password_confirmation'];
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                function getStrength(password) {
                    if (!password || password.length < 8) return 'weak';
                    var hasLetter = /[a-zA-Z]/.test(password);
                    var hasNumber = /\d/.test(password);
                    var hasSpecial = /[^a-zA-Z0-9]/.test(password);
                    if (hasLetter && hasNumber && (hasSpecial || password.length >= 12)) return 'strong';
                    if (hasLetter && hasNumber) return 'moderate';
                    return 'moderate';
                }

                function updateStrength() {
                    var passwordEl = document.getElementById('password');
                    var p = passwordEl ? passwordEl.value : '';
                    if (!strengthEl || !strengthSpan) return;
                    strengthEl.classList.remove('visible', 'weak', 'moderate', 'strong');
                    if (p.length === 0) return;
                    var level = getStrength(p);
                    strengthSpan.textContent = strengthEl.getAttribute('data-' + level) || '';
                    strengthEl.classList.add('visible', level);
                }

                function allFilled() {
                    return inputs.every(function(id) {
                        var el = document.getElementById(id);
                        return el && (el.value || '').trim().length > 0;
                    });
                }

                function passwordsMatch() {
                    var p = document.getElementById('password');
                    var c = document.getElementById('password_confirmation');
                    if (!p || !c) return true;
                    return p.value === c.value;
                }

                function emailValid() {
                    var el = document.getElementById('email');
                    if (!el) return true;
                    var val = (el.value || '').trim();
                    if (val.length === 0) return true;
                    return emailRegex.test(val);
                }

                function updateButton() {
                    updateStrength();
                    var filled = allFilled();
                    var match = passwordsMatch();
                    var emailOk = emailValid();
                    var emailEl = document.getElementById('email');
                    var emailHasValue = emailEl && (emailEl.value || '').trim().length > 0;
                    var passwordEl = document.getElementById('password');
                    var confirmEl = document.getElementById('password_confirmation');
                    var bothHaveValue = (passwordEl && passwordEl.value.length > 0) && (confirmEl && confirmEl.value.length > 0);

                    if (emailHasValue && !emailOk) {
                        if (emailInvalidSpan) { emailInvalidSpan.textContent = emailInvalidText; }
                        if (emailInvalidEl) { emailInvalidEl.classList.add('visible'); }
                    } else {
                        if (emailInvalidSpan) { emailInvalidSpan.textContent = ''; }
                        if (emailInvalidEl) { emailInvalidEl.classList.remove('visible'); }
                    }

                    if (bothHaveValue && !match) {
                        if (msgSpan) { msgSpan.textContent = msgText; }
                        if (msgEl) { msgEl.classList.add('visible'); }
                    } else {
                        if (msgSpan) { msgSpan.textContent = ''; }
                        if (msgEl) { msgEl.classList.remove('visible'); }
                    }

                    if (emailHasValue && !emailOk) {
                        btn.disabled = true;
                        btn.classList.add('btn-disabled');
                    } else if (bothHaveValue && !match) {
                        btn.disabled = true;
                        btn.classList.add('btn-disabled');
                    } else {
                        var ok = filled && match && emailOk;
                        btn.disabled = !ok;
                        btn.classList.toggle('btn-disabled', !ok);
                    }
                }

                inputs.forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.addEventListener('input', updateButton);
                        el.addEventListener('blur', updateButton);
                    }
                });
                updateButton();
                updateStrength();
            })();

            (function() {
                var loginBtn = document.getElementById('btn-login');
                var loginEmail = document.getElementById('login-email');
                var loginPassword = document.getElementById('login-password');
                var loginEmailInvalidEl = document.getElementById('login-email-invalid-msg');
                var loginEmailInvalidSpan = loginEmailInvalidEl ? loginEmailInvalidEl.querySelector('span') : null;
                var loginEmailInvalidText = loginEmailInvalidEl ? loginEmailInvalidEl.getAttribute('data-msg') : '';
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                function isEmailValid(email) {
                    if (!email || email.trim().length === 0) return true; // Empty is valid (not required to show error)
                    return emailRegex.test(email);
                }

                function updateLoginButton() {
                    var emailValue = loginEmail ? (loginEmail.value || '').trim() : '';
                    var emailFilled = emailValue.length > 0;
                    var emailValid = isEmailValid(emailValue);
                    var passwordFilled = loginPassword && (loginPassword.value || '').trim().length > 0;
                    var bothFilled = emailFilled && passwordFilled;
                    var canSubmit = bothFilled && emailValid;

                    // Show/hide email invalid message
                    if (emailFilled && !emailValid) {
                        if (loginEmailInvalidSpan) { loginEmailInvalidSpan.textContent = loginEmailInvalidText; }
                        if (loginEmailInvalidEl) { loginEmailInvalidEl.classList.add('visible'); }
                    } else {
                        if (loginEmailInvalidSpan) { loginEmailInvalidSpan.textContent = ''; }
                        if (loginEmailInvalidEl) { loginEmailInvalidEl.classList.remove('visible'); }
                    }

                    // Enable/disable login button
                    if (loginBtn) {
                        loginBtn.disabled = !canSubmit;
                        loginBtn.classList.toggle('btn-disabled', !canSubmit);
                    }
                }

                if (loginEmail) {
                    loginEmail.addEventListener('input', updateLoginButton);
                    loginEmail.addEventListener('blur', updateLoginButton);
                }
                if (loginPassword) {
                    loginPassword.addEventListener('input', updateLoginButton);
                    loginPassword.addEventListener('blur', updateLoginButton);
                }
                updateLoginButton();
            })();
        </script>
    </body>
</html>
