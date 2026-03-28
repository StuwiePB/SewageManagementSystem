<x-layouts::customer :title="__('Contact Customer Support') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            #support-input::placeholder {
                color: rgba(255, 255, 255, 0.3);
                font-family: Poppins, sans-serif;
            }
            .input-pill:focus-within { outline: 1.7px solid #04BCFF; }
            #support-send:active { transform: scale(0.9); }
            #support-attach:active { transform: scale(0.9); }
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
            @keyframes msgPop {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .msg-preview { max-width: 56px; max-height: 56px; border-radius: 8px; object-fit: cover; }
            .preview-wrap {
                display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px;
            }
            .preview-remove {
                width: 20px; height: 20px; border-radius: 9999px;
                background: rgba(255,255,255,0.2); border: none; color: white; cursor: pointer;
                font-size: 14px; line-height: 1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            }
            .concerns-rect {
                background: rgba(66, 106, 120, 0.16);
                border-radius: 16px;
                border: none;
                padding: 16px;
                margin-bottom: 12px;
            }
            .concerns-heading {
                color: white;
                font-size: 14px;
                font-weight: 700;
                font-family: Poppins, sans-serif;
                margin: 0 0 12px 0;
            }
            .concerns-cat {
                color: rgba(255, 255, 255, 0.45);
                font-size: 11px;
                font-weight: 600;
                font-family: Poppins, sans-serif;
                margin: 10px 0 6px 0;
            }
            .concerns-cat:first-of-type { margin-top: 0; }
            .concerns-pills {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }
            .concern-pill {
                padding: 5px 10px;
                border-radius: 999px;
                background: rgba(66, 106, 120, 0.25);
                border: none;
                color: white;
                font-size: 9px;
                font-family: Poppins, sans-serif;
                font-weight: 400;
                cursor: pointer;
                transition: transform 0.1s ease, background 0.2s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
            }
            .concern-pill:active { transform: scale(0.96); }
            .concern-pill:hover { background: rgba(66, 106, 120, 0.4); }
            .msg-system {
                align-self: center;
                color: rgba(255, 255, 255, 0.5);
                font-size: 10px;
                font-family: Poppins, sans-serif;
                font-weight: 400;
                padding: 8px 12px;
                text-align: center;
            }
        </style>
    @endpush

    {{-- Desktop: normal background --}}
    <div class="hidden lg:block fixed inset-0 z-0" style="background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>

    {{-- Mobile: rotated -90deg background --}}
    <div class="lg:hidden" style="position: fixed; inset: 0; overflow: hidden; z-index: 0;">
        <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
    </div>

    @php $user = auth()->user(); @endphp

    {{-- Header: back arrow + Contact Customer Support --}}
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.customersupport', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; transition: transform 0.1s ease;" aria-label="{{ __('Back') }}">
            <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
        </a>
        <span style="color: white; font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;">{{ __('Contact Customer Support') }}</span>
    </div>

    {{-- Chat area (no dark background, like Ziqah) --}}
    <div style="position: fixed; left: 16px; right: 16px; top: 10vh; bottom: -10vh; z-index: 5; display: flex; flex-direction: column; padding: 16px;">
        {{-- Chat messages --}}
        <div id="support-messages" style="flex: 1; overflow-y: auto; padding: 16px 0; display: flex; flex-direction: column; gap: 12px; scrollbar-width: none;">
            <div class="msg-bot" id="greeting-msg">Hello {{ $user->name ?? 'there' }}, how can we assist you today?</div>
            {{-- Most common reported concerns (rectangle, no stroke) --}}
            <div class="concerns-rect" style="display: none;">
                <p class="concerns-heading">Most common reported concerns:</p>
                <p class="concerns-cat">Account & Access</p>
                <div class="concerns-pills">
                    <button type="button" class="concern-pill" data-text="Forgot Password">Forgot Password</button>
                    <button type="button" class="concern-pill" data-text="Wrong email/name on account">Wrong email/name on account</button>
                    <button type="button" class="concern-pill" data-text="Want to delete or deactivate account">Want to delete or deactivate account</button>
                    <button type="button" class="concern-pill" data-text="Can't Log in">Can't Log in</button>
                </div>
                <p class="concerns-cat">Reporting issues</p>
                <div class="concerns-pills">
                    <button type="button" class="concern-pill" data-text="Can't submit a report">Can't submit a report</button>
                    <button type="button" class="concern-pill" data-text="Form not working">Form not working</button>
                    <button type="button" class="concern-pill" data-text="Submitted a report but no response or update">Submitted a report but no response or update</button>
                </div>
                <p class="concerns-cat">Complaints & feedback</p>
                <div class="concerns-pills">
                    <button type="button" class="concern-pill" data-text="Unhappy with how a report was handled">Unhappy with how a report was handled</button>
                    <button type="button" class="concern-pill" data-text="Suggestions for new features">Suggestions for new features</button>
                    <button type="button" class="concern-pill" data-text="Response was too slow">Response was too slow</button>
                    <button type="button" class="concern-pill" data-text="Feedback that doesn't fit FAQ or Ziqah">Feedback that doesn't fit FAQ or Ziqah</button>
                </div>
            </div>
        </div>

        {{-- Input bar (mimic Ziqah) --}}
        <input id="support-file" type="file" accept="image/*" style="display: none;" />
        <div id="support-preview" class="preview-wrap" style="display: none;">
            <img id="support-preview-img" class="msg-preview" alt="" />
            <button id="support-preview-remove" class="preview-remove" type="button">×</button>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10vh;">
            <div class="input-pill" style="flex: 1; height: 44px; border-radius: 9999px; border: none; outline: 1.7px solid rgba(255, 255, 255, 0.21); background: rgba(66, 106, 120, 0.16); backdrop-filter: blur(1.5px); display: flex; align-items: center; overflow: hidden;">
                <button id="support-attach" style="width: 44px; height: 44px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: transform 0.1s ease;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <input id="support-input" type="text" placeholder="Type a message..." style="flex: 1; height: 44px; border: none; outline: none; background: transparent; padding: 0 16px 0 0; color: white; font-size: 13px; font-family: Poppins, sans-serif;" />
            </div>
            <button id="support-send" style="width: 44px; height: 44px; border-radius: 9999px; border: none; background: #04BCFF; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.1s ease; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#040929" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
            </button>
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

        var supportBox = document.getElementById('support-messages');
        var supportInput = document.getElementById('support-input');
        var supportSend = document.getElementById('support-send');
        var greetingEl = supportBox.querySelector('.msg-bot');
        var concernsRect = document.querySelector('.concerns-rect');
        var initialHTML = supportBox.innerHTML;
        var pendingImage = null;
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function appendSystemMessage() {
            var div = document.createElement('div');
            div.className = 'msg-system';
            div.textContent = '------- You have connected to our customer support -------';
            return div;
        }

        var chatTerminated = false;

        function resetChat() {
            chatTerminated = false;
            hasAdminReply = false;
            supportBox.innerHTML = initialHTML;
            var greeting = supportBox.querySelector('.msg-bot');
            var rect = supportBox.querySelector('.concerns-rect');
            if (greeting) greeting.style.display = '';
            if (rect) rect.style.display = '';
            concernsRect = rect;
            if (supportInput) { supportInput.disabled = false; supportInput.placeholder = 'Type a message...'; supportInput.value = ''; }
            if (supportSend) { supportSend.disabled = false; supportSend.style.opacity = '1'; }
            var attachBtn = document.getElementById('support-attach');
            if (attachBtn) { attachBtn.disabled = false; attachBtn.style.opacity = '1'; }
            bindConcernPills();
        }

        function disableInput() {
            chatTerminated = true;
            if (supportInput) { supportInput.disabled = true; supportInput.placeholder = 'Chat ended'; }
            if (supportSend) { supportSend.disabled = true; supportSend.style.opacity = '0.4'; }
            var attachBtn = document.getElementById('support-attach');
            if (attachBtn) { attachBtn.disabled = true; attachBtn.style.opacity = '0.4'; }
        }

        function renderMessage(m, options) {
            options = options || {};
            if (m.message === '[TERMINATED]') {
                resetChat();
                return;
            }
            var firstAdminShown = options.firstAdminShown !== false;
            if (!m.from_customer) {
                if (firstAdminShown) {
                    supportBox.appendChild(appendSystemMessage());
                    options.firstAdminShown = false;
                }
                var bot = document.createElement('div');
                bot.className = 'msg-bot';
                if (m.image_path) {
                    var img = document.createElement('img');
                    img.src = m.image_path;
                    img.style.cssText = 'max-width: 200px; border-radius: 12px; display: block; margin-bottom: 4px;';
                    bot.appendChild(img);
                }
                if (m.message && m.message !== '[Image]') {
                    var span = document.createElement('span');
                    span.textContent = m.message;
                    bot.appendChild(span);
                } else if (m.image_path && (!m.message || m.message === '[Image]')) {
                    var img2 = document.createElement('img');
                    img2.src = m.image_path;
                    img2.style.cssText = 'max-width: 200px; border-radius: 12px; display: block;';
                    bot.appendChild(img2);
                }
                supportBox.appendChild(bot);
            } else {
                var group = document.createElement('div');
                group.style.cssText = 'display: flex; flex-direction: column; align-items: flex-end; gap: 4px;';
                if (m.image_path) {
                    var imgWrap = document.createElement('div');
                    imgWrap.style.alignSelf = 'flex-end';
                    var img = document.createElement('img');
                    img.src = m.image_path;
                    img.style.cssText = 'max-width: 200px; border-radius: 12px; display: block;';
                    imgWrap.appendChild(img);
                    group.appendChild(imgWrap);
                }
                if (m.message && m.message !== '[Image]') {
                    var userMsg = document.createElement('div');
                    userMsg.className = 'msg-user';
                    userMsg.textContent = m.message;
                    group.appendChild(userMsg);
                }
                if (group.children.length) supportBox.appendChild(group);
            }
        }

        var messagesLoaded = false;
        var hasAdminReply = false;
        var waitMsgShown = false;

        var greetingHTML = document.getElementById('greeting-msg').outerHTML;

        function loadMessages() {
            fetch('{{ route("customer.support.messages") }}', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) { return r.json(); }).then(function(data) {
                var messages = data.messages || [];
                if (messages.length > 0) {
                    supportBox.innerHTML = greetingHTML;
                    var firstAdmin = true;
                    messages.forEach(function(m) {
                        renderMessage(m, { firstAdminShown: firstAdmin });
                        if (!m.from_customer) { firstAdmin = false; hasAdminReply = true; }
                    });
                    if (!hasAdminReply) {
                        waitMsgShown = true;
                        var wait = document.createElement('div');
                        wait.className = 'msg-bot';
                        wait.id = 'wait-msg';
                        wait.textContent = 'Please wait while we connect you to a customer support representative.';
                        supportBox.appendChild(wait);
                    }
                } else {
                    var rect = supportBox.querySelector('.concerns-rect');
                    if (rect) rect.style.display = '';
                    concernsRect = rect;
                    bindConcernPills();
                }
                supportBox.scrollTop = supportBox.scrollHeight;
                messagesLoaded = true;
            }).catch(function() { messagesLoaded = true; });
        }

        function sendToApi(text, imageData, cb) {
            var fd = new FormData();
            fd.append('_token', token);
            if (text) fd.append('message', text);
            if (imageData) fd.append('image', imageData);
            fetch('{{ route("customer.support.store") }}', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                    'X-Socket-Id': window.Echo ? window.Echo.socketId() : ''
                }
            }).then(function(r) {
                if (!r.ok && r.status === 419) throw new Error('Session expired');
                return r.json();
            }).then(function(data) {
                if (cb) cb();
            }).catch(function() { if (cb) cb(); });
        }

        function sendSupportMessage(textOverride) {
            if (chatTerminated) return;
            var text = textOverride !== undefined ? String(textOverride) : (supportInput ? supportInput.value.trim() : '');
            var imgToSend = pendingImage;
            var hasImage = !!imgToSend;
            if (!text && !hasImage) return;

            if (concernsRect) concernsRect.style.display = 'none';

            var imgWrap, img, userMsg, group;
            if (hasImage && text) {
                group = document.createElement('div');
                group.style.cssText = 'display: flex; flex-direction: column; align-items: flex-end; gap: 4px;';
                imgWrap = document.createElement('div');
                imgWrap.style.alignSelf = 'flex-end';
                img = document.createElement('img');
                img.src = imgToSend;
                img.style.cssText = 'max-width: 200px; border-radius: 12px; display: block;';
                imgWrap.appendChild(img);
                group.appendChild(imgWrap);
                userMsg = document.createElement('div');
                userMsg.className = 'msg-user';
                userMsg.textContent = text;
                group.appendChild(userMsg);
                supportBox.appendChild(group);
            } else if (hasImage) {
                imgWrap = document.createElement('div');
                imgWrap.style.alignSelf = 'flex-end';
                img = document.createElement('img');
                img.src = imgToSend;
                img.style.cssText = 'max-width: 200px; border-radius: 12px; display: block;';
                imgWrap.appendChild(img);
                supportBox.appendChild(imgWrap);
            } else {
                userMsg = document.createElement('div');
                userMsg.className = 'msg-user';
                userMsg.textContent = text;
                supportBox.appendChild(userMsg);
            }
            if (!waitMsgShown && !hasAdminReply) {
                waitMsgShown = true;
                var wait = document.createElement('div');
                wait.className = 'msg-bot';
                wait.id = 'wait-msg';
                wait.textContent = 'Please wait while we connect you to a customer support representative.';
                supportBox.appendChild(wait);
            }

            if (supportInput) supportInput.value = '';
            pendingImage = null;
            if (document.getElementById('support-preview')) document.getElementById('support-preview').style.display = 'none';
            supportBox.scrollTop = supportBox.scrollHeight;

            sendToApi(text, imgToSend || null, function() { supportBox.scrollTop = supportBox.scrollHeight; });
        }

        loadMessages();

        function setupEcho() {
            if (!window.Echo) { setTimeout(setupEcho, 100); return; }
            window.Echo.private('support.{{ auth()->id() }}')
                .listen('.SupportMessageSent', function(data) {
                    if (chatTerminated) return;
                    if (!data.from_customer) {
                        var waitEl = document.getElementById('wait-msg');
                        if (waitEl) waitEl.remove();
                        if (!hasAdminReply) {
                            supportBox.appendChild(appendSystemMessage());
                            hasAdminReply = true;
                        }
                        if (concernsRect) concernsRect.style.display = 'none';
                    }
                    renderMessage(data, { firstAdminShown: false });
                    supportBox.scrollTop = supportBox.scrollHeight;
                })
                .listen('.SupportChatTerminated', function() {
                    var sys = document.createElement('div');
                    sys.className = 'msg-system';
                    sys.textContent = '-------- Chat has been terminated. Thank You! --------';
                    supportBox.appendChild(sys);
                    disableInput();
                    supportBox.scrollTop = supportBox.scrollHeight;
                });
        }
        setupEcho();

        if (supportSend) supportSend.addEventListener('click', function() { sendSupportMessage(); });
        if (supportInput) supportInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); sendSupportMessage(); }
        });

        var supportAttach = document.getElementById('support-attach');
        var supportFile = document.getElementById('support-file');
        if (supportAttach) supportAttach.addEventListener('click', function() { supportFile.click(); });

        var previewEl = document.getElementById('support-preview');
        var previewImg = document.getElementById('support-preview-img');
        var previewRemove = document.getElementById('support-preview-remove');
        if (supportFile) supportFile.addEventListener('change', function() {
            var file = supportFile.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function(e) {
                pendingImage = e.target.result;
                previewImg.src = pendingImage;
                previewEl.style.display = 'flex';
            };
            reader.readAsDataURL(file);
            supportFile.value = '';
        });
        if (previewRemove) previewRemove.addEventListener('click', function() {
            pendingImage = null;
            previewImg.src = '';
            previewEl.style.display = 'none';
        });

        function bindConcernPills() {}
        supportBox.addEventListener('click', function(e) {
            var btn = e.target.closest('.concern-pill');
            if (!btn) return;
            var text = btn.getAttribute('data-text');
            if (!text) return;
            if (concernsRect) concernsRect.style.display = 'none';
            sendSupportMessage(text);
        });
    </script>
    @endpush
</x-layouts::customer>
