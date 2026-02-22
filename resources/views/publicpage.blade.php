@php
    $districts = $districts ?? config('brunei.districts', []);
    $mukims = $mukims ?? config('brunei.mukims', []);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewage Issue Reporting – Government of Brunei Darussalam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Brunei Government palette - national colours */
            --bn-gold: #CF9B00;
            --bn-gold-light: #F7D117;
            --bn-black: #000000;
            --bn-white: #FFFFFF;
            --bn-navy: #0C2340;
            --bn-navy-light: #1B3A5C;
            --bn-card-bg: #122942;
            --bn-border: #2A4A6B;
            --bn-text-muted: #94A3B8;
            --secondary-blue: #3B82F6;
            --accent-teal: #0D9488;
            --success-green: #059669;
            --warning-yellow: var(--bn-gold);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bn-navy);
            color: var(--bn-white);
            line-height: 1.6;
            position: relative;
            font-size: 1rem;
        }
        .text-muted, .text-white-50, .text-secondary { color: var(--bn-text-muted) !important; }
        .page-bg-blur {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }
        .page-bg-blur .layer1 {
            position: absolute;
            inset: 0;
            background: url('/images/background-blur.png') center/cover no-repeat;
            opacity: 0.18;
            filter: blur(40px);
        }
        .page-bg-blur .layer2 {
            position: absolute;
            inset: 0;
            background: url('/images/background-blur.png') center/120% no-repeat;
            opacity: 0.12;
            filter: blur(80px);
        }
        .page-bg-blur .layer3 {
            position: absolute;
            inset: 0;
            background: url('/images/background-blur.png') center/100% no-repeat;
            opacity: 0.15;
            filter: blur(12px);
        }
        /* Animated background paths */
        .page-bg-blur .bg-paths {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }
        .page-bg-blur .bg-paths svg {
            width: 100%;
            height: 100%;
            opacity: 0.4;
        }
        .page-bg-blur .bg-paths path {
            fill: none;
            stroke: var(--bn-gold);
            stroke-opacity: 0.06;
            stroke-linecap: round;
            stroke-dasharray: 350 700;
            stroke-dashoffset: 0;
            animation: pathDraw 22s linear infinite;
        }
        .page-bg-blur .bg-paths path:nth-child(1) { animation-delay: 0s; stroke-opacity: 0.05; animation-duration: 24s; }
        .page-bg-blur .bg-paths path:nth-child(2) { animation-delay: -3s; stroke-opacity: 0.06; animation-duration: 20s; }
        .page-bg-blur .bg-paths path:nth-child(3) { animation-delay: -6s; stroke-opacity: 0.07; animation-duration: 26s; }
        .page-bg-blur .bg-paths path:nth-child(4) { animation-delay: -9s; stroke-opacity: 0.05; animation-duration: 22s; }
        .page-bg-blur .bg-paths path:nth-child(5) { animation-delay: -12s; stroke-opacity: 0.06; animation-duration: 25s; }
        .page-bg-blur .bg-paths path:nth-child(6) { animation-delay: -15s; stroke-opacity: 0.04; animation-duration: 21s; }
        .page-bg-blur .bg-paths path:nth-child(7) { animation-delay: -18s; stroke-opacity: 0.05; animation-duration: 23s; }
        .page-bg-blur .bg-paths path:nth-child(8) { animation-delay: -20s; stroke-opacity: 0.06; animation-duration: 27s; }
        .page-bg-blur .bg-paths svg:last-child path:nth-child(1) { animation-delay: -4s; animation-duration: 19s; }
        .page-bg-blur .bg-paths svg:last-child path:nth-child(2) { animation-delay: -10s; animation-duration: 28s; }
        .page-bg-blur .bg-paths svg:last-child path:nth-child(3) { animation-delay: -16s; animation-duration: 24s; }
        @keyframes pathDraw {
            0% { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: -700; }
        }
        .page-content { position: relative; z-index: 1; }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
        }
        
        .navbar {
            background: var(--bn-navy);
            border-bottom: 2px solid var(--bn-gold);
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--bn-white) !important;
            letter-spacing: 0.02em;
        }
        
        .navbar-brand .gov-badge {
            display: block;
            font-size: 0.65rem;
            font-weight: 500;
            color: var(--bn-gold);
            letter-spacing: 0.05em;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--bn-gold) !important;
        }
        
        .hero-section {
            position: relative;
            padding: 4rem 0;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--bn-gold);
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            background: url('/images/mosque-bg.png') center/cover no-repeat;
            filter: blur(2px);
            opacity: 0.9;
        }
        .hero-section::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(to bottom, rgba(12,35,64,0.6), rgba(12,35,64,0.75));
        }
        .hero-section .container {
            position: relative;
            z-index: 2;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(207,155,0,0.15);
            border: 1px solid var(--bn-gold);
            color: var(--bn-gold);
            padding: 0.35rem 1rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }
        
        .hero-title {
            color: var(--bn-white);
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }
        
        .hero-subtitle {
            color: var(--bn-text-muted);
            font-size: 1.05rem;
            max-width: 640px;
            margin: 0 auto 2rem;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .report-card {
            background: var(--bn-card-bg);
            border: 1px solid var(--bn-border);
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .report-card .card-body { padding: 1.75rem; }
        
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0,0,0,0.2);
        }
        
        .section-title {
            color: var(--bn-white);
            border-bottom: 3px solid var(--bn-gold);
            padding-bottom: 0.5rem;
            display: inline-block;
            margin-bottom: 1.5rem;
            font-size: 1.35rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.375rem;
            border: 1px solid var(--bn-border);
            background: var(--bn-navy);
            color: var(--bn-white);
            padding: 0.625rem 1rem;
            transition: all 0.2s;
        }
        .form-control::placeholder { color: var(--bn-text-muted); }
        .form-control:focus, .form-select:focus {
            border-color: var(--bn-gold);
            box-shadow: 0 0 0 3px rgba(207,155,0,0.2);
            outline: none;
        }
        
        .btn-primary {
            background-color: var(--bn-gold);
            border-color: var(--bn-gold);
            color: var(--bn-black);
            border-radius: 0.375rem;
            padding: 0.625rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background-color: var(--bn-gold-light);
            border-color: var(--bn-gold-light);
            color: var(--bn-black);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(207,155,0,0.3);
        }
        
        .btn-outline-warning {
            border-color: var(--bn-gold);
            color: var(--bn-gold);
            border-radius: 0.375rem;
        }
        
        .btn-outline-warning:hover {
            background-color: rgba(207,155,0,0.15);
            color: var(--bn-gold-light);
            border-color: var(--bn-gold);
        }
        
        .btn-secondary {
            background-color: var(--bn-navy-light);
            border-color: var(--bn-border);
            color: var(--bn-white);
            border-radius: 0.375rem;
        }
        
        .btn-secondary:hover {
            background-color: var(--bn-border);
            border-color: var(--bn-border);
            color: var(--bn-white);
        }
        
        .map-container {
            height: 300px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            background-color: #e9ecef;
            position: relative;
        }
        
        .map-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-light);
        }
        
        .map-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary-blue);
        }
        
        .status-card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--accent-teal);
            margin-bottom: 1.5rem;
        }
        
        .status-card.resolved {
            border-left-color: var(--success-green);
        }
        
        .status-card.in-progress {
            border-left-color: var(--warning-yellow);
        }
        
        .status-card.received {
            border-left-color: var(--secondary-blue);
        }
        
        .status-badge {
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .badge-received {
            background-color: rgba(0, 119, 204, 0.1);
            color: var(--secondary-blue);
        }
        
        .badge-in-progress {
            background-color: rgba(255, 190, 11, 0.1);
            color: #b38a00;
        }
        
        .badge-resolved {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-green);
        }
        
        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(0, 119, 204, 0.1);
            color: var(--secondary-blue);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 1.5rem;
            background: var(--bn-card-bg);
            border: 1px solid var(--bn-border);
            border-radius: 10px;
            height: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-title {
            color: #fff;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .feature-card p {
            color: var(--bn-text-muted);
        }
        
        .info-box, .info-box li {
            color: var(--bn-text-muted);
        }
        .info-box {
            background: var(--bn-card-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            border: 1px solid var(--bn-border);
            border-left: 4px solid var(--bn-gold);
            margin-top: 2rem;
        }
        
        .footer {
            background: var(--bn-navy);
            border-top: 2px solid var(--bn-gold);
            color: var(--bn-white);
            padding: 2.5rem 0;
            margin-top: 4rem;
        }
        
        .footer p, .footer .footer-title + p {
            color: var(--bn-text-muted);
        }
        .footer-title {
            color: var(--bn-white);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        
        .footer-links a {
            color: var(--bn-text-muted);
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.2s;
        }
        
        .footer-links a:hover {
            color: var(--bn-gold);
        }
        
        .copyright {
            text-align: center;
            padding-top: 1.5rem;
            margin-top: 2rem;
            border-top: 1px solid var(--bn-border);
            color: var(--bn-text-muted);
            font-size: 0.9rem;
        }
        
        .progress-tracker {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 2rem;
            counter-reset: step;
        }
        
        .progress-tracker::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            z-index: 1;
        }
        
        .progress-step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        
        .progress-step::before {
            counter-increment: step;
            content: counter(step);
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: var(--text-light);
            display: block;
            margin: 0 auto 0.5rem;
            font-weight: 600;
        }
        
        .progress-step.active::before {
            background-color: var(--accent-teal);
            color: white;
        }
        
        .progress-step.completed::before {
            background-color: var(--success-green);
            color: white;
        }
        
        .step-title {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .progress-step.active .step-title {
            color: var(--accent-teal);
            font-weight: 600;
        }
        
        .progress-step.completed .step-title {
            color: var(--success-green);
        }
        
        .report-summary {
            background-color: rgba(0, 119, 204, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 4px solid var(--secondary-blue);
        }
        
        .photo-preview {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .photo-preview:hover {
            border-color: var(--secondary-blue);
            background-color: rgba(0, 119, 204, 0.02);
        }
        
        .photo-preview i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-blue);
        }
        
        /* AI Chatbot section */
        #chatbot .chat-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        #chatbot .chat-suggestion-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.85rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--bn-border);
            border-radius: 2rem;
            color: var(--bn-text-muted);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        #chatbot .chat-suggestion-chip:hover {
            background: rgba(207,155,0,0.15);
            border-color: var(--bn-gold);
            color: var(--bn-gold);
        }
        #chatbot .chat-input-row {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        #chatbot .chatbot-card {
            background: var(--bn-card-bg);
            border: 1px solid var(--bn-border);
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        #chatbot .chatbot-header {
            background: var(--bn-navy);
            border-bottom: 1px solid var(--bn-border);
            color: #fff;
            padding: 1rem 1.25rem;
        }
        #chatbot .chatbot-header h3 { font-size: 1.1rem; margin: 0 0 0.2rem 0; font-weight: 600; }
        #chatbot .chatbot-header p { margin: 0; font-size: 0.85rem; opacity: 0.9; }
        #chatbot .chatbot-messages {
            height: 24rem;
            overflow-y: auto;
            padding: 1rem 1.25rem;
            background: var(--bn-navy);
        }
        #chatbot .chat-msg { margin-bottom: 0.75rem; display: flex; }
        #chatbot .chat-msg.user { justify-content: flex-end; }
        #chatbot .chat-msg.bot { justify-content: flex-start; }
        #chatbot .chat-bubble {
            max-width: 75%;
            padding: 0.6rem 0.9rem;
            border-radius: 1.25rem;
            font-size: 0.95rem;
        }
        #chatbot .chat-bubble.user {
            background: var(--secondary-blue);
            color: #fff;
            border-bottom-right-radius: 0.35rem;
        }
        #chatbot .chat-bubble.bot {
            background: var(--bn-card-bg);
            color: #ffffff;
            border-bottom-left-radius: 0.35rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        #chatbot .chatbot-footer {
            border-top: 1px solid var(--bn-border);
            background: var(--bn-navy);
            padding: 0.75rem 1rem 1rem;
        }
        #chatbot .chatbot-footer .form-control {
            border-radius: 0.5rem;
            padding: 0.6rem 0.85rem;
            background: var(--bn-navy);
            border: 1px solid var(--bn-border);
            color: #fff;
        }
        #chatbot .typing-dots span {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #9ca3af;
            margin: 0 2px;
            animation: typingBounce 1s infinite;
        }
        #chatbot .typing-dots span:nth-child(2) { animation-delay: 0.15s; }
        #chatbot .typing-dots span:nth-child(3) { animation-delay: 0.3s; }
        @keyframes typingBounce {
            0%, 100% { transform: translateY(0); opacity: 0.6; }
            50% { transform: translateY(-4px); opacity: 1; }
        }

        /* How It Works Carousel */
        @keyframes cardFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        @keyframes centerPop {
            0% { box-shadow: 0 8px 0 4px var(--bn-border); }
            50% { box-shadow: 0 12px 0 6px var(--bn-border); }
            100% { box-shadow: 0 8px 0 4px var(--bn-border); }
        }
        .how-it-works-carousel {
            position: relative;
            width: 100%;
            height: 600px;
            overflow: hidden;
            background: rgba(18, 41, 66, 0.5);
            border-radius: 0.5rem;
            border: 1px solid var(--bn-border);
        }
        .how-it-works-card {
            position: absolute;
            left: 50%;
            top: 50%;
            cursor: pointer;
            border: 2px solid var(--bn-border);
            padding: 1.5rem 2rem;
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1),
                        border-color 0.3s ease,
                        background 0.4s ease,
                        box-shadow 0.4s ease;
            background: #1a2942;
            color: #fff;
            clip-path: polygon(50px 0%, calc(100% - 50px) 0%, 100% 50px, 100% 100%, calc(100% - 50px) 100%, 50px 100%, 0 100%, 0 0);
            animation: cardFadeIn 0.5s ease-out forwards;
        }
        .how-it-works-card:hover {
            border-color: rgba(207, 155, 0, 0.6);
        }
        .how-it-works-card.center {
            z-index: 10;
            background: var(--bn-gold);
            color: var(--bn-black);
            border-color: var(--bn-gold);
            box-shadow: 0 8px 0 4px var(--bn-border);
            animation: cardFadeIn 0.5s ease-out forwards, centerPop 2s ease-in-out 0.5s;
        }
        .how-it-works-card .how-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(207, 155, 0, 0.2);
            color: var(--bn-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        .how-it-works-card:hover .how-icon {
            transform: scale(1.08);
        }
        .how-it-works-card.center .how-icon {
            background: rgba(0, 0, 0, 0.15);
            color: var(--bn-black);
            animation: iconPulse 2.5s ease-in-out infinite;
        }
        .how-it-works-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .how-it-works-card p.byline {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            right: 2rem;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .how-it-works-card.center p.byline { color: rgba(0, 0, 0, 0.8); }
        .how-it-works-card:not(.center) p.byline { color: #9ca3af; }
        .how-it-works-nav {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
        }
        .how-it-works-nav button {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bn-navy);
            border: 2px solid var(--bn-border);
            color: #fff;
            font-size: 1.25rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .how-it-works-nav button:hover {
            background: var(--bn-gold);
            color: var(--bn-black);
            border-color: var(--bn-gold);
            transform: scale(1.08);
        }
        .how-it-works-nav button:active {
            transform: scale(0.95);
        }
        @media (max-width: 639px) {
            .how-it-works-carousel { height: 550px; }
        }

        /* Admin Panel */
        #admin-panel-public {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--bn-card-bg);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            width: 400px;
            max-width: 90%;
            z-index: 10001;
            border: 2px solid var(--bn-gold);
        }
        #admin-panel-public h2 { margin-top: 0; color: var(--bn-white); border-bottom: 2px solid var(--bn-gold); padding-bottom: 10px; }
        #admin-panel-public input { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid var(--bn-border); border-radius: 8px; font-size: 16px; box-sizing: border-box; background: var(--bn-navy); color: var(--bn-white); }
        #admin-panel-public input:focus { border-color: var(--bn-gold); outline: none; }
        #admin-panel-public button { background: var(--bn-gold); color: var(--bn-black); border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 16px; width: 100%; margin: 5px 0; transition: background 0.3s; }
        #admin-panel-public button:hover { background: var(--bn-gold-light); }
        #admin-panel-public button.danger { background: #dc3545; color: white; }
        #admin-overlay-public { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 10000; }
        .admin-close-btn { position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; color: var(--bn-text-muted); }
        .admin-close-btn:hover { color: var(--bn-white); }
        #admin-console-public { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 8px; font-family: monospace; height: 180px; overflow-y: auto; margin: 10px 0; font-size: 13px; }
        .admin-tool-group { margin: 12px 0; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px; }
        .admin-tool-group h3 { margin: 0 0 10px 0; color: var(--bn-gold); font-size: 14px; }
        .admin-tool-group button { margin: 4px; width: auto; }

        @media (max-width: 768px) {
            .hero-section {
                padding: 2.5rem 0;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .report-card {
                padding: 1.5rem;
            }
            
            .progress-tracker {
                font-size: 0.8rem;
            }
            
            .progress-step::before {
                width: 25px;
                height: 25px;
                line-height: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="page-bg-blur">
        <div class="layer1"></div>
        <div class="layer2"></div>
        <div class="layer3"></div>
        <div class="bg-paths">
            <svg viewBox="0 0 1400 800" preserveAspectRatio="xMidYMid slice">
                <path d="M-100,100 Q200,50 500,150 T1100,100 T1500,200" stroke-width="0.8"/>
                <path d="M-50,250 Q300,200 700,300 T1400,250" stroke-width="0.6"/>
                <path d="M0,400 Q400,350 800,450 T1600,400" stroke-width="1"/>
                <path d="M-100,550 Q250,500 650,600 T1250,550 T1650,600" stroke-width="0.7"/>
                <path d="M-80,200 Q150,100 500,250 T1000,180 T1400,300" stroke-width="0.5"/>
                <path d="M-120,500 Q200,400 600,550 T1200,450" stroke-width="0.6"/>
                <path d="M0,650 Q350,600 750,700 T1500,650" stroke-width="0.8"/>
                <path d="M-60,350 Q180,300 550,400 T1150,350 T1550,450" stroke-width="0.5"/>
            </svg>
            <svg viewBox="0 0 1400 800" preserveAspectRatio="xMidYMid slice" style="transform: scaleX(-1);">
                <path d="M-100,120 Q250,80 600,180 T1200,120" stroke-width="0.7"/>
                <path d="M-80,350 Q220,300 580,400 T1180,350" stroke-width="0.6"/>
                <path d="M0,550 Q380,500 780,600 T1580,550" stroke-width="0.8"/>
            </svg>
        </div>
    </div>
    <div class="page-content">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <img src="/images/bruflow-logo.png" alt="BruFlow" class="me-2" style="height: 36px; width: auto;">
                <span>
                    <span class="d-block">BruFlow</span>
                    <span class="gov-badge">Government of Brunei Darussalam</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#report">Report Issue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#status">Check Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#information">Information</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#chatbot">AI Assistant</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('operations.dashboard') }}">Operations</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <div class="hero-badge">
                <i class="fas fa-shield-alt"></i>
                Citizen Services — Government Portal
            </div>
            <h1 class="hero-title">Report Sewage Issues in Our Community</h1>
            <p class="hero-subtitle">
                Help us keep our sewage system running smoothly. Report blockages, overflows, or maintenance issues quickly and easily. 
                Your reports help us respond faster and protect our environment.
            </p>
            <a href="#report" class="btn btn-primary btn-lg">
                <i class="fas fa-flag me-2"></i> Report a New Issue
            </a>
            <a href="#status" class="btn btn-outline-warning btn-lg ms-2">
                <i class="fas fa-search me-2"></i> Check Report Status
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-container">
        <div class="container">
            <!-- AI Chatbot Section (first after hero) -->
            <section id="chatbot" class="mb-5 scroll-mt-5">
                <h2 class="section-title">
                    <i class="fas fa-comments me-2"></i>Ask Our AI Assistant
                </h2>
                <div class="chatbot-card">
                    <div class="chatbot-header">
                        <h3>Sewage System AI Assistant</h3>
                        <p>Get instant answers about sewage issues, reporting, and more</p>
                    </div>
                    <div id="chatbotMessages" class="chatbot-messages">
                        <div class="chat-msg bot">
                            <div class="chat-bubble bot">Hello! I'm here to help you with sewage-related questions. How can I assist you today?</div>
                        </div>
                    </div>
                    <div class="chatbot-footer">
                        <div class="chat-suggestions">
                            <span class="chat-suggestion-chip" data-question="What do I do if I have a sewage problem?">What do I do</span>
                            <span class="chat-suggestion-chip" data-question="What is the emergency number for sewage?">Emergency</span>
                            <span class="chat-suggestion-chip" data-question="Help, I need to report a sewage issue">Help</span>
                            <span class="chat-suggestion-chip" data-question="How do I report an issue?">How to report</span>
                            <span class="chat-suggestion-chip" data-question="How do I check my report status?">Check status</span>
                            <span class="chat-suggestion-chip" data-question="What types of sewage issues can I report?">Report types</span>
                        </div>
                        <div class="chat-input-row">
                            <input type="text" id="chatbotInput" class="form-control flex-grow-1" placeholder="Type your question or search..." />
                            <button type="button" id="chatbotSend" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <p class="small mb-0 mt-2" style="color: var(--bn-text-muted);">Type <strong>admin</strong> for Admin Tools</p>
                    </div>
                </div>
            </section>

            <!-- Report Issue Section -->
            <section id="report" class="mb-5 scroll-mt-5">
                <h2 class="section-title"><i class="fas fa-flag me-2"></i>Report a Sewage Issue</h2>
                <p class="mb-4" style="color: var(--bn-text-muted);">Submit a detailed report to our maintenance team</p>
                <div class="report-card">
                    <div class="card-body">
                        <form id="reportForm" action="{{ route('reports.store') }}" method="POST">
                            @csrf
                            @if($errors->any())
                                <div class="alert alert-danger mb-4">
                                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                </div>
                            @endif
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Type of Issue <span class="text-danger">*</span></label>
                                    <select name="issue_type" class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="blockage" {{ old('issue_type')=='blockage'?'selected':'' }}>Sewage Blockage</option>
                                        <option value="overflow" {{ old('issue_type')=='overflow'?'selected':'' }}>Sewage Overflow</option>
                                        <option value="odor" {{ old('issue_type')=='odor'?'selected':'' }}>Strong Sewage Odor</option>
                                        <option value="maintenance" {{ old('issue_type')=='maintenance'?'selected':'' }}>Maintenance Required</option>
                                        <option value="other" {{ old('issue_type')=='other'?'selected':'' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Severity <span class="text-danger">*</span></label>
                                    <select name="severity" class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="low" {{ old('severity')=='low'?'selected':'' }}>Low</option>
                                        <option value="medium" {{ old('severity')=='medium'?'selected':'' }}>Medium</option>
                                        <option value="high" {{ old('severity')=='high'?'selected':'' }}>High</option>
                                        <option value="critical" {{ old('severity')=='critical'?'selected':'' }}>Critical</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">District <span class="text-danger">*</span></label>
                                    <select name="district" id="district" class="form-select" required>
                                        <option value="">Select</option>
                                        @foreach($districts as $slug=>$name)
                                            <option value="{{ $slug }}" {{ old('district')==$slug?'selected':'' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mukim</label>
                                    <select name="mukim" id="mukim" class="form-select">
                                        <option value="">Select (optional)</option>
                                        @foreach($mukims as $dSlug=>$list)
                                            @foreach($list as $mSlug=>$mName)
                                                <option value="{{ $mSlug }}" data-district="{{ $dSlug }}">{{ $mName }}</option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="location_address" class="form-control" required value="{{ old('location_address') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Your Name (optional)</label>
                                    <input type="text" name="reporter_name" class="form-control" value="{{ old('reporter_name') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact (optional)</label>
                                <input type="text" name="reporter_contact" class="form-control" value="{{ old('reporter_contact') }}">
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-danger px-4">Submit Report</button>
                            </div>
                        </form>
                        @if(session('report_success') && session('report_number'))
                            <div class="alert alert-success mt-3 mb-0">
                                <strong>Report Submitted!</strong> Reference: <code>{{ session('report_number') }}</code>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Check Status Section -->
            <section id="status" class="mb-5 scroll-mt-5">
                <h2 class="section-title"><i class="fas fa-search me-2"></i>Check Report Status</h2>
                <p class="mb-4" style="color: var(--bn-text-muted);">Track the progress of your submitted reports</p>
                <div class="report-card">
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-3">
                            <input type="text" id="trackingNumber" class="form-control flex-grow-1" placeholder="E.g. RPT-20260206-XXXXXX" style="max-width:20rem">
                            <button type="button" id="trackButton" class="btn btn-primary">Track</button>
                        </div>
                        <div id="statusDisplay" style="display:none" class="mt-3 pt-3 border-top border-secondary">
                            <div class="p-3 rounded bg-dark bg-opacity-50">
                                <span class="badge bg-success mb-2">Resolved</span>
                                <h5 class="text-white mb-1">Blockage at Main Street Drain</h5>
                                <p class="small mb-0" style="color: var(--bn-text-muted);">REF: <span id="statusRef">RPT-20260206-XXXXXX</span></p>
                                <p class="small mt-2 mb-0" style="color: var(--bn-text-muted);"><a href="{{ route('checkreportstatus') }}" style="color: var(--bn-gold);">Full tracking page</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Information Section -->
            <section id="information" class="mb-5">
                <h2 class="section-title">How It Works</h2>
                
                <div id="howItWorksCarousel" class="how-it-works-carousel">
                    <div id="howItWorksCards"></div>
                    <div class="how-it-works-nav">
                        <button type="button" id="howPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" id="howNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                
                <div class="info-box mt-4">
                    <h4><i class="fas fa-lightbulb me-2"></i> What to Report</h4>
                    <ul class="mb-0">
                        <li><strong>Sewage blockages</strong> - Water backing up from drains or toilets</li>
                        <li><strong>Sewage overflows</strong> - Sewage coming from manholes or drains onto streets</li>
                        <li><strong>Strong sewage odors</strong> - Persistent bad smells from drains or sewer areas</li>
                        <li><strong>Damaged sewer infrastructure</strong> - Broken manhole covers, exposed pipes</li>
                        <li><strong>Illegal dumping into sewers</strong> - Witnessed dumping of hazardous materials</li>
                    </ul>
                </div>
            </section>
            
            <!-- Contact Section -->
            <section id="contact" class="mb-5">
                <h2 class="section-title">Contact Public Works</h2>
                
                <div class="report-card">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h5><i class="fas fa-phone-alt me-2"></i> Emergency Contact</h5>
                            <p>For sewage emergencies that pose immediate health or environmental risks:</p>
                            <div class="alert alert-warning">
                                <h6 class="alert-heading mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Emergency Hotline</h6>
                                <h4 class="mb-0">(555) 123-EMER (3637)</h4>
                                <p class="mb-0 small">Available 24/7 for urgent sewage emergencies</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <h5><i class="fas fa-envelope me-2"></i> General Inquiries</h5>
                            <p>For non-emergency questions about the sewage system:</p>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-inbox me-2"></i> Email: Public Works, Brunei Darussalam</li>
                                <li class="mb-2"><i class="fas fa-phone me-2"></i> Phone: (555) 123-4567 (Mon-Fri, 8AM-5PM)</li>
                                <li><i class="fas fa-map-marker-alt me-2"></i> Address: 123 Public Works Blvd, City, State 12345</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Admin Panel (chatbot command: type "admin") -->
    <div id="admin-overlay-public"></div>
    <div id="admin-panel-public">
        <span class="admin-close-btn" onclick="window.hideAdminPanel()">&times;</span>
        <h2>Admin Troubleshooting</h2>
        <div id="admin-login-form">
            <input type="hidden" id="admin-csrf" value="{{ csrf_token() }}">
            <input type="password" id="admin-password-public" placeholder="Enter admin password" onkeypress="if(event.key==='Enter')window.adminVerify()">
            <button onclick="window.adminVerify()">Login</button>
            <p class="small mb-0 mt-2" style="color: var(--bn-text-muted); text-align: center;">Password: admin123</p>
        </div>
        <div id="admin-tools-public" style="display: none;">
            <div class="admin-tool-group">
                <h3>Diagnostics</h3>
                <button onclick="adminDiagnostic()">Test API</button>
                <button onclick="adminNetwork()">Network Status</button>
            </div>
            <div class="admin-tool-group">
                <h3>Cache & Storage</h3>
                <button onclick="adminClearCache()">Clear Local Storage</button>
                <button onclick="adminReload()">Reload Page</button>
            </div>
            <div class="admin-tool-group">
                <h3>Visual Debug</h3>
                <button onclick="adminBrokenImages()">Find Broken Images</button>
                <button onclick="adminResponsive()">Check Layout</button>
            </div>
            <div class="admin-tool-group">
                <h3>Console</h3>
                <div id="admin-console-public">Ready...</div>
                <button onclick="document.getElementById('admin-console-public').innerHTML='Ready...'">Clear</button>
            </div>
            <button class="danger" onclick="window.adminLogout()">Logout</button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">BruFlow — Public Works</h5>
                    <p>Official Government of Brunei Darussalam portal for reporting and tracking sewage system issues. Together we maintain a clean and functional infrastructure for the nation.</p>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <div class="footer-links">
                        <a href="#report">Report Issue</a>
                        <a href="#status">Check Status</a>
                        <a href="#information">Information</a>
                        <a href="#contact">Contact</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Resources</h5>
                    <div class="footer-links">
                        <a href="#">Sewage System Maintenance Tips</a>
                        <a href="#">Environmental Impact Information</a>
                        <a href="#">Public Works Department</a>
                        <a href="#">Public Services</a>
                    </div>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h5 class="footer-title">Stay Informed</h5>
                    <p>Subscribe to receive updates about sewage system maintenance and alerts in your area.</p>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Your email address">
                        <button class="btn btn-primary" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p class="mb-0">&copy; {{ date('Y') }} Government of Brunei Darussalam. All rights reserved.</p>
            </div>
        </div>
    </footer>
    </div><!-- end page-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var trackButton = document.getElementById('trackButton');
        var trackingInput = document.getElementById('trackingNumber');
        var statusDisplay = document.getElementById('statusDisplay');
        if (trackButton && statusDisplay) {
            trackButton.addEventListener('click', function() {
                var num = (trackingInput && trackingInput.value) ? trackingInput.value.trim() : '';
                if (!num) { alert('Please enter a report tracking number'); return; }
                var refEl = document.getElementById('statusRef');
                if (refEl) refEl.textContent = num;
                statusDisplay.style.display = 'block';
                statusDisplay.scrollIntoView({ behavior: 'smooth' });
            });
        }
        var districtEl = document.getElementById('district');
        var mukimEl = document.getElementById('mukim');
        if (districtEl && mukimEl) {
            function filterMukims() {
                var d = districtEl.value;
                mukimEl.querySelectorAll('option[data-district]').forEach(function(o) {
                    o.style.display = o.getAttribute('data-district') === d ? '' : 'none';
                });
                if (!d) mukimEl.value = '';
            }
            districtEl.addEventListener('change', filterMukims);
            filterMukims();
        }

        // AI Chatbot (React-style inline assistant)
        (function() {
            var messagesEl = document.getElementById('chatbotMessages');
            var inputEl = document.getElementById('chatbotInput');
            var sendBtn = document.getElementById('chatbotSend');
            if (!messagesEl || !inputEl || !sendBtn) return;

            function appendMessage(text, role) {
                var row = document.createElement('div');
                row.className = 'chat-msg ' + role;
                var bubble = document.createElement('div');
                bubble.className = 'chat-bubble ' + role;
                bubble.textContent = text;
                row.appendChild(bubble);
                messagesEl.appendChild(row);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            function showTyping() {
                var row = document.createElement('div');
                row.className = 'chat-msg bot';
                row.id = 'chatbotTyping';
                var bubble = document.createElement('div');
                bubble.className = 'chat-bubble bot';
                bubble.innerHTML = '<span class="typing-dots"><span></span><span></span><span></span></span>';
                row.appendChild(bubble);
                messagesEl.appendChild(row);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            function hideTyping() {
                var el = document.getElementById('chatbotTyping');
                if (el) el.remove();
            }

            function sendMessage() {
                var text = inputEl.value.trim();
                if (!text) return;
                
                var adminCommand = /^(admin|admin\s*tools?|open\s*admin|\/admin)\s*$/i.test(text);
                if (adminCommand) {
                    inputEl.value = '';
                    if (typeof window.showAdminPanel === 'function') window.showAdminPanel();
                    return;
                }
                
                appendMessage(text, 'user');
                inputEl.value = '';
                sendBtn.disabled = true;
                showTyping();

                // Call OpenAI API via Laravel route
                fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                    credentials: 'same-origin',
                })
                .then(function(r) {
                    return r.json().then(function(data) {
                        if (!r.ok) throw new Error((data && data.error) || 'Server error');
                        return data;
                    }).catch(function(e) {
                        if (e instanceof SyntaxError) throw new Error('Invalid response');
                        throw e;
                    });
                })
                .then(function(data) {
                    hideTyping();
                    if (data.reply) {
                        appendMessage(data.reply, 'bot');
                    } else if (data.error) {
                        appendMessage('Sorry, ' + data.error + ' Please check your OpenAI API key configuration.', 'bot');
                    } else {
                        appendMessage('Sorry, I could not generate a response. Please try again.', 'bot');
                    }
                })
                .catch(function(err) {
                    hideTyping();
                    var msg = (err && err.message) ? err.message : 'Could not reach the AI. Check connection and try again.';
                    appendMessage('Sorry, ' + msg, 'bot');
                    console.error('Chatbot error:', err);
                })
                .finally(function() {
                    sendBtn.disabled = false;
                });
            }

            sendBtn.addEventListener('click', sendMessage);
            inputEl.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); sendMessage(); }
            });
            document.querySelectorAll('.chat-suggestion-chip').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    var q = chip.getAttribute('data-question');
                    if (q) { inputEl.value = q; sendMessage(); }
                });
            });
        })();

        // Admin Panel (type "admin" in chatbot)
        (function() {
            var consoleEl = document.getElementById('admin-console-public');
            function log(msg) {
                if (consoleEl) {
                    var t = new Date().toLocaleTimeString();
                    consoleEl.innerHTML += '<div style="color:#55ff55">[' + t + '] ' + msg + '</div>';
                    consoleEl.scrollTop = consoleEl.scrollHeight;
                }
            }
            window.showAdminPanel = function() {
                document.getElementById('admin-overlay-public').style.display = 'block';
                document.getElementById('admin-panel-public').style.display = 'block';
                document.getElementById('admin-login-form').style.display = 'block';
                document.getElementById('admin-tools-public').style.display = 'none';
                document.getElementById('admin-password-public').value = '';
                document.getElementById('admin-password-public').focus();
            };
            window.hideAdminPanel = function() {
                document.getElementById('admin-overlay-public').style.display = 'none';
                document.getElementById('admin-panel-public').style.display = 'none';
            };
            window.adminVerify = function() {
                var p = document.getElementById('admin-password-public').value;
                if (p === 'admin123') {
                    document.getElementById('admin-login-form').style.display = 'none';
                    document.getElementById('admin-tools-public').style.display = 'block';
                    log('Admin access granted.');
                } else {
                    alert('Incorrect password.');
                }
            };
            window.adminLogout = function() {
                document.getElementById('admin-login-form').style.display = 'block';
                document.getElementById('admin-tools-public').style.display = 'none';
                hideAdminPanel();
            };
            adminDiagnostic = function() {
                log('Testing AI route...');
                fetch('/ai/chat', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.getElementById('admin-csrf')||{}).value || '' }, body: JSON.stringify({message:'test'}) })
                    .then(function() { log('AI route reachable.'); }).catch(function() { log('AI route error.', 'error'); });
            };
            adminNetwork = function() { log('Online: ' + navigator.onLine); };
            adminClearCache = function() { if (confirm('Clear local storage?')) { localStorage.clear(); log('Cleared.'); } };
            adminReload = function() { if (confirm('Reload?')) location.reload(); };
            adminBrokenImages = function() {
                var imgs = document.getElementsByTagName('img');
                var n = 0;
                for (var i = 0; i < imgs.length; i++) { if (!imgs[i].complete || imgs[i].naturalHeight === 0) { imgs[i].style.border = '3px solid red'; n++; } }
                log(n > 0 ? n + ' broken image(s)' : 'No broken images.');
            };
            adminResponsive = function() { log('Viewport: ' + window.innerWidth + ' x ' + window.innerHeight); };
            document.getElementById('admin-overlay-public').onclick = hideAdminPanel;
        })();

        // How It Works Carousel
        (function() {
            var steps = [
                { title: "1. Report Issue", text: "Use our simple form to report sewage issues in Brunei Darussalam. Provide details, district, mukim, and location.", icon: "fa-flag" },
                { title: "2. We Assess & Prioritize", text: "Our team reviews reports, assesses severity, and prioritizes based on urgency and public safety.", icon: "fa-tasks" },
                { title: "3. Dispatch & Resolve", text: "Maintenance crews are dispatched to resolve issues. We work efficiently to minimize disruptions.", icon: "fa-truck" },
                { title: "4. Track Your Report", text: "Use your reference number to check status anytime. Stay informed from submission to resolution.", icon: "fa-search" },
                { title: "5. Issue Resolved", text: "Once fixed, your report is marked resolved. Our goal is a clean and functional sewage system for all.", icon: "fa-check-circle" }
            ];
            var list = steps.map(function(s, i) { return { tempId: i, title: s.title, text: s.text, icon: s.icon }; });
            var cardsEl = document.getElementById('howItWorksCards');
            var prevBtn = document.getElementById('howPrev');
            var nextBtn = document.getElementById('howNext');
            if (!cardsEl || !prevBtn || !nextBtn) return;

            function getCardSize() { return window.innerWidth >= 640 ? 365 : 290; }

            function render() {
                var cardSize = getCardSize();
                var len = list.length;
                var half = len % 2 ? (len + 1) / 2 : len / 2;
                var html = '';
                for (var i = 0; i < len; i++) {
                    var pos = i - half;
                    var isCenter = pos === 0;
                    var tx = (cardSize / 1.5) * pos;
                    var ty = isCenter ? -65 : (pos % 2 ? 15 : -15);
                    var rot = isCenter ? 0 : (pos % 2 ? 2.5 : -2.5);
                    var cls = 'how-it-works-card' + (isCenter ? ' center' : '');
                    html += '<div class="' + cls + '" data-pos="' + pos + '" style="width:' + cardSize + 'px;height:' + cardSize + 'px;transform:translate(-50%,-50%) translateX(' + tx + 'px) translateY(' + ty + 'px) rotate(' + rot + 'deg)">' +
                        '<div class="how-icon"><i class="fas ' + list[i].icon + '"></i></div>' +
                        '<h3>' + list[i].title + '</h3>' +
                        '<p class="byline">' + list[i].text + '</p></div>';
                }
                cardsEl.innerHTML = html;
                cardsEl.querySelectorAll('.how-it-works-card').forEach(function(el) {
                    el.addEventListener('click', function() { handleMove(parseInt(el.getAttribute('data-pos'), 10)); });
                });
            }

            function handleMove(steps) {
                if (steps > 0) {
                    for (var i = steps; i > 0; i--) {
                        var item = list.shift();
                        if (!item) return;
                        list.push({ tempId: Math.random(), title: item.title, text: item.text, icon: item.icon });
                    }
                } else if (steps < 0) {
                    for (var i = steps; i < 0; i++) {
                        var item = list.pop();
                        if (!item) return;
                        list.unshift({ tempId: Math.random(), title: item.title, text: item.text, icon: item.icon });
                    }
                }
                render();
            }

            prevBtn.addEventListener('click', function() { handleMove(-1); });
            nextBtn.addEventListener('click', function() { handleMove(1); });
            window.addEventListener('resize', render);
            render();
        })();
    </script>
</body>
</html>