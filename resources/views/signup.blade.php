<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>BruDMS</title>
        @include('partials.favicon')
        <link rel="preload" as="image" href="{{ asset('images/crdboard.png') }}">
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
            a,
            button,
            input,
            textarea,
            select {
                -webkit-tap-highlight-color: transparent;
            }
            a:focus,
            button:focus,
            input:focus,
            textarea:focus,
            select:focus {
                outline: none;
                box-shadow: none;
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
                    --mobile-glass-min-height: 60vh;
                    min-height: var(--mobile-glass-min-height) !important;
                    height: auto !important;
                    animation: glass-box-enter 0.5s ease-out forwards !important;
                    transition: min-height 0.22s ease-out !important;
                }
                .right-panel-signup {
                    transition: transform 0.44s ease-out, opacity 0.12s ease-out !important;
                }
                .right-panel-login.visible {
                    transition-duration: 0.2s !important;
                }
                .right-panel-login.exit {
                    transition: transform 0.44s ease-out, opacity 0.12s ease-out !important;
                }
                .right-panel-forgot {
                    transition: transform 0.44s ease-out, opacity 0.12s ease-out !important;
                }
                .right-panel-forgot.exit {
                    transition: transform 0.44s ease-out, opacity 0.12s ease-out !important;
                }
                .right-panel-code {
                    transition: transform 0.44s ease-out, opacity 0.12s ease-out !important;
                }
                .right-panel-code.exit {
                    transition: transform 0.44s ease-out, opacity 0.12s ease-out !important;
                }
                .right-panel-verified {
                    transition: transform 0.44s ease-out, opacity 0.12s ease-out !important;
                }
                .right-panel-verified.exit {
                    transition: transform 0.44s ease-out, opacity 0.12s ease-out !important;
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
                #right-panel-forgot .create-account-header {
                    top: 17% !important;
                }
                #right-panel-code .create-account-header {
                    top: 17% !important;
                }
                #right-panel-signup-code .create-account-header {
                    top: 15% !important;
                }
                #right-panel-verified .create-account-header {
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
                #code-invalid-msg.visible {
                    margin-top: 0.04rem !important;
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
                #right-panel-forgot .create-account-header {
                    top: 26% !important;
                }
                #right-panel-code .create-account-header {
                    top: 26% !important;
                }
                #right-panel-verified .create-account-header {
                    top: 26% !important;
                }
                #right-panel-login .signup-form-wrap.login-form-wrap {
                    top: 36% !important;
                }
                #right-panel-forgot .signup-form-wrap.login-form-wrap {
                    top: 36% !important;
                }
                #right-panel-code .signup-form-wrap.login-form-wrap {
                    top: 36% !important;
                }
                #right-panel-verified .signup-form-wrap.login-form-wrap {
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
            .code-beta-hint {
                margin: 0.55rem 0 0;
                font-family: 'Poppins', sans-serif;
                font-size: 0.72rem;
                font-weight: 500;
                color: rgba(255, 255, 255, 0.55);
                text-align: center;
                letter-spacing: 0.04em;
            }
            .code-digit:focus {
                border-color: rgba(255, 255, 255, 0.35);
                background: rgba(255, 255, 255, 0.12);
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
            .login-form-prompt {
                margin-right: 0.45rem;
            }
            #right-panel-signup .login-prompt {
                text-align: right;
                margin-right: 0.85rem;
            }
            .signup-back-prompt {
                text-align: center;
                margin-right: 0;
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
            .login-link.login-link-muted,
            .login-link.login-link-muted:visited,
            .login-link.login-link-muted:hover,
            .login-link.login-link-muted:active {
                color: rgba(255, 255, 255, 0.62);
                text-decoration: none;
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
            #code-invalid-msg.visible {
                margin: 0.14rem 0 0 0;
            }
            #code-invalid-msg {
                display: flex;
                justify-content: center;
            }
            #code-invalid-msg span {
                text-align: center;
                padding-left: 0;
                padding-right: 0;
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
                transform: translateX(-56px);
                opacity: 0;
                pointer-events: none;
                transition: transform 0.4s ease-out, opacity 0.12s ease-out;
            }
            .right-panel-signup.hidden {
                visibility: hidden;
                pointer-events: none;
                opacity: 0;
                transform: translateX(24px);
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
                transform: translateX(24px);
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
                transform: translateX(-56px);
                opacity: 0;
                pointer-events: none;
                transition: transform 0.4s ease-out, opacity 0.12s ease-out;
            }
            .right-panel-forgot {
                position: absolute;
                inset: 0;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateX(24px);
                transition: none;
            }
            .right-panel-forgot.visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(0);
                transition: transform 0.28s ease-out, opacity 0.28s ease-out, visibility 0s;
            }
            .right-panel-forgot.exit {
                transform: translateX(-56px);
                opacity: 0;
                pointer-events: none;
                transition: transform 0.4s ease-out, opacity 0.12s ease-out;
            }
            .right-panel-code {
                position: absolute;
                inset: 0;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateX(24px);
                transition: none;
            }
            .right-panel-code.visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(0);
                transition: transform 0.28s ease-out, opacity 0.28s ease-out, visibility 0s;
            }
            .right-panel-code.exit {
                transform: translateX(-56px);
                opacity: 0;
                pointer-events: none;
                transition: transform 0.4s ease-out, opacity 0.12s ease-out;
            }
            .right-panel-verified {
                position: absolute;
                inset: 0;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateX(24px);
                transition: none;
            }
            .right-panel-verified.visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(0);
                transition: transform 0.28s ease-out, opacity 0.28s ease-out, visibility 0s;
            }
            .right-panel-verified.exit {
                transform: translateX(-56px);
                opacity: 0;
                pointer-events: none;
                transition: transform 0.4s ease-out, opacity 0.12s ease-out;
            }
            .glass-box-right {
                border-radius: 0.75rem;
            }
        </style>
    </head>
    <body style="min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; padding: 1rem; box-sizing: border-box;">
        <div id="bg-desktop" style="position: fixed; inset: 0; z-index: -1; background-image: url('{{ e(asset('images/crdboard.png')) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;" aria-hidden="true"></div>
        <div id="bg-mobile" style="position: fixed; inset: 0; z-index: -1; overflow: hidden; display: none;" aria-hidden="true">
            <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('{{ e(asset('images/crdboard.png')) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
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
                @php
                    $betaSignupPasscode = '251206';
                    $betaForgotPasscode = '112904';
                @endphp
                <!-- Right side content goes here -->
                <div id="right-panel-signup" class="right-panel-signup{{ request()->is('login') || request()->routeIs('password.request') ? ' hidden' : '' }}">
                    <div class="create-account-header" style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                        <div class="create-account-text" style="font-family: 'Poppins', sans-serif; font-size: 2.1rem; font-weight: 600; color: #fff; white-space: nowrap;">
                            {{ __('create_account') }}
                        </div>
                        <p class="tagline" style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 0.78rem; font-weight: 400; font-style: italic; color: rgba(255, 255, 255, 0.9); text-align: center;">
                            &ldquo;Join <span style="color: #3b82f6;">Bru</span>Flow, report what you know&rdquo;
                        </p>
                    </div>
                    <div class="signup-form-wrap" style="position: absolute; top: 31%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 26rem; padding: 0 1rem; box-sizing: border-box;">
                        <form id="signup-form" class="signup-form" action="{{ route('register.store') }}" method="post" autocomplete="off">
                            @csrf
                            <input type="text" class="glass-input" name="name" placeholder="{{ __('username') }}" autocomplete="nickname" id="signup-name" value="{{ old('name') }}" autocapitalize="words" spellcheck="false" data-lpignore="true" data-1p-ignore="true">
                            <input type="tel" class="glass-input" name="phone" placeholder="+673 XXXXXXXX" autocomplete="tel" id="phone" value="{{ old('phone', '+673 ') }}" inputmode="tel" data-lpignore="true" data-1p-ignore="true">
                            <div class="email-invalid-msg" id="phone-invalid-msg" data-msg="Invalid phone number." aria-live="polite"><span></span></div>
                            @if ($errors->has('phone'))
                                <div class="email-invalid-msg visible" id="phone-server-error-msg" aria-live="polite"><span>{{ $errors->first('phone') }}</span></div>
                            @elseif ($errors->has('email'))
                                <div class="email-invalid-msg visible" id="phone-server-error-msg" aria-live="polite"><span>{{ $errors->first('email') }}</span></div>
                            @endif
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
                <div id="right-panel-signup-code" class="right-panel-code right-panel-signup-code">
                    <div class="create-account-header" style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                        <div class="create-account-text" style="font-family: 'Poppins', sans-serif; font-size: 2.1rem; font-weight: 600; color: #fff; white-space: nowrap;">
                            Enter your code
                        </div>
                        <p class="tagline" style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 0.78rem; font-weight: 400; font-style: italic; color: rgba(255, 255, 255, 0.9); text-align: center;">
                            "Everybody does, enter your email"
                        </p>
                    </div>
                    <div class="signup-form-wrap login-form-wrap" style="position: absolute; top: 32%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 26rem; padding: 0 1rem; box-sizing: border-box;">
                        <form id="signup-code-form" class="signup-form" action="#" method="post" onsubmit="return false;" autocomplete="off">
                            <div class="code-input-row" id="signup-code-input-row" aria-label="6 digit code">
                                <input type="text" class="signup-code-digit code-digit" id="signup-code-digit-1" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" autocorrect="off" spellcheck="false" data-lpignore="true" data-1p-ignore="true">
                                <input type="text" class="signup-code-digit code-digit" id="signup-code-digit-2" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                                <input type="text" class="signup-code-digit code-digit" id="signup-code-digit-3" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                                <input type="text" class="signup-code-digit code-digit" id="signup-code-digit-4" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                                <input type="text" class="signup-code-digit code-digit" id="signup-code-digit-5" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                                <input type="text" class="signup-code-digit code-digit" id="signup-code-digit-6" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                            </div>
                            <p class="code-beta-hint">BETA: {{ $betaSignupPasscode }}</p>
                            <div class="email-invalid-msg" id="signup-code-invalid-msg" data-msg="Invalid code." aria-live="polite"><span></span></div>
                        </form>
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
                        <form id="login-form" class="signup-form" action="{{ route('login.store') }}" method="post" autocomplete="on">
                            @csrf
                            <input type="text" class="glass-input" name="email" placeholder="Email or phone number" autocomplete="email" id="login-email" value="{{ old('email') }}" autocapitalize="off" spellcheck="false">
                            <div class="email-invalid-msg" id="login-email-invalid-msg" data-msg="Enter a valid email or phone number." aria-live="polite"><span></span></div>
                            @if ($errors->has('email'))
                                @php
                                    $loginError = $errors->first('email');
                                    if (stripos($loginError, 'credentials do not match') !== false) {
                                        $loginError = 'Check your username or password';
                                    }
                                @endphp
                                <div class="email-invalid-msg visible" id="login-email-server-error-msg" aria-live="polite"><span>{{ $loginError }}</span></div>
                            @endif
                            <div class="password-wrap">
                                <input type="password" class="glass-input" name="password" placeholder="{{ __('password') }}" autocomplete="current-password" id="login-password">
                                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false" data-target="login-password">
                                    <span class="icon-show" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                                    <span class="icon-hide" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></span>
                                </button>
                            </div>
                        </form>
                        @if (Route::has('password.request'))
                            <p class="login-prompt login-form-prompt"><a href="#forgot" class="login-link" id="show-forgot">{{ __('Forgot password?') }}</a></p>
                        @endif
                        <button type="submit" form="login-form" class="btn-create-account btn-disabled" id="btn-login" disabled>{{ __('log_in_btn') }}</button>
                        <p class="login-prompt login-form-prompt signup-back-prompt"><a href="{{ route('home') }}" class="login-link login-link-muted signup-back-link" id="show-signup">{{ __('create_account_prompt') }}</a></p>
                    </div>
                </div>
                <div id="right-panel-forgot" class="right-panel-forgot{{ request()->routeIs('password.request') ? ' visible' : '' }}">
                    <div class="create-account-header" style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                        <div class="create-account-text" style="font-family: 'Poppins', sans-serif; font-size: 2.1rem; font-weight: 600; color: #fff; white-space: nowrap;">
                            Forgot Password?
                        </div>
                        <p class="tagline" style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 0.78rem; font-weight: 400; font-style: italic; color: rgba(255, 255, 255, 0.9); text-align: center;">
                            "Everybody does, enter your email"
                        </p>
                    </div>
                    <div class="signup-form-wrap login-form-wrap" style="position: absolute; top: 31%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 26rem; padding: 0 1rem; box-sizing: border-box;">
                        <form id="forgot-form" class="signup-form" action="{{ route('password.email') }}" method="post" autocomplete="on">
                            @csrf
                            <input type="text" class="glass-input" name="email" placeholder="Email or phone number" autocomplete="email" id="forgot-email" value="{{ request()->routeIs('password.request') ? old('email') : '' }}" autocapitalize="off" spellcheck="false">
                            <div class="email-invalid-msg" id="forgot-email-invalid-msg" data-msg="Enter a valid email or phone number." aria-live="polite"><span></span></div>
                        </form>
                        <p class="login-prompt login-form-prompt"><a href="#login" class="login-link" id="show-login-from-forgot">{{ __('log_in') }}</a></p>
                        <button type="button" class="btn-create-account btn-disabled" id="btn-forgot" disabled>SEND CODE</button>
                    </div>
                </div>
                <div id="right-panel-code" class="right-panel-code">
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
                            <p class="code-beta-hint">BETA: {{ $betaForgotPasscode }}</p>
                            <div class="email-invalid-msg" id="code-invalid-msg" data-msg="Invalid code." aria-live="polite"><span></span></div>
                        </form>
                        <a href="#verified" id="show-verified-from-code" style="display:none" aria-hidden="true"></a>
                    </div>
                </div>
                <div id="right-panel-verified" class="right-panel-verified">
                    <div class="create-account-header" style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                        <div class="create-account-text" style="font-family: 'Poppins', sans-serif; font-size: 2.1rem; font-weight: 600; color: #fff; white-space: nowrap;">
                            Change Password
                        </div>
                        <p class="tagline" style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 0.78rem; font-weight: 400; font-style: italic; color: rgba(255, 255, 255, 0.9); text-align: center;">
                            "Almost done, choose your new password"
                        </p>
                    </div>
                    <div class="signup-form-wrap login-form-wrap" style="position: absolute; top: 31%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 26rem; padding: 0 1rem; box-sizing: border-box;">
                        <form id="change-password-form" class="signup-form" action="{{ route('password.update-customer') }}" method="post">
                            <input type="hidden" id="verified-reset-email" name="identifier" value="">
                            <div class="password-wrap">
                                <input type="password" class="glass-input" name="new_password" placeholder="{{ __('password') }}" autocomplete="new-password" id="verified-password">
                                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false" data-target="verified-password">
                                    <span class="icon-show" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                                    <span class="icon-hide" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></span>
                                </button>
                            </div>
                            <div class="password-strength" id="verified-password-strength" data-weak="{{ __('password_strength_weak') }}" data-moderate="{{ __('password_strength_moderate') }}" data-strong="{{ __('password_strength_strong') }}" aria-live="polite"><span></span></div>
                            <div class="password-wrap">
                                <input type="password" class="glass-input" name="new_password_confirmation" placeholder="{{ __('confirm_password') }}" autocomplete="new-password" id="verified-password-confirmation">
                                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false" data-target="verified-password-confirmation">
                                    <span class="icon-show" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                                    <span class="icon-hide" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></span>
                                </button>
                            </div>
                        </form>
                        <div class="password-match-msg" id="verified-password-match-msg" data-msg="{{ __('validation_password_match') }}" aria-live="polite"><span></span></div>
                        <div class="email-invalid-msg" id="verified-reset-msg" aria-live="polite"><span></span></div>
                        <div style="height: 1.4rem;"></div>
                        <button type="submit" form="change-password-form" class="btn-create-account btn-disabled" id="btn-confirm" disabled>CONFIRM</button>
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

            (function() {
                var glassBox = document.querySelector('.glass-box');

                function adjustMobileGlassBox() {
                    if (!glassBox) return;
                    if (window.innerWidth > 768) {
                        glassBox.style.removeProperty('--mobile-glass-min-height');
                        return;
                    }

                    var activePanel = document.querySelector('.right-panel-signup:not(.hidden), .right-panel-signup-code.visible, .right-panel-login.visible, .right-panel-forgot.visible, .right-panel-code.visible, .right-panel-verified.visible');
                    if (!activePanel) return;

                    var anchor = null;
                    if (activePanel.id === 'right-panel-login') {
                        var linkAnchors = activePanel.querySelectorAll('a.login-link');
                        var maxBottom = -Infinity;
                        linkAnchors.forEach(function(link) {
                            var style = window.getComputedStyle(link);
                            if (style.display === 'none' || style.visibility === 'hidden') return;
                            var rect = link.getBoundingClientRect();
                            if (rect.bottom > maxBottom) {
                                maxBottom = rect.bottom;
                                anchor = link;
                            }
                        });
                    }

                    if (!anchor) {
                        if (activePanel.id === 'right-panel-signup') {
                            anchor = activePanel.querySelector('#btn-create-account');
                        } else if (activePanel.id === 'right-panel-login') {
                            anchor = activePanel.querySelector('#btn-login');
                        } else if (activePanel.id === 'right-panel-signup-code') {
                            anchor = activePanel.querySelector('.code-input-row');
                        } else if (activePanel.id === 'right-panel-forgot') {
                            anchor = activePanel.querySelector('#btn-forgot');
                        } else if (activePanel.id === 'right-panel-code') {
                            anchor = activePanel.querySelector('.code-input-row');
                        } else if (activePanel.id === 'right-panel-verified') {
                            anchor = activePanel.querySelector('#btn-confirm');
                        } else {
                            anchor = activePanel.querySelector('#btn-create-account, #btn-login, #btn-forgot, #btn-confirm, .code-input-row');
                        }
                    }
                    if (!anchor) return;

                    var boxRect = glassBox.getBoundingClientRect();
                    var anchorRect = anchor.getBoundingClientRect();
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
                var signupCodePanel = document.getElementById('right-panel-signup-code');
                var loginPanel = document.getElementById('right-panel-login');
                var forgotPanel = document.getElementById('right-panel-forgot');
                var codePanel = document.getElementById('right-panel-code');
                var verifiedPanel = document.getElementById('right-panel-verified');
                var showLogin = document.getElementById('show-login');
                var showSignup = document.getElementById('show-signup');
                var showForgot = document.getElementById('show-forgot');
                var showSignupCodeFromSignup = document.getElementById('btn-create-account');
                var showLoginFromForgot = document.getElementById('show-login-from-forgot');
                var showCodeFromForgot = document.getElementById('btn-forgot');
                var showVerifiedFromCode = document.getElementById('show-verified-from-code');
                var forgotEmail = document.getElementById('forgot-email');
                var verifiedResetEmail = document.getElementById('verified-reset-email');
                var isSwitching = false;

                function onPanelExit(panel, done) {
                    var finished = false;
                    function finish() {
                        if (finished) return;
                        finished = true;
                        panel.removeEventListener('transitionend', onExit);
                        done();
                    }
                    function onExit(e) {
                        if (e.target !== panel || e.propertyName !== 'opacity') return;
                        finish();
                    }

                    panel.addEventListener('transitionend', onExit);
                    // Fallback in case transitionend is skipped by browser/compositor.
                    window.setTimeout(finish, 450);
                }

                function hidePanel(panel) {
                    if (panel === signupPanel) {
                        panel.classList.add('hidden');
                        panel.classList.remove('exit');
                        return;
                    }
                    panel.classList.remove('visible');
                    panel.classList.remove('exit');
                }

                function showPanel(panel) {
                    if (panel === signupPanel) {
                        panel.classList.remove('hidden');
                        panel.classList.remove('exit');
                        return;
                    }
                    panel.classList.add('visible');
                    panel.classList.remove('exit');
                }

                function switchPanel(fromPanel, toPanel, url) {
                    if (!fromPanel || !toPanel || isSwitching) return;
                    isSwitching = true;
                    fromPanel.classList.add('exit');
                    onPanelExit(fromPanel, function() {
                        hidePanel(fromPanel);
                        showPanel(toPanel);
                        if (url) {
                            window.history.pushState({}, '', url);
                        }
                        isSwitching = false;
                    });
                }

                function bindPanelSwitch(trigger, fromPanel, toPanel, url) {
                    if (!trigger) return;
                    trigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        switchPanel(fromPanel, toPanel, url);
                    });
                }

                function goToLogin() {
                    bindPanelSwitch(showLogin, signupPanel, loginPanel, '{{ route("login") }}');
                    bindPanelSwitch(showLoginFromForgot, forgotPanel, loginPanel, '{{ route("login") }}');
                }

                function goToSignup() {
                    bindPanelSwitch(showSignup, loginPanel, signupPanel, '{{ route("home") }}');
                }

                function goToForgot() {
                    bindPanelSwitch(showForgot, loginPanel, forgotPanel, '{{ route("password.request") }}');
                }

                function goToSignupCode() {
                    bindPanelSwitch(showSignupCodeFromSignup, signupPanel, signupCodePanel, null);
                }

                function goToCode() {
                    bindPanelSwitch(showCodeFromForgot, forgotPanel, codePanel, null);
                }

                function goToVerified() {
                    bindPanelSwitch(showVerifiedFromCode, codePanel, verifiedPanel, null);
                }

                function persistResetEmail() {
                    if (!forgotEmail || !verifiedResetEmail) return;
                    verifiedResetEmail.value = (forgotEmail.value || '').trim();
                }

                window.addEventListener('popstate', function() {
                    var path = window.location.pathname;
                    if (path === '{{ route("home", [], false) }}') {
                        hidePanel(loginPanel);
                        hidePanel(signupCodePanel);
                        hidePanel(forgotPanel);
                        hidePanel(codePanel);
                        hidePanel(verifiedPanel);
                        showPanel(signupPanel);
                        return;
                    }
                    if (path === '{{ route("password.request", [], false) }}') {
                        hidePanel(signupPanel);
                        hidePanel(loginPanel);
                        hidePanel(signupCodePanel);
                        hidePanel(codePanel);
                        hidePanel(verifiedPanel);
                        showPanel(forgotPanel);
                        return;
                    }
                    if (path === '{{ route("login", [], false) }}') {
                        hidePanel(signupPanel);
                        hidePanel(forgotPanel);
                        hidePanel(signupCodePanel);
                        hidePanel(codePanel);
                        hidePanel(verifiedPanel);
                        showPanel(loginPanel);
                    }
                });

                if (window.location.pathname === '{{ route("password.request", [], false) }}') {
                    hidePanel(signupPanel);
                    hidePanel(loginPanel);
                    hidePanel(signupCodePanel);
                    hidePanel(codePanel);
                    hidePanel(verifiedPanel);
                    showPanel(forgotPanel);
                } else if (window.location.pathname === '{{ route("login", [], false) }}') {
                    hidePanel(signupPanel);
                    hidePanel(forgotPanel);
                    hidePanel(signupCodePanel);
                    hidePanel(codePanel);
                    hidePanel(verifiedPanel);
                    showPanel(loginPanel);
                } else {
                    hidePanel(loginPanel);
                    hidePanel(forgotPanel);
                    hidePanel(signupCodePanel);
                    hidePanel(codePanel);
                    hidePanel(verifiedPanel);
                    showPanel(signupPanel);
                }

                goToLogin();
                goToSignup();
                goToForgot();
                goToSignupCode();
                goToCode();
                goToVerified();

                if (showCodeFromForgot) {
                    showCodeFromForgot.addEventListener('click', persistResetEmail);
                }
            })();

            (function() {
                var btn = document.getElementById('btn-create-account');
                var msgEl = document.getElementById('password-match-msg');
                var msgSpan = msgEl ? msgEl.querySelector('span') : null;
                var msgText = msgEl ? msgEl.getAttribute('data-msg') : '';
                var phoneInvalidEl = document.getElementById('phone-invalid-msg');
                var phoneInvalidSpan = phoneInvalidEl ? phoneInvalidEl.querySelector('span') : null;
                var phoneInvalidText = phoneInvalidEl ? phoneInvalidEl.getAttribute('data-msg') : '';
                var strengthEl = document.getElementById('password-strength');
                var strengthSpan = strengthEl ? strengthEl.querySelector('span') : null;
                var inputs = ['signup-name', 'phone', 'password', 'password_confirmation'];
                var phoneRegex = /^\+673\s\d{3}\s\d{4}$/;

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

                function phoneValid() {
                    var el = document.getElementById('phone');
                    if (!el) return true;
                    var val = (el.value || '').trim();
                    if (val.length === 0) return true;
                    return phoneRegex.test(val);
                }

                function formatBruneiPhone(rawValue) {
                    var digits = (rawValue || '').replace(/\D/g, '');
                    if (digits.startsWith('673')) {
                        digits = digits.slice(3);
                    }
                    digits = digits.slice(0, 7);
                    var first = digits.slice(0, 3);
                    var second = digits.slice(3, 7);
                    return '+673 ' + first + (second.length ? ' ' + second : '');
                }

                function localDigitsCount(rawValue) {
                    var digits = (rawValue || '').replace(/\D/g, '');
                    if (digits.startsWith('673')) {
                        digits = digits.slice(3);
                    }
                    return Math.min(digits.length, 7);
                }

                function updateButton() {
                    updateStrength();
                    var phoneInput = document.getElementById('phone');
                    if (phoneInput) {
                        phoneInput.value = formatBruneiPhone(phoneInput.value || '');
                    }
                    var filled = allFilled();
                    var match = passwordsMatch();
                    var phoneOk = phoneValid();
                    var phoneEl = document.getElementById('phone');
                    var phoneDigits = phoneEl ? localDigitsCount(phoneEl.value || '') : 0;
                    var phoneHasValue = phoneDigits > 0;
                    var passwordEl = document.getElementById('password');
                    var confirmEl = document.getElementById('password_confirmation');
                    var bothHaveValue = (passwordEl && passwordEl.value.length > 0) && (confirmEl && confirmEl.value.length > 0);

                    if (phoneHasValue && !phoneOk) {
                        if (phoneInvalidSpan) { phoneInvalidSpan.textContent = phoneInvalidText; }
                        if (phoneInvalidEl) { phoneInvalidEl.classList.add('visible'); }
                    } else {
                        if (phoneInvalidSpan) { phoneInvalidSpan.textContent = ''; }
                        if (phoneInvalidEl) { phoneInvalidEl.classList.remove('visible'); }
                    }

                    if (bothHaveValue && !match) {
                        if (msgSpan) { msgSpan.textContent = msgText; }
                        if (msgEl) { msgEl.classList.add('visible'); }
                    } else {
                        if (msgSpan) { msgSpan.textContent = ''; }
                        if (msgEl) { msgEl.classList.remove('visible'); }
                    }

                    if (phoneHasValue && !phoneOk) {
                        btn.disabled = true;
                        btn.classList.add('btn-disabled');
                    } else if (bothHaveValue && !match) {
                        btn.disabled = true;
                        btn.classList.add('btn-disabled');
                    } else {
                        var ok = filled && match && phoneOk;
                        btn.disabled = !ok;
                        btn.classList.toggle('btn-disabled', !ok);
                    }
                }

                function clearPhoneServerError() {
                    var serverErrorEl = document.getElementById('phone-server-error-msg');
                    if (!serverErrorEl) return;
                    serverErrorEl.classList.remove('visible');
                    var span = serverErrorEl.querySelector('span');
                    if (span) { span.textContent = ''; }
                }

                inputs.forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el) {
                        if (id === 'phone') {
                            el.addEventListener('input', clearPhoneServerError);
                        }
                        el.addEventListener('input', updateButton);
                        el.addEventListener('blur', updateButton);
                    }
                });
                updateButton();
                updateStrength();
            })();

            (function() {
                var loginBtn = document.getElementById('btn-login');
                var loginForm = document.getElementById('login-form');
                var loginEmail = document.getElementById('login-email');
                var loginPassword = document.getElementById('login-password');
                var loginEmailInvalidEl = document.getElementById('login-email-invalid-msg');
                var loginEmailInvalidSpan = loginEmailInvalidEl ? loginEmailInvalidEl.querySelector('span') : null;
                var loginEmailInvalidText = loginEmailInvalidEl ? loginEmailInvalidEl.getAttribute('data-msg') : '';
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                function isEmailOrPhoneValid(value) {
                    if (!value || value.trim().length === 0) return true; // Empty is valid (not required to show error)
                    var v = value.trim();
                    if (emailRegex.test(v)) return true;
                    var digits = v.replace(/\D/g, '');
                    if (digits.indexOf('673') === 0) { digits = digits.slice(3); }
                    return digits.length === 7;
                }

                function updateLoginButton() {
                    var emailValue = loginEmail ? (loginEmail.value || '').trim() : '';
                    var emailFilled = emailValue.length > 0;
                    var emailValid = isEmailOrPhoneValid(emailValue);
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

                // Password autofill often skips "input" until later; re-sync a few times after load
                function scheduleAutofillResync() {
                    [0, 50, 100, 200, 400, 800].forEach(function(ms) {
                        setTimeout(updateLoginButton, ms);
                    });
                }
                window.addEventListener('load', scheduleAutofillResync);
                if (document.readyState === 'complete') {
                    scheduleAutofillResync();
                }
                // Back/forward cache can restore a stale @csrf; refresh so token matches the session
                window.addEventListener('pageshow', function(ev) {
                    if (!ev.persisted) return;
                    var panel = document.getElementById('right-panel-login');
                    if (panel && panel.classList.contains('visible')) {
                        window.location.reload();
                    }
                });

                if (loginEmail) {
                    loginEmail.addEventListener('input', updateLoginButton);
                    loginEmail.addEventListener('blur', updateLoginButton);
                    loginEmail.addEventListener('change', updateLoginButton);
                }
                if (loginPassword) {
                    loginPassword.addEventListener('input', updateLoginButton);
                    loginPassword.addEventListener('blur', updateLoginButton);
                    loginPassword.addEventListener('change', updateLoginButton);
                }

                // If the browser or password manager triggers submit before our JS "sees" autofill, wait and re-submit
                if (loginForm) {
                    loginForm.addEventListener('submit', function onLoginFormSubmit(e) {
                        updateLoginButton();
                        if (!loginBtn || !loginBtn.disabled) {
                            return;
                        }
                        e.preventDefault();
                        var n = 0;
                        var max = 40;
                        var t = setInterval(function() {
                            n += 1;
                            updateLoginButton();
                            if (loginBtn && !loginBtn.disabled) {
                                clearInterval(t);
                                try {
                                    loginForm.requestSubmit(loginBtn);
                                } catch (err) {
                                    loginBtn.disabled = false;
                                    loginForm.submit();
                                }
                            } else if (n >= max) {
                                clearInterval(t);
                            }
                        }, 50);
                    });
                }

                updateLoginButton();
            })();

            (function() {
                var forgotBtn = document.getElementById('btn-forgot');
                var forgotEmail = document.getElementById('forgot-email');
                var forgotEmailInvalidEl = document.getElementById('forgot-email-invalid-msg');
                var forgotEmailInvalidSpan = forgotEmailInvalidEl ? forgotEmailInvalidEl.querySelector('span') : null;
                var forgotEmailInvalidText = forgotEmailInvalidEl ? forgotEmailInvalidEl.getAttribute('data-msg') : '';
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                function isEmailOrPhoneValid(value) {
                    if (!value || value.trim().length === 0) return true;
                    var v = value.trim();
                    if (emailRegex.test(v)) return true;
                    var digits = v.replace(/\D/g, '');
                    if (digits.indexOf('673') === 0) { digits = digits.slice(3); }
                    return digits.length === 7;
                }

                function setInvalidEmailMessage(visible) {
                    if (!forgotEmailInvalidEl || !forgotEmailInvalidSpan) return;
                    forgotEmailInvalidSpan.textContent = visible ? forgotEmailInvalidText : '';
                    forgotEmailInvalidEl.classList.toggle('visible', visible);
                }

                function setButtonState(enabled) {
                    if (!forgotBtn) return;
                    forgotBtn.disabled = !enabled;
                    forgotBtn.classList.toggle('btn-disabled', !enabled);
                }

                function updateForgotButton() {
                    var emailValue = forgotEmail ? (forgotEmail.value || '').trim() : '';
                    var emailFilled = emailValue.length > 0;
                    var emailValid = isEmailOrPhoneValid(emailValue);
                    var canSubmit = emailFilled && emailValid;

                    if (emailFilled && !emailValid) {
                        setInvalidEmailMessage(true);
                    } else {
                        setInvalidEmailMessage(false);
                    }

                    setButtonState(canSubmit);
                }

                if (forgotEmail) {
                    forgotEmail.addEventListener('input', updateForgotButton);
                    forgotEmail.addEventListener('blur', updateForgotButton);
                }

                updateForgotButton();
            })();

            (function() {
                var codeDigits = Array.prototype.slice.call(document.querySelectorAll('.signup-code-digit'));
                var codeInvalidEl = document.getElementById('signup-code-invalid-msg');
                var codeInvalidSpan = codeInvalidEl ? codeInvalidEl.querySelector('span') : null;
                var codeInvalidText = codeInvalidEl ? codeInvalidEl.getAttribute('data-msg') : '';
                var signupForm = document.getElementById('signup-form');
                var DUMMY_SIGNUP_CODE = @json($betaSignupPasscode);

                function sanitizeDigit(value) {
                    return (value || '').replace(/\D/g, '').slice(0, 1);
                }

                function currentCode() {
                    return codeDigits.map(function(input) {
                        return sanitizeDigit(input.value);
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

                function trySubmitWithDummyCode() {
                    if (codeDigits.length !== 6) return;
                    var code = currentCode();
                    if (code.length < 6) return;
                    if (code === DUMMY_SIGNUP_CODE) {
                        clearCodeError();
                        if (signupForm) signupForm.submit();
                        return;
                    }
                    showCodeError();
                }

                codeDigits.forEach(function(input, index) {
                    input.addEventListener('input', function() {
                        this.value = sanitizeDigit(this.value);
                        clearCodeError();
                        if (this.value && index < codeDigits.length - 1) {
                            codeDigits[index + 1].focus();
                        }
                        trySubmitWithDummyCode();
                    });

                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Backspace' && !this.value && index > 0) {
                            codeDigits[index - 1].focus();
                        }
                        if (e.key === 'ArrowLeft' && index > 0) {
                            e.preventDefault();
                            codeDigits[index - 1].focus();
                        }
                        if (e.key === 'ArrowRight' && index < codeDigits.length - 1) {
                            e.preventDefault();
                            codeDigits[index + 1].focus();
                        }
                    });
                });

                var codeInputRow = document.getElementById('signup-code-input-row');
                if (codeInputRow) {
                    codeInputRow.addEventListener('paste', function(e) {
                        var pasted = (e.clipboardData && e.clipboardData.getData('text')) || '';
                        var digits = pasted.replace(/\D/g, '').slice(0, codeDigits.length).split('');
                        if (!digits.length) return;
                        e.preventDefault();
                        codeDigits.forEach(function(input, index) {
                            input.value = digits[index] || '';
                        });
                        var focusIndex = Math.min(digits.length, codeDigits.length - 1);
                        codeDigits[focusIndex].focus();
                        clearCodeError();
                        trySubmitWithDummyCode();
                    });
                }
            })();

            (function() {
                var codeDigits = Array.prototype.slice.call(document.querySelectorAll('#code-input-row .code-digit'));
                var codeInvalidEl = document.getElementById('code-invalid-msg');
                var codeInvalidSpan = codeInvalidEl ? codeInvalidEl.querySelector('span') : null;
                var codeInvalidText = codeInvalidEl ? codeInvalidEl.getAttribute('data-msg') : '';
                var showVerifiedFromCode = document.getElementById('show-verified-from-code');
                var DUMMY_CODE = @json($betaForgotPasscode);

                function sanitizeDigit(value) {
                    return (value || '').replace(/\D/g, '').slice(0, 1);
                }

                function currentCode() {
                    return codeDigits.map(function(input) {
                        return sanitizeDigit(input.value);
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

                function tryAdvanceWithDummyCode() {
                    if (codeDigits.length !== 6) return;
                    var code = currentCode();
                    if (code.length < 6) return;
                    if (code === DUMMY_CODE) {
                        clearCodeError();
                        if (showVerifiedFromCode) { showVerifiedFromCode.click(); }
                        return;
                    }
                    showCodeError();
                }

                codeDigits.forEach(function(input, index) {
                    input.addEventListener('input', function() {
                        this.value = sanitizeDigit(this.value);
                        clearCodeError();
                        if (this.value && index < codeDigits.length - 1) {
                            codeDigits[index + 1].focus();
                        }
                        tryAdvanceWithDummyCode();
                    });

                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Backspace' && !this.value && index > 0) {
                            codeDigits[index - 1].focus();
                        }
                        if (e.key === 'ArrowLeft' && index > 0) {
                            e.preventDefault();
                            codeDigits[index - 1].focus();
                        }
                        if (e.key === 'ArrowRight' && index < codeDigits.length - 1) {
                            e.preventDefault();
                            codeDigits[index + 1].focus();
                        }
                    });
                });

                var codeInputRow = document.getElementById('code-input-row');
                if (codeInputRow) {
                    codeInputRow.addEventListener('paste', function(e) {
                        var pasted = (e.clipboardData && e.clipboardData.getData('text')) || '';
                        var digits = pasted.replace(/\D/g, '').slice(0, codeDigits.length).split('');
                        if (!digits.length) return;
                        e.preventDefault();
                        codeDigits.forEach(function(input, index) {
                            input.value = digits[index] || '';
                        });
                        var focusIndex = Math.min(digits.length, codeDigits.length - 1);
                        codeDigits[focusIndex].focus();
                        clearCodeError();
                        tryAdvanceWithDummyCode();
                    });
                }
            })();

            (function() {
                var form = document.getElementById('change-password-form');
                var confirmBtn = document.getElementById('btn-confirm');
                var passwordEl = document.getElementById('verified-password');
                var confirmPasswordEl = document.getElementById('verified-password-confirmation');
                var emailEl = document.getElementById('verified-reset-email');
                var strengthEl = document.getElementById('verified-password-strength');
                var strengthSpan = strengthEl ? strengthEl.querySelector('span') : null;
                var matchEl = document.getElementById('verified-password-match-msg');
                var matchSpan = matchEl ? matchEl.querySelector('span') : null;
                var matchText = matchEl ? matchEl.getAttribute('data-msg') : '';
                var resetMsgEl = document.getElementById('verified-reset-msg');
                var resetMsgSpan = resetMsgEl ? resetMsgEl.querySelector('span') : null;

                function getStrength(password) {
                    if (!password || password.length < 8) return 'weak';
                    var hasLetter = /[a-zA-Z]/.test(password);
                    var hasNumber = /\d/.test(password);
                    var hasSpecial = /[^a-zA-Z0-9]/.test(password);
                    if (hasLetter && hasNumber && (hasSpecial || password.length >= 12)) return 'strong';
                    if (hasLetter && hasNumber) return 'moderate';
                    return 'moderate';
                }

                function updateConfirmState() {
                    var passwordValue = passwordEl ? passwordEl.value : '';
                    var confirmValue = confirmPasswordEl ? confirmPasswordEl.value : '';
                    var bothFilled = passwordValue.length > 0 && confirmValue.length > 0;
                    var match = passwordValue === confirmValue;

                    if (strengthEl && strengthSpan) {
                        strengthEl.classList.remove('visible', 'weak', 'moderate', 'strong');
                        if (passwordValue.length > 0) {
                            var level = getStrength(passwordValue);
                            strengthSpan.textContent = strengthEl.getAttribute('data-' + level) || '';
                            strengthEl.classList.add('visible', level);
                        } else {
                            strengthSpan.textContent = '';
                        }
                    }

                    if (matchEl && matchSpan) {
                        if (bothFilled && !match) {
                            matchSpan.textContent = matchText;
                            matchEl.classList.add('visible');
                        } else {
                            matchSpan.textContent = '';
                            matchEl.classList.remove('visible');
                        }
                    }

                    if (confirmBtn) {
                        var canSubmit = bothFilled && match;
                        confirmBtn.disabled = !canSubmit;
                        confirmBtn.classList.toggle('btn-disabled', !canSubmit);
                    }
                }

                function setResetMessage(message) {
                    if (!resetMsgEl || !resetMsgSpan) return;
                    resetMsgSpan.textContent = message || '';
                    resetMsgEl.classList.toggle('visible', !!message);
                }

                if (passwordEl) {
                    passwordEl.addEventListener('input', updateConfirmState);
                    passwordEl.addEventListener('blur', updateConfirmState);
                }
                if (confirmPasswordEl) {
                    confirmPasswordEl.addEventListener('input', updateConfirmState);
                    confirmPasswordEl.addEventListener('blur', updateConfirmState);
                }

                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        updateConfirmState();
                        setResetMessage('');

                        if (!confirmBtn || confirmBtn.disabled) return;
                        var identifierValue = emailEl ? (emailEl.value || '').trim() : '';
                        if (!identifierValue) {
                            setResetMessage('Reset session expired. Start again from Forgot Password.');
                            return;
                        }

                        confirmBtn.disabled = true;
                        confirmBtn.classList.add('btn-disabled');

                        fetch('{{ route("password.update-customer") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                identifier: identifierValue,
                                password: passwordEl ? passwordEl.value : '',
                                password_confirmation: confirmPasswordEl ? confirmPasswordEl.value : ''
                            })
                        }).then(function(response) {
                            return response.json().catch(function() { return {}; }).then(function(data) {
                                return { ok: response.ok, data: data };
                            });
                        }).then(function(result) {
                            if (!result.ok || !result.data.ok) {
                                setResetMessage(result.data.message || 'Unable to change password.');
                                updateConfirmState();
                                return;
                            }
                            window.location.href = '{{ route("login") }}';
                        }).catch(function() {
                            setResetMessage('Unable to change password.');
                            updateConfirmState();
                        });
                    });
                }

                updateConfirmState();
            })();

        </script>
    </body>
</html>
