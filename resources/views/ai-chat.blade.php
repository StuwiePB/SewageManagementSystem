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
    </style>
</head>
<body>
    <div class="page-wrapper">
        <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #0056a6, #0077cc);">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                    <i class="fas fa-water me-2"></i>
                    Aqua Guard
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
                            Try asking: "How do I report an issue?" or "What's the emergency number?"
                        </small>
                        <button id="aiChatSend" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" style="background-color: #0056a6; border-color: #0056a6;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
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
    </script>
</body>
</html>

