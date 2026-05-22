<x-layouts::customer :title="__('Customer Support') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    @endpush

    @include('r_customer.partials.page-background')


    @php $user = auth()->user(); @endphp

    {{-- Header: back arrow + Customer Support --}}
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.general', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; transition: transform 0.1s ease;" aria-label="{{ __('Back') }}">
            <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
        </a>
        <span style="color: white; font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;">{{ __('Customer Support') }}</span>
    </div>

    {{-- Content --}}
    <div style="position: fixed; top: 12vh; left: 20px; right: 20px; bottom: 20px; z-index: 5; padding: 2px;">
        <p style="color: white; font-size: 17px; font-family: Poppins, sans-serif; font-weight: 600; margin: 0; margin-left: 24px;">Hi there, how can we help you?</p>
        <div style="margin-top: 24px; margin-left: 8px; margin-right: 8px; min-height: 260px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); padding: 16px;">
            <p style="color: white; font-size: 13px; font-family: Poppins, sans-serif; font-weight: 600; margin: 0; margin-left: 4px; margin-top: 4px;">Announcements:</p>
            <div style="margin-top: 14px; margin-left: auto; margin-right: auto; max-width: 88%;">
                <p style="color: rgba(255,255,255,0.9); font-size: 10px; font-family: Poppins, sans-serif; font-weight: 400; margin: 0 0 12px 0; line-height: 1.5;">Dear {{ $user->name ?? 'User' }},</p>
                <p style="color: rgba(255,255,255,0.9); font-size: 10px; font-family: Poppins, sans-serif; font-weight: 400; margin: 0 0 12px 0; line-height: 1.5;">&gt; If you have general questions or want to browse common answers, visit the FAQ page.</p>
                <p style="color: rgba(255,255,255,0.9); font-size: 10px; font-family: Poppins, sans-serif; font-weight: 400; margin: 0 0 12px 0; line-height: 1.5;">&gt; If you have a specific question about reporting incidents, drainage issues, or how to use BruDMS, use Ziqah (AI).</p>
                <p style="color: rgba(255,255,255,0.9); font-size: 10px; font-family: Poppins, sans-serif; font-weight: 400; margin: 0; line-height: 1.5;">&gt; If you have found a bug, have account problems, or any other issue not covered by the FAQ or Ziqah (AI), contact us by pressing the Contact Customer Support button below.</p>
            </div>
        </div>

        {{-- Contact Customer Support button --}}
        <div style="position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 10; max-width: 310px; width: calc(100% - 60px);">
            <a href="{{ route('customer.contactcustomersupport', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 42px; border-radius: 12px; background: #04BCFF; color: #000; font-size: 13px; font-weight: 700; font-family: Poppins, sans-serif; text-decoration: none;">
                <img src="{{ asset('images/Vectors/customersupport_support.svg') }}" alt="" style="width: 21px; height: 21px;" />
                <span>Contact Customer Support</span>
            </a>
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
    </script>
    @endpush
</x-layouts::customer>
