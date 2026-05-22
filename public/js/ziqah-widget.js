(function () {
    var cfg = window.BrudmsZiqahConfig || {};
    var size = 44;
    var edgePad = 12;
    var fabStorageKey = 'brudms_ziqah_fab_pos';
    var sheetMaxRatio = 0.96;
    var sheetCloseBelowRatio = 0.7;
    var defaultFabYRatio = 0.72;
    var tapThreshold = 10;

    function clamp01(n) {
        return Math.min(1, Math.max(0, n));
    }

    function fabMaxY() {
        return window.innerHeight - size - edgePad;
    }

    function clampFabY(y) {
        return Math.min(fabMaxY(), Math.max(edgePad, y));
    }

    function xSide(side) {
        return side === 'left' ? edgePad : window.innerWidth - size - edgePad;
    }

    function yFromRatio(ratio) {
        var span = window.innerHeight - size - edgePad * 2;
        return edgePad + clamp01(ratio) * Math.max(0, span);
    }

    function ratioFromY(y) {
        var span = window.innerHeight - size - edgePad * 2;
        if (span <= 0) return 0;
        return clamp01((y - edgePad) / span);
    }

    function snapFab(x, y) {
        var side = (x + size / 2) < window.innerWidth / 2 ? 'left' : 'right';
        return { side: side, x: xSide(side), y: clampFabY(y) };
    }

    function setFabPos(fab, x, y) {
        fab.style.left = x + 'px';
        fab.style.top = clampFabY(y) + 'px';
        fab.style.right = 'auto';
        fab.style.bottom = 'auto';
    }

    function applyFabEdge(fab, side, yRatio) {
        var s = side === 'left' ? 'left' : 'right';
        setFabPos(fab, xSide(s), yFromRatio(typeof yRatio === 'number' ? yRatio : defaultFabYRatio));
        return s;
    }

    function saveFab(side, y) {
        try {
            localStorage.setItem(fabStorageKey, JSON.stringify({
                side: side,
                yRatio: ratioFromY(y),
            }));
        } catch (e) {}
    }

    function readFabPrefs() {
        try {
            var d = JSON.parse(localStorage.getItem(fabStorageKey) || 'null');
            if (!d) return { side: 'right', yRatio: defaultFabYRatio };
            var side = d.side === 'left' ? 'left' : 'right';
            var yRatio = defaultFabYRatio;
            if (typeof d.yRatio === 'number') {
                yRatio = clamp01(d.yRatio);
            } else if (typeof d.y === 'number') {
                yRatio = ratioFromY(clampFabY(d.y));
            }
            return { side: side, yRatio: yRatio };
        } catch (e) {
            return { side: 'right', yRatio: defaultFabYRatio };
        }
    }

    function restoreFab(fab) {
        var p = readFabPrefs();
        applyFabEdge(fab, p.side, p.yRatio);
    }

    function initZiqahChat() {
        var chatBox = document.getElementById('ziqah-chat-messages');
        var chatInput = document.getElementById('ziqah-chat-input');
        var chatSend = document.getElementById('ziqah-chat-send');
        var chatAttach = document.getElementById('ziqah-chat-attach');
        var chatFile = document.getElementById('ziqah-chat-file');
        var previewEl = document.getElementById('ziqah-chat-preview');
        var previewImg = document.getElementById('ziqah-chat-preview-img');
        var previewRemove = document.getElementById('ziqah-chat-preview-remove');
        if (!chatBox || !chatInput || !chatSend || chatBox.dataset.chatReady === '1') return;
        chatBox.dataset.chatReady = '1';

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
                reader.onload = function (ev) {
                    pendingImage = ev.target.result;
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

    function mountZiqah() {
        var fab = document.getElementById('ziqah-fab');
        var overlay = document.getElementById('ziqah-overlay');
        var sheet = document.getElementById('ziqah-sheet');
        if (!fab) return;

        var openSheet = function () {};
        var sheetOpen = false;
        var draggingFab = false;
        var pointerId = null;
        var offsetX = 0;
        var offsetY = 0;
        var startX = 0;
        var startY = 0;
        var dragPx = 0;
        var resizeTimer = null;

        if (fab.dataset.ziqahMounted !== '1') {
            fab.dataset.ziqahMounted = '1';

            function onPointerDown(e) {
                if (sheetOpen) return;
                if (e.pointerType === 'mouse' && e.button !== 0) return;
                draggingFab = true;
                dragPx = 0;
                pointerId = e.pointerId;
                fab.classList.add('is-dragging');
                var r = fab.getBoundingClientRect();
                var p = { x: e.clientX, y: e.clientY };
                startX = p.x;
                startY = p.y;
                offsetX = p.x - r.left;
                offsetY = p.y - r.top;
                try { fab.setPointerCapture(e.pointerId); } catch (err) {}
                e.preventDefault();
            }

            function onPointerMove(e) {
                if (!draggingFab || e.pointerId !== pointerId) return;
                dragPx = Math.max(dragPx, Math.hypot(e.clientX - startX, e.clientY - startY));
                var maxX = window.innerWidth - size - edgePad;
                var x = Math.min(maxX, Math.max(edgePad, e.clientX - offsetX));
                setFabPos(fab, x, e.clientY - offsetY);
                e.preventDefault();
            }

            function onPointerEnd(e) {
                if (!draggingFab || e.pointerId !== pointerId) return;
                draggingFab = false;
                pointerId = null;
                fab.classList.remove('is-dragging');
                var r = fab.getBoundingClientRect();
                var s = snapFab(r.left, r.top);
                setFabPos(fab, s.x, s.y);
                saveFab(s.side, s.y);
                try { fab.releasePointerCapture(e.pointerId); } catch (err) {}
                if (dragPx < tapThreshold) openSheet();
                e.preventDefault();
            }

            fab.addEventListener('pointerdown', onPointerDown);
            fab.addEventListener('pointermove', onPointerMove);
            fab.addEventListener('pointerup', onPointerEnd);
            fab.addEventListener('pointercancel', onPointerEnd);

            restoreFab(fab);

            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    if (draggingFab) return;
                    restoreFab(fab);
                }, 120);
            });
            window.addEventListener('orientationchange', function () {
                setTimeout(function () {
                    if (draggingFab) return;
                    restoreFab(fab);
                }, 280);
            });
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden && !draggingFab) restoreFab(fab);
            });
        }

        if (!overlay || !sheet || sheet.dataset.ziqahSheetMounted === '1') return;
        sheet.dataset.ziqahSheetMounted = '1';

        var backdrop = document.getElementById('ziqah-overlay-bg');
        var closeBtn = document.getElementById('ziqah-sheet-close');
        var sheetTop = document.getElementById('ziqah-sheet-top');
        var chatInput = document.getElementById('ziqah-chat-input');
        var resizingSheet = false;
        var sheetDragPointerId = null;
        var resizeStartY = 0;
        var resizeStartH = 0;
        var resizeDragPx = 0;

        function sheetMaxH() {
            return Math.floor(window.innerHeight * sheetMaxRatio);
        }

        function sheetCloseBelowH() {
            return Math.floor(window.innerHeight * sheetCloseBelowRatio);
        }

        function applySheetHeight(h) {
            var maxH = sheetMaxH();
            var minDragH = Math.floor(window.innerHeight * 0.12);
            h = Math.min(maxH, Math.max(minDragH, h));
            sheet.style.setProperty('--ziqah-sheet-h', h + 'px');
            sheet.style.height = h + 'px';
            return h;
        }

        openSheet = function () {
            if (sheetOpen) return;
            sheetOpen = true;
            applySheetHeight(sheetMaxH());
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            sheet.classList.add('is-open');
            sheet.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            initZiqahChat();
            requestAnimationFrame(function () {
                if (chatInput) chatInput.focus();
            });
        };

        var closeSheet = function () {
            if (!sheetOpen) return;
            sheetOpen = false;
            sheet.classList.remove('is-open', 'is-resizing');
            sheet.setAttribute('aria-hidden', 'true');
            if (sheetTop) sheetTop.classList.remove('is-dragging');
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        function onSheetTopDown(e) {
            if (!sheetOpen || (e.pointerType === 'mouse' && e.button !== 0)) return;
            if (e.target.closest('#ziqah-sheet-close')) return;
            resizingSheet = true;
            resizeDragPx = 0;
            sheetDragPointerId = e.pointerId;
            resizeStartY = e.clientY;
            resizeStartH = sheet.offsetHeight;
            sheet.classList.add('is-resizing');
            if (sheetTop) sheetTop.classList.add('is-dragging');
            try { (sheetTop || sheet).setPointerCapture(e.pointerId); } catch (err) {}
            e.preventDefault();
        }

        function onSheetTopMove(e) {
            if (!resizingSheet || e.pointerId !== sheetDragPointerId) return;
            resizeDragPx = Math.max(resizeDragPx, Math.abs(e.clientY - resizeStartY));
            applySheetHeight(resizeStartH + (resizeStartY - e.clientY));
            e.preventDefault();
        }

        function onSheetTopEnd(e) {
            if (!resizingSheet || e.pointerId !== sheetDragPointerId) return;
            resizingSheet = false;
            sheetDragPointerId = null;
            sheet.classList.remove('is-resizing');
            if (sheetTop) sheetTop.classList.remove('is-dragging');
            var h = sheet.offsetHeight;
            var closeBelow = sheetCloseBelowH();
            if (h <= closeBelow) {
                closeSheet();
            } else {
                applySheetHeight(sheetMaxH());
            }
            try { (sheetTop || sheet).releasePointerCapture(e.pointerId); } catch (err) {}
            e.preventDefault();
        }

        if (backdrop) backdrop.addEventListener('click', closeSheet);
        if (closeBtn) closeBtn.addEventListener('click', closeSheet);
        if (sheetTop) {
            sheetTop.addEventListener('pointerdown', onSheetTopDown);
            sheetTop.addEventListener('pointermove', onSheetTopMove);
            sheetTop.addEventListener('pointerup', onSheetTopEnd);
            sheetTop.addEventListener('pointercancel', onSheetTopEnd);
        }

        window.addEventListener('resize', function () {
            if (!sheetOpen || resizingSheet) return;
            applySheetHeight(sheetMaxH());
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sheetOpen) closeSheet();
        });
    }

    function boot() {
        mountZiqah();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
    document.addEventListener('livewire:navigated', boot);
})();
