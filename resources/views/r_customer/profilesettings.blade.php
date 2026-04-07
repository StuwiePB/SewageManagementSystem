<x-layouts::customer :title="__('Profile Settings') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            #username-input::placeholder, #phone-input::placeholder { color: rgba(255, 255, 255, 0.5); }
            #save-profile-btn { background: rgba(66, 106, 120, 0.35) !important; border: 2px solid rgba(255, 255, 255, 0.25) !important; color: rgba(255, 255, 255, 0.65) !important; cursor: not-allowed; transition: transform 0.1s ease; }
            #save-profile-btn.has-changes { background: #22d3ee !important; border: none !important; color: black !important; cursor: pointer; }
            .bottom-bar-btn { transition: transform 0.1s ease; }
            .bottom-bar-btn:active { transform: scale(0.93) !important; }
        </style>
    @endpush

    {{-- Desktop: normal background --}}
    <div class="hidden lg:block fixed inset-0 z-0" style="background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>

    {{-- Mobile: rotated -90deg background --}}
    <div class="lg:hidden" style="position: fixed; inset: 0; overflow: hidden; z-index: 0;">
        <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
    </div>

    @php $user = auth()->user(); @endphp

    {{-- Header: back arrow + Profile settings --}}
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.general', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; transition: transform 0.1s ease;" aria-label="{{ __('Back') }}">
            <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
        </a>
        <span style="color: white; font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;">Profile settings</span>
    </div>

    {{-- Content area --}}
    <div style="position: fixed; top: 22vh; left: 28px; right: 28px; bottom: 20px; z-index: 5; overflow-y: auto; display: flex; flex-direction: column; align-items: center; padding-top: 0; margin-top: -72px;">
        @php
            $photoPath = $user->profile_photo_path ?? null;
            $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::url($photoPath) : null;
        @endphp
        <div style="display: flex; flex-direction: column; align-items: center; width: 100%; gap: 24px;">
            <div style="display: flex; align-items: flex-start; gap: 16px;">
                {{-- Circle 1: Current profile picture (only changes after Save) --}}
                <div id="circle-1-current" style="width: 72px; height: 72px; border-radius: 9999px; border: 1px solid rgba(255, 255, 255, 0.35); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; margin-top: 20px;">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="" style="width: 100%; height: 100%; object-fit: cover;" />
                    @elseif(file_exists(public_path('images/default-avatar.png')))
                        <img src="{{ asset('images/default-avatar.png') }}" alt="" style="width: 100%; height: 100%; object-fit: cover;" />
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="" style="width: 42px; height: 42px; object-fit: contain;" />
                    @endif
                </div>
                <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px; transform: scaleX(-1); opacity: 0.7; align-self: center;" />
                {{-- Circle 2: Pick new photo – shows camera or preview of picked photo (resets on Save) --}}
                <label for="profile-photo-input" class="press-btn" id="circle-2-label" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; margin-top: 20px;">
                    <div id="circle-2-content" style="width: 72px; height: 72px; border-radius: 9999px; border: 1px solid rgba(255, 255, 255, 0.35); background: rgba(255, 255, 255, 0.15); flex-shrink: 0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img id="circle-2-camera" src="{{ asset('images/Vectors/profilesettings_camera.svg') }}" alt="" style="width: 28px; height: 28px; object-fit: contain;">
                        <img id="circle-2-preview" src="" alt="" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <span style="margin-top: 6px; color: #22d3ee; font-family: Poppins, sans-serif; font-size: 11px; font-weight: 500;">Edit Picture</span>
                </label>
            </div>
            <form id="profile-form" method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data" style="width: 100%; max-width: 400px; margin-top: 12px;">
                @csrf
                <input type="file" id="profile-photo-input" name="photo" accept="image/*" style="display: none;">
                <label for="username-input" style="display: block; color: rgba(255, 255, 255, 0.6); font-size: 11px; font-weight: 400; font-family: Poppins, sans-serif; margin-bottom: 6px; margin-left: 10px;">Username</label>
                <input type="text" id="username-input" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Muhammad Ali" style="width: 100%; height: 44px; padding: 0 16px; border-radius: 12px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); color: white; font-family: Poppins, sans-serif; font-size: 14px; font-weight: 500; outline: none; box-sizing: border-box;">
                <label for="phone-input" style="display: block; color: rgba(255, 255, 255, 0.6); font-size: 11px; font-weight: 400; font-family: Poppins, sans-serif; margin-bottom: 6px; margin-left: 10px; margin-top: 10px;">Phone Number</label>
                <input type="tel" id="phone-input" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="+673 012 3456" style="width: 100%; height: 44px; padding: 0 16px; border-radius: 12px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); color: white; font-family: Poppins, sans-serif; font-size: 14px; font-weight: 500; outline: none; box-sizing: border-box;">
                @if($errors->has('phone'))
                <p id="profile-phone-error" style="margin: 8px 0 0 10px; color: #fca5a5; font-size: 12px; font-family: Poppins, sans-serif;">{{ $errors->first('phone') }}</p>
                @endif
            </form>
        </div>
    </div>

    {{-- Bottom rectangle: Cancel & Save Changes buttons --}}
    <div style="position: fixed; bottom: -18px; left: -10px; right: -10px; height: calc(10vh + 10px); background: rgba(255, 255, 255, 0.03); border: 2px solid rgba(255, 255, 255, 0.03); border-radius: 12px 12px 0 0; z-index: 15; display: flex; align-items: center; justify-content: center; gap: 16px; padding: 14px 36px 28px; box-sizing: border-box;">
        <a href="{{ route('customer.general', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav bottom-bar-btn" style="flex: 1; max-width: 220px; height: 42px; border-radius: 10px; background: rgba(66, 106, 120, 0.35); border: 2px solid rgba(255, 255, 255, 0.25); color: rgba(255, 255, 255, 0.65); font-family: Poppins, sans-serif; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; padding-top: 2px; text-decoration: none;">Cancel</a>
        <button type="submit" form="profile-form" id="save-profile-btn" class="press-btn bottom-bar-btn" disabled style="flex: 1; max-width: 220px; height: 42px; border-radius: 10px; font-family: Poppins, sans-serif; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; padding-top: 2px;">Save Changes</button>
    </div>

    @push('scripts')
    <script>
        (function() {
            var nameInput = document.getElementById('username-input');
            var phoneInput = document.getElementById('phone-input');
            var photoInput = document.getElementById('profile-photo-input');
            var saveBtn = document.getElementById('save-profile-btn');
            var circle2Camera = document.getElementById('circle-2-camera');
            var circle2Preview = document.getElementById('circle-2-preview');
            var originalName = nameInput.value;
            var originalPhone = phoneInput.value || '';
            function checkChanges() {
                var nameChanged = nameInput.value !== originalName;
                var phoneChanged = (phoneInput.value || '') !== originalPhone;
                var photoChanged = photoInput.files && photoInput.files.length > 0;
                if (nameChanged || phoneChanged || photoChanged) {
                    saveBtn.classList.add('has-changes');
                    saveBtn.disabled = false;
                } else {
                    saveBtn.classList.remove('has-changes');
                    saveBtn.disabled = true;
                }
            }
            nameInput.addEventListener('input', checkChanges);
            nameInput.addEventListener('change', checkChanges);
            phoneInput.addEventListener('input', checkChanges);
            phoneInput.addEventListener('change', checkChanges);
            photoInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    var url = URL.createObjectURL(this.files[0]);
                    circle2Camera.style.display = 'none';
                    circle2Preview.src = url;
                    circle2Preview.style.display = 'block';
                    checkChanges();
                } else {
                    circle2Camera.style.display = 'block';
                    circle2Preview.src = '';
                    circle2Preview.style.display = 'none';
                    checkChanges();
                }
            });
            checkChanges();
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
    @endpush
</x-layouts::customer>
