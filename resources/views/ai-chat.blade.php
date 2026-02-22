<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Portal – AI Sewage Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: #f0f8ff;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-container {
            max-width: 1120px;
            margin: 2.5rem auto 3rem;
            padding: 0 1.25rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0056a6;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.4rem;
            border-bottom: 3px solid #00a896;
            margin-bottom: 1.5rem;
        }

        .chat-card {
            background-color: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: linear-gradient(135deg, #0056a6, #0077cc);
            color: #ffffff;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .chat-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background-color: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .chat-header-sub {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .chat-body {
            padding: 1.25rem 1.5rem;
            height: 24rem;
            overflow-y: auto;
            background-color: #f9fafb;
        }

        .chat-message {
            margin-bottom: 0.9rem;
            font-size: 0.95rem;
            display: flex;
        }

        .chat-message.user {
            justify-content: flex-end;
        }

        .chat-message.ai {
            justify-content: flex-start;
        }

        .chat-bubble {
            max-width: 70%;
            padding: 0.65rem 0.9rem;
            border-radius: 1.25rem;
        }

        .chat-bubble.user {
            background-color: #0056a6;
            color: #ffffff;
            border-bottom-right-radius: 0.4rem;
        }

        .chat-bubble.ai {
            background-color: #ffffff;
            color: #111827;
            border-bottom-left-radius: 0.4rem;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }

        .chat-footer {
            padding: 0.9rem 1.25rem 1.1rem;
            border-top: 1px solid #e5e7eb;
            background-color: #ffffff;
        }

        .chat-input {
            border-radius: 0.75rem;
            border: 1px solid #d1d5db;
            padding: 0.65rem 0.9rem;
            font-size: 0.95rem;
        }

        .chat-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .chat-status {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .typing-dots {
            display: flex;
            gap: 0.25rem;
        }

        .typing-dots span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background-color: #9ca3af;
            animation: bounce 1s infinite;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.15s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: 0.3s;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
                opacity: 0.6;
            }
            50% {
                transform: translateY(-3px);
                opacity: 1;
            }
        }

        /* Admin Troubleshooting Toolbar */
        #admin-toolbar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            font-family: Arial, sans-serif;
        }
        #admin-login-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s;
        }
        #admin-login-btn:hover {
            background: #0056b3;
            transform: scale(1.05);
        }
        #admin-panel {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 400px;
            max-width: 90%;
            z-index: 10000;
            border: 2px solid #007bff;
        }
        #admin-panel h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        #admin-panel input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        #admin-panel input:focus {
            border-color: #007bff;
            outline: none;
        }
        #admin-panel button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin: 5px 0;
            transition: background 0.3s;
        }
        #admin-panel button:hover {
            background: #0056b3;
        }
        #admin-panel button.danger {
            background: #dc3545;
        }
        #admin-panel button.danger:hover {
            background: #c82333;
        }
        #admin-panel button.success {
            background: #28a745;
        }
        #admin-panel button.success:hover {
            background: #218838;
        }
        #admin-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        .close-btn:hover {
            color: #333;
        }
        #output-console {
            background: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            height: 200px;
            overflow-y: auto;
            margin: 10px 0;
            font-size: 14px;
        }
        .tool-group {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .tool-group h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        .admin-badge {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 10001;
            display: none;
        }
        .quick-fix-btn {
            background: #6c757d;
            margin: 2px !important;
            width: auto !important;
            display: inline-block !important;
            padding: 8px 12px !important;
            font-size: 14px !important;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #0056a6, #0077cc);">
            <div class="container">
                <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('home') }}">
                    <img src="/images/bruflow-logo.png" alt="BruFlow" class="me-2" style="height: 36px; width: auto;">
                    BruFlow
                </a>
                <span class="navbar-text text-white-50 d-none d-md-inline">
                    Public Portal · AI Assistant
                </span>
            </div>
        </nav>

        <div class="main-container">
            <div class="mb-4">
                <h2 class="section-title">
                    <i class="fas fa-comments"></i>
                    Ask Our AI Assistant
                </h2>
                <p class="text-muted mb-0">
                    Get instant answers about sewage issues, how to report a problem, check status, or what to do in an emergency.
                </p>
            </div>

            <div class="chat-card">
                <div class="chat-header">
                    <div class="chat-header-title">
                        <div class="chat-header-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">Sewage System AI Assistant</div>
                            <div class="chat-header-sub">Ask about reporting, status checks, and emergencies</div>
                        </div>
                    </div>
                </div>

                <div id="aiChatMessages" class="chat-body">
                    <div class="chat-message ai">
                        <div class="chat-bubble ai">
                            Hello! I'm here to help you with sewage-related questions. How can I assist you today?
                        </div>
                    </div>
                </div>

                <div class="chat-footer">
                    <div class="mb-2">
                        <input
                            id="aiChatInput"
                            type="text"
                            class="form-control chat-input"
                            placeholder="Ask about reporting, status checks, emergencies..."
                        />
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small id="aiChatStatus" class="chat-status">
                            Try: "How do I report an issue?" or type <strong>admin</strong> for Admin Tools
                        </small>
                        <button id="aiChatSend" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" style="background-color: #0056a6; border-color: #0056a6;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Badge -->
    <div id="admin-badge" class="admin-badge">🛠️ ADMIN MODE ACTIVE</div>
    <!-- Admin Overlay -->
    <div id="admin-overlay"></div>
    <!-- Admin Panel -->
    <div id="admin-panel">
        <span class="close-btn" onclick="hideAdminPanel()">&times;</span>
        <h2>🛠️ Admin Troubleshooting</h2>
        <div id="login-form">
            <input type="password" id="admin-password" placeholder="Enter admin password" onkeypress="adminCheckEnter(event)">
            <button onclick="adminVerifyPassword()">Login</button>
            <p style="color: #666; font-size: 12px; text-align: center;">Default password: admin123</p>
        </div>
        <div id="admin-tools" style="display: none;">
            <div class="tool-group">
                <h3>📊 Diagnostics</h3>
                <button onclick="checkConsoleErrors()">Check Console Errors</button>
                <button onclick="testApiConnections()">Test API Connections</button>
                <button onclick="checkLocalStorage()">Check Local Storage</button>
                <button onclick="checkNetworkStatus()">Network Status</button>
            </div>
            <div class="tool-group">
                <h3>🔄 Cache & Storage</h3>
                <button onclick="clearBrowserCache()">Clear Browser Cache</button>
                <button onclick="clearLocalStorage()">Clear Local Storage</button>
                <button onclick="clearSessionStorage()">Clear Session Storage</button>
                <button onclick="reloadPage()">Reload Page</button>
            </div>
            <div class="tool-group">
                <h3>🎨 Visual Debug</h3>
                <button onclick="toggleOutlines()">Toggle Element Outlines</button>
                <button onclick="highlightBrokenImages()">Find Broken Images</button>
                <button onclick="checkResponsive()">Check Responsive Layout</button>
            </div>
            <div class="tool-group">
                <h3>⚡ Quick Fixes</h3>
                <button class="quick-fix-btn" onclick="fixJavaScriptErrors()">Fix JS Errors</button>
                <button class="quick-fix-btn" onclick="resetCss()">Reset CSS</button>
                <button class="quick-fix-btn" onclick="forceRepaint()">Force Repaint</button>
                <button class="quick-fix-btn" onclick="disableAnimations()">Disable Animations</button>
            </div>
            <div class="tool-group">
                <h3>📝 Console Output</h3>
                <div id="output-console">Ready to diagnose...</div>
                <button onclick="clearOutput()">Clear Output</button>
            </div>
            <div class="tool-group">
                <h3>🔧 Advanced</h3>
                <button onclick="inspectElement()">Inspect Element Mode</button>
                <button onclick="showPageInfo()">Page Information</button>
                <button class="danger" onclick="adminLogout()">Logout</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const input = document.getElementById('aiChatInput');
            const sendButton = document.getElementById('aiChatSend');
            const messages = document.getElementById('aiChatMessages');
            const status = document.getElementById('aiChatStatus');

            if (!input || !sendButton || !messages || !status) {
                return;
            }

            let isTyping = false;

            function appendMessage(text, type) {
                const wrapper = document.createElement('div');
                wrapper.className = 'chat-message ' + type;

                const bubble = document.createElement('div');
                bubble.className = 'chat-bubble ' + type;
                bubble.textContent = text;

                wrapper.appendChild(bubble);
                messages.appendChild(wrapper);
                messages.scrollTop = messages.scrollHeight;
            }

            function showTypingIndicator() {
                if (isTyping) return;
                isTyping = true;

                const wrapper = document.createElement('div');
                wrapper.className = 'chat-message ai';
                wrapper.id = 'ai-typing-indicator';

                const bubble = document.createElement('div');
                bubble.className = 'chat-bubble ai';

                const dots = document.createElement('div');
                dots.className = 'typing-dots';

                for (let i = 0; i < 3; i++) {
                    const dot = document.createElement('span');
                    dots.appendChild(dot);
                }

                bubble.appendChild(dots);
                wrapper.appendChild(bubble);
                messages.appendChild(wrapper);
                messages.scrollTop = messages.scrollHeight;
            }

            function hideTypingIndicator() {
                const typing = document.getElementById('ai-typing-indicator');
                if (typing) {
                    typing.remove();
                }
                isTyping = false;
            }

            function sendMessage() {
                const text = input.value.trim();
                if (!text) {
                    return;
                }

                // Check for admin command
                const adminCommand = /^(admin|admin\s*tools?|open\s*admin|\/admin)\s*$/i.test(text);
                if (adminCommand) {
                    input.value = '';
                    if (typeof window.showLogin === 'function') window.showLogin();
                    return;
                }

                appendMessage(text, 'user');
                input.value = '';
                status.textContent = 'Contacting AI...';
                sendButton.disabled = true;

                showTypingIndicator();

                // Call OpenAI API via Laravel route
                fetch('{{ route("ai.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                })
                .then(response => response.json())
                .then(data => {
                    hideTypingIndicator();
                    if (data.reply) {
                        appendMessage(data.reply, 'ai');
                        status.textContent = 'Type another question or ask about reporting, status, or emergencies.';
                    } else if (data.error) {
                        appendMessage('Sorry, ' + data.error + ' Please check your OpenAI API key configuration.', 'ai');
                        status.textContent = 'AI service unavailable.';
                    } else {
                        appendMessage('Sorry, I could not generate a response. Please try again.', 'ai');
                        status.textContent = 'Error occurred.';
                    }
                })
                .catch(error => {
                    hideTypingIndicator();
                    appendMessage('Sorry, I could not reach the AI service. Please check your internet connection and try again.', 'ai');
                    status.textContent = 'Connection error.';
                    console.error('Chatbot error:', error);
                })
                .finally(() => {
                    sendButton.disabled = false;
                });
            }

            sendButton.addEventListener('click', sendMessage);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            });
        })();

        // Admin Troubleshooting Tools
        (function() {
            let adminActive = false;
            let outlineMode = false;

            function adminLogToConsole(message, type) {
                const consoleEl = document.getElementById('output-console');
                if (!consoleEl) return;
                const timestamp = new Date().toLocaleTimeString();
                let color = '#00ff00';
                if (type === 'error') color = '#ff5555';
                if (type === 'success') color = '#55ff55';
                if (type === 'warning') color = '#ffff55';
                consoleEl.innerHTML += '<div style="color:' + color + '">[' + timestamp + '] ' + message + '</div>';
                consoleEl.scrollTop = consoleEl.scrollHeight;
            }

            window.adminVerifyPassword = function() {
                const password = document.getElementById('admin-password').value;
                if (password === 'admin123') {
                    adminActive = true;
                    document.getElementById('login-form').style.display = 'none';
                    document.getElementById('admin-tools').style.display = 'block';
                    document.getElementById('admin-badge').style.display = 'block';
                    document.getElementById('admin-panel').style.borderColor = '#28a745';
                    adminLogToConsole('Admin access granted. Troubleshooting mode active.', 'success');
                } else {
                    alert('Incorrect password!');
                    adminLogToConsole('Failed login attempt', 'error');
                }
            };

            window.showLogin = function() {
                document.getElementById('admin-overlay').style.display = 'block';
                document.getElementById('admin-panel').style.display = 'block';
                document.getElementById('login-form').style.display = 'block';
                document.getElementById('admin-tools').style.display = 'none';
                document.getElementById('admin-password').value = '';
                document.getElementById('admin-password').focus();
            };

            window.hideAdminPanel = function() {
                document.getElementById('admin-overlay').style.display = 'none';
                document.getElementById('admin-panel').style.display = 'none';
            };

            window.adminCheckEnter = function(e) {
                if (e.key === 'Enter') window.adminVerifyPassword();
            };

            window.clearOutput = function() {
                const c = document.getElementById('output-console');
                if (c) c.innerHTML = 'Ready to diagnose...';
            };

            window.checkConsoleErrors = function() {
                adminLogToConsole('Error catching enabled. Check browser console (F12).', 'success');
            };

            window.testApiConnections = function() {
                adminLogToConsole('Testing API connections...');
                fetch(window.location.origin + '/ai/chat', { method: 'OPTIONS' })
                    .then(function() { adminLogToConsole('AI chat route reachable', 'success'); })
                    .catch(function() { adminLogToConsole('AI chat route not reachable', 'error'); });
            };

            window.checkLocalStorage = function() {
                adminLogToConsole('Local storage: ' + localStorage.length + ' items', 'info');
            };

            window.checkNetworkStatus = function() {
                adminLogToConsole('Online: ' + (navigator.onLine ? 'Yes' : 'No'), 'info');
            };

            window.clearBrowserCache = function() {
                if (confirm('Clear cache and reload?')) location.reload(true);
            };

            window.clearLocalStorage = function() {
                if (confirm('Clear local storage?')) { localStorage.clear(); adminLogToConsole('Local storage cleared', 'success'); }
            };

            window.clearSessionStorage = function() {
                if (confirm('Clear session storage?')) { sessionStorage.clear(); adminLogToConsole('Session storage cleared', 'success'); }
            };

            window.reloadPage = function() {
                if (confirm('Reload page?')) location.reload();
            };

            window.toggleOutlines = function() {
                outlineMode = !outlineMode;
                var style = document.getElementById('admin-outline-style');
                if (outlineMode) {
                    style = document.createElement('style');
                    style.id = 'admin-outline-style';
                    style.textContent = '* { outline: 2px solid red !important; }';
                    document.head.appendChild(style);
                    adminLogToConsole('Element outlines enabled', 'warning');
                } else {
                    if (style) style.remove();
                    adminLogToConsole('Element outlines disabled');
                }
            };

            window.highlightBrokenImages = function() {
                var imgs = document.getElementsByTagName('img');
                var broken = 0;
                for (var i = 0; i < imgs.length; i++) {
                    if (!imgs[i].complete || imgs[i].naturalHeight === 0) {
                        imgs[i].style.border = '5px solid red';
                        broken++;
                    }
                }
                adminLogToConsole(broken > 0 ? broken + ' broken image(s)' : 'No broken images', broken > 0 ? 'error' : 'success');
            };

            window.checkResponsive = function() {
                adminLogToConsole('Viewport: ' + window.innerWidth + ' x ' + window.innerHeight);
            };

            window.fixJavaScriptErrors = function() {
                window.onerror = function() { return true; };
                adminLogToConsole('Error handling enabled', 'success');
            };

            window.resetCss = function() {
                var els = document.getElementsByTagName('*');
                for (var i = 0; i < els.length; i++) { if (els[i].style) els[i].style.cssText = ''; }
                adminLogToConsole('CSS reset complete', 'success');
            };

            window.forceRepaint = function() {
                document.body.style.display = 'none';
                document.body.offsetHeight;
                document.body.style.display = '';
                adminLogToConsole('Repaint complete', 'success');
            };

            window.disableAnimations = function() {
                var s = document.createElement('style');
                s.textContent = '* { animation-duration: 0s !important; transition-duration: 0s !important; }';
                document.head.appendChild(s);
                adminLogToConsole('Animations disabled', 'success');
            };

            window.inspectElement = function() {
                adminLogToConsole('Inspect mode: click any element for details', 'warning');
            };

            window.showPageInfo = function() {
                adminLogToConsole('URL: ' + window.location.href);
                adminLogToConsole('Title: ' + document.title);
            };

            window.adminLogout = function() {
                adminActive = false;
                document.getElementById('login-form').style.display = 'block';
                document.getElementById('admin-tools').style.display = 'none';
                document.getElementById('admin-badge').style.display = 'none';
                document.getElementById('admin-panel').style.borderColor = '#007bff';
                hideAdminPanel();
                if (outlineMode) toggleOutlines();
            };

            document.getElementById('admin-overlay').onclick = hideAdminPanel;

            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'A') { e.preventDefault(); showLogin(); }
            });
        })();
    </script>
</body>
</html>

