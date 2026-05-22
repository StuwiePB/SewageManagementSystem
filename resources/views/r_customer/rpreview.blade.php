<x-layouts::customer :title="__('Preview Report') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;600;700&display=swap" rel="stylesheet">
        @include('r_customer.partials.report-flow-spacing')
        <style>
            #preview-problem-text { font-size: clamp(12px, 4vw, 20px); font-weight: 700; font-family: Poppins, sans-serif; color: white; white-space: nowrap; }
            #preview-date-text { font-size: 12px; font-weight: 600; font-family: Poppins, sans-serif; color: rgba(255, 255, 255, 0.5); }
            .press-btn { transition: transform 0.06s ease; }
            .press-btn:active { transform: scale(0.92) !important; }
            #preview-photo-wrapper { position: fixed; left: calc(20px + (40px - 21px) / 2); top: calc(11vh + 100px); right: 20px; z-index: 2; display: flex; flex-direction: column; gap: var(--rflow-gap-section); align-items: stretch; }
            #preview-photo-box { width: calc(50vw - 20px - (40px - 21px) / 2); height: auto; aspect-ratio: 4/3; flex-shrink: 0; }
            .preview-info-row { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 10px; background: rgba(217, 217, 217, 0.06); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.25); color: white; font-size: 11px; font-family: Poppins, sans-serif; line-height: 1.3; }
            .preview-info-row img { flex-shrink: 0; width: 16px; height: 16px; }
            .preview-info-row.preview-details { align-items: stretch; min-height: 150px; background: rgba(217, 217, 217, 0.08); padding: 8px 3px 3px 3px; flex-direction: column; gap: 8px; }
            .preview-info-row.preview-details .preview-details-header { display: flex; align-items: center; gap: 8px; flex-shrink: 0; margin-left: 9px; }
            .preview-info-row.preview-details .preview-details-header img { margin: 0; }
            .preview-info-row.preview-details .preview-details-inner { display: flex; flex: 1; min-height: 0; width: 100%; }
            .preview-info-row.preview-details textarea { flex: 1; min-height: 130px; width: 100%; border-radius: 0 0 8px 8px; background: transparent; border: 1px solid rgba(255, 255, 255, 0.25); padding: 10px 6px 6px 12px; margin: 0; color: rgba(255, 255, 255, 0.9); font-size: 12px; font-family: Poppins, sans-serif; resize: none; outline: none; box-sizing: border-box; }
            .preview-info-row.preview-details textarea::placeholder { color: rgba(255, 255, 255, 0.4); }
            #preview-phone-text::placeholder,
            #preview-name-text::placeholder { color: rgba(255, 255, 255, 0.4); }
            #submit-report-btn:disabled {
                background: #6b7280 !important;
                color: rgba(255, 255, 255, 0.9) !important;
                box-shadow: none !important;
                cursor: not-allowed !important;
                opacity: 0.9;
            }
            #submit-report-btn:disabled:active {
                transform: none !important;
            }
            .temp-success-card {
                position: fixed;
                left: 50%;
                top: 51%;
                transform: translate(-50%, -50%);
                width: min(58vw, 216px);
                border-radius: 14px;
                background: #000024;
                border: 0.7px solid rgba(255,255,255,0.12);
                box-shadow: 0 12px 32px rgba(0,0,0,0.35);
                z-index: 60;
                padding: 0 10px 6px;
                box-sizing: border-box;
                overflow: hidden;
                color: #f4f4f5;
                font-family: Poppins, sans-serif;
                display: flex;
                flex-direction: column;
            }
            .temp-success-backdrop {
                position: fixed;
                inset: 0;
                z-index: 55;
                background: rgba(8, 14, 30, 0.22);
                backdrop-filter: blur(6px);
                -webkit-backdrop-filter: blur(6px);
            }
            .temp-success-inner {
                display: flex;
                flex-direction: column;
                transform: translateY(-32px);
                margin-bottom: -14px;
            }
            .temp-success-body {
                display: flex;
                flex-direction: column;
                flex-shrink: 0;
            }
            .temp-success-icon-wrap {
                width: 36px;
                height: 36px;
                margin: 0 auto 8px;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                /* Visual-only: lower + enlarge spinner/tick without shifting layout below */
                transform: translateY(var(--temp-icon-drop, 68px)) scale(var(--temp-icon-scale, 1.4));
            }
            .temp-half-spinner {
                width: 32px;
                height: 32px;
                animation: temp-spin 0.85s linear infinite;
                transition: opacity 0.25s ease;
                color: #04BCFF;
            }
            .temp-half-spinner-arc {
                fill: none;
                stroke: rgba(255, 255, 255, 0.45) !important;
                stroke-width: 4;
                stroke-linecap: round;
                stroke-dasharray: 56 120;
            }
            .temp-check {
                position: absolute;
                width: 30px;
                height: 30px;
                color: #04BCFF;
                opacity: 0;
                transform: scale(0.6);
                transition: opacity 0.25s ease, transform 0.25s ease;
            }
            #temp-loader-check.done .temp-half-spinner {
                opacity: 0;
                animation: none;
            }
            #temp-loader-check.done .temp-check {
                opacity: 1;
                transform: scale(1);
            }
            .temp-success-title {
                margin: 0 0 4px;
                text-align: center;
                font-size: 17px;
                line-height: 1;
                font-weight: 600;
                letter-spacing: -0.3px;
                color: #ece8eb;
                /* Visual-only: does not shift subtitle / divider / rows (margin would) */
                --temp-title-drop: 78px;
                transform: translateY(var(--temp-title-drop));
                transition: opacity 0.25s ease, transform 0.25s ease;
            }
            .temp-success-title--loading {
                /* Same cadence as .temp-half-spinner (0.85s linear) */
                animation: temp-title-loading 0.85s linear infinite;
            }
            @keyframes temp-title-loading {
                0%, 100% { opacity: 0.45; transform: translateY(var(--temp-title-drop)); }
                50% { opacity: 1; transform: translateY(var(--temp-title-drop)); }
            }
            .temp-success-title--done {
                animation: temp-title-success-in 0.32s ease-out forwards;
            }
            @keyframes temp-title-success-in {
                0% {
                    opacity: 0.55;
                    transform: translateY(var(--temp-title-drop)) scale(0.96);
                }
                100% {
                    opacity: 1;
                    transform: translateY(var(--temp-title-drop)) scale(1);
                }
            }
            .temp-success-sub {
                margin: 84px auto 0;
                max-width: 220px;
                text-align: center;
                font-size: 8px;
                line-height: 1.2;
                font-weight: 400;
                color: rgba(236, 232, 235, 0.7);
                /* Reserve ~3 lines so wrapping / copy changes don’t push the divider & rows */
                min-height: 32px;
            }
            .temp-success-divider {
                border: 0;
                border-top: 1px solid rgba(255,255,255,0.35);
                /* Negative top margin pairs with subtitle so line stays put (84 − 12 = 72) */
                margin: -12px 0 8px;
            }
            .temp-success-detail-rows {
                margin-top: 4px;
            }
            .temp-row {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                gap: 10px;
                margin: 4px 0;
                font-size: 8.5px;
            }
            .temp-row .k {
                color: rgba(236,232,235,0.9);
                font-weight: 500;
            }
            .temp-success-detail-rows .temp-row .k {
                padding-left: 10px;
            }
            .temp-success-detail-rows .temp-row .v {
                padding-right: 10px;
            }
            .temp-row .v {
                color: rgba(236,232,235,0.95);
                font-weight: 500;
                text-align: right;
                word-break: break-word;
            }
            .temp-continue {
                margin-top: 6px;
                flex-shrink: 0;
                display: block;
                width: 96%;
                margin-left: 2%;
                margin-right: 2%;
                height: 30px;
                /* Visual-only: sits lower without extra flex gap (margin-top grows the whole card) */
                transform: translateY(10px);
                border: none;
                border-radius: 9999px;
                background: #16c7ff;
                color: #000c2c;
                font-weight: 700;
                font-size: 11px;
                letter-spacing: 0.2px;
                cursor: pointer;
                transition: background 0.2s ease, color 0.2s ease, opacity 0.2s ease;
            }
            .temp-continue:disabled {
                background: #6b7280;
                color: rgba(236, 232, 235, 0.95);
                cursor: not-allowed;
                opacity: 0.9;
            }
            @keyframes temp-spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>
    @endpush

    @include('r_customer.partials.page-background')


    @php
        $guestReportFlow = filter_var($guestReportFlow ?? false, FILTER_VALIDATE_BOOLEAN);
        $user = auth()->user();
        $reportStep = function (string $step) use ($guestReportFlow, $user) {
            return $guestReportFlow
                ? route('guest.report.'.$step)
                : route('customer.'.$step, ['name' => $user->profileSlug()]);
        };
    @endphp
    @if($errors->any())
    <div style="position: fixed; top: 11vh; left: 20px; right: 20px; z-index: 25; padding: 12px; background: rgba(239, 68, 68, 0.9); color: white; border-radius: 12px; font-size: 13px; font-family: Poppins, sans-serif;">{{ $errors->first() }}</div>
    @endif
    <a href="{{ $reportStep('rdetails') }}" class="press-btn delayed-nav" style="position: fixed; top: 4vh; left: 20px; z-index: 20; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none;" aria-label="{{ __('Back') }}">
        <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
    </a>

    <div style="position: fixed; left: 6px; right: 6px; top: 11vh; bottom: -50vh; border-radius: 21px 21px 0 0; background: rgba(217, 217, 217, 0.07); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 1; pointer-events: none;"></div>

    <img src="{{ asset('images/Vectors/report_rpreview.svg') }}" alt="" style="position: fixed; left: calc(20px + (40px - 21px) / 2); top: calc(11vh + 30px); z-index: 5; width: 30px; height: 31px; pointer-events: none;" />

    <div id="preview-header" class="rflow-preview-header" style="position: fixed; left: 0; right: 0; top: calc(11vh + 28px); z-index: 5; display: flex; flex-direction: column;">
        <div class="rflow-header-col">
            <span class="rflow-header-line-primary">Finalised information, queued for action</span>
            <span class="rflow-header-line-secondary">Please verify all details before final submission</span>
        </div>
    </div>

    <div id="preview-photo-wrapper">
        <div id="preview-photo-box" style="border-radius: 7px; background: rgba(217, 217, 217, 0.06); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.25); overflow: hidden;">
            <img id="preview-photo" src="" alt="" style="width: 100%; height: 100%; object-fit: cover; display: none;" />
        </div>
        <div id="preview-info-stack" class="rflow-info-stack">
        <div class="rflow-name-phone-row">
            <div class="preview-info-row" id="preview-name-row" style="flex: 1; min-width: 0;">
                <img src="{{ asset('images/Vectors/rpreview_name.svg') }}" alt="" />
                <input type="text" id="preview-name-text" name="reporter_name" form="submit-report-form" value="{{ old('reporter_name', $user->name ?? '') }}" placeholder="Muhammad Ali" autocomplete="name" style="flex: 1; min-width: 0; background: transparent; border: none; padding: 0; margin: 0; color: white; font-size: 11px; font-family: Poppins, sans-serif; font-weight: 600; outline: none;" />
            </div>
            <div class="preview-info-row" id="preview-phone-row" style="flex: 1; min-width: 0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-opacity="0.44" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <input type="tel" id="preview-phone-text" value="{{ old('phone', $user->phone ?? '') }}" placeholder="+673 123 1234" autocomplete="tel" inputmode="tel" pattern="^\+673\s\d{3}\s\d{4}$" readonly aria-readonly="true" style="flex: 1; min-width: 0; background: transparent; border: none; padding: 0; margin: 0; color: rgba(255, 255, 255, 0.58); font-size: 11px; font-family: Poppins, sans-serif; font-weight: 600; outline: none; cursor: not-allowed;" />
            </div>
        </div>
        <div class="preview-info-row" id="preview-location-row">
            <img src="{{ asset('images/Vectors/all_location.svg') }}" alt="" />
            <span id="preview-location-text" style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
        </div>
        <div class="preview-info-row" id="preview-severity-row" style="min-width: 0;">
            <img src="{{ asset('images/Vectors/rpreview_severity.svg') }}" alt="" />
            <span id="preview-severity-text" style="font-weight: 600;"></span>
        </div>
        <div class="preview-info-row preview-details" id="preview-details-row">
            <div class="preview-details-header">
                <img src="{{ asset('images/Vectors/rpreview_details.svg') }}" alt="" />
            </div>
            <div class="preview-details-inner" style="flex: 1; min-width: 0; width: 100%;">
                <textarea id="preview-details-text" placeholder="Describe the problem..." readonly style="min-height: 120px;"></textarea>
            </div>
        </div>
        </div>
    </div>
    <form id="submit-report-form" method="POST" action="{{ $guestReportFlow ? route('guest.report.submit') : route('customer.report.submit', ['name' => $user->profileSlug()]) }}" style="position: fixed; left: 0; right: 0; bottom: 24px; z-index: 7; display: flex; justify-content: center;">
        @csrf
        <input type="hidden" name="problem_type" id="submit-problem_type" value="">
        <input type="hidden" name="phone" id="submit-phone" value="">
        <input type="hidden" name="severity" id="submit-severity" value="">
        <input type="hidden" name="description" id="submit-description" value="">
        <input type="hidden" name="address" id="submit-address" value="">
        <input type="hidden" name="latitude" id="submit-latitude" value="">
        <input type="hidden" name="longitude" id="submit-longitude" value="">
        <input type="hidden" name="photo" id="submit-photo" value="">
        <button type="submit" class="press-btn" id="submit-report-btn" disabled style="width: 82%; max-width: 360px; height: 48px; border: none; border-radius: 16px; background: #04BCFF; color: #0a1628; font-family: Poppins, sans-serif; font-weight: 700; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(4, 188, 255, 0.3);">Submit report</button>
    </form>
    <span id="preview-problem-text" style="position: fixed; left: 50%; top: calc(11vh + 106px); margin-left: 12px; max-width: calc(50vw - 12px - 24px); padding-right: 12px; z-index: 2; line-height: 1.2;"></span>
    <img src="{{ asset('images/Vectors/all_calempty.svg') }}" alt="" style="position: fixed; left: 50%; top: calc(11vh + 134px); margin-left: 12px; width: 15px; height: 15px; z-index: 2;" />
    <span id="preview-date-text" style="position: fixed; left: 50%; top: calc(11vh + 134px); margin-left: 36px; z-index: 2; line-height: 15px;"></span>

    <div id="js-submit-error" class="hidden" style="position: fixed; top: 11vh; left: 20px; right: 20px; z-index: 70; padding: 12px; background: rgba(239, 68, 68, 0.95); color: white; border-radius: 12px; font-size: 13px; font-family: Poppins, sans-serif;"></div>

    {{-- Post-submit success (shown after AJAX submit) --}}
    <div id="temp-success-backdrop" class="temp-success-backdrop" style="display: none;"></div>
    <div id="temp-success-card" class="temp-success-card" style="display: none;" data-redirect-url="">
        <div class="temp-success-inner">
        <div class="temp-success-body">
            <div id="temp-loader-check" class="temp-success-icon-wrap">
                <svg class="temp-half-spinner" viewBox="0 0 40 40" aria-hidden="true">
                    <circle class="temp-half-spinner-arc" cx="20" cy="20" r="12"></circle>
                </svg>
                <svg class="temp-check" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p id="temp-success-title" class="temp-success-title temp-success-title--loading" aria-live="polite" data-submitting-label="{{ __('Submitting…') }}" data-success-label="{{ __('Report submitted') }}">{{ __('Submitting…') }}</p>
            <p class="temp-success-sub">{{ __('Your report will be reviewed within 24 hours.') }}</p>
            <hr class="temp-success-divider">
            <div class="temp-success-detail-rows">
                <div class="temp-row"><span class="k">Ref Number</span><span class="v" id="temp-ref-text">FR SAL/0327/26(6456)</span></div>
                <div class="temp-row"><span class="k">Report Time</span><span class="v" id="temp-time-text">12:02:58</span></div>
                <div class="temp-row"><span class="k">Sender Name</span><span class="v" id="temp-name-text">{{ $user->name ?? 'Guest User' }}</span></div>
            </div>
        </div>
        <button type="button" id="temp-continue-btn" class="temp-continue">CONTINUE</button>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            var successAnimTimer = null;
            var card = document.getElementById('temp-success-card');
            var backdrop = document.getElementById('temp-success-backdrop');
            var cont = document.getElementById('temp-continue-btn');
            var loaderEl = document.getElementById('temp-loader-check');
            function setSuccessPopupVisible(visible) {
                if (card) card.style.display = visible ? 'flex' : 'none';
                if (backdrop) backdrop.style.display = visible ? 'block' : 'none';
            }
            function clearReportDraftStorage() {
                try {
                    [
                        'rpicture',
                        'rproblem_photo',
                        'rproblem_choice',
                        'rproblem_address',
                        'rproblem_lat',
                        'rproblem_lng',
                        'rproblem_severity',
                        'rproblem_description'
                    ].forEach(function(key) {
                        sessionStorage.removeItem(key);
                    });
                } catch (e) {}
            }

            function showSubmitError(msg) {
                var box = document.getElementById('js-submit-error');
                if (!box) return;
                box.textContent = msg || 'Something went wrong.';
                box.classList.remove('hidden');
                box.style.display = 'block';
            }
            function hideSubmitError() {
                var box = document.getElementById('js-submit-error');
                if (!box) return;
                box.textContent = '';
                box.classList.add('hidden');
                box.style.display = 'none';
            }

            function startSuccessRevealTimer() {
                if (successAnimTimer) clearTimeout(successAnimTimer);
                successAnimTimer = setTimeout(function() {
                    loaderEl.classList.add('done');
                    var titleEl = document.getElementById('temp-success-title');
                    if (titleEl) {
                        var ok = titleEl.getAttribute('data-success-label') || 'Report submitted';
                        titleEl.classList.remove('temp-success-title--loading');
                        titleEl.textContent = ok;
                        titleEl.classList.add('temp-success-title--done');
                    }
                    if (cont) {
                        cont.disabled = false;
                    }
                    successAnimTimer = null;
                }, 2200);
            }

            window.showReportSubmitting = function() {
                hideSubmitError();
                if (!card || !loaderEl) return;
                var ref = document.getElementById('temp-ref-text');
                var tim = document.getElementById('temp-time-text');
                var nam = document.getElementById('temp-name-text');
                var titleEl = document.getElementById('temp-success-title');
                if (ref) ref.textContent = '—';
                if (tim) tim.textContent = '—';
                if (nam) {
                    var ne = document.getElementById('preview-name-text');
                    nam.textContent = (ne && ne.value) ? ne.value.trim() : (nam.textContent || '');
                }
                card.setAttribute('data-redirect-url', '');
                setSuccessPopupVisible(true);
                loaderEl.classList.remove('done');
                if (cont) {
                    cont.disabled = true;
                }
                if (titleEl) {
                    var submitting = titleEl.getAttribute('data-submitting-label') || 'Submitting…';
                    titleEl.classList.remove('temp-success-title--done');
                    titleEl.classList.add('temp-success-title--loading');
                    titleEl.textContent = submitting;
                }
                if (successAnimTimer) clearTimeout(successAnimTimer);
            };

            window.showReportSuccessPopup = function(data) {
                hideSubmitError();
                if (!card || !loaderEl) return;
                var ref = document.getElementById('temp-ref-text');
                var tim = document.getElementById('temp-time-text');
                var nam = document.getElementById('temp-name-text');
                var titleEl = document.getElementById('temp-success-title');
                if (ref) ref.textContent = data.reference_code || '';
                if (tim) {
                    // Prefer full timestamp so browser timezone displays the correct "submitted at" time.
                    if (data && data.report_created_at) {
                        var d = new Date(data.report_created_at);
                        if (!isNaN(d.getTime())) {
                            var hh = String(d.getHours()).padStart(2, '0');
                            var mm = String(d.getMinutes()).padStart(2, '0');
                            var ss = String(d.getSeconds()).padStart(2, '0');
                            tim.textContent = hh + ':' + mm + ':' + ss;
                        } else {
                            tim.textContent = data.report_time || '';
                        }
                    } else {
                        tim.textContent = data.report_time || '';
                    }
                }
                if (nam) nam.textContent = data.sender_name || '';
                card.setAttribute('data-redirect-url', data.redirect_url || '');
                setSuccessPopupVisible(true);

                loaderEl.classList.remove('done');
                if (cont) {
                    cont.disabled = true;
                }
                if (titleEl) {
                    var submitting = titleEl.getAttribute('data-submitting-label') || 'Submitting…';
                    titleEl.classList.remove('temp-success-title--done');
                    titleEl.classList.add('temp-success-title--loading');
                    titleEl.textContent = submitting;
                }
                startSuccessRevealTimer();
            };

            if (cont && card) {
                cont.addEventListener('click', function() {
                    var url = card.getAttribute('data-redirect-url') || '';
                    setSuccessPopupVisible(false);
                    if (url) {
                        window.location.href = url;
                    }
                });
            }

            window.hideSubmitError = hideSubmitError;

            function firstJsonError(body) {
                if (!body) return 'Request failed.';
                if (body.message && typeof body.message === 'string') return body.message;
                if (body.errors && typeof body.errors === 'object') {
                    var keys = Object.keys(body.errors);
                    if (keys.length && body.errors[keys[0]] && body.errors[keys[0]][0]) {
                        return body.errors[keys[0]][0];
                    }
                }
                return 'Something went wrong.';
            }

            var submitForm = document.getElementById('submit-report-form');
            if (submitForm) {
                submitForm.addEventListener('submit', function(e) {
                    var btn = document.getElementById('submit-report-btn');
                    if (btn && btn.disabled) {
                        e.preventDefault();
                        return;
                    }
                    e.preventDefault();
                    var form = this;
                    hideSubmitError();
                    window.showReportSubmitting();
                    document.getElementById('submit-problem_type').value = sessionStorage.getItem('rproblem_choice') || '';
                    document.getElementById('submit-severity').value = sessionStorage.getItem('rproblem_severity') || '';
                    document.getElementById('submit-description').value = sessionStorage.getItem('rproblem_description') || '';
                    document.getElementById('submit-address').value = sessionStorage.getItem('rproblem_address') || '';
                    document.getElementById('submit-latitude').value = sessionStorage.getItem('rproblem_lat') || '';
                    document.getElementById('submit-longitude').value = sessionStorage.getItem('rproblem_lng') || '';
                    document.getElementById('submit-phone').value = (document.getElementById('preview-phone-text') && document.getElementById('preview-phone-text').value) ? document.getElementById('preview-phone-text').value.trim() : '';
                    document.getElementById('submit-photo').value = sessionStorage.getItem('rpicture') || sessionStorage.getItem('rproblem_photo') || '';
                    btn.disabled = true;
                    btn.textContent = 'Submitting…';

                    var token = document.querySelector('meta[name="csrf-token"]');
                    var tokenVal = token ? token.getAttribute('content') : '';

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': tokenVal
                        },
                        credentials: 'same-origin'
                    }).then(function(response) {
                        return response.text().then(function(text) {
                            var body = {};
                            try {
                                body = text ? JSON.parse(text) : {};
                            } catch (e) {
                                body = { message: response.status === 419 ? 'Session expired. Refresh the page and try again.' : 'Request failed.' };
                            }
                            return { ok: response.ok, status: response.status, body: body };
                        });
                    }).then(function(result) {
                        if (!result.ok) {
                            setSuccessPopupVisible(false);
                            btn.disabled = false;
                            btn.textContent = 'Submit report';
                            showSubmitError(firstJsonError(result.body));
                            return;
                        }
                        if (result.body && result.body.ok) {
                            clearReportDraftStorage();
                            window.showReportSuccessPopup(result.body);
                            btn.disabled = true;
                            btn.textContent = 'Submit report';
                            return;
                        }
                        setSuccessPopupVisible(false);
                        btn.disabled = false;
                        btn.textContent = 'Submit report';
                    }).catch(function() {
                        setSuccessPopupVisible(false);
                        btn.disabled = false;
                        btn.textContent = 'Submit report';
                        showSubmitError('Network error. Please try again.');
                    });
                });
            }
        })();

        function isValidPhoneInput(value) {
            var s = (value || '').trim();
            if (!s) return false;
            if (!/^\+?[0-9\s\-()]+$/.test(s)) return false;
            var digits = s.replace(/\D/g, '');
            return digits.indexOf('673') === 0 && digits.length === 10;
        }
        function formatBruneiPhone(value) {
            var raw = String(value || '');
            var digits = (value || '').replace(/\D/g, '');
            if (!digits) {
                return '';
            }

            var hasCountryCode = digits.indexOf('673') === 0 || raw.trim().indexOf('+673') === 0;
            var local = hasCountryCode ? digits.substring(3) : digits;
            local = local.substring(0, 7);

            // While editing, do not force +673 back in if user is deleting.
            if (!hasCountryCode) {
                if (local.length <= 3) {
                    return local;
                }
                return local.substring(0, 3) + ' ' + local.substring(3, 7);
            }

            if (local.length === 0) {
                return '+673 ';
            }
            if (local.length <= 3) {
                return '+673 ' + local;
            }
            return '+673 ' + local.substring(0, 3) + ' ' + local.substring(3, 7);
        }
        function syncPreviewSubmitState() {
            var btn = document.getElementById('submit-report-btn');
            if (!btn || btn.textContent === 'Submitting…') {
                return;
            }
            var nameEl = document.getElementById('preview-name-text');
            var phoneEl = document.getElementById('preview-phone-text');
            var locEl = document.getElementById('preview-location-text');
            var nameOk = nameEl && nameEl.value.trim() !== '';
            var phoneOk = phoneEl && isValidPhoneInput(phoneEl.value);
            var locOk = locEl && locEl.textContent.trim() !== '';
            btn.disabled = !(nameOk && phoneOk && locOk);
        }
        (function() {
            function showPhoto() {
                var photo = sessionStorage.getItem('rpicture') || sessionStorage.getItem('rproblem_photo');
                var img = document.getElementById('preview-photo');
                if (photo && img && photo.indexOf('data:') === 0) {
                    img.src = photo;
                    img.style.display = 'block';
                }
            }
            function showProblemText() {
                var choice = sessionStorage.getItem('rproblem_choice');
                var el = document.getElementById('preview-problem-text');
                if (choice && el) el.textContent = choice;
            }
            function showDate() {
                var d = new Date();
                var day = d.getDate();
                var ord = (day === 1 || day === 21 || day === 31) ? 'st' : (day === 2 || day === 22) ? 'nd' : (day === 3 || day === 23) ? 'rd' : 'th';
                var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                var str = day + ord + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
                var el = document.getElementById('preview-date-text');
                if (el) el.textContent = str;
            }
            function showInfoRows() {
                var addr = sessionStorage.getItem('rproblem_address');
                var elAddr = document.getElementById('preview-location-text');
                if (elAddr) elAddr.textContent = addr || '';
                var sev = sessionStorage.getItem('rproblem_severity');
                var elSev = document.getElementById('preview-severity-text');
                if (elSev) elSev.textContent = (sev === 'urgent') ? 'Urgent' : (sev === 'nonurgent') ? 'Non-Urgent' : '';
                var desc = sessionStorage.getItem('rproblem_description');
                var elDesc = document.getElementById('preview-details-text');
                if (elDesc) elDesc.value = desc || '';
            }
            function init() {
                showPhoto();
                showProblemText();
                showDate();
                showInfoRows();
                syncPreviewSubmitState();
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
        var previewNameInput = document.getElementById('preview-name-text');
        var previewPhoneInput = document.getElementById('preview-phone-text');
        if (previewNameInput) {
            previewNameInput.addEventListener('input', syncPreviewSubmitState);
            previewNameInput.addEventListener('change', syncPreviewSubmitState);
        }
        if (previewPhoneInput) {
            var PHONE_PREFIX = '+673 ';
            var initialPhone = (previewPhoneInput.value || '').trim();
            previewPhoneInput.value = initialPhone ? formatBruneiPhone(initialPhone) : PHONE_PREFIX;
            function lockPrefixCaret() {
                try {
                    var min = PHONE_PREFIX.length;
                    if ((previewPhoneInput.value || '').indexOf(PHONE_PREFIX) !== 0) {
                        previewPhoneInput.value = formatBruneiPhone(previewPhoneInput.value || '');
                        if ((previewPhoneInput.value || '').indexOf(PHONE_PREFIX) !== 0) {
                            previewPhoneInput.value = PHONE_PREFIX;
                        }
                    }
                    if (previewPhoneInput.selectionStart < min || previewPhoneInput.selectionEnd < min) {
                        previewPhoneInput.setSelectionRange(min, min);
                    }
                } catch (e) {}
            }
            previewPhoneInput.addEventListener('input', function() {
                previewPhoneInput.value = formatBruneiPhone(previewPhoneInput.value);
                if ((previewPhoneInput.value || '').trim() === '') {
                    previewPhoneInput.value = PHONE_PREFIX;
                }
                lockPrefixCaret();
            });
            previewPhoneInput.addEventListener('keydown', function(e) {
                var start = previewPhoneInput.selectionStart || 0;
                var end = previewPhoneInput.selectionEnd || 0;
                var min = PHONE_PREFIX.length;
                if ((e.key === 'Backspace' && start <= min) || (e.key === 'Delete' && start < min)) {
                    e.preventDefault();
                    previewPhoneInput.setSelectionRange(min, min);
                    return;
                }
                if (start < min || end < min) {
                    e.preventDefault();
                    previewPhoneInput.setSelectionRange(min, min);
                }
            });
            previewPhoneInput.addEventListener('focus', function() {
                if ((previewPhoneInput.value || '').trim() === '') {
                    previewPhoneInput.value = PHONE_PREFIX;
                }
                lockPrefixCaret();
            });
            previewPhoneInput.addEventListener('blur', function() {
                if ((previewPhoneInput.value || '').trim() === '') {
                    previewPhoneInput.value = PHONE_PREFIX;
                }
            });
            previewPhoneInput.addEventListener('click', lockPrefixCaret);
            previewPhoneInput.addEventListener('keyup', lockPrefixCaret);
            previewPhoneInput.addEventListener('input', syncPreviewSubmitState);
            previewPhoneInput.addEventListener('change', syncPreviewSubmitState);
        }
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
