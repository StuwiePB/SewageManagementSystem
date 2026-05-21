<x-layouts::customer :title="__('Chatbot') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            .tab-btn {
                transition: outline-color 0.3s ease, transform 0.1s ease;
            }
            .tab-btn:active, .tab-btn-active:active {
                transform: scale(0.95);
            }
            .tab-btn-active {
                transition: transform 0.1s ease;
            }
            .tab-btn:hover {
                outline-color: #04BCFF !important;
            }
            .tab-btn:hover svg {
                stroke: #04BCFF;
                transition: stroke 0.3s ease;
            }
            #chat-input::placeholder {
                color: rgba(255, 255, 255, 0.3);
                font-family: Poppins, sans-serif;
            }
            .input-pill:focus-within {
                outline: 1.7px solid #04BCFF;
            }
            #chat-send:active {
                transform: scale(0.9);
            }
            #chat-attach:active {
                transform: scale(0.9);
            }
            .msg-img {
                max-width: 200px;
                border-radius: 12px;
                border: none;
                outline: none;
                display: block;
            }
            .msg-img-only {
                align-self: flex-end;
                animation: msgPop 0.2s ease-out;
            }
            .msg-group {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 4px;
            }
            .msg-preview {
                max-width: 56px;
                max-height: 56px;
                border-radius: 8px;
                object-fit: cover;
            }
            .preview-wrap {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                margin-bottom: 8px;
            }
            .preview-remove {
                width: 20px;
                height: 20px;
                border-radius: 9999px;
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                cursor: pointer;
                font-size: 14px;
                line-height: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .msg-user {
                align-self: flex-end;
                background: #04BCFF;
                color: #040929;
                border-radius: 16px 16px 4px 16px;
                padding: 10px 14px;
                max-width: 75%;
                font-size: 11px;
                font-family: Poppins, sans-serif;
                font-weight: 400;
                word-wrap: break-word;
                animation: msgPop 0.2s ease-out;
            }
            .msg-bot {
                align-self: flex-start;
                background: rgba(255, 255, 255, 0.08);
                color: white;
                border-radius: 16px 16px 16px 4px;
                padding: 10px 14px;
                max-width: 75%;
                font-size: 11px;
                font-family: Poppins, sans-serif;
                font-weight: 300;
                word-wrap: break-word;
                animation: msgPop 0.2s ease-out;
            }
            .msg-typing {
                align-self: flex-start;
                padding: 10px 14px;
                font-size: 11px;
                font-family: Poppins, sans-serif;
                color: rgba(255, 255, 255, 0.4);
                font-weight: 300;
                animation: blink 1s ease-in-out infinite;
            }
            .msg-action-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                height: 34px;
                padding: 0 14px;
                border-radius: 10px;
                background: #04BCFF;
                color: #0a1628;
                text-decoration: none;
                font-size: 11px;
                font-family: Poppins, sans-serif;
                font-weight: 700;
                box-shadow: 0 2px 8px rgba(4, 188, 255, 0.3);
                transition: transform 0.1s ease, background 0.2s ease, box-shadow 0.2s ease;
                width: 100%;
                margin-top: 10px;
                box-sizing: border-box;
            }
            .msg-action-btn:active {
                transform: scale(0.96);
            }
            .msg-action-btn:hover {
                background: #2acbff;
                box-shadow: 0 3px 10px rgba(4, 188, 255, 0.38);
            }
            .msg-quick-actions {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                margin-top: 10px;
            }
            .quick-chip {
                height: 30px;
                border-radius: 9999px;
                border: 1px solid rgba(255, 255, 255, 0.25);
                background: rgba(66, 106, 120, 0.2);
                color: #e5e7eb;
                padding: 0 11px;
                font-size: 10px;
                font-family: Poppins, sans-serif;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.1s ease, border-color 0.2s ease;
            }
            .quick-chip:active {
                transform: scale(0.96);
            }
            .quick-chip.is-active {
                border-color: #04BCFF;
                color: #ffffff;
                background: rgba(4, 188, 255, 0.24);
            }
            .quick-chip.quick-chip-text {
                height: auto;
                padding: 0;
                border: none;
                border-radius: 0;
                background: transparent;
                color: #9cdcff;
                text-decoration: underline;
                text-underline-offset: 2px;
                font-weight: 700;
            }
            .quick-chip.quick-chip-text:hover {
                color: #c2ebff;
                border: none;
            }
            .quick-chip.quick-chip-text.is-active {
                color: #ffffff;
                background: transparent;
            }
            .quick-chip:hover {
                border-color: #04BCFF;
                color: #ffffff;
            }
            @keyframes msgPop {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes blink {
                0%, 100% { opacity: 0.4; }
                50% { opacity: 1; }
            }
        </style>
    @endpush

    {{-- Desktop: normal background --}}
    <div class="hidden lg:block fixed inset-0 z-0" style="background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>

    {{-- Mobile: rotated -90deg background --}}
    <div class="lg:hidden" style="position: fixed; inset: 0; overflow: hidden; z-index: 0;">
        <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
    </div>

    {{-- Header: logo + BruDMS + profile photo --}}
    @php
        $user = auth()->user();
        $photoPath = $user->profile_photo_path ?? null;
        $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::url($photoPath) : null;
    @endphp
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer;" aria-label="{{ __('Home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" style="width: 36px; height: 36px; object-fit: contain;" />
            <span style="color: white; font-size: 24px; font-weight: 600; font-family: Poppins, sans-serif;">BruDMS</span>
        </a>
        <a href="{{ route('customer.general', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="cursor: pointer; display: flex; text-decoration: none;">
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="" style="width: 40px; height: 40px; border-radius: 9999px; object-fit: cover;" />
        @elseif(file_exists(public_path('images/default-avatar.png')))
            <img src="{{ asset('images/default-avatar.png') }}" alt="" style="width: 40px; height: 40px; border-radius: 9999px; object-fit: cover;" />
        @else
            <div style="width: 40px; height: 40px; border-radius: 9999px; background: #3f3f46; display: flex; align-items: center; justify-content: center; color: #e4e4e7; font-size: 14px; font-weight: 600;">{{ $user->initials() }}</div>
        @endif
        </a>
    </div>

    {{-- Rectangle --}}
    <div style="position: fixed; left: 6px; right: 6px; top: 11vh; bottom: -50vh; border-radius: 21px 21px 0 0; background: rgba(217, 217, 217, 0.07); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 1;"></div>

    {{-- Tab buttons --}}
    <div style="position: fixed; top: 13.5vh; left: 22px; right: 22px; z-index: 10; display: flex; justify-content: center; gap: 10px;">
        {{-- Home --}}
        <a href="{{ route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="tab-btn" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(66, 106, 120, 0.16); outline: 1.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px);">
            <img src="{{ asset('images/Vectors/tab_home.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
        {{-- Ziqah (AI) (active) --}}
        <a href="{{ route('customer.brudmsgpt', ['name' => $user->profileSlug()]) }}" class="tab-btn-active" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: #04BCFF; backdrop-filter: blur(1.5px);">
            <img src="{{ asset('images/Vectors/tab_chat-active.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
        {{-- History --}}
        <a href="{{ route('customer.myhistory', ['name' => $user->profileSlug()]) }}" class="tab-btn delayed-nav" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(66, 106, 120, 0.16); outline: 1.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); text-decoration: none;">
            <img src="{{ asset('images/Vectors/tab_myhistory.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
    </div>

    {{-- Chat inner rectangle --}}
    <div style="position: fixed; left: 16px; right: 16px; top: 20vh; bottom: -10vh; border-radius: 16px 16px 0 0; background: rgba(0, 0, 0, 0.25); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 5; display: flex; flex-direction: column; padding: 16px;">
        <span style="color: white; font-size: 14px; font-weight: 600; font-family: Poppins, sans-serif; text-align: center;">Ziqah (AI) 1.0 <span style="font-weight: 300; font-size: 11px; opacity: 0.6;">(Beta)</span></span>

        {{-- Chat messages area --}}
        <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 10px 0; margin-top: 8px; margin-bottom: 14px; display: flex; flex-direction: column; gap: 12px; scrollbar-width: none;">
            <div class="msg-bot">Ziqah handles the flow. What's clogged, leaking, or overflowing? Show me.</div>
        </div>

        {{-- Input bar --}}
        <input id="chat-file" type="file" accept="image/*" style="display: none;" />
        <div id="chat-preview" class="preview-wrap" style="display: none;">
            <img id="chat-preview-img" class="msg-preview" alt="" />
            <button id="chat-preview-remove" class="preview-remove" type="button">×</button>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10vh;">
            <div class="input-pill" style="flex: 1; height: 44px; border-radius: 9999px; border: none; outline: 1.7px solid rgba(255, 255, 255, 0.21); background: rgba(66, 106, 120, 0.16); backdrop-filter: blur(1.5px); display: flex; align-items: center; overflow: hidden;">
                <button id="chat-attach" style="width: 44px; height: 44px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: transform 0.1s ease;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <input id="chat-input" type="text" placeholder="Type a message..." style="flex: 1; height: 44px; border: none; outline: none; background: transparent; padding: 0 16px 0 0; color: white; font-size: 13px; font-family: Poppins, sans-serif;" />
            </div>
            <button id="chat-send" style="width: 44px; height: 44px; border-radius: 9999px; border: none; background: #04BCFF; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.1s ease; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#040929" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) window.location.reload();
        });

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', function() {
                document.documentElement.style.setProperty('--vh', window.visualViewport.height + 'px');
            });
        }

        var chatBox = document.getElementById('chat-messages');
        var chatInput = document.getElementById('chat-input');
        var chatSend = document.getElementById('chat-send');
        var chatStateKey = 'brudms_ai_chat_{{ $user->id }}';

        var mockReplies = [
            "I'm still learning! This is a placeholder response.",
            "Ziqah (AI) is under development. Add OPENAI_API_KEY to your .env to enable real AI.",
        ];

        var pendingImage = null;
        var chatUrl = '{{ route("customer.chat") }}';
        var reportUrl = '{{ route("customer.rproblem", ["name" => $user->profileSlug()]) }}';
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function saveChatState() {
            try {
                if (!chatBox) return;
                var clone = chatBox.cloneNode(true);
                clone.querySelectorAll('.msg-typing').forEach(function(el) { el.remove(); });
                var payload = {
                    html: clone.innerHTML || '',
                    pendingImage: pendingImage || null,
                };
                localStorage.setItem(chatStateKey, JSON.stringify(payload));
            } catch (e) {}
        }

        var cachedUserLat = null;
        var cachedUserLng = null;
        (function prefetchLocation() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    cachedUserLat = pos.coords.latitude;
                    cachedUserLng = pos.coords.longitude;
                },
                function() {},
                { enableHighAccuracy: false, maximumAge: 180000, timeout: 12000 }
            );
        })();

        function attachLocationForChat(payload, text, callback) {
            if (cachedUserLat != null && cachedUserLng != null) {
                payload.latitude = cachedUserLat;
                payload.longitude = cachedUserLng;
                callback(payload);
                return;
            }
            var wantsNearby = /near|nearest|dekat|closest|around\s*(me|here)|nearby|sekitar|berhampiran|terdekat|radius/i.test(text || '');
            if (wantsNearby && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        cachedUserLat = pos.coords.latitude;
                        cachedUserLng = pos.coords.longitude;
                        payload.latitude = cachedUserLat;
                        payload.longitude = cachedUserLng;
                        callback(payload);
                    },
                    function() { callback(payload); },
                    { enableHighAccuracy: false, maximumAge: 60000, timeout: 8000 }
                );
                return;
            }
            callback(payload);
        }

        function appendBotReply(rawReply, reportImageUrl, quickActions, forceShowReportButton) {
            var text = String(rawReply || '');
            var showReportButton = text.indexOf('SHOW_REPORT_BUTTON') !== -1;
            text = text.replace(/SHOW_REPORT_BUTTON/g, '').trim();
            if (forceShowReportButton === true) {
                showReportButton = true;
            }
            if (!text) text = mockReplies[0];

            var botMsg = document.createElement('div');
            botMsg.className = 'msg-bot';
            botMsg.textContent = text;
            chatBox.appendChild(botMsg);

            if (showReportButton) {
                var a = document.createElement('a');
                a.className = 'msg-action-btn';
                a.href = reportUrl;
                a.textContent = 'Go to report';
                botMsg.appendChild(a);
            }

            if (reportImageUrl) {
                var reportImg = document.createElement('img');
                reportImg.className = 'msg-img';
                reportImg.src = reportImageUrl;
                reportImg.alt = 'report image';
                reportImg.style.marginTop = '10px';
                botMsg.appendChild(reportImg);
            }

            if (Array.isArray(quickActions) && quickActions.length) {
                var wrap = document.createElement('div');
                wrap.className = 'msg-quick-actions';
                quickActions.forEach(function(action) {
                    if (!action || !action.label || !action.type || !action.value) return;
                    var el = document.createElement(action.type === 'url' || action.type === 'tel' ? 'a' : 'button');
                    el.className = 'quick-chip';
                    if (action.type === 'tel') {
                        el.classList.add('quick-chip-text');
                        el.textContent = String(action.value);
                    } else {
                        el.textContent = action.label;
                    }
                    if (action.type === 'url') {
                        el.href = action.value;
                        el.target = '_blank';
                        el.rel = 'noopener noreferrer';
                    } else if (action.type === 'tel') {
                        el.href = 'tel:' + action.value;
                    } else {
                        el.type = 'button';
                        el.addEventListener('click', function() {
                            el.classList.add('is-active');
                            chatInput.value = action.value;
                            sendMessage();
                            setTimeout(function() { el.classList.remove('is-active'); }, 250);
                        });
                    }
                    el.addEventListener('click', function() {
                        el.classList.add('is-active');
                        setTimeout(function() { el.classList.remove('is-active'); }, 250);
                    });
                    wrap.appendChild(el);
                });
                if (wrap.children.length) botMsg.appendChild(wrap);
            }
            chatBox.scrollTop = chatBox.scrollHeight;
            saveChatState();
        }

        function sendMessage() {
            var text = chatInput.value.trim();
            var hasImage = !!pendingImage;
            if (!text && !hasImage) return;

            if (hasImage && text) {
                var group = document.createElement('div');
                group.className = 'msg-group';
                var imgWrap = document.createElement('div');
                imgWrap.className = 'msg-img-only';
                var img = document.createElement('img');
                img.src = pendingImage;
                img.className = 'msg-img';
                imgWrap.appendChild(img);
                group.appendChild(imgWrap);
                var userMsg = document.createElement('div');
                userMsg.className = 'msg-user';
                userMsg.textContent = text;
                group.appendChild(userMsg);
                chatBox.appendChild(group);
            } else if (hasImage) {
                var imgWrap = document.createElement('div');
                imgWrap.className = 'msg-img-only';
                var img = document.createElement('img');
                img.src = pendingImage;
                img.className = 'msg-img';
                imgWrap.appendChild(img);
                chatBox.appendChild(imgWrap);
            } else if (text) {
                var userMsg = document.createElement('div');
                userMsg.className = 'msg-user';
                userMsg.textContent = text;
                chatBox.appendChild(userMsg);
            }
            chatInput.value = '';
            var imageToSend = pendingImage;
            pendingImage = null;
            document.getElementById('chat-preview').style.display = 'none';
            chatBox.scrollTop = chatBox.scrollHeight;
            saveChatState();

            var typing = document.createElement('div');
            typing.className = 'msg-typing';
            typing.textContent = hasImage ? 'analyzing image...' : 'typing...';
            chatBox.appendChild(typing);
            chatBox.scrollTop = chatBox.scrollHeight;

            var payload = { message: text || null, image: imageToSend || null };
            attachLocationForChat(payload, text, function(finalPayload) {
                fetch(chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(finalPayload),
                })
                .then(function(r) {
                    return r.json().then(function(data) {
                        return { ok: r.ok, data: data };
                    });
                })
                .then(function(res) {
                    if (typing.parentNode) chatBox.removeChild(typing);
                    appendBotReply(
                        res.ok && res.data.reply ? res.data.reply : (res.data.error || mockReplies[0]),
                        res.ok ? (res.data.report_image_url || null) : null,
                        res.ok ? (res.data.quick_actions || []) : [],
                        res.ok ? (res.data.show_report_button === true) : false
                    );
                })
                .catch(function(err) {
                    if (typing.parentNode) chatBox.removeChild(typing);
                    var botMsg = document.createElement('div');
                    botMsg.className = 'msg-bot';
                    botMsg.textContent = 'Something went wrong. Please try again.';
                    chatBox.appendChild(botMsg);
                    chatBox.scrollTop = chatBox.scrollHeight;
                    saveChatState();
                });
            });
        }

        chatSend.addEventListener('click', sendMessage);
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') sendMessage();
        });

        var chatAttach = document.getElementById('chat-attach');
        var chatFile = document.getElementById('chat-file');

        chatAttach.addEventListener('click', function() {
            chatFile.click();
        });

        var previewEl = document.getElementById('chat-preview');
        var previewImg = document.getElementById('chat-preview-img');
        var previewRemove = document.getElementById('chat-preview-remove');

        (function restoreChatState() {
            try {
                var raw = localStorage.getItem(chatStateKey);
                if (!raw) return;
                var data = JSON.parse(raw);
                if (data && typeof data.html === 'string' && data.html.trim().length > 0) {
                    chatBox.innerHTML = data.html;
                }
                if (data && data.pendingImage) {
                    pendingImage = data.pendingImage;
                    previewImg.src = pendingImage;
                    previewEl.style.display = 'flex';
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            } catch (e) {}
        })();

        chatFile.addEventListener('change', function() {
            var file = chatFile.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function(e) {
                pendingImage = e.target.result;
                previewImg.src = pendingImage;
                previewEl.style.display = 'flex';
                saveChatState();
            };
            reader.readAsDataURL(file);
            chatFile.value = '';
        });

        previewRemove.addEventListener('click', function() {
            pendingImage = null;
            previewImg.src = '';
            previewEl.style.display = 'none';
            saveChatState();
        });

        document.addEventListener('paste', function(e) {
            var items = e.clipboardData && e.clipboardData.items;
            if (!items) return;
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    e.preventDefault();
                    var file = items[i].getAsFile();
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        pendingImage = ev.target.result;
                        previewImg.src = pendingImage;
                        previewEl.style.display = 'flex';
                        saveChatState();
                    };
                    reader.readAsDataURL(file);
                    break;
                }
            }
        });

        window.addEventListener('beforeunload', saveChatState);

        document.querySelectorAll('.delayed-nav').forEach(function(el) {
            el.style.transition = 'transform 0.1s ease';
            el.addEventListener('click', function(e) {
                e.preventDefault();
                var href = el.getAttribute('href');
                el.style.transform = 'scale(0.95)';
                setTimeout(function() {
                    el.style.transform = 'scale(1)';
                    setTimeout(function() {
                        window.location.href = href;
                    }, 100);
                }, 100);
            });
        });
    </script>
    @endpush
</x-layouts::customer>
