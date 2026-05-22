<x-layouts::customer :title="__('Confirm Report') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;600;700&display=swap" rel="stylesheet">
        @include('r_customer.partials.report-flow-spacing')
        <style>
            .press-btn { transition: transform 0.06s ease; }
            .press-btn:active { transform: scale(0.92) !important; }
            #confirm-details-btn:active { transform: translateX(-50%) scale(0.92) !important; }
            #description-textbox::placeholder { color: rgba(255, 255, 255, 0.4); }
        </style>
    @endpush

    @php
        $guestReportFlow = filter_var($guestReportFlow ?? false, FILTER_VALIDATE_BOOLEAN);
        $user = auth()->user();
        $reportStep = function (string $step) use ($guestReportFlow, $user) {
            return $guestReportFlow
                ? route('guest.report.'.$step)
                : route('customer.'.$step, ['name' => $user->profileSlug()]);
        };
    @endphp
    <a href="{{ $reportStep('rlocation') }}" class="press-btn delayed-nav" style="position: fixed; top: 4vh; left: 20px; z-index: 20; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none;" aria-label="{{ __('Back') }}">
        <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
    </a>

    <div style="position: fixed; left: 6px; right: 6px; top: 11vh; bottom: -50vh; border-radius: 21px 21px 0 0; background: rgba(217, 217, 217, 0.07); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 1; pointer-events: none;"></div>

    <img src="{{ asset('images/Vectors/report_rdetails.svg') }}" alt="" style="position: fixed; left: calc(20px + (40px - 21px) / 2); top: calc(11vh + 30px); z-index: 5; width: 30px; height: 31px; pointer-events: none;" />

    <div class="rflow-main-stack rflow-main-stack--inert">
        <div class="rflow-header-col">
            <span class="rflow-header-line-primary">Clear descriptions reduce delays on site</span>
            <span class="rflow-header-line-secondary">Add any additional details that may help</span>
        </div>
    </div>

    <div id="severity-box" style="position: fixed; left: 50%; top: calc(11vh + 28px + 58px + 24px); transform: translateX(-50%); width: 85%; max-width: 320px; height: 40px; border-radius: 14px; background: rgba(217, 217, 217, 0.06); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.25); z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 0 16px;">
        <span style="color: rgba(255, 255, 255, 0.9); font-size: 12px; font-family: Poppins, sans-serif; font-weight: 600;">Severity</span>
        <span id="severity-options" style="color: rgba(255, 255, 255, 0.5); font-size: 12px; font-family: Poppins, sans-serif; font-weight: 500;">
            <span id="opt-urgent" class="severity-opt" data-value="urgent" style="cursor: pointer;">Urgent</span>
            <span style="margin: 0 4px;">/</span>
            <span id="opt-nonurgent" class="severity-opt" data-value="nonurgent" style="cursor: pointer;">Non-Urgent</span>
        </span>
    </div>

    <div class="rflow-details-block" style="position: fixed; left: 50%; top: calc(11vh + 28px + 58px + 24px + 40px + 8px); transform: translateX(-50%); width: 85%; max-width: 320px; height: 320px; border-radius: 14px; background: rgba(217, 217, 217, 0.06); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.25); z-index: 2; padding: 8px 16px 12px; box-sizing: border-box; display: flex; flex-direction: column;">
        <span style="color: rgba(255, 255, 255, 0.9); font-size: 12px; font-family: Poppins, sans-serif; font-weight: 600;">Description</span>
        <div style="flex: 1; min-height: 0; margin: 0 -13px -9px -13px; display: flex;">
            <textarea name="description" id="description-textbox" placeholder="Describe the problem..." style="flex: 1; min-height: 0; width: 100%; border-radius: 11px; background: transparent; border: 1px solid rgba(255, 255, 255, 0.25); padding: 10px 12px; color: rgba(255, 255, 255, 0.9); font-size: 12px; font-family: Poppins, sans-serif; resize: none; outline: none; box-sizing: border-box;"></textarea>
        </div>
    </div>

    <div style="position: fixed; left: 6px; right: 6px; bottom: 0; height: 100px; border-radius: 0; background: linear-gradient(to top, rgba(217, 217, 217, 0.02), transparent); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); mask-image: linear-gradient(to top, black 60%, transparent); -webkit-mask-image: linear-gradient(to top, black 60%, transparent); z-index: 6; pointer-events: none;"></div>
    <button type="button" id="confirm-details-btn" class="press-btn cust-btn-confirm cust-btn-primary" style="position: fixed; left: 50%; transform: translateX(-50%); bottom: 24px; z-index: 7; width: 82%; max-width: 360px; height: 48px; border: none; border-radius: 16px; background: var(--brudms-primary); color: #0a1628; font-family: Poppins, sans-serif; font-weight: 700; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(var(--brudms-primary-rgb), 0.3);">Confirm details</button>

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
        document.getElementById('confirm-details-btn').addEventListener('click', function() {
            try {
                var desc = document.getElementById('description-textbox').value || '';
                sessionStorage.setItem('rproblem_description', desc);
            } catch (e) {}
            window.location.href = '{{ $reportStep("rpreview") }}';
        });
        var selectedSeverity = null;
        document.querySelectorAll('.severity-opt').forEach(function(el) {
            el.addEventListener('click', function() {
                document.querySelectorAll('.severity-opt').forEach(function(o) {
                    o.style.color = 'rgba(255, 255, 255, 0.5)';
                    o.style.textDecoration = 'none';
                });
                this.style.color = 'var(--brudms-primary)';
                this.style.textDecoration = 'underline';
                selectedSeverity = this.getAttribute('data-value');
                try { sessionStorage.setItem('rproblem_severity', selectedSeverity); } catch (e) {}
            });
        });
        try {
            var saved = sessionStorage.getItem('rproblem_severity');
            if (saved === 'urgent') document.getElementById('opt-urgent').click();
            else if (saved === 'nonurgent') document.getElementById('opt-nonurgent').click();
            var savedDesc = sessionStorage.getItem('rproblem_description');
            if (savedDesc) document.getElementById('description-textbox').value = savedDesc;
        } catch (e) {}
    </script>
    @endpush
</x-layouts::customer>
