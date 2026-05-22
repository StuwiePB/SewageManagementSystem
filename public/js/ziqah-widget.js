(function () {
    var cfg = window.BrudmsZiqahConfig || {};
    var fab = document.getElementById('ziqah-fab');
    var overlay = document.getElementById('ziqah-overlay');
    var sheet = document.getElementById('ziqah-sheet');
    if (!fab || !overlay || !sheet) return;

    var size = 44;
    var edgePad = 12;
    var storageKey = 'brudms_ziqah_fab_pos';
    var dragging = false;
    var offsetX = 0;
    var offsetY = 0;
    var startX = 0;
    var startY = 0;
    var dragPx = 0;
    var chatReady = false;
    var closeTimer = null;

    function ptr(e) {
        if (e.touches && e.touches[0]) return { x: e.touches[0].clientX, y: e.touches[0].clientY };
        return { x: e.clientX, y: e.clientY };
    }

    function clampY(y) {
        var maxY = window.innerHeight - size - edgePad;
        return Math.min(maxY, Math.max(edgePad, y));
    }

    function xSide(side) {
        return side === 'left' ? edgePad : window.innerWidth - size - edgePad;
    }

    function snap(x, y) {
        var side = (x + size / 2) < window.innerWidth / 2 ? 'left' : 'right';
        return { side: side, x: xSide(side), y: clampY(y) };
    }

    function setFabPos(x, y) {
        fab.style.left = x + 'px';
        fab.style.top = y + 'px';
        fab.style.right = 'auto';
        fab.style.bottom = 'auto';
    }

    function saveFab(side, y) {
        try { localStorage.setItem(storageKey, JSON.stringify({ side: side, y: y })); } catch (e) {}
    }

    function restoreFab() {
        try {
            var d = JSON.parse(localStorage.getItem(storageKey) || 'null');
            if (d && (d.side === 'left' || d.side === 'right')) {
                setFabPos(xSide(d.side), typeof d.y === 'number' ? d.y : edgePad);
            }
        } catch (e) {}
    }

    function fabOrigin() {
        var r = fab.getBoundingClientRect();
        return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
    }

    function openZiqah() {
        var o = fabOrigin();
        sheet.style.transformOrigin = o.x + 'px ' + o.y + 'px';
        sheet.classList.remove('is-expanded');
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        fab.style.visibility = 'hidden';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                sheet.classList.add('is-expanded');
            });
        });
        if (!chatReady) {
            initZiqahChat();
            chatReady = true;
        }
    }

    function closeZiqah() {
        var o = fabOrigin();
        sheet.style.transformOrigin = o.x + 'px ' + o.y + 'px';
        sheet.classList.remove('is-expanded');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        fab.style.visibility = '';
        clearTimeout(closeTimer);
        closeTimer = setTimeout(function () {
            overlay.classList.remove('is-open');
        }, 420);
    }

    fab.addEventListener('mousedown', function (e) {
        if (e.button !== 0) return;
        dragging = true;
        dragPx = 0;
        fab.classList.add('is-dragging');
        var r = fab.getBoundingClientRect();
        var p = ptr(e);
        startX = p.x;
        startY = p.y;
        offsetX = p.x - r.left;
        offsetY = p.y - r.top;
        e.preventDefault();
    });
    fab.addEventListener('touchstart', function (e) {
        dragging = true;
        dragPx = 0;
        fab.classList.add('is-dragging');
        var r = fab.getBoundingClientRect();
        var p = ptr(e);
        startX = p.x;
        startY = p.y;
        offsetX = p.x - r.left;
        offsetY = p.y - r.top;
        e.preventDefault();
    }, { passive: false });

    function move(e) {
        if (!dragging) return;
        var p = ptr(e);
        var maxX = window.innerWidth - size - edgePad;
        setFabPos(Math.min(maxX, Math.max(edgePad, p.x - offsetX)), p.y - offsetY);
        dragPx = Math.max(dragPx, Math.hypot(p.x - startX, p.y - startY));
        e.preventDefault();
    }

    function up() {
        if (!dragging) return;
        dragging = false;
        fab.classList.remove('is-dragging');
        var r = fab.getBoundingClientRect();
        var s = snap(r.left, r.top);
        setFabPos(s.x, s.y);
        saveFab(s.side, s.y);
        if (dragPx < 10) openZiqah();
    }

    window.addEventListener('mousemove', move);
    window.addEventListener('touchmove', move, { passive: false });
    window.addEventListener('mouseup', up);
    window.addEventListener('touchend', up);
    window.addEventListener('touchcancel', up);

    var closeBtn = document.getElementById('ziqah-close');
    var backdrop = document.getElementById('ziqah-overlay-bg');
    if (closeBtn) closeBtn.addEventListener('click', closeZiqah);
    if (backdrop) backdrop.addEventListener('click', closeZiqah);

    restoreFab();

    function initZiqahChat() {
        var chatBox = document.getElementById('ziqah-chat-messages');
        var chatInput = document.getElementById('ziqah-chat-input');
        var chatSend = document.getElementById('ziqah-chat-send');
        var chatAttach = document.getElementById('ziqah-chat-attach');
        var chatFile = document.getElementById('ziqah-chat-file');
        var previewEl = document.getElementById('ziqah-chat-preview');
        var previewImg = document.getElementById('ziqah-chat-preview-img');
        var previewRemove = document.getElementById('ziqah-chat-preview-remove');
        if (!chatBox || !chatInput || !chatSend) return;

        var chatStateKey = 'brudms_ai_chat_' + (cfg.userId || '');
        var mockReplies = [
            "I'm still learning! This is a placeholder response.",
            'Ziqah (AI) is under development. Add OPENAI_API_KEY to your .env to enable real AI.',
        ];
        var pendingImage = null;
        var chatUrl = cfg.chatUrl || '';
        var reportUrl = cfg.reportUrl || '';
        var csrfToken = cfg.csrf || '';

        function saveChatState() {
            try {
                var clone = chatBox.cloneNode(true);
                clone.querySelectorAll('.msg-typing').forEach(function (el) { el.remove(); });
                localStorage.setItem(chatStateKey, JSON.stringify({
                    html: clone.innerHTML || '',
                    pendingImage: pendingImage || null,
                }));
            } catch (e) {}
        }

        var cachedUserLat = null;
        var cachedUserLng = null;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    cachedUserLat = pos.coords.latitude;
                    cachedUserLng = pos.coords.longitude;
                },
                function () {},
                { enableHighAccuracy: false, maximumAge: 180000, timeout: 12000 }
            );
        }

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
                    function (pos) {
                        cachedUserLat = pos.coords.latitude;
                        cachedUserLng = pos.coords.longitude;
                        payload.latitude = cachedUserLat;
                        payload.longitude = cachedUserLng;
                        callback(payload);
                    },
                    function () { callback(payload); },
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
            if (forceShowReportButton === true) showReportButton = true;
            if (!text) text = mockReplies[0];

            var botMsg = document.createElement('div');
            botMsg.className = 'msg-bot';
            botMsg.textContent = text;
            chatBox.appendChild(botMsg);

            if (showReportButton && reportUrl) {
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
                quickActions.forEach(function (action) {
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
                        el.addEventListener('click', function () {
                            chatInput.value = action.value;
                            sendMessage();
                        });
                    }
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
                var imgOnly = document.createElement('div');
                imgOnly.className = 'msg-img-only';
                var imgEl = document.createElement('img');
                imgEl.src = pendingImage;
                imgEl.className = 'msg-img';
                imgOnly.appendChild(imgEl);
                chatBox.appendChild(imgOnly);
            } else {
                var userMsgOnly = document.createElement('div');
                userMsgOnly.className = 'msg-user';
                userMsgOnly.textContent = text;
                chatBox.appendChild(userMsgOnly);
            }

            chatInput.value = '';
            var imageToSend = pendingImage;
            pendingImage = null;
            if (previewEl) previewEl.style.display = 'none';
            chatBox.scrollTop = chatBox.scrollHeight;
            saveChatState();

            var typing = document.createElement('div');
            typing.className = 'msg-typing';
            typing.textContent = hasImage ? 'analyzing image...' : 'typing...';
            chatBox.appendChild(typing);
            chatBox.scrollTop = chatBox.scrollHeight;

            var payload = { message: text || null, image: imageToSend || null };
            attachLocationForChat(payload, text, function (finalPayload) {
                fetch(chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(finalPayload),
                })
                    .then(function (r) {
                        return r.json().then(function (data) {
                            return { ok: r.ok, data: data };
                        });
                    })
                    .then(function (res) {
                        if (typing.parentNode) chatBox.removeChild(typing);
                        appendBotReply(
                            res.ok && res.data.reply ? res.data.reply : (res.data.error || mockReplies[0]),
                            res.ok ? (res.data.report_image_url || null) : null,
                            res.ok ? (res.data.quick_actions || []) : [],
                            res.ok ? (res.data.show_report_button === true) : false
                        );
                    })
                    .catch(function () {
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
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') sendMessage();
        });
        if (chatAttach && chatFile) {
            chatAttach.addEventListener('click', function () { chatFile.click(); });
            chatFile.addEventListener('change', function () {
                var file = chatFile.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    pendingImage = e.target.result;
                    if (previewImg) previewImg.src = pendingImage;
                    if (previewEl) previewEl.style.display = 'flex';
                    saveChatState();
                };
                reader.readAsDataURL(file);
                chatFile.value = '';
            });
        }
        if (previewRemove) {
            previewRemove.addEventListener('click', function () {
                pendingImage = null;
                if (previewImg) previewImg.src = '';
                if (previewEl) previewEl.style.display = 'none';
                saveChatState();
            });
        }

        try {
            var raw = localStorage.getItem(chatStateKey);
            if (raw) {
                var data = JSON.parse(raw);
                if (data && typeof data.html === 'string' && data.html.trim()) {
                    chatBox.innerHTML = data.html;
                }
                if (data && data.pendingImage && previewImg && previewEl) {
                    pendingImage = data.pendingImage;
                    previewImg.src = pendingImage;
                    previewEl.style.display = 'flex';
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        } catch (e) {}
    }
})();
