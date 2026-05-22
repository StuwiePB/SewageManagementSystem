<x-layouts::customer :title="__('Report Problem') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;600;700&display=swap" rel="stylesheet">
        @include('r_customer.partials.report-flow-spacing')
        <style>
            .press-btn { transition: transform 0.06s ease; }
            .press-btn:active { transform: scale(0.92) !important; }
            #confirm-choice-btn:active { transform: translateX(-50%) scale(0.92) !important; }
            .choices-scroll::-webkit-scrollbar { display: none; }
            .choice-card .choice-tagline { font-size: clamp(11px, 7.5cqw, 18px); }
            .choice-card .choice-subtitle { font-size: clamp(9px, 6cqw, 14px); }
            .choice-card { cursor: pointer; transition: box-shadow 0.2s; }
            .choice-card.selected { box-shadow: 0 0 0 2px #04BCFF; }
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
        $backUrl = $guestReportFlow ? route('guest.explore') : route('customer.dashboard', ['name' => $user->profileSlug()]);
    @endphp
    <a href="{{ $backUrl }}" class="press-btn delayed-nav" style="position: fixed; top: 4vh; left: 20px; z-index: 10; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none;" aria-label="{{ __('Back') }}">
        <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
    </a>

    <div style="position: fixed; left: 6px; right: 6px; top: 11vh; bottom: -50vh; border-radius: 21px 21px 0 0; background: rgba(217, 217, 217, 0.07); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 1;"></div>

    <img src="{{ asset('images/Vectors/report_rproblem.svg') }}" alt="" style="position: fixed; left: calc(20px + (40px - 21px) / 2); top: calc(11vh + 30px); z-index: 5; width: 30px; height: 31px;" />

    <button type="button" id="confirm-choice-btn" class="press-btn" style="position: fixed; left: 50%; transform: translateX(-50%); bottom: 24px; z-index: 7; width: 82%; max-width: 360px; height: 48px; border: none; border-radius: 16px; background: #04BCFF; color: #0a1628; font-family: Poppins, sans-serif; font-weight: 700; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(4, 188, 255, 0.3);">Confirm choice</button>

    <div style="position: fixed; left: 6px; right: 6px; bottom: 0; height: 100px; border-radius: 0; background: linear-gradient(to top, rgba(217, 217, 217, 0.02), transparent); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); mask-image: linear-gradient(to top, black 60%, transparent); -webkit-mask-image: linear-gradient(to top, black 60%, transparent); z-index: 6; pointer-events: none;"></div>

    <div class="rflow-main-stack">
        <div class="rflow-header-col">
            <span class="rflow-header-line-primary">Help keep <span style="color: #04BCFF;">Brunei</span> clean and safe by reporting sewage problems accurately</span>
            <span class="rflow-header-line-secondary">State your problem type</span>
        </div>
        <div class="choices-scroll" style="margin-left: 40px; margin-right: 40px; overflow-y: auto; max-height: 75vh; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; padding-bottom: 8px;">
            <div class="rflow-choices-grid">
                <div class="choice choice-card" data-tagline="Damaged pipelines" style="aspect-ratio: 17 / 21; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); padding: 1px; display: flex; flex-direction: column; overflow: hidden; container-type: inline-size; container-name: card;">
                    <div style="height: 65%; min-height: 0; overflow: hidden; border-radius: 8px 8px 0 0; border: 0.7px solid rgba(255, 255, 255, 0.21); box-sizing: border-box; flex-shrink: 0;">
                        <img src="{{ asset('images/rproblem/corrosion-large-pipe-2-1200.jpg') }}" alt="" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                    </div>
                    <div class="rflow-choice-text">
                        <span class="choice-tagline" style="color: white; font-weight: 600; font-family: Poppins, sans-serif;">Damaged pipelines</span>
                        <span class="choice-subtitle" style="color: rgba(255, 255, 255, 0.46); font-weight: 300; font-family: Poppins, sans-serif;">Cracked, broken, collapsed pipes</span>
                    </div>
                </div>
                <div class="choice choice-card" data-tagline="Clogged Drains" style="aspect-ratio: 17 / 21; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); padding: 1px; display: flex; flex-direction: column; overflow: hidden; container-type: inline-size; container-name: card;">
                    <div style="height: 65%; min-height: 0; overflow: hidden; border-radius: 8px 8px 0 0; border: 0.7px solid rgba(255, 255, 255, 0.21); box-sizing: border-box; flex-shrink: 0;">
                        <img src="{{ asset('images/rproblem/Clogged-storm-drains.webp') }}" alt="" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                    </div>
                    <div class="rflow-choice-text">
                        <span class="choice-tagline" style="color: white; font-weight: 600; font-family: Poppins, sans-serif;">Clogged Drains</span>
                        <span class="choice-subtitle" style="color: rgba(255, 255, 255, 0.46); font-weight: 300; font-family: Poppins, sans-serif;">Blockages (debris, roots, dumping, etc.)</span>
                    </div>
                </div>
                <div class="choice choice-card" data-tagline="Street Pooling" style="aspect-ratio: 17 / 21; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); padding: 1px; display: flex; flex-direction: column; overflow: hidden; container-type: inline-size; container-name: card;">
                    <div style="height: 65%; min-height: 0; overflow: hidden; border-radius: 8px 8px 0 0; border: 0.7px solid rgba(255, 255, 255, 0.21); box-sizing: border-box; flex-shrink: 0;">
                        <img src="{{ asset('images/rproblem/file_FDCB1305-EDBF-49C8-A1AE-C0C6FF8AAE0C-scaled.jpeg') }}" alt="" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                    </div>
                    <div class="rflow-choice-text">
                        <span class="choice-tagline" style="color: white; font-weight: 600; font-family: Poppins, sans-serif;">Street Pooling</span>
                        <span class="choice-subtitle" style="color: rgba(255, 255, 255, 0.46); font-weight: 300; font-family: Poppins, sans-serif;">Standing water, street flooding</span>
                    </div>
                </div>
                <div class="choice choice-card" data-tagline="Manhole issues" style="aspect-ratio: 17 / 21; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); padding: 1px; display: flex; flex-direction: column; overflow: hidden; container-type: inline-size; container-name: card;">
                    <div style="height: 65%; min-height: 0; overflow: hidden; border-radius: 8px 8px 0 0; border: 0.7px solid rgba(255, 255, 255, 0.21); box-sizing: border-box; flex-shrink: 0;">
                        <img src="{{ asset('images/rproblem/Screenshot 2026-02-28 181135.png') }}" alt="" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                    </div>
                    <div class="rflow-choice-text">
                        <span class="choice-tagline" style="color: white; font-weight: 600; font-family: Poppins, sans-serif;">Manhole issues</span>
                        <span class="choice-subtitle" style="color: rgba(255, 255, 255, 0.46); font-weight: 300; font-family: Poppins, sans-serif;">Missing, damaged, or displaced covers</span>
                    </div>
                </div>
                <div class="choice choice-card" data-tagline="Sewage overflow" style="aspect-ratio: 17 / 21; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); padding: 1px; display: flex; flex-direction: column; overflow: hidden; container-type: inline-size; container-name: card;">
                    <div style="height: 65%; min-height: 0; overflow: hidden; border-radius: 8px 8px 0 0; border: 0.7px solid rgba(255, 255, 255, 0.21); box-sizing: border-box; flex-shrink: 0;">
                        <img src="{{ asset('images/rproblem/iStock-684764482.jpg') }}" alt="" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                    </div>
                    <div class="rflow-choice-text">
                        <span class="choice-tagline" style="color: white; font-weight: 600; font-family: Poppins, sans-serif;">Sewage overflow</span>
                        <span class="choice-subtitle" style="color: rgba(255, 255, 255, 0.46); font-weight: 300; font-family: Poppins, sans-serif;">Sewage coming up from drains or manholes</span>
                    </div>
                </div>
                <div class="choice choice-card" data-tagline="Odor complaint" style="aspect-ratio: 17 / 21; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); padding: 1px; display: flex; flex-direction: column; overflow: hidden; container-type: inline-size; container-name: card;">
                    <div style="height: 65%; min-height: 0; overflow: hidden; border-radius: 8px 8px 0 0; border: 0.7px solid rgba(255, 255, 255, 0.21); box-sizing: border-box; flex-shrink: 0;">
                        <img src="{{ asset('images/rproblem/Why-Does-My-Water-Smell-Like-Sewage.webp') }}" alt="" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                    </div>
                    <div class="rflow-choice-text">
                        <span class="choice-tagline" style="color: white; font-weight: 600; font-family: Poppins, sans-serif;">Odor complaint</span>
                        <span class="choice-subtitle" style="color: rgba(255, 255, 255, 0.46); font-weight: 300; font-family: Poppins, sans-serif;">Persistent foul smell</span>
                    </div>
                </div>
                <div style="grid-column: span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 0;">
                    <div style="aspect-ratio: 17 / 12; visibility: hidden;"></div>
                    <div style="aspect-ratio: 17 / 12; visibility: hidden;"></div>
                </div>
            </div>
        </div>
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
        function selectChoice(card) {
            document.querySelectorAll('.choice-card').forEach(function(c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            var tagline = card.getAttribute('data-tagline');
            document.getElementById('confirm-choice-btn').textContent = 'Confirm ' + tagline;
            try { sessionStorage.setItem('rproblem_choice', tagline); } catch (e) {}
        }
        document.querySelectorAll('.choice-card[data-tagline]').forEach(function(card) {
            card.addEventListener('click', function() { selectChoice(card); });
        });
        try {
            var saved = sessionStorage.getItem('rproblem_choice');
            if (saved) {
                document.querySelectorAll('.choice-card[data-tagline]').forEach(function(card) {
                    if (card.getAttribute('data-tagline') === saved) selectChoice(card);
                });
            }
        } catch (e) {}
        document.getElementById('confirm-choice-btn').addEventListener('click', function() {
            var selected = document.querySelector('.choice-card.selected');
            if (!selected) return;
            window.location.href = '{{ $reportStep("rpicture") }}';
        });
    </script>
    @endpush
</x-layouts::customer>
