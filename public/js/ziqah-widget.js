(function () {
    'use strict';

    var cfg = window.BrudmsZiqahConfig || {};
    var userId = cfg.userId || 0;
    var chatUrl = cfg.chatUrl || '';
    var reportUrl = cfg.reportUrl || '';
    var csrfToken = cfg.csrf || '';

    var fab = document.getElementById('ziqah-fab');
    var overlay = document.getElementById('ziqah-overlay');
    var overlayBg = document.getElementById('ziqah-overlay-bg');
    var sheet = document.getElementById('ziqah-sheet');
    var sheetTop = document.getElementById('ziqah-sheet-top');
    var sheetClose = document.getElementById('ziqah-sheet-close');
    var chatBox = document.getElementById('ziqah-chat-messages');
    var chatInput = document.getElementById('ziqah-chat-input');
    var chatSend = document.getElementById('ziqah-chat-send');
    var chatAttach = document.getElementById('ziqah-chat-attach');
    var chatFile = document.getElementById('ziqah-chat-file');
    var previewEl = document.getElementById('ziqah-chat-preview');
    var previewImg = document.getElementById('ziqah-chat-preview-img');
    var previewRemove = document.getElementById('ziqah-chat-preview-remove');

    if (!fab || !sheet || !chatBox) {
        return;
    }

    var chatStateKey = 'brudms_ai_chat_' + userId;
    var mockReplies = [
        "I'm still learning! This is a placeholder response.",
        'Ziqah (AI) is under development. Add OPENAI_API_KEY to your .env to enable real AI.',
    ];
    var pendingImage = null;
    var cachedUserLat = null;
    var cachedUserLng = null;

    var sheetOpen = false;
    var defaultSheetVh = 96;
    var minSheetVh = 42;
    var maxSheetVh = 96;

    function setSheetHeight(vh) {
        var h = Math.max(minSheetVh, Math.min(maxSheetVh, vh));
        sheet.style.setProperty('--ziqah-sheet-h', h + 'vh');
    }

    function openSheet() {
        sheetOpen = true;
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        sheet.classList.add('is-open');
        sheet.setAttribute('aria-hidden', 'false');
        setSheetHeight(defaultSheetVh);
        if (chatInput) {
            setTimeout(function () {
                chatInput.focus();
            }, 380);
        }
    }

    function closeSheet() {
        sheetOpen = false;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        sheet.classList.remove('is-open');
        sheet.setAttribute('aria-hidden', 'true');
        saveChatState();
    }

    function toggleSheet() {
        if (sheetOpen) {
            closeSheet();
        } else {
            openSheet();
        }
    }

    if (sheetClose) {
        sheetClose.addEventListener('click', closeSheet);
    }
    if (overlayBg) {
        overlayBg.addEventListener('click', closeSheet);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sheetOpen) {
            closeSheet();
        }
    });

    /* FAB: drag + tap-to-open */
    (function initFab() {
        var dragging = false;
        var moved = false;
        var startX = 0;
        var startY = 0;
        var startLeft = 0;
        var startTop = 0;
        var dragThreshold = 8;

        function clampFab(left, top) {
            var rect = fab.getBoundingClientRect();
            var w = rect.width;
            var h = rect.height;
            var maxL = window.innerWidth - w - 8;
            var maxT = window.innerHeight - h - 8;
            return {
                left: Math.max(8, Math.min(maxL, left)),
                top: Math.max(8, Math.min(maxT, top)),
            };
        }

        function placeFab(left, top) {
            fab.style.right = 'auto';
            fab.style.bottom = 'auto';
            fab.style.left = left + 'px';
            fab.style.top = top + 'px';
        }

        function onPointerDown(e) {
            if (e.button !== undefined && e.button !== 0) {
                return;
            }
            dragging = true;
            moved = false;
            fab.classList.add('is-dragging');
            var rect = fab.getBoundingClientRect();
            startLeft = rect.left;
            startTop = rect.top;
            startX = e.clientX;
            startY = e.clientY;
            if (fab.setPointerCapture && e.pointerId !== undefined) {
                try {
                    fab.setPointerCapture(e.pointerId);
                } catch (err) {}
            }
            e.preventDefault();
        }

        function onPointerMove(e) {
            if (!dragging) {
                return;
            }
            var dx = e.clientX - startX;
            var dy = e.clientY - startY;
            if (!moved && (Math.abs(dx) > dragThreshold || Math.abs(dy) > dragThreshold)) {
                moved = true;
            }
            if (moved) {
                var pos = clampFab(startLeft + dx, startTop + dy);
                placeFab(pos.left, pos.top);
            }
        }

        function onPointerUp(e) {
            if (!dragging) {
                return;
            }
            dragging = false;
            fab.classList.remove('is-dragging');
            if (fab.releasePointerCapture && e.pointerId !== undefined) {
                try {
                    fab.releasePointerCapture(e.pointerId);
                } catch (err) {}
            }
            if (!moved) {
                toggleSheet();
            }
        }

        fab.addEventListener('pointerdown', onPointerDown);
        fab.addEventListener('pointermove', onPointerMove);
        fab.addEventListener('pointerup', onPointerUp);
        fab.addEventListener('pointercancel', onPointerUp);
    })();

    /* Sheet resize via top grabber */
    (function initSheetResize() {
        if (!sheetTop) {
            return;
        }
        var resizing = false;
        var startY = 0;
        var startH = 0;

        function onResizeDown(e) {
            if (e.button !== undefined && e.button !== 0) {
                return;
            }
            resizing = true;
            sheet.classList.add('is-resizing');
            sheetTop.classList.add('is-dragging');
            startY = e.clientY;
            var current = parseFloat(
                (sheet.style.getPropertyValue('--ziqah-sheet-h') || defaultSheetVh + 'vh').replace('vh', '')
            );
            startH = isNaN(current) ? defaultSheetVh : current;
            e.preventDefault();
        }

        function onResizeMove(e) {
            if (!resizing) {
                return;
            }
            var dy = startY - e.clientY;
            var deltaVh = (dy / window.innerHeight) * 100;
            setSheetHeight(startH + deltaVh);
        }

        function onResizeUp() {
            if (!resizing) {
                return;
            }
            resizing = false;
            sheet.classList.remove('is-resizing');
            sheetTop.classList.remove('is-dragging');
        }

        sheetTop.addEventListener('pointerdown', onResizeDown);
        window.addEventListener('pointermove', onResizeMove);
        window.addEventListener('pointerup', onResizeUp);
        window.addEventListener('pointercancel', onResizeUp);
    })();

    function saveChatState() {
        try {
            if (!chatBox) {
                return;
            }
            var clone = chatBox.cloneNode(true);
            clone.querySelectorAll('.msg-typing').forEach(function (el) {
                el.remove();
            });
            localStorage.setItem(
                chatStateKey,
                JSON.stringify({
                    html: clone.innerHTML || '',
                    pendingImage: pendingImage || null,
                })
            );
        } catch (e) {}
    }

    (function prefetchLocation() {
        if (!navigator.geolocation) {
            return;
        }
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                cachedUserLat = pos.coords.latitude;
                cachedUserLng = pos.coords.longitude;
            },
            function () {},
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
                function (pos) {
                    cachedUserLat = pos.coords.latitude;
                    cachedUserLng = pos.coords.longitude;
                    payload.latitude = cachedUserLat;
                    payload.longitude = cachedUserLng;
                    callback(payload);
                },
                function () {
                    callback(payload);
                },
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
        if (!text) {
            text = mockReplies[0];
        }

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

        chatBox.scrollTop = chatBox.scrollHeight;
        saveChatState();
    }

    function sendMessage() {
        if (!chatInput || !chatUrl) {
            return;
        }
        var text = chatInput.value.trim();
        var hasImage = !!pendingImage;
        if (!text && !hasImage) {
            return;
        }

        if (hasImage && text) {
            var group = document.createElement('div');
            var imgWrap = document.createElement('div');
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
            var imgOnly = document.createElement('img');
            imgOnly.src = pendingImage;
            imgOnly.className = 'msg-img';
            var userImgWrap = document.createElement('div');
            userImgWrap.className = 'msg-user';
            userImgWrap.appendChild(imgOnly);
            chatBox.appendChild(userImgWrap);
        } else {
            var userMsgOnly = document.createElement('div');
            userMsgOnly.className = 'msg-user';
            userMsgOnly.textContent = text;
            chatBox.appendChild(userMsgOnly);
        }

        chatInput.value = '';
        var imageToSend = pendingImage;
        pendingImage = null;
        if (previewEl) {
            previewEl.style.display = 'none';
        }
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
                    if (typing.parentNode) {
                        chatBox.removeChild(typing);
                    }
                    appendBotReply(
                        res.ok && res.data.reply ? res.data.reply : res.data.error || mockReplies[0],
                        res.ok ? res.data.report_image_url || null : null,
                        res.ok ? res.data.quick_actions || [] : [],
                        res.ok ? res.data.show_report_button === true : false
                    );
                })
                .catch(function () {
                    if (typing.parentNode) {
                        chatBox.removeChild(typing);
                    }
                    var errMsg = document.createElement('div');
                    errMsg.className = 'msg-bot';
                    errMsg.textContent = 'Something went wrong. Please try again.';
                    chatBox.appendChild(errMsg);
                    chatBox.scrollTop = chatBox.scrollHeight;
                    saveChatState();
                });
        });
    }

    if (chatSend) {
        chatSend.addEventListener('click', sendMessage);
    }
    if (chatInput) {
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    if (chatAttach && chatFile) {
        chatAttach.addEventListener('click', function () {
            chatFile.click();
        });
        chatFile.addEventListener('change', function () {
            var file = chatFile.files[0];
            if (!file) {
                return;
            }
            var reader = new FileReader();
            reader.onload = function (ev) {
                pendingImage = ev.target.result;
                if (previewImg) {
                    previewImg.src = pendingImage;
                }
                if (previewEl) {
                    previewEl.style.display = 'flex';
                }
                saveChatState();
            };
            reader.readAsDataURL(file);
            chatFile.value = '';
        });
    }

    if (previewRemove) {
        previewRemove.addEventListener('click', function () {
            pendingImage = null;
            if (previewImg) {
                previewImg.src = '';
            }
            if (previewEl) {
                previewEl.style.display = 'none';
            }
            saveChatState();
        });
    }

    (function restoreChatState() {
        try {
            var raw = localStorage.getItem(chatStateKey);
            if (!raw) {
                return;
            }
            var data = JSON.parse(raw);
            if (data && typeof data.html === 'string' && data.html.trim().length > 0) {
                chatBox.innerHTML = data.html;
            }
            if (data && data.pendingImage) {
                pendingImage = data.pendingImage;
                if (previewImg) {
                    previewImg.src = pendingImage;
                }
                if (previewEl) {
                    previewEl.style.display = 'flex';
                }
            }
            chatBox.scrollTop = chatBox.scrollHeight;
        } catch (e) {}
    })();

    window.addEventListener('beforeunload', saveChatState);
})();
