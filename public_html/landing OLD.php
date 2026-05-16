<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <!-- Primary Meta Tags -->
    <title>Ask Visa – Visa made simple, approved fast</title>
    <meta name="title" content="Ask Visa – Visa assistance with 99.3% approval">
    <meta name="description"
        content="Get tourist eVisas for Thailand, Dubai, Singapore and more. 99.3% approval rate, average 3 day processing. Apply online in minutes.">
    <meta name="keywords" content="visa online, e-visa, Thailand visa, Dubai visa, Singapore visa, visa assistance">
    <meta name="author" content="Ask Visa">
    <!-- Favicon (simple inline for demo) -->
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90' fill='%23C62828'>✈️</text></svg>">
    <!-- Font Awesome 6 (free) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ---------- GLOBAL RESET & VARIABLES ---------- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        :root {
            --primary: #8E1B1B; /* Keeping the signature Ruby but as an accent */
            --primary-glow: rgba(142, 27, 27, 0.4);
            --secondary: #0A0A0C;
            --ethereal-1: #6366F1; /* Indigo */
            --ethereal-2: #A855F7; /* Purple */
            --ethereal-3: #EC4899; /* Pink */
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #FFFFFF;
            --text-dim: rgba(255, 255, 255, 0.6);
            --radius-xl: 40px;
            --transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--secondary);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Dynamic Mesh Gradient Background */
        .mesh-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            background: radial-gradient(at 0% 0%, var(--ethereal-1) 0px, transparent 50%),
                        radial-gradient(at 50% 0%, var(--ethereal-2) 0px, transparent 50%),
                        radial-gradient(at 100% 0%, var(--ethereal-3) 0px, transparent 50%),
                        radial-gradient(at 50% 100%, #1E1B4B 0px, transparent 50%);
            filter: blur(80px);
            opacity: 0.4;
            animation: meshMove 20s infinite alternate ease-in-out;
        }

        @keyframes meshMove {
            0% { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.2) translate(5%, 5%); }
        }

        a, button {
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        /* ---------- TYPOGRAPHY & UTILITIES ---------- */
        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .section-title {
            font-size: 4rem;
            font-weight: 800;
            letter-spacing: -0.05em;
            margin-bottom: 24px;
            background: linear-gradient(to bottom, #fff, rgba(255,255,255,0.7));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-subhead {
            font-size: 1.25rem;
            color: var(--text-dim);
            margin-bottom: 60px;
            max-width: 600px;
        }

        /* ---------- NAVIGATION (Deep Glassmorphism) ---------- */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
            padding: 0 40px;
            max-width: 1200px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-radius: var(--radius-xl);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            position: fixed;
            top: 32px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: calc(100% - 64px);
            box-sizing: border-box;
            transition: var(--transition);
        }

        nav.scrolled {
            height: 70px;
            top: 20px;
            background: rgba(10, 10, 12, 0.8);
            border-color: rgba(255, 255, 255, 0.05);
        }



        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo img {
            height: 48px;
            width: auto;
            display: block;
        }

        .nav-links {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-dim);
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: -0.02em;
        }

        .nav-links a:hover {
            color: white;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        .nav-cta {
            background: linear-gradient(135deg, var(--ethereal-1), var(--ethereal-2));
            color: white !important;
            padding: 12px 28px;
            border-radius: var(--radius-xl);
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .nav-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.5);
        }

        /* ---------- HERO SECTION (Ethereal Floating) ---------- */
        .hero {
            padding: 240px 40px 120px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.05);
            padding: 12px 24px;
            border-radius: var(--radius-xl);
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid var(--glass-border);
            margin-bottom: 32px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
        }

        .hero h1 {
            font-size: 6rem;
            line-height: 0.9;
            margin-bottom: 24px;
            font-weight: 900;
        }

        .highlight {
            color: var(--ethereal-2);
            text-shadow: 0 0 40px rgba(168, 85, 247, 0.4);
        }

        .hero-subhead {
            font-size: 1.5rem;
            color: var(--text-dim);
            margin-bottom: 60px;
            max-width: 700px;
        }

        /* Floating Search Portal */
        .search-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(40px) saturate(150%);
            -webkit-backdrop-filter: blur(40px) saturate(150%);
            padding: 12px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            max-width: 760px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            animation: heroFloat 6s infinite ease-in-out;
            position: relative;
        }

        @keyframes heroFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .search-icon {
            font-size: 1.5rem;
            color: var(--ethereal-1);
            margin: 0 12px 0 20px;
        }

        .search-container select {
            flex: 1;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            outline: none;
            padding: 16px 0;
            appearance: none;
        }

        .search-container select option {
            background: var(--secondary);
            color: white;
        }

        .search-btn {
            background: linear-gradient(135deg, var(--ethereal-1), var(--ethereal-2));
            padding: 20px 48px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 1.1rem;
            border: none;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }

        .search-btn:hover {
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.5);
        }

        /* Trust Strip (Airy) */
        .trust-strip {
            display: flex;
            gap: 48px;
            margin-top: 80px;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dim);
        }

        .trust-item i {
            color: var(--ethereal-2);
            font-size: 1.25rem;
        }

        .search-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.02);
            box-shadow: 0 15px 30px rgba(142, 27, 27, 0.3);
        }

        /* Trust Strip */
        .trust-strip {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 64px;
            flex-wrap: wrap;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--gray-600);
            font-weight: 600;
            font-size: 0.95rem;
            padding: 12px 24px;
            background: var(--gray-100);
            border-radius: var(--radius-full);
            border: 1px solid var(--gray-200);
        }

        .trust-item i {
            color: var(--primary);
            font-size: 1.2rem;
        }

        /* ---------- TRENDING CARDS SECTION ---------- */
        .trending {
            padding: 120px 40px;
            background: transparent;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 35px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            transition: var(--transition);
            position: relative;
        }

        .card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .card:hover {
            transform: translateY(-12px) scale(1.02);
            border-color: var(--ethereal-2);
            box-shadow: 0 20px 50px rgba(168, 85, 247, 0.2);
            background: rgba(255, 255, 255, 0.07);
        }

        .card-img-container {
            height: 240px;
            overflow: hidden;
            position: relative;
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
            filter: saturate(1.2);
        }

        .card:hover .card-img {
            transform: scale(1.15);
        }

        .badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            color: white;
            padding: 8px 16px;
            border-radius: var(--radius-xl);
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-content {
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .country-name {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .visa-info {
            color: var(--gray-600);
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.5;
        }

        .processing-time {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            color: var(--secondary);
            font-weight: 700;
            margin-top: 8px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-100);
        }

        .processing-time i {
            color: #10B981; /* Emerald for success/speed */
        }


        /* ---------- HOW IT WORKS ---------- */
        .how-it-works {
            padding: 120px 40px;
            background: transparent;
            text-align: center;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 60px;
            margin-top: 80px;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .step-icon {
            background: rgba(255, 255, 255, 0.03);
            width: 100px;
            height: 100px;
            border-radius: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            color: var(--ethereal-1);
            font-size: 2.5rem;
            transition: var(--transition);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
        }

        .step-item:hover .step-icon {
            transform: translateY(-10px) rotate(-8deg);
            background: var(--ethereal-1);
            color: white;
            border-color: var(--ethereal-1);
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
        }

        .step-item h3 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .step-item p {
            color: var(--text-dim);
            max-width: 280px;
            line-height: 1.6;
        }

        /* ---------- TESTIMONIALS (Consolidated) ---------- */
        .testimonials {
            position: relative;
            background: rgba(255, 255, 255, 0.02);
            color: white;
            padding: 140px 40px;
            overflow: hidden;
            border-radius: 60px;
            margin: 0 24px 80px;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
        }

        .testimonial-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 70% 30%, var(--ethereal-2), transparent 40%);
            opacity: 0.1;
            pointer-events: none;
            z-index: 1;
        }

        .testimonials .section-title,
        .testimonials .section-subhead {
            color: white;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }

        .testimonials-slider-container {
            max-width: 1000px;
            margin: 60px auto 0;
            position: relative;
            min-height: 350px;
            z-index: 2;
        }

        .testimonial-slide {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 80px;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            opacity: 0;
            transform: translateX(40px);
            transition: opacity 0.8s ease, transform 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
            pointer-events: none;
        }

        .testimonial-slide.active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
            position: relative;
        }

        .slide-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .quote-icon {
            font-size: 3rem;
            color: var(--ethereal-1);
            opacity: 0.4;
            margin-bottom: 24px;
        }

        .review-text {
            font-size: 1.75rem;
            line-height: 1.4;
            font-weight: 600;
            margin-bottom: 40px;
            font-style: italic;
            letter-spacing: -0.02em;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .reviewer-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--ethereal-1), var(--ethereal-2));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.25rem;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .reviewer-info strong {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .reviewer-info span {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }


        /* ---------- CONTACT SECTION ---------- */
        .contact-section {
            padding: 120px 40px;
            background: transparent;
        }

        .contact-wrapper {
            display: flex;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-radius: 50px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.3);
            margin-bottom: 80px;
        }

        .contact-form-container {
            flex: 1.2;
            padding: 80px;
        }

        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 20px 24px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            font-size: 1rem;
            color: white;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            background: rgba(255, 255, 255, 0.07);
            border-color: var(--ethereal-1);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
            outline: none;
        }

        .send-btn {
            background: linear-gradient(135deg, var(--ethereal-1), var(--ethereal-2));
            color: white;
            border: none;
            padding: 20px 48px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 1.1rem;
            margin-top: 24px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .contact-image-container {
            flex: 0.8;
            background: linear-gradient(135deg, #1E1B4B 0%, var(--secondary) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            color: white;
            position: relative;
            text-align: center;
            border-left: 1px solid var(--glass-border);
        }

        .contact-quote {
            font-size: 3rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 24px;
            letter-spacing: -0.05em;
            background: linear-gradient(to bottom, #fff, var(--ethereal-1));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .contact-card {
            background: rgba(255, 255, 255, 0.03);
            padding: 48px 32px;
            border-radius: var(--radius-xl);
            text-align: center;
            border: 1px solid var(--glass-border);
            transition: var(--transition);
            backdrop-filter: blur(12px);
        }

        .contact-card:hover {
            border-color: var(--ethereal-2);
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(168, 85, 247, 0.2);
        }

        .icon-box {
            background: rgba(99, 102, 241, 0.1);
            width: 80px;
            height: 80px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            color: var(--ethereal-1);
            font-size: 2rem;
        }

        /* ---------- MODAL (Refined) ---------- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(24px);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.6s ease;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(40px);
            width: 95%;
            max-width: 650px;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
            transform: scale(0.95) translateY(40px);
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid var(--glass-border);
            color: white;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1) translateY(0);
        }

        .modal-hero-img {
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .modal-body {
            padding: 48px;
        }

        .modal-body h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.04em;
        }

        .apply-btn {
            display: block;
            background: linear-gradient(135deg, var(--ethereal-1), var(--ethereal-2));
            color: white;
            padding: 20px;
            border-radius: 24px;
            font-weight: 800;
            font-size: 1.25rem;
            text-align: center;
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
        }

        /* ---------- ANIMATIONS ---------- */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-up {
            opacity: 0;
            animation: fadeUp 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }


        /* ---------- FOOTER ---------- */
        footer {
            background: linear-gradient(to bottom, transparent, rgba(30, 27, 75, 0.5));
            color: white;
            padding: 80px 40px 40px;
            border-top: 1px solid var(--glass-border);
            margin-top: 120px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            gap: 80px;
        }

        .footer-logo img {
            height: 80px;
            filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.5));
        }

        .footer-col h4 {
            font-size: 1.2rem;
            margin-bottom: 24px;
            font-weight: 800;
        }

        .footer-col a {
            color: var(--text-dim);
            display: block;
            margin-bottom: 12px;
            transition: var(--transition);
        }

        .footer-col a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 40px;
            margin-top: 60px;
            border-top: 1px solid var(--glass-border);
            color: var(--text-dim);
            font-size: 0.9rem;
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 1000px) {
            .contact-wrapper {
                flex-direction: column;
            }

            .contact-image-container {
                padding: 60px 20px;
                border-left: none;
                border-top: 1px solid var(--glass-border);
            }

            .steps-grid {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 3.5rem;
            }

            .section-title {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 720px) {
            nav {
                flex-direction: column;
                gap: 16px;
                border-radius: 32px;
            }

            .nav-links {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 24px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .search-container {
                flex-wrap: nowrap;
                /* Keep on one line */
                border-radius: 50px;
                /* Rounder */
                padding: 6px;
                /* Tighter */
                gap: 8px;
            }

            .search-btn {
                width: 48px;
                height: 48px;
                padding: 0;
                justify-content: center;
                margin-top: 0;
                border-radius: 50%;
                flex-shrink: 0;
            }

            .btn-text {
                display: none;
            }

            .trust-strip {
                gap: 20px;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .contact-cards {
                grid-template-columns: 1fr;
            }

            .footer-content {
                flex-direction: column;
            }

            .footer-links {
                flex-direction: column;
                gap: 30px;
            }
        }

        @media (max-width: 480px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        .animate-up {
            animation: fadeUp 0.7s cubic-bezier(0.2, 0.9, 0.3, 1) forwards;
            opacity: 0;
        }

        @keyframes fadeUp {
            0% {
                opacity: 0;
                transform: translateY(16px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Nav Styles */
        .hamburger-icon {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--secondary);
            margin-left: 16px;
        }

        /* Mobile Menu */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            justify-content: flex-end;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .mobile-menu-overlay.active {
            display: flex;
            opacity: 1;
        }

        .mobile-nav-links-container {
            background: white;
            width: 70%;
            max-width: 300px;
            height: 100%;
            padding: 80px 32px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.3, 1);
            position: relative;
        }

        .mobile-menu-overlay.active .mobile-nav-links-container {
            transform: translateX(0);
        }

        .mobile-nav-links-container a {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--secondary);
            text-decoration: none;
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .close-mobile-menu {
            position: absolute;
            top: 24px;
            right: 24px;
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: var(--secondary);
            line-height: 1;
        }

        @media (max-width: 768px) {
            nav {
                padding: 0 16px;
                /* Restore padding */
                position: fixed;
                /* Keep fixed on mobile */
                justify-content: flex-start;
                /* Align logo to start */
                left: 50%;
                transform: translateX(-50%);
                width: calc(100% - 24px);
                /* Mobile width */
            }

            .nav-links .desktop-link {
                display: none;
            }

            .hamburger-icon {
                display: block;
            }

            /* Force Logo Left and Lock it */
            .logo {
                position: absolute;
                left: 16px;
                /* Lock to left padding */
                top: 50%;
                transform: translateY(-50%);
                margin-right: 0;
                flex-shrink: 0;
            }

            /* Absolute positioning to LOCK it to the right */
            .nav-links {
                position: absolute;
                right: 16px;
                /* Lock to right padding */
                top: 50%;
                transform: translateY(-50%);
                gap: 12px;
                display: flex;
                align-items: center;
                margin: 0;
                height: auto;
                width: auto;
            }
        }
    </style>
    <!-- ENTRANCE ANIMATION STYLES -->
    <style>
        #entrance-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.8s ease-in-out;
        }

        .entrance-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        #preloader-logo {
            height: 120px;
            width: auto;
            margin-bottom: 24px;
            /* Ensure smooth hardware accelerated transition */
            transform-origin: center center;
            transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
        }

        #welcome-text {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease-out;
            margin-top: 20px;
            /* Space below logo */
            text-align: center;
            white-space: nowrap;
        }

        /* Helper to ensure nav logo doesn't flicker in early */
        .nav-logo-hidden {
            opacity: 0 !important;
        }
    </style>
</head>

<body>
    <div class="mesh-gradient"></div>

    <!-- ========== ENTRANCE OVERLAY ========== -->
    <div id="entrance-overlay">
        <div class="entrance-content">
            <img src="assets/ask-visa-logo-final.png" id="preloader-logo" alt="Ask Visa">
            <h1 id="welcome-text">Prepare for Departure</h1>
        </div>
    </div>

    <!-- ========== NAVIGATION with LOGO from assets/ask-visa-logo-final.png ========== -->
    <nav id="main-nav">
        <a href="#" class="logo">
            <img src="assets/ask-visa-logo-final.png" alt="Ask Visa Logo" id="nav-logo-real">
        </a>
        <div class="nav-links">
            <a href="#" class="desktop-link">Home</a>
            <a href="#trending" class="desktop-link">Services</a>
            <a href="#testimonials" class="desktop-link">Reviews</a>
            <a href="#contact" class="nav-cta">Get Started</a>
            <div class="hamburger-icon" id="hamburger-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- ========== HERO SECTION ========== -->
    <header class="hero">
        <div class="hero-badge animate-up">
            <i class="fas fa-shield-check"></i> 99.3% approval · 1,000+ visas delivered
        </div>
        <h1 class="animate-up" style="animation-delay: 0.1s;">
            Visa assistance <span class="highlight">made simple</span>, approved fast
        </h1>
        <p class="hero-subhead animate-up" style="animation-delay: 0.2s;">
            Your gateway to the world. Apply for Thailand, Dubai, and Singapore e-visas in under 5 minutes.
        </p>

        <!-- Search / destination select (refined) -->
        <form action="index.php" method="GET" class="search-container animate-up" style="animation-delay: 0.3s;">
            <i class="fas fa-search search-icon"></i>
            <select name="country" required>
                <option value="" disabled selected>Where are you flying to?</option>
                <option value="Thailand">🇹🇭 Thailand</option>
                <option value="Singapore">🇸🇬 Singapore</option>
                <option value="Malaysia">🇲🇾 Malaysia</option>
                <option value="Vietnam">🇻🇳 Vietnam</option>
                <option value="Dubai">🇦🇪 Dubai (UAE)</option>
            </select>
            <button type="submit" class="search-btn">
                <span>Explore</span> 
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <!-- trust indicators -->
        <div class="trust-strip animate-up" style="animation-delay: 0.4s;">
            <div class="trust-item"><i class="fas fa-bolt"></i> 2–5 days</div>
            <div class="trust-item"><i class="fas fa-shield-heart"></i> 99.3% Success</div>
            <div class="trust-item"><i class="fas fa-comment-check"></i> 24/7 Support</div>
        </div>
    </header>

    <script>
        // Nav Scroll Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('main-nav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    </script>


    <!-- ========== TRENDING DESTINATIONS ========== -->
    <section class="trending" id="trending">
        <div class="container">
            <h2 class="section-title animate-up">Trending destinations</h2>
            <p class="section-subhead animate-up">Most travellers this month are heading here</p>

            <div class="cards-grid">
                <!-- Thailand -->
                <div class="card"
                    onclick="openCountryModal('Thailand', 'Tourist Visa & Visa On Arrival', '4 days', 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80', ['Passport front & back', 'Passport size photo', 'Return flight ticket', 'Hotel confirmation'])">
                    <div class="card-img-container">
                        <img src="https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                            class="card-img" alt="Thailand" loading="lazy">
                        <div class="badge"><i class="fas fa-check-circle"></i> 200K+ processed</div>
                    </div>
                    <div class="card-content">
                        <div class="country-name">🇹🇭 Thailand</div>
                        <p class="visa-info">Tourist & Visa on Arrival • e-visa ready</p>
                        <div class="processing-time"><i class="far fa-calendar-check"></i> Get visa in 4 days</div>
                    </div>
                </div>
                <!-- Dubai -->
                <div class="card"
                    onclick="openCountryModal('Dubai (UAE)', '30/60 Days Tourist Visa', '2 days', 'https://images.unsplash.com/photo-1546412414-e1885259563a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80', ['Passport front & back', 'Passport size photo', 'Travel insurance (optional)'])">
                    <div class="card-img-container">
                        <img src="https://images.unsplash.com/photo-1546412414-e1885259563a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                            class="card-img" alt="Dubai" loading="lazy">
                        <div class="badge"><i class="fas fa-check-circle"></i> 50K+ processed</div>
                    </div>
                    <div class="card-content">
                        <div class="country-name">🇦🇪 Dubai (UAE)</div>
                        <p class="visa-info">30/60 days • express available</p>
                        <div class="processing-time"><i class="far fa-calendar-check"></i> Get visa in 2 days</div>
                    </div>
                </div>
                <!-- Singapore -->
                <div class="card"
                    onclick="openCountryModal('Singapore', 'Electronic Visa (e-Visa)', '5 days', 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80', ['Passport front & back', 'Passport photo', 'Hotel booking', 'Flight itinerary'])">
                    <div class="card-img-container">
                        <img src="https://images.unsplash.com/photo-1525625293386-3f8f99389edd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                            class="card-img" alt="Singapore" loading="lazy">
                        <div class="badge"><i class="fas fa-check-circle"></i> 84K+ processed</div>
                    </div>
                    <div class="card-content">
                        <div class="country-name">🇸🇬 Singapore</div>
                        <p class="visa-info">e-Visa • fully online</p>
                        <div class="processing-time"><i class="far fa-calendar-check"></i> Get visa in 5 days</div>
                    </div>
                </div>
                <!-- Vietnam -->
                <div class="card"
                    onclick="openCountryModal('Vietnam', '30 Days Tourist Visa', '3 days', 'https://images.unsplash.com/photo-1528127269322-539801943592?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80', ['Passport front & back', 'Passport photo', 'Entry date'])">
                    <div class="card-img-container">
                        <img src="https://images.unsplash.com/photo-1528127269322-539801943592?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                            class="card-img" alt="Vietnam" loading="lazy">
                        <div class="badge"><i class="fas fa-check-circle"></i> 74K+ processed</div>
                    </div>
                    <div class="card-content">
                        <div class="country-name">🇻🇳 Vietnam</div>
                        <p class="visa-info">30 days single entry</p>
                        <div class="processing-time"><i class="far fa-calendar-check"></i> Get visa in 3 days</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== HOW IT WORKS ========== -->
    <section class="how-it-works">
        <div class="container">
            <h2 class="section-title animate-up">Visa in 3 steps</h2>
            <p class="section-subhead animate-up">We’ve made it ridiculously simple</p>
            <div class="steps-grid">
                <div class="step-item animate-up" style="animation-delay: 0.1s;">
                    <div class="step-icon"><i class="fas fa-globe"></i></div>
                    <h3>1. Choose country</h3>
                    <p>Pick your destination and visa type. We show exactly what you need.</p>
                </div>
                <div class="step-item animate-up" style="animation-delay: 0.15s;">
                    <div class="step-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <h3>2. Upload documents</h3>
                    <p>Passport scan, photo – that’s it. We verify them instantly.</p>
                </div>
                <div class="step-item animate-up" style="animation-delay: 0.2s;">
                    <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                    <h3>3. Get visa by email</h3>
                    <p>We process and send your e-visa directly. No courier needed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== TESTIMONIALS (Revamped) ========== -->
    <section class="testimonials" id="testimonials">
        <!-- Faded Background Image -->
        <div class="testimonial-bg"></div>
        
        <div class="container" style="position: relative; z-index: 2;">
            <h2 class="section-title animate-up">Trusted by frequent flyers</h2>
            <p class="section-subhead animate-up">Real travellers, real 5‑star experiences</p>

            <div class="testimonials-slider-container animate-up" style="animation-delay: 0.2s;">
                <div class="testimonials-track" id="track">
                    
                    <!-- Slide 1 -->
                    <div class="testimonial-slide active">
                        <div class="slide-left">
                            <i class="fas fa-quote-left quote-icon"></i>
                            <p class="review-text">“Applied for Thailand visa on Monday, got it on Wednesday. The approval rate is no joke – 5/5.”</p>
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">RK</div>
                                <div>
                                    <strong>Ravi K.</strong>
                                    <span>Bengaluru</span>
                                </div>
                            </div>
                        </div>
                        <div class="slide-right">
                            <div class="stamp-container">
                                <div class="visa-stamp">
                                    <div class="stamp-inner">
                                        <span>VISA</span>
                                        <strong>APPROVED</strong>
                                        <i class="fas fa-plane-departure"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2 -->
                    <div class="testimonial-slide">
                        <div class="slide-left">
                            <i class="fas fa-quote-left quote-icon"></i>
                            <p class="review-text">“Super smooth experience for my Dubai trip. No paperwork hassle, just uploaded and done!”</p>
                            <div class="reviewer-info">
                                <div class="reviewer-avatar" style="background: var(--accent);">PJ</div>
                                <div>
                                    <strong>Priya J.</strong>
                                    <span>Mumbai</span>
                                </div>
                            </div>
                        </div>
                        <div class="slide-right">
                            <div class="stamp-container">
                                <div class="visa-stamp" style="border-color: var(--accent); color: var(--accent);">
                                    <div class="stamp-inner">
                                        <span>UAE</span>
                                        <strong>GRANTED</strong>
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3 -->
                    <div class="testimonial-slide">
                        <div class="slide-left">
                            <i class="fas fa-quote-left quote-icon"></i>
                            <p class="review-text">“Ask Visa saved my last-minute Singapore plans. The e-visa came faster than expected.”</p>
                            <div class="reviewer-info">
                                <div class="reviewer-avatar" style="background: #10b981;">AS</div>
                                <div>
                                    <strong>Arjun S.</strong>
                                    <span>Delhi</span>
                                </div>
                            </div>
                        </div>
                        <div class="slide-right">
                            <div class="stamp-container">
                                <div class="visa-stamp" style="border-color: #10b981; color: #10b981;">
                                    <div class="stamp-inner">
                                        <span>SG</span>
                                        <strong>VERIFIED</strong>
                                        <i class="fas fa-passport"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Script merged below -->


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll('.testimonial-slide');
            const track = document.getElementById('track');
            let currentSlide = 0;
            const intervalTime = 5000; // 5 seconds

            function nextSlide() {
                // Remove active class from current
                slides[currentSlide].classList.remove('active');
                
                // Calculate next
                currentSlide = (currentSlide + 1) % slides.length;
                
                // Add active class to next (triggers CSS transition)
                slides[currentSlide].classList.add('active');
            }

            // Auto Play
            setInterval(nextSlide, intervalTime);
        });
    </script>

    <!-- ========== CONTACT US (refined) ========== -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="contact-wrapper animate-up">
                <!-- Left: form -->
                <div class="contact-form-container">
                    <h2>Drop us a line</h2>
                    <p>We reply within 2 hours, usually sooner.</p>
                    <form action="#" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Your name</label>
                                <input type="text" name="name" placeholder="e.g. Alex Chen" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="alex@example.com" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone (with country code)</label>
                                <input type="tel" name="phone" placeholder="+91 98765 43210" required>
                            </div>
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" placeholder="Visa enquiry" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Your question</label>
                            <textarea name="message" placeholder="I need help with..." required></textarea>
                        </div>
                        <button type="submit" class="send-btn">Send message <i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
                <!-- Right: elegant red panel with quote -->
                <div class="contact-image-container">
                    <i class="fas fa-comment-dots"></i>
                    <div class="contact-quote">
                        “Don’t hesitate<br>just ask”
                        <span>— we’re here 24/7</span>
                    </div>
                </div>
            </div>

            <!-- contact cards -->
            <div class="contact-cards">
                <div class="contact-card animate-up" style="animation-delay: 0.1s;">
                    <div class="icon-box"><i class="fas fa-envelope"></i></div>
                    <h3>Email</h3>
                    <p>hello@askvisa.com</p>
                    <p>support@askvisa.com</p>
                </div>
                <div class="contact-card animate-up" style="animation-delay: 0.2s;">
                    <div class="icon-box"><i class="fas fa-phone-alt"></i></div>
                    <h3>Call / WhatsApp</h3>
                    <p>+91 78807 89486</p>
                    <p>+91 78629 92570</p>
                </div>
                <div class="contact-card animate-up" style="animation-delay: 0.3s;">
                    <div class="icon-box"><i class="fas fa-share-nodes"></i></div>
                    <h3>Social</h3>
                    <p>Let’s connect · travel tips</p>
                    <div class="social-icons">
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== MODAL (Country detail) ========== -->
    <div id="countryModal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-modal" onclick="closeCountryModal()">&times;</button>
            <div class="modal-header">
                <img id="modalImg" src="" alt="destination preview" class="modal-hero-img">
            </div>
            <div class="modal-body">
                <h2 id="modalTitle">Country</h2>
                <div class="modal-info-row">
                    <span id="modalVisaType"><i class="fas fa-passport"></i> Visa type</span>
                    <span id="modalTime"><i class="far fa-clock"></i> Processing</span>
                </div>
                <div style="background: #FEF2F2; border-radius: 16px; padding: 12px 20px; margin-bottom: 16px;">
                    <i class="fas fa-shield-alt" style="color: var(--primary);"></i>
                    <span style="font-weight:600;">99.3% approval</span> — we only ask for exactly what’s needed.
                </div>
                <div class="modal-section">
                    <h3 style="font-size: 1.2rem; margin-bottom: 16px;"><i class="fas fa-list-check"></i> Documents
                        required</h3>
                    <ul id="modalDocs" class="docs-list"></ul>
                </div>
                <div class="modal-actions">
                    <a id="applyBtn" href="#" class="apply-btn">
                        Apply now <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== FOOTER with LOGO from assets/ask-visa-logo-final.png ========== -->
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="assets/ask-visa-logo-final.png" alt="Ask Visa Logo">
            </div>
            <div class="footer-links">
                <div class="footer-col">
                    <h4 style="color:white; margin-bottom: 20px;">Company</h4>
                    <a href="#">About us</a>
                    <a href="#">Careers</a>
                    <a href="#">Blog</a>
                </div>
                <div class="footer-col">
                    <h4 style="color:white; margin-bottom: 20px;">Support</h4>
                    <a href="#contact">Contact</a>
                    <a href="#">FAQs</a>
                    <a href='privacy_policy.php'>Privacy policy</a>
                    <a href="terms_of_use.php">Terms of use</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Ask Visa. All rights reserved. | ✈️ Visa assistance simplified.</p>
        </div>
    </footer>

    <!-- ========== MODAL JAVASCRIPT ========== -->
    <script>
        function openCountryModal(country, visaInfo, time, imgSrc, docs) {
            document.getElementById('modalTitle').textContent = country;
            document.getElementById('modalVisaType').innerHTML = '<i class="fas fa-passport"></i> ' + visaInfo;
            document.getElementById('modalTime').innerHTML = '<i class="far fa-clock"></i> ' + time;
            document.getElementById('modalImg').src = imgSrc;
            document.getElementById('applyBtn').href = 'index.php?country=' + encodeURIComponent(country);

            const docsList = document.getElementById('modalDocs');
            docsList.innerHTML = '';
            docs.forEach(doc => {
                const li = document.createElement('li');
                li.innerHTML = '<i class="fas fa-check-circle"></i> ' + doc;
                docsList.appendChild(li);
            });

            const modal = document.getElementById('countryModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);

            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = (window.innerWidth - document.documentElement.clientWidth) + 'px';
        }

        function closeCountryModal() {
            const modal = document.getElementById('countryModal');
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 250);
        }

        // close on overlay click
        document.getElementById('countryModal').addEventListener('click', function (e) {
            if (e.target === this) closeCountryModal();
        });

        // close with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('countryModal');
                if (modal.classList.contains('active')) closeCountryModal();
            }
        });
    </script>
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobile-menu">
        <div class="mobile-nav-links-container">
            <button class="close-mobile-menu" id="close-mobile-menu">&times;</button>
            <a href="#">Home</a>
            <a href="#trending">Services</a>
            <a href="#contact">Contact</a>
        </div>
    </div>

    <script>
        // Force scroll to top on reload to ensure "Home" start
        if (history.scrollRestoration) {
            history.scrollRestoration = 'manual';
        }
        window.onbeforeunload = function () {
            window.scrollTo(0, 0);
        }
        window.scrollTo(0, 0);

        // Mobile Menu Logic
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const closeMobileBtn = document.getElementById('close-mobile-menu');
        const mobileLinks = document.querySelectorAll('.mobile-nav-links-container a');

        function toggleMenu() {
            if (!mobileMenu) return;
            mobileMenu.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        }

        if (hamburgerBtn) hamburgerBtn.addEventListener('click', toggleMenu);
        if (closeMobileBtn) closeMobileBtn.addEventListener('click', toggleMenu);

        // Close when clicking a link
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                toggleMenu();
            });
        });

        // Close when clicking outside content (on overlay)
        if (mobileMenu) {
            mobileMenu.addEventListener('click', (e) => {
                if (e.target === mobileMenu) {
                    toggleMenu();
                }
            });
        }

        // Entrance Animation Logic
        window.addEventListener('load', () => {
            const overlay = document.getElementById('entrance-overlay');
            const preloaderLogo = document.getElementById('preloader-logo');
            const welcomeText = document.getElementById('welcome-text');
            const navLogo = document.getElementById('nav-logo-real');

            // 1. Initial Setup: Lock scroll and hide nav logo
            document.body.style.overflow = 'hidden';
            navLogo.style.opacity = '0';

            // 2. Timeline
            setTimeout(() => {
                // Show Welcome Text
                welcomeText.style.opacity = '1';
                welcomeText.style.transform = 'translateY(0)';
            }, 300);

            setTimeout(() => {
                // Hide Welcome Text
                welcomeText.style.opacity = '0';
                welcomeText.style.transform = 'translateY(-20px)';
            }, 2000);

            setTimeout(() => {
                // 3. FLIP Animation for Logo
                const startRect = preloaderLogo.getBoundingClientRect();
                const endRect = navLogo.getBoundingClientRect();

                // Calculate scales
                const scaleX = endRect.width / startRect.width;
                const scaleY = endRect.height / startRect.height;
                const scale = Math.min(scaleX, scaleY); // Maintain aspect ratio based on fit

                // Calculate translation (center to center)
                const startCenterX = startRect.left + startRect.width / 2;
                const startCenterY = startRect.top + startRect.height / 2;
                const endCenterX = endRect.left + endRect.width / 2;
                const endCenterY = endRect.top + endRect.height / 2;

                const translateX = endCenterX - startCenterX;
                const translateY = endCenterY - startCenterY;

                // Apply transition
                preloaderLogo.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;

                // Fade out white background
                overlay.style.backgroundColor = 'rgba(255,255,255,0)';

            }, 2800);

            setTimeout(() => {
                // 4. Swap Logos and Cleanup
                navLogo.style.transition = 'opacity 0.3s ease';
                navLogo.style.opacity = '1';
                preloaderLogo.style.opacity = '0';

                // Restore scroll
                document.body.style.overflow = '';
                overlay.style.pointerEvents = 'none';

                setTimeout(() => {
                    overlay.remove();
                }, 1000);
            }, 3800); // 2800 + 1200 transition time approx
        });

        // Scroll Observer for Cards
        const observerOptions = {
            threshold: 0.1, // Trigger when 10% of the card is visible (more reliable)
            rootMargin: "0px"
        };

        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                } else {
                    // Remove class when out of view to reset animation (Replay)
                    entry.target.classList.remove('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card').forEach(card => {
            cardObserver.observe(card);
        });


    </script>
</body>

</html>