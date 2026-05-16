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
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/ask-visa-red.png">
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
            --primary: #0ea5e9;
            --primary-glow: rgba(14, 165, 233, 0.4);
            --secondary: #020617;
            --aviation-blue: #0ea5e9;
            --aviation-deep: #0369a1;
            --aviation-soft: rgba(14, 165, 233, 0.1);
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #FFFFFF;
            --text-dim: rgba(255, 255, 255, 0.6);
            --radius-xl: 32px;
            --radius-full: 9999px;
            --transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #020617;
            color: white;
            line-height: 1.6;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ---------- CLOUD PARALLAX & PLANE ---------- */
        .sky-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
            /* New Photographic Background */
            background-image: url('assets/Background.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }

        /* Dark overlay for text readability */
        .sky-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(2, 6, 23, 0.75); /* Dark aviation blue overlay */
            z-index: -1; /* Keep below clouds and planes, but above image */
        }

        .cloud {
            position: absolute;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            animation: cloudMove linear infinite;
        }

        @keyframes cloudMove {
            from { transform: translateX(-200px); }
            to { transform: translateX(120vw); }
        }

        .plane-silhouette {
            position: absolute;
            top: 20%;
            left: -100px;
            width: 40px;
            height: 40px;
            color: rgba(255, 255, 255, 0.2);
            filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.5));
            animation: planeTravel 30s linear infinite;
            z-index: 1;
        }

        @keyframes planeTravel {
            0% { transform: translate(0, 0) rotate(15deg); opacity: 0; }
            5% { opacity: 0.5; }
            95% { opacity: 0.5; }
            100% { transform: translate(120vw, -10vh) rotate(15deg); opacity: 0; }
        }

        /* Flight Progress Bar */
        .flight-progress-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.05);
            z-index: 2000;
        }

        #flight-progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(to right, var(--aviation-blue), var(--aviation-blue));
            box-shadow: 0 0 10px var(--aviation-blue);
            transition: width 0.2s ease-out;
            position: relative;
        }

        #flight-progress-bar::after {
            content: '✈';
            position: absolute;
            right: -10px;
            top: -8px;
            font-size: 16px;
            color: white;
            text-shadow: 0 0 10px var(--aviation-blue);
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
            background: linear-gradient(135deg, var(--aviation-blue), var(--aviation-deep));
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

        /* ---------- HERO SECTION (Aura Aviation) ---------- */
        .hero {
            padding: 240px 40px 120px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .hero-badge {
            background: rgba(14, 165, 233, 0.1);
            padding: 12px 24px;
            border-radius: var(--radius-xl);
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid var(--aviation-blue);
            margin-bottom: 32px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--aviation-blue);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
        }

        .hero h1 {
            font-size: 6rem;
            line-height: 0.85;
            margin-bottom: 24px;
            font-weight: 950;
            letter-spacing: -4px;
        }

        .hero h1 span.takeoff {
            color: var(--aviation-blue);
            text-shadow: 0 0 50px rgba(14, 165, 233, 0.6);
            display: inline-block;
            animation: textTakeoff 2s ease-out forwards;
        }

        @keyframes textTakeoff {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .hero-subhead {
            font-size: 1.5rem;
            color: var(--text-dim);
            margin-bottom: 60px;
            max-width: 700px;
            font-weight: 500;
        }

        /* Flight Search Portal (Cockpit Widget) */
        .search-container {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            padding: 10px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            max-width: 800px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5), 
                        inset 0 0 20px rgba(14, 165, 233, 0.1);
            position: relative;
        }

        .search-group {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .search-group:last-of-type {
            border-right: none;
        }

        .search-group label {
            position: absolute;
            top: -25px;
            left: 24px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--aviation-blue);
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .search-icon {
            font-size: 1.25rem;
            color: var(--aviation-blue);
        }

        .search-container select {
            flex: 1;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            outline: none;
            appearance: none;
            cursor: pointer;
        }

        .search-btn {
            background: linear-gradient(135deg, var(--aviation-blue), #0369a1);
            padding: 22px 50px;
            border-radius: 32px;
            font-weight: 900;
            font-size: 1.1rem;
            border: none;
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .search-btn:hover {
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 15px 40px rgba(14, 165, 233, 0.6);
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
            color: var(--aviation-deep);
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

        /* ---------- TRENDING DESTINATIONS (Boarding Pass) ---------- */
        .trending {
            padding: 120px 40px;
            background: transparent;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .card {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            transition: var(--transition);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--aviation-blue);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), 
                        0 0 30px rgba(14, 165, 233, 0.2);
        }

        .card-img-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: saturate(1.2) brightness(0.8);
            transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .card:hover .card-img {
            transform: scale(1.1) rotate(1deg);
        }

        /* Perforated Divider */
        .card::after {
            content: '';
            position: absolute;
            top: 185px;
            left: -10px;
            right: -10px;
            height: 20px;
            background-image: radial-gradient(circle, #020617 8px, transparent 8px);
            background-size: 30px 20px;
            z-index: 10;
        }

        .card-content {
            padding: 30px;
            background: rgba(255, 255, 255, 0.02);
            position: relative;
        }

        .boarding-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }

        .flight-code {
            color: var(--aviation-blue);
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: 2px;
        }

        .gate-info {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-dim);
            font-weight: 700;
        }

        .country-name {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 5px;
            letter-spacing: -1px;
        }

        .visa-info {
            color: var(--aviation-blue);
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .boarding-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(14, 165, 233, 0.05);
            padding: 15px 20px;
            border-radius: 12px;
        }

        .processing-time {
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .processing-time i {
            color: var(--aviation-blue);
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
            color: var(--aviation-blue);
            font-size: 2.5rem;
            transition: var(--transition);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
        }

        .step-item:hover .step-icon {
            transform: translateY(-10px) rotate(-8deg);
            background: var(--aviation-blue);
            color: white;
            border-color: var(--aviation-blue);
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
            background: radial-gradient(circle at 70% 30%, var(--aviation-deep), transparent 40%);
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
            color: var(--aviation-blue);
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
            background: linear-gradient(135deg, var(--aviation-blue), var(--aviation-deep));
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


        /* ---------- CONTACT SECTION (Ground Control) ---------- */
        .contact-section {
            padding: 120px 40px;
            background: transparent;
        }

        .contact-wrapper {
            display: flex;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-radius: 40px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.4);
            margin-bottom: 80px;
        }

        .contact-form-container {
            flex: 1.2;
            padding: 60px;
        }

        .form-header {
            margin-bottom: 40px;
            text-align: left;
        }

        .form-header h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--aviation-blue);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.8rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--aviation-blue);
            text-transform: uppercase;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 18px 24px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--aviation-blue);
            background: rgba(14, 165, 233, 0.05);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
        }

        .send-btn {
            background: linear-gradient(135deg, var(--aviation-blue), #0369a1);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1rem;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .contact-info-panel {
            flex: 0.8;
            background: linear-gradient(135deg, #0f172a, #1e1b4b);
            padding: 60px;
            display: flex;
            flex-direction: column;
            gap: 40px;
            border-left: 1px solid rgba(255, 255, 255, 0.05);
            text-align: left;
        }

        .info-item {
            display: flex;
            gap: 20px;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: rgba(14, 165, 233, 0.1);
            border: 1px solid var(--aviation-blue);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--aviation-blue);
            font-size: 1.25rem;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .info-item:hover .info-icon {
            background: var(--aviation-blue);
            color: white;
            transform: scale(1.1);
        }

        .info-text h4 {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--text-dim);
            margin-bottom: 4px;
        }

        .info-text p {
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* ---------- MODAL (Aura Aviation) ---------- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(2, 6, 23, 0.9);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .modal-content {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(40px);
            width: 95%;
            max-width: 650px;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 50px 100px rgba(0, 0, 0, 0.6);
            transform: scale(0.9) translateY(40px);
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            position: relative;
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
            filter: brightness(0.8);
        }

        .modal-body {
            padding: 48px;
        }

        .modal-body h2 {
            font-size: 2.8rem;
            font-weight: 900;
            margin-bottom: 8px;
            letter-spacing: -2px;
        }

        .apply-btn {
            display: block;
            background: linear-gradient(135deg, var(--aviation-blue), #0369a1);
            color: white;
            padding: 22px;
            border-radius: 20px;
            font-weight: 900;
            font-size: 1.1rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 15px 30px rgba(14, 165, 233, 0.4);
            text-decoration: none;
            transition: var(--transition);
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(14, 165, 233, 0.6);
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


        /* ---------- FOOTER (Landing Strip) ---------- */
        footer {
            padding: 100px 40px 60px;
            background: linear-gradient(to top, #020617, transparent);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: 80px;
        }

        .footer-logo {
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -2px;
            margin-bottom: 40px;
            display: block;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 80px;
            text-align: left;
        }

        .footer-col h4 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 24px;
            color: var(--aviation-blue);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: var(--text-dim);
            font-size: 0.95rem;
            transition: var(--transition);
            text-decoration: none;
        }

        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }

        .footer-bottom {
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
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
    <div class="flight-progress-container"><div id="flight-progress-bar"></div></div>

    <div class="sky-container">
        <div class="cloud" style="width: 400px; height: 400px; top: 10%; animation-duration: 60s;"></div>
        <div class="cloud" style="width: 600px; height: 600px; top: 40%; animation-duration: 90s; left: 20%;"></div>
        <div class="cloud" style="width: 300px; height: 300px; top: 70%; animation-duration: 45s; left: 50%;"></div>
        <div class="plane-silhouette">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
        </div>
    </div>

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
    <header class="hero animate-up">
        <div class="hero-badge">
            <i class="fas fa-plane-departure"></i> Global Flight Readiness: Active
        </div>
        <h1 style="animation-delay: 0.1s;">Your Visa is Ready for <br><span class="takeoff">Takeoff</span></h1>
        <p class="hero-subhead" style="animation-delay: 0.2s;">Simple, fast, and 99.3% guaranteed. We handle the paperwork so you can focus on the journey.</p>

        <form action="index.php" method="GET" class="search-container" style="animation-delay: 0.3s;">
            <div class="search-group">
                <label>Destination City</label>
                <i class="fas fa-map-marker-alt search-icon"></i>
                <select name="country" required>
                    <option value="" disabled selected>Where are you flying?</option>
                    <option value="Thailand">Bangkok, Thailand</option>
                    <option value="Dubai">Dubai, UAE</option>
                    <option value="Singapore">Singapore City, Singapore</option>
                    <option value="Vietnam">Hanoi, Vietnam</option>
                    <option value="Malaysia">Kuala Lumpur, Malaysia</option>
                </select>
            </div>
            
            <button type="submit" class="search-btn">
                <span>Check Status</span> <i class="fas fa-passport"></i>
            </button>
        </form>

        <div class="trust-strip" style="animation-delay: 0.4s;">
            <div class="trust-item"><i class="fas fa-bolt"></i> 24h Approval</div>
            <div class="trust-item"><i class="fas fa-shield-alt"></i> 99.3% Success</div>
            <div class="trust-item"><i class="fas fa-globe"></i> 50+ Countries</div>
        </div>
    </header>

    <script>
        // Nav & Flight Progress Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('main-nav');
            const progressBar = document.getElementById('flight-progress-bar');
            
            // Flight Progress logic
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            if (progressBar) progressBar.style.width = scrolled + "%";

            // Nav logic
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
                    </div>
                    <div class="card-content">
                        <div class="boarding-header">
                            <span class="flight-code">FLIGHT TH-701</span>
                            <span class="gate-info">GATE A1</span>
                        </div>
                        <div class="country-name">🇹🇭 Thailand</div>
                        <p class="visa-info">Digital Tourist e-Visa</p>
                        <div class="boarding-footer">
                            <div class="processing-time"><i class="far fa-clock"></i> 4 DAYS</div>
                            <i class="fas fa-barcode" style="opacity: 0.3; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
                <!-- Dubai -->
                <div class="card"
                    onclick="openCountryModal('Dubai (UAE)', '30/60 Days Tourist Visa', '2 days', 'https://images.unsplash.com/photo-1546412414-e1885259563a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80', ['Passport front & back', 'Passport size photo', 'Travel insurance (optional)'])">
                    <div class="card-img-container">
                        <img src="https://images.unsplash.com/photo-1546412414-e1885259563a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                            class="card-img" alt="Dubai" loading="lazy">
                    </div>
                    <div class="card-content">
                        <div class="boarding-header">
                            <span class="flight-code">FLIGHT DXB-202</span>
                            <span class="gate-info">GATE B12</span>
                        </div>
                        <div class="country-name">🇦🇪 Dubai (UAE)</div>
                        <p class="visa-info">Express Tourist Entry</p>
                        <div class="boarding-footer">
                            <div class="processing-time"><i class="far fa-clock"></i> 2 DAYS</div>
                            <i class="fas fa-barcode" style="opacity: 0.3; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
                <!-- Singapore -->
                <div class="card"
                    onclick="openCountryModal('Singapore', 'Electronic Visa (e-Visa)', '5 days', 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80', ['Passport front & back', 'Passport photo', 'Hotel booking', 'Flight itinerary'])">
                    <div class="card-img-container">
                        <img src="https://images.unsplash.com/photo-1525625293386-3f8f99389edd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                            class="card-img" alt="Singapore" loading="lazy">
                    </div>
                    <div class="card-content">
                        <div class="boarding-header">
                            <span class="flight-code">FLIGHT SG-404</span>
                            <span class="gate-info">GATE C3</span>
                        </div>
                        <div class="country-name">🇸🇬 Singapore</div>
                        <p class="visa-info">Business & Tourist Pass</p>
                        <div class="boarding-footer">
                            <div class="processing-time"><i class="far fa-clock"></i> 5 DAYS</div>
                            <i class="fas fa-barcode" style="opacity: 0.3; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
                <!-- Vietnam -->
                <div class="card"
                    onclick="openCountryModal('Vietnam', '30 Days Tourist Visa', '3 days', 'https://images.unsplash.com/photo-1528127269322-539801943592?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80', ['Passport front & back', 'Passport photo', 'Entry date'])">
                    <div class="card-img-container">
                        <img src="https://images.unsplash.com/photo-1528127269322-539801943592?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                            class="card-img" alt="Vietnam" loading="lazy">
                    </div>
                    <div class="card-content">
                        <div class="boarding-header">
                            <span class="flight-code">FLIGHT VN-109</span>
                            <span class="gate-info">GATE D7</span>
                        </div>
                        <div class="country-name">🇻🇳 Vietnam</div>
                        <p class="visa-info">Single Entry Tourist</p>
                        <div class="boarding-footer">
                            <div class="processing-time"><i class="far fa-clock"></i> 3 DAYS</div>
                            <i class="fas fa-barcode" style="opacity: 0.3; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== HOW IT WORKS (Flight Journey) ========== -->
    <section class="how-it-works">
        <div class="container">
            <h2 class="section-title animate-up">Your Journey to Approval</h2>
            <p class="section-subhead animate-up">Follow the flight path to your destination</p>
            <div class="steps-grid">
                <div class="step-item animate-up" style="animation-delay: 0.1s;">
                    <div class="step-icon"><i class="fas fa-ticket-alt"></i></div>
                    <h3>1. Digital Check-in</h3>
                    <p>Select your flight destination and review the specific requirements instantly.</p>
                </div>
                <div class="step-item animate-up" style="animation-delay: 0.15s;">
                    <div class="step-icon"><i class="fas fa-user-shield"></i></div>
                    <h3>2. Security Scan</h3>
                    <p>Upload your passport and photos. Our system verifies everything for takeoff.</p>
                </div>
                <div class="step-item animate-up" style="animation-delay: 0.2s;">
                    <div class="step-icon"><i class="fas fa-plane-arrival"></i></div>
                    <h3>3. Cleared for Takeoff</h3>
                    <p>Receive your approved e-visa via priority dispatch. You’re ready to fly!</p>
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

    <!-- ========== CONTACT SECTION (Ground Control) ========== -->
    <section class="contact-section" id="contact">
        <div class="container">
            <h2 class="section-title animate-up" style="text-align: center;">Ground Control</h2>
            <p class="section-subhead animate-up" style="text-align: center; margin-left: auto; margin-right: auto;">Need assistance? Our flight support team is on standby 24/7.</p>
            
            <div class="contact-wrapper animate-up">
                <div class="contact-form-container">
                    <div class="form-header">
                        <h3>Lodge Query</h3>
                        <p>Support Ticket #AV - Active</p>
                    </div>
                    <form action="#" class="contact-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Passenger Name</label>
                                <input type="text" placeholder="Full Name" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" placeholder="example@mail.com" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Destination Country</label>
                            <input type="text" placeholder="e.g. Thailand" required>
                        </div>
                        <div class="form-group" style="margin-top: 20px;">
                            <label>Detailed Message</label>
                            <textarea rows="4" placeholder="How can we help with your visa?"></textarea>
                        </div>
                        <button type="submit" class="send-btn">
                            <span>Dispatch Message</span> <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>

                <div class="contact-info-panel">
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-headset"></i></div>
                        <div class="info-text">
                            <h4>24/7 Hotline</h4>
                            <p>+1 (800) AVIATION</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-envelope-open-text"></i></div>
                        <div class="info-text">
                            <h4>Email Support</h4>
                            <p>fly@askvisa.com</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="info-text">
                            <h4>Global HQ</h4>
                            <p>Terminal 3, Sky City</p>
                        </div>
                    </div>
                    
                    <div class="social-links" style="margin-top: auto; display: flex; gap: 15px;">
                        <a href="#" class="info-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="info-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="info-icon"><i class="fab fa-linkedin"></i></a>
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

    <!-- ========== FOOTER (Landing Strip) ========== -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <span class="footer-logo">ASK<span style="color: var(--aviation-blue);">VISA</span></span>
                    <p style="color: var(--text-dim); max-width: 300px;">Providing premium visa assistance for the modern traveller. Fast, secure, and globally trusted.</p>
                </div>
                <div class="footer-col">
                    <h4>Destinations</h4>
                    <ul class="footer-links">
                        <li><a href="#">Thailand</a></li>
                        <li><a href="#">Dubai (UAE)</a></li>
                        <li><a href="#">Singapore</a></li>
                        <li><a href="#">Vietnam</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Company</h4>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press Office</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Settings</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Ask Visa Aviation Services. All flight paths cleared.</p>
                <div style="display: flex; gap: 30px;">
                    <a href="#">Status: Green <i class="fas fa-circle" style="color: #10b981; font-size: 8px;"></i></a>
                    <a href="#">System Integrity: 100%</a>
                </div>
            </div>
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

        // Scroll Logic for Navbar and Progress Bar
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('main-nav');
            const progressBar = document.getElementById('flight-progress-bar');
            
            // Navbar background transition
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            // Flight Progress Bar calculation
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            if (progressBar) {
                progressBar.style.width = scrolled + "%";
            }
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