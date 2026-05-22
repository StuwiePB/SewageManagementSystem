<x-layouts::customer :title="__('Help FAQ?') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            .faq-answer {
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                transition: max-height 0.3s ease, opacity 0.25s ease;
            }
            .faq-answer.faq-open {
                max-height: 300px;
                opacity: 1;
            }
            .faq-content { overflow-y: auto; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none; }
            .faq-content::-webkit-scrollbar { display: none; }
        </style>
    @endpush

    @include('r_customer.partials.page-background')


    @php $user = auth()->user(); @endphp

    {{-- Header: back arrow + FAQ? --}}
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.general', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; transition: transform 0.1s ease;" aria-label="{{ __('Back') }}">
            <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
        </a>
        <span style="color: white; font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;">{{ __('Help FAQ?') }}</span>
    </div>

    {{-- Content (no scrollbar, touch scroll) --}}
    <div class="faq-content" style="position: fixed; top: 12vh; left: 20px; right: 20px; bottom: 20px; z-index: 5; padding: 2px;">
        <div style="max-width: 320px; margin: 0 auto;">
        {{-- General --}}
        <p style="color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif; margin-bottom: 10px; margin-left: 12px;">{{ __('General') }}</p>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('What is BruDMS?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('BruDMS is a drainage and sewage reporting platform that helps residents report issues and track their resolution.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('Who can use BruDMS?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('BruDMS is available to all residents within the service area who wish to report drainage or sewage issues.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('Is BruDMS free to use?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Yes, BruDMS is free to use for all residents.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('In which areas does BruDMS operate?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('BruDMS operates within the Brunei service area for drainage and sewage reporting.') }}</p>
            </div>
        </div>

        {{-- Account & Profile --}}
        <p style="color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif; margin-bottom: 10px; margin-left: 12px; margin-top: 20px;">{{ __('Account & Profile') }}</p>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How do I create an account?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Go to the Sign Up screen, enter your name, email, and password, then tap Create account. Verify your email if prompted.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How do I reset my password?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Use Forgot password on the login page to receive a reset link by email.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How do I update my profile information?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Tap your profile photo → General → Edit Profile to update your name, phone, and photo.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How do I delete my account?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Go to settings/profile for the delete account option, or contact Customer Support.') }}</p>
            </div>
        </div>

        {{-- Reporting Incidents --}}
        <p style="color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif; margin-bottom: 10px; margin-left: 12px; margin-top: 20px;">{{ __('Reporting Incidents') }}</p>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How do I report a drainage or sewage issue?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('From Home, tap the + Add report card. Choose a problem type (e.g. Clogged Drains, Street Pooling), add a photo, set the location, confirm details, and submit.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('Can I attach photos when reporting?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Yes. When reporting, you add a photo on the camera step (after choosing problem type, before setting location).') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How do I know if my report was received?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('You get a confirmation after submitting. Check the History tab to see your reports and their status.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('Can I report anonymously?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Go to General → Preference → Anonymous Report. When set to Anonymous, your name is hidden from reports.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How long does it take for a report to be processed?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Processing times vary. Check History for status (pending, in progress, resolved).') }}</p>
            </div>
        </div>

        {{-- Ziqah (AI) --}}
        <p style="color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 600; font-family: Poppins, sans-serif; margin-bottom: 10px; margin-left: 12px; margin-top: 20px;">{{ __('Ziqah (AI)') }}</p>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('What is Ziqah (AI)?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Ziqah (AI) is an assistant that helps you report drainage issues, categorize problems, and answer questions. Access it via the Chat tab on Home.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How do I view the live map?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('From Home, scroll down and tap View Live Map to see reports near you on a map.') }}</p>
            </div>
        </div>
        <div class="faq-item" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 14px; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px;" data-faq>
            <span style="color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;">{{ __('How do I change my preferences?') }}</span>
            <svg class="faq-chevron" style="width: 16px; height: 16px; color: white; flex-shrink: 0; transition: transform 0.25s ease;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"></polyline></svg>
        </div>
        <div class="faq-answer" style="margin: 0;">
            <div style="padding: 10px 14px 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <p style="color: rgba(255,255,255,0.9); font-size: 11px; font-family: Poppins, sans-serif; margin: 0; line-height: 1.5;">{{ __('Go to General → Preference to change Appearance (dark/light), Language (BM/EN), and Anonymous Report.') }}</p>
            </div>
        </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // FAQ accordion
        document.querySelectorAll('[data-faq]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var answer = this.nextElementSibling;
                var chevron = this.querySelector('.faq-chevron');
                if (!answer || !answer.classList.contains('faq-answer')) return;
                var isOpen = answer.classList.contains('faq-open');
                answer.classList.toggle('faq-open', !isOpen);
                if (chevron) chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
            });
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
