(function () {
    'use strict';

    var cfg = window.BrudmsZiqahConfig || {};
    var userId = cfg.userId || 0;
    var chatUrl = cfg.chatUrl || '';
    var reportUrl = cfg.reportUrl || '';
    var csrfToken = cfg.csrf || '';
    var currentPage = cfg.currentPage || null;

    var root = document.getElementById('ziqah-cr');
    var fab = document.getElementById('ziqah-fab');
    var panel = document.getElementById('ziqah-panel');
    var panelClose = document.getElementById('ziqah-sheet-close');
    var chatBox = document.getElementById('ziqah-chat-messages');
    var chatInput = document.getElementById('ziqah-chat-input');
    var chatSend = document.getElementById('ziqah-chat-send');
    var chatAttach = document.getElementById('ziqah-chat-attach');
    var chatFile = document.getElementById('ziqah-chat-file');
    var previewEl = document.getElementById('ziqah-chat-preview');
    var previewImg = document.getElementById('ziqah-chat-preview-img');
    var previewRemove = document.getElementById('ziqah-chat-preview-remove');

    if (!root || !fab || !panel || !chatBox) {
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
    var inFlight = false;
    var panelOpen = false;
    var history = [];

    var inputMaxHeight = 96;

    function autoGrowInput() {
        if (!chatInput) {
            return;
        }
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, inputMaxHeight) + 'px';
    }

    function setBusy(busy) {
        inFlight = busy;
        if (chatSend) {
            chatSend.disabled = busy;
        }
        if (chatAttach) {
            chatAttach.disabled = busy;
        }
    }

    function openPanel() {
        panelOpen = true;
        root.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(function () {
            panel.classList.add('is-visible');
        });
        if (chatInput) {
            setTimeout(function () {
                chatInput.focus();
            }, 180);
        }
    }

    function closePanel() {
        panelOpen = false;
        panel.classList.remove('is-visible');
        panel.setAttribute('aria-hidden', 'true');
        saveChatState();
        setTimeout(function () {
            if (!panelOpen) {
                root.classList.remove('is-open');
            }
        }, 200);
    }

    function togglePanel() {
        if (panelOpen) {
            closePanel();
        } else {
            openPanel();
        }
    }

    fab.addEventListener('click', togglePanel);

    if (panelClose) {
        panelClose.addEventListener('click', closePanel);
    }

    document.addEventListener('click', function (e) {
        if (!panelOpen) {
            return;
        }
        if (root.contains(e.target)) {
            return;
        }
        closePanel();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panelOpen) {
            closePanel();
        }
    });

    function saveChatState() {
        try {
            if (!chatBox) {
                return;
            }
            var clone = chatBox.cloneNode(true);
            clone.querySelectorAll('.cr-typing').forEach(function (el) {
                el.remove();
            });
            localStorage.setItem(
                chatStateKey,
                JSON.stringify({
                    html: clone.innerHTML || '',
                    pendingImage: pendingImage || null,
                    history: history,
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

    function normalizeListMarkers(text) {
        // Some replies run list items together on one line; force each marker onto its own line.
        text = text.replace(/([^\n])[ \t]+(?=\d{1,2}[).]\s)/g, '$1\n');
        text = text.replace(/([^\n])[ \t]+(?=[•]\s)/g, '$1\n');
        return text;
    }

    function renderMessageContent(container, rawText) {
        var text = normalizeListMarkers(String(rawText || ''));
        var lines = text.split(/\r?\n/);
        var listEl = null;
        var listType = null;

        function closeList() {
            listEl = null;
            listType = null;
        }

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i].trim();
            if (line === '') {
                closeList();
                continue;
            }

            var numbered = line.match(/^(\d{1,2})[).]\s+(.*)$/);
            var bulleted = !numbered ? line.match(/^[•\-*]\s+(.*)$/) : null;

            if (numbered) {
                if (listType !== 'ol') {
                    listEl = document.createElement('ol');
                    listEl.className = 'cr-msg-list';
                    container.appendChild(listEl);
                    listType = 'ol';
                }
                var liOl = document.createElement('li');
                liOl.textContent = numbered[2];
                listEl.appendChild(liOl);
            } else if (bulleted) {
                if (listType !== 'ul') {
                    listEl = document.createElement('ul');
                    listEl.className = 'cr-msg-list';
                    container.appendChild(listEl);
                    listType = 'ul';
                }
                var liUl = document.createElement('li');
                liUl.textContent = bulleted[1];
                listEl.appendChild(liUl);
            } else {
                closeList();
                var p = document.createElement('p');
                p.className = 'cr-msg-p';
                p.textContent = line;
                container.appendChild(p);
            }
        }
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
        renderMessageContent(botMsg, text);
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

        history.push({ role: 'assistant', content: text });
        chatBox.scrollTop = chatBox.scrollHeight;
        saveChatState();
    }

    function appendErrorBubble(message) {
        var errMsg = document.createElement('div');
        errMsg.className = 'msg-error';
        errMsg.textContent = message || 'Something went wrong. Please try again.';
        chatBox.appendChild(errMsg);
        chatBox.scrollTop = chatBox.scrollHeight;
        saveChatState();
    }

    function showTyping(label) {
        var typing = document.createElement('div');
        typing.className = 'cr-typing';
        typing.setAttribute('aria-label', label || 'typing');
        typing.innerHTML = '<span></span><span></span><span></span>';
        chatBox.appendChild(typing);
        chatBox.scrollTop = chatBox.scrollHeight;
        return typing;
    }

    function sendMessage() {
        if (inFlight || !chatInput || !chatUrl) {
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

        history.push({ role: 'user', content: text || '(image)' });

        chatInput.value = '';
        autoGrowInput();
        var imageToSend = pendingImage;
        pendingImage = null;
        if (previewEl) {
            previewEl.style.display = 'none';
        }
        chatBox.scrollTop = chatBox.scrollHeight;
        saveChatState();

        var typing = showTyping(hasImage ? 'analyzing image...' : 'typing...');
        setBusy(true);

        var payload = { message: text || null, image: imageToSend || null, page: currentPage };
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
                    setBusy(false);
                    if (res.ok && (res.data.reply || res.data.reply === '')) {
                        appendBotReply(
                            res.data.reply,
                            res.data.report_image_url || null,
                            res.data.quick_actions || [],
                            res.data.show_report_button === true
                        );
                    } else {
                        appendErrorBubble(res.data && res.data.error ? res.data.error : mockReplies[0]);
                    }
                })
                .catch(function () {
                    if (typing.parentNode) {
                        chatBox.removeChild(typing);
                    }
                    setBusy(false);
                    appendErrorBubble('Something went wrong. Please try again.');
                });
        });
    }

    if (chatSend) {
        chatSend.addEventListener('click', sendMessage);
    }
    if (chatInput) {
        chatInput.addEventListener('input', autoGrowInput);
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
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
            if (data && Array.isArray(data.history)) {
                history = data.history;
            }
            chatBox.scrollTop = chatBox.scrollHeight;
        } catch (e) {}
    })();

    window.addEventListener('beforeunload', saveChatState);
})();
