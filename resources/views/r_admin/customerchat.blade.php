<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Admin Dashboard – BruDMS</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .admin-layout { display: flex; min-height: 100vh; font-family: Poppins, sans-serif; }
        .admin-sidebar { width: 280px; background: #0f172a; border-right: 1px solid #334155; padding: 16px; overflow-y: auto; }
        .admin-main { flex: 1; background: #020617; padding: 20px; }
        .admin-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .admin-title { color: white; font-size: 20px; font-weight: 700; }
        .conv-list { display: flex; flex-direction: column; gap: 6px; }
        .conv-item { padding: 12px 14px; background: rgba(255,255,255,0.06); border-radius: 10px; cursor: pointer; transition: background 0.15s; border: none; width: 100%; text-align: left; color: #e2e8f0; }
        .conv-item:hover { background: rgba(255,255,255,0.1); }
        .conv-item.active { background: rgba(4, 188, 255, 0.2); outline: 1px solid #04BCFF; }
        .conv-name { font-weight: 600; font-size: 14px; }
        .conv-preview { font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .chat-panel { display: flex; flex-direction: column; height: calc(100vh - 100px); background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); overflow: hidden; }
        .chat-header { padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,0.08); color: white; font-weight: 600; font-size: 15px; display: flex; align-items: center; justify-content: space-between; }
        .terminate-btn { padding: 6px 14px; border-radius: 8px; background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.4); cursor: pointer; font-size: 12px; font-family: Poppins, sans-serif; transition: background 0.15s; }
        .terminate-btn:hover { background: rgba(239,68,68,0.35); }
        .chat-messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
        .chat-msg { max-width: 75%; padding: 10px 14px; border-radius: 12px; font-size: 13px; word-wrap: break-word; }
        .chat-msg.customer { align-self: flex-start; background: rgba(255,255,255,0.08); color: white; }
        .chat-msg.admin { align-self: flex-end; background: #04BCFF; color: #040929; }
        .chat-msg-system { align-self: center; color: rgba(255,255,255,0.5); font-size: 11px; padding: 8px; }
        .chat-input-row { display: flex; gap: 10px; padding: 14px; border-top: 1px solid rgba(255,255,255,0.08); }
        .chat-input { flex: 1; padding: 12px 16px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.2); background: rgba(30,41,59,0.5); color: white; font-size: 13px; font-family: Poppins, sans-serif; outline: none; }
        .chat-input::placeholder { color: rgba(255,255,255,0.4); }
        .chat-send { width: 44px; height: 44px; border-radius: 999px; border: none; background: #04BCFF; color: #040929; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: transform 0.1s; }
        .chat-send:hover { transform: scale(1.05); }
        .chat-send:active { transform: scale(0.95); }
        .empty-state { color: rgba(255,255,255,0.4); text-align: center; padding: 40px; font-size: 14px; }
        .logout-btn { padding: 8px 16px; border-radius: 8px; background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.4); cursor: pointer; font-size: 13px; font-family: Poppins, sans-serif; }
        .logout-btn:hover { background: rgba(239,68,68,0.3); }
        .chat-img { max-width: 200px; border-radius: 8px; display: block; margin-top: 6px; cursor: pointer; transition: opacity 0.15s; }
        .chat-img:hover { opacity: 0.8; }
        .img-overlay { position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.85); display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .img-overlay img { max-width: 90vw; max-height: 90vh; border-radius: 12px; object-fit: contain; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-header">
                <span class="admin-title">Customer Support</span>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn">Log out</button>
                </form>
            </div>
            <p style="color: rgba(255,255,255,0.5); font-size: 12px; margin-bottom: 12px;">Select a conversation</p>
            <div id="conv-list" class="conv-list"></div>
            <div id="conv-empty" class="empty-state" style="margin-top: 20px; display: none;">No conversations yet. Waiting for customer messages.</div>
        </aside>
        <main class="admin-main">
            <div id="chat-panel" class="chat-panel" style="display: none;">
                <div id="chat-header" class="chat-header">
                    <span id="chat-header-name"></span>
                    <button type="button" id="terminate-btn" class="terminate-btn" onclick="terminateChat()">Terminate Chat</button>
                </div>
                <div id="chat-messages" class="chat-messages"></div>
                <form class="chat-input-row" id="chat-form" onsubmit="sendReply(); return false;">
                    <input type="text" id="chat-input" class="chat-input" placeholder="Type your reply..." autocomplete="off" />
                    <button type="submit" id="chat-send" class="chat-send" aria-label="Send">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                    </button>
                </form>
            </div>
            <div id="chat-placeholder" class="empty-state" style="display: flex; align-items: center; justify-content: center; height: 400px;">
                Select a conversation from the list
            </div>
        </main>
    </div>

    <script>
        function openImageOverlay(src) {
            var overlay = document.createElement('div');
            overlay.className = 'img-overlay';
            var img = document.createElement('img');
            img.src = src;
            overlay.appendChild(img);
            overlay.addEventListener('click', function() { overlay.remove(); });
            document.body.appendChild(overlay);
        }

        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        var currentCustomerId = null;
        var chatTerminated = false;
        var currentChannel = null;
        var hasAdminReply = false;
        var convListEl = document.getElementById('conv-list');
        var convEmptyEl = document.getElementById('conv-empty');
        var chatPanelEl = document.getElementById('chat-panel');
        var chatPlaceholderEl = document.getElementById('chat-placeholder');
        var chatHeaderEl = document.getElementById('chat-header');
        var chatMessagesEl = document.getElementById('chat-messages');
        var chatInputEl = document.getElementById('chat-input');
        var chatSendEl = document.getElementById('chat-send');

        function loadConversations() {
            fetch('{{ route("admin.support.conversations") }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var convs = data.conversations || [];
                    convListEl.innerHTML = '';
                    convEmptyEl.style.display = convs.length === 0 ? 'block' : 'none';
                    convs.forEach(function(c) {
                        var btn = document.createElement('button');
                        btn.className = 'conv-item' + (currentCustomerId === c.id ? ' active' : '');
                        btn.type = 'button';
                        btn.dataset.id = c.id;
                        btn.dataset.name = c.name || '';
                        btn.innerHTML = '<span class="conv-name">' + escapeHtml(c.name || 'Unknown') + '</span><span class="conv-preview">' + escapeHtml(c.last_preview || 'No messages') + '</span>';
                        btn.addEventListener('click', function() { openConversation(c.id, c.name); });
                        convListEl.appendChild(btn);
                    });
                }).catch(function() {});
        }

        function escapeHtml(s) {
            var d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function openConversation(userId, name) {
            if (currentChannel && window.Echo) {
                window.Echo.leave('support.' + currentCustomerId);
            }

            currentCustomerId = parseInt(userId, 10);
            chatPlaceholderEl.style.display = 'none';
            chatPanelEl.style.display = 'flex';
            document.getElementById('chat-header-name').textContent = name || 'Customer';
            chatTerminated = false;
            hasAdminReply = false;
            document.getElementById('chat-form').style.display = '';
            document.getElementById('terminate-btn').style.display = '';
            document.querySelectorAll('.conv-item').forEach(function(el) {
                el.classList.toggle('active', parseInt(el.dataset.id, 10) === currentCustomerId);
            });
            loadMessages();

            function subscribeToChannel() {
                if (!window.Echo) { setTimeout(subscribeToChannel, 100); return; }
                currentChannel = window.Echo.private('support.' + currentCustomerId)
                    .listen('.SupportMessageSent', function(data) {
                        if (chatTerminated) return;
                        if (data.from_customer) {
                            renderMessage(data, { showSystemBanner: false });
                            chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
                            loadConversations();
                        }
                    })
                    .listen('.SupportChatTerminated', function() {
                        chatTerminated = true;
                        var sys = document.createElement('div');
                        sys.className = 'chat-msg chat-msg-system';
                        sys.textContent = '-------- Chat has been terminated. Thank You! --------';
                        chatMessagesEl.appendChild(sys);
                        document.getElementById('chat-form').style.display = 'none';
                        document.getElementById('terminate-btn').style.display = 'none';
                        chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
                    });
            }
            subscribeToChannel();
        }

        function renderMessage(m, opts) {
            opts = opts || {};
            if (m.message === '[TERMINATED]') {
                var sys = document.createElement('div');
                sys.className = 'chat-msg chat-msg-system';
                sys.textContent = '-------- Chat has been terminated. Thank You! --------';
                chatMessagesEl.appendChild(sys);
                chatTerminated = true;
                document.getElementById('chat-form').style.display = 'none';
                document.getElementById('terminate-btn').style.display = 'none';
                return;
            }
            if (!m.from_customer) {
                if (opts.showSystemBanner) {
                    var sys = document.createElement('div');
                    sys.className = 'chat-msg chat-msg-system';
                    sys.textContent = '------- You have connected to our customer support -------';
                    chatMessagesEl.appendChild(sys);
                }
                var div = document.createElement('div');
                div.className = 'chat-msg admin';
                div.textContent = m.message;
                chatMessagesEl.appendChild(div);
            } else {
                var hasText = m.message && m.message !== '[Image]';
                if (m.image_path) {
                    var img = document.createElement('img');
                    img.src = m.image_path;
                    img.className = 'chat-img';
                    img.style.alignSelf = 'flex-start';
                    img.addEventListener('click', function() { openImageOverlay(m.image_path); });
                    chatMessagesEl.appendChild(img);
                }
                if (hasText) {
                    var d = document.createElement('div');
                    d.className = 'chat-msg customer';
                    d.textContent = m.message;
                    chatMessagesEl.appendChild(d);
                }
            }
        }

        function loadMessages() {
            if (!currentCustomerId) return;
            chatMessagesEl.innerHTML = '';
            hasAdminReply = false;
            fetch('/admin/support/conversations/' + currentCustomerId + '/messages', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var messages = data.messages || [];
                    var firstAdmin = true;
                    messages.forEach(function(m) {
                        var showBanner = !m.from_customer && firstAdmin;
                        renderMessage(m, { showSystemBanner: showBanner });
                        if (!m.from_customer) { firstAdmin = false; hasAdminReply = true; }
                    });
                    chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
                }).catch(function() {});
        }

        function sendReply() {
            var text = (chatInputEl && chatInputEl.value || '').trim();
            if (!text || !currentCustomerId || chatTerminated) return;

            var fd = new FormData();
            fd.append('_token', token);
            fd.append('message', text);

            chatInputEl.value = '';

            fetch('/admin/support/conversations/' + currentCustomerId + '/reply', {
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
                if (!r.ok) throw new Error('Request failed');
                return r.json();
            }).then(function(data) {
                if (data.message) {
                    renderMessage(data.message, { showSystemBanner: !hasAdminReply });
                    hasAdminReply = true;
                    chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
                }
            }).catch(function(err) {
                console.error('Send failed:', err);
            });
        }

        function terminateChat() {
            if (!currentCustomerId) return;
            if (!confirm('Terminate this chat? All messages will be cleared.')) return;
            var fd = new FormData();
            fd.append('_token', token);
            fetch('/admin/support/conversations/' + currentCustomerId + '/terminate', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token }
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.terminated) {
                    if (currentChannel && window.Echo) {
                        window.Echo.leave('support.' + currentCustomerId);
                        currentChannel = null;
                    }
                    currentCustomerId = null;
                    chatTerminated = true;
                    chatPanelEl.style.display = 'none';
                    chatPlaceholderEl.style.display = 'flex';
                    chatMessagesEl.innerHTML = '';
                    loadConversations();
                }
            }).catch(function(err) { console.error('Terminate failed:', err); });
        }

        loadConversations();
        setInterval(loadConversations, 10000);
    </script>
</body>
</html>
