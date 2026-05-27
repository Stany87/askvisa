<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Visa Processing Partner — AskVisa</title>
    <meta name="description" content="India's trusted B2B visa processing platform for travel agents. Real-time tracking, document management, dashboard & agent-only pricing.">
    <link rel="icon" href="../assets/ask-visa-logo-final red.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 25%, #f0f4f8 50%, #fff 75%, #f8f9fa 100%);
            background-size: 400% 400%;
            animation: bgShift 20s ease-in-out infinite;
            color: #111827;
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        @keyframes bgShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        a { color: #dc2626; text-decoration: none; }
        a:hover { color: #b91c1c; }
        img { max-width: 100%; height: auto; }
        .container { max-width: 1120px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 2; }
        .section { padding: 100px 0; }
        .section-title {
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            margin-bottom: 10px;
            line-height: 1.15;
        }
        .section-sub {
            color: #6b7280;
            font-size: 1.02rem;
            margin-bottom: 48px;
            max-width: 560px;
            line-height: 1.6;
        }
        .section-cta-row {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .section-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        }
        .section-cta.primary {
            background: #dc2626;
            color: #fff;
            box-shadow: 0 8px 20px rgba(220,38,38,0.2);
        }
        .section-cta.primary:hover {
            color: #fff;
            background: #b91c1c;
            transform: translateY(-1px);
        }
        .section-cta.secondary {
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .section-cta.secondary:hover {
            color: #111827;
            border-color: #9ca3af;
            transform: translateY(-1px);
        }
        .badge-soon {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 600;
            color: #9ca3af;
            border: 1px solid #e5e7eb;
            padding: 3px 10px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .badge-live {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 600;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            padding: 3px 10px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1), transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .fade-up.visible { opacity: 1; transform: translateY(0); }
        .parallax-layer { will-change: transform; }
        .scroll-progress {
            position: fixed; top: 0; left: 0;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #f87171);
            z-index: 200;
            width: 0%;
            transition: width 0.1s linear;
            pointer-events: none;
        }
        .mouse-glow {
            position: fixed; pointer-events: none; z-index: 9999;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(220,38,38,0.07) 0%, transparent 70%);
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 0.4s;
        }
        .mouse-glow.visible { opacity: 1; }
        /* -- SVG icon styles -- */
        .svg-icon { display: inline-block; vertical-align: middle; }

        /* -- Nav -- */
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(255,255,255,0.92);
            border-bottom: 1px solid rgba(229,231,235,0.8);
            z-index: 100; height: 68px;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 10px 30px rgba(17,24,39,0.06);
        }
        nav::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 10%; right: 10%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #dc2626, #f87171, transparent);
            opacity: 0.4;
        }
        .nav-inner {
            max-width: 1120px; margin: 0 auto; padding: 0 24px;
            height: 100%; display: flex; align-items: center; justify-content: space-between;
        }
        .nav-logo {
            display: flex; align-items: center; gap: 12px;
            font-weight: 800; font-size: 1.05rem; color: #111827;
            text-decoration: none;
        }
        .nav-logo-img {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #fecaca;
            box-shadow: 0 6px 16px rgba(220,38,38,0.2);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .nav-logo:hover .nav-logo-img {
            transform: translateY(-1px) scale(1.03);
            box-shadow: 0 10px 24px rgba(220,38,38,0.25);
        }
        .nav-brand { display: flex; align-items: center; gap: 6px; }
        .nav-brand-badge {
            font-size: 0.6rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            padding: 2px 10px; border-radius: 4px;
            background: #dc2626; color: #fff; line-height: 1.6;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 26px;
            background: rgba(255,255,255,0.86);
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            padding: 6px 8px 6px 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
        }
        .nav-links a {
            font-size: 0.85rem; font-weight: 500; color: #4b5563;
            position: relative; padding: 4px 0;
            transition: color 0.2s;
        }
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0;
            width: 0; height: 2px;
            background: #dc2626;
            transition: width 0.25s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .nav-links a:hover { color: #111827; }
        .nav-links a:hover::after { width: 100%; }
        .btn-nav-login {
            font-size: 0.85rem;
            font-weight: 600;
            color: #dc2626;
            background: transparent;
            padding: 4px 0;
            border-radius: 0;
            border: none;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-nav-login:hover {
            color: #b91c1c;
            transform: none;
            box-shadow: none;
            filter: none;
        }
        .btn-nav-login:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .nav-links a.btn-nav-login,
        .nav-links a.btn-nav-login:visited {
            color: #dc2626;
        }
        .nav-links a.btn-nav-login:hover {
            color: #b91c1c;
        }
        /* -- HERO -- */
        .hero {
            position: relative;
            padding: 160px 0 130px;
            background: #0b1120;
            overflow: hidden;
            text-align: center;
        }
        .hero-bg {
            position: absolute; inset: 0;
            background: url('../assets/Background.jpeg') center 30% / cover no-repeat;
            opacity: 0.3;
        }
        .hero-bg::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(11,17,32,0.65) 0%, rgba(11,17,32,0.92) 100%);
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0; right: 0;
            height: 80px;
            background: #f8f9fa;
            clip-path: polygon(0 40%, 100% 0, 100% 100%, 0% 100%);
            z-index: 2;
            pointer-events: none;
        }
        .hero > .container { position: relative; z-index: 5; }
        .hero h1 {
            font-size: 2.8rem; font-weight: 900; letter-spacing: -0.035em;
            line-height: 1.1; margin-bottom: 16px;
            color: #fff;
        }
        .hero h1 span { background: linear-gradient(135deg, #f87171, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero p {
            font-size: 1.08rem;
            color: rgba(255,255,255,0.6);
            max-width: 540px;
            margin: 0 auto 36px;
        }
        .hero-stats {
            display: flex; justify-content: center;
            gap: 56px; margin-bottom: 40px; flex-wrap: wrap;
        }
        .hero-stat { text-align: center; }
        .hero-stat-num {
            font-size: 1.8rem; font-weight: 800;
            color: #fff;
            text-shadow: 0 0 30px rgba(220,38,38,0.2);
        }
        .hero-stat-label {
            font-size: 0.75rem; font-weight: 600;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase; letter-spacing: 1px;
        }
        .search-wrap { max-width: 620px; margin: 0 auto; }
        .hero-cta-row {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 0 0 26px;
        }
        .hero-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 10px;
            font-size: 0.86rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        }
        .hero-cta.primary {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: #fff;
            box-shadow: 0 10px 24px rgba(220,38,38,0.3);
        }
        .hero-cta.primary:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(220,38,38,0.34);
        }
        .hero-cta.secondary {
            border: 1px solid rgba(255,255,255,0.26);
            color: #fff;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .hero-cta.secondary:hover {
            color: #fff;
            background: rgba(255,255,255,0.12);
            transform: translateY(-1px);
        }
        .hero-product-shot {
            max-width: 860px;
            margin: 0 auto 28px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.18);
            box-shadow: 0 30px 70px rgba(2,6,23,0.45), inset 0 1px 0 rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            position: relative;
            z-index: 3;
        }
        .hero-product-shot img {
            display: block;
            width: 100%;
            height: auto;
        }
        .search-box {
            display: flex;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 14px;
            overflow: hidden;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.08);
            transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
        }
        .search-box:focus-within {
            border-color: rgba(220,38,38,0.4);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 8px 48px rgba(220,38,38,0.12), inset 0 1px 0 rgba(255,255,255,0.12);
        }
        .search-box input {
            flex: 1; border: none; padding: 16px 22px;
            font-size: 0.95rem; font-family: inherit;
            outline: none; color: #fff;
            background: transparent;
        }
        .search-box input::placeholder { color: rgba(255,255,255,0.35); }
        .search-box button {
            background: #dc2626; color: #fff; border: none;
            padding: 16px 32px; font-size: 0.88rem; font-weight: 600;
            font-family: inherit; cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            transition: background 0.15s;
        }
        .search-box button:hover { background: #b91c1c; }
        .search-chips { display: flex; justify-content: center; gap: 8px; margin-top: 16px; flex-wrap: wrap; }
        .chip {
            font-size: 0.78rem; color: rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 5px 16px; border-radius: 20px;
            cursor: pointer; transition: all 0.2s;
        }
        .chip:hover { border-color: #dc2626; color: #fff; background: rgba(220,38,38,0.2); }

        /* -- Hero SVG decorations -- */
        .hero-svg-deco {
            position: absolute;
            pointer-events: none;
            z-index: 1;
        }
        .svg-visa-deco {
            top: 12%; left: 5%;
            width: 80px;
            color: rgba(220,38,38,0.15);
            animation: visaDrift 14s ease-in-out infinite;
        }
        .svg-passport-deco {
            bottom: 35%; right: 6%;
            width: 70px;
            color: rgba(255,255,255,0.08);
            animation: passportBob 10s ease-in-out infinite;
        }
        @keyframes visaDrift {
            0%, 100% { transform: translateY(0) rotate(-3deg); opacity: 0.4; }
            50% { transform: translateY(-14px) rotate(2deg); opacity: 0.8; }
        }
        @keyframes passportBob {
            0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; }
            50% { transform: translateY(-8px) scale(1.05); opacity: 0.6; }
        }

        /* -- Hero world map -- */
        .hero-map {
            position: absolute;
            top: -145px; left: 0; right: 0;
            margin: 0 auto;
            width: 1080px; max-width: 140%;
            opacity: 0.22;
            pointer-events: none;
            z-index: 4;
            filter: drop-shadow(0 0 40px rgba(220,38,38,0.06));
            mix-blend-mode: screen;
        }
        .hero-map::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 40%;
            background: linear-gradient(0deg, rgba(11,17,32,0.6) 0%, transparent 100%);
            pointer-events: none;
        }

        /* -- Trust Bar -- */
        .trust-bar {
            padding: 32px 0;
            background: #fff;
            position: relative;
        }
        .trust-bar::before {
            content: '';
            position: absolute;
            top: -40px; left: 0; right: 0;
            height: 40px;
            background: #f8f9fa;
            clip-path: polygon(0 100%, 100% 0, 100% 100%, 0% 100%);
            pointer-events: none;
        }
        .trust-bar::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(220,38,38,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }
        .trust-bar .container { position: relative; z-index: 1; }
        .trust-inner {
            display: flex; justify-content: center;
            align-items: center; gap: 48px; flex-wrap: wrap;
        }
        .trust-item {
            display: flex; align-items: center; gap: 10px;
            font-size: 0.82rem; font-weight: 500; color: #6b7280;
        }
        .trust-item svg { color: #dc2626; width: 16px; height: 16px; flex-shrink: 0; }
        .trust-item strong { color: #111827; font-weight: 700; }

        /* -- Search Results -- */
        .results-wrap { display: none; padding: 60px 0 30px; }
        .results-wrap.visible { display: block; }
        .results-header {
            display: flex; align-items: center; gap: 16px; margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .results-header .flag { font-size: 2.4rem; line-height: 1; }
        .results-header .rh-info { flex: 1; }
        .results-header .rh-info h2 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
        .results-header .rh-info p { font-size: 0.82rem; color: #6b7280; margin-top: 2px; }
        .results-header .rh-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.75rem; font-weight: 600; color: #16a34a;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            padding: 4px 14px; border-radius: 20px;
        }
        .visa-cards { display: flex; flex-direction: column; gap: 10px; }
        .visa-card {
            display: flex;
            align-items: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px 24px;
            gap: 20px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .visa-card:hover {
            border-color: rgba(220,38,38,0.2);
            box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        }
        .vc-visual {
            width: 48px; height: 48px;
            background: #fef2f2;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #dc2626;
            flex-shrink: 0;
        }
        .vc-visual svg { width: 22px; height: 22px; }
        .vc-info { flex: 1; min-width: 0; }
        .vc-info h3 { font-size: 0.92rem; font-weight: 700; margin-bottom: 4px; }
        .vc-meta {
            display: flex; flex-wrap: wrap; gap: 6px 16px;
            font-size: 0.78rem; color: #6b7280;
        }
        .vc-meta span { display: inline-flex; align-items: center; gap: 4px; }
        .vc-meta svg { width: 12px; height: 12px; color: #9ca3af; }
        .vc-docs {
            font-size: 0.78rem; color: #9ca3af;
            margin-top: 6px; max-width: 300px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .vc-price {
            font-size: 1.2rem; font-weight: 800;
            color: #dc2626;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .vc-action a {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.82rem; font-weight: 600;
            background: #dc2626; color: #fff;
            padding: 9px 22px; border-radius: 8px;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .vc-action a:hover {
            background: #b91c1c; color: #fff;
            box-shadow: 0 2px 12px rgba(220,38,38,0.25);
            transform: translateY(-1px);
        }
        .results-none { text-align: center; padding: 50px 20px; color: #9ca3af; font-size: 0.95rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; }

        /* -- Dotted background pattern for sections -- */
        .dot-pattern {
            position: relative;
        }
        .dot-pattern::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(220,38,38,0.04) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
            z-index: 0;
        }

        /* -- Countries — image cards -- */
        .countries { background: #fff; position: relative; overflow: hidden; }
        .countries::before {
            content: '';
            position: absolute;
            right: -20%; top: -20%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(220,38,38,0.04) 0%, transparent 70%);
            pointer-events: none;
        }
        .countries-bg-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(220,38,38,0.03) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }
        .countries-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .country-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e5e7eb;
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
            cursor: pointer;
        }
        .country-card:hover {
            border-color: rgba(220,38,38,0.2);
            box-shadow: 0 6px 24px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }
        .cc-img {
            width: 100%;
            height: 130px;
            object-fit: cover;
            display: block;
            transition: transform 0.4s;
        }
        .country-card:hover .cc-img { transform: scale(1.05); }
        .cc-overlay {
            position: relative;
            padding: 12px 16px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .cc-flag { font-size: 1.6rem; line-height: 1; flex-shrink: 0; }
        .cc-info { flex: 1; min-width: 0; }
        .cc-name { font-weight: 700; font-size: 0.85rem; color: #111827; }
        .cc-visas { font-size: 0.72rem; color: #9ca3af; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cc-badge { flex-shrink: 0; }
        .cc-badge .badge-live, .cc-badge .badge-soon { padding: 3px 10px; font-size: 0.6rem; }

        /* -- Features — unique alternating -- */
        .features { background: #f8f9fa; position: relative; }
        .features::before {
            content: '';
            position: absolute;
            top: -40%; left: -10%;
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(220,38,38,0.04) 0%, transparent 70%);
            pointer-events: none;
        }
        .feat-rows { display: flex; flex-direction: column; gap: 6px; }
        .feat-row {
            display: flex;
            align-items: stretch;
            gap: 6px;
        }
        .feat-row.reverse { flex-direction: row-reverse; }
        .feat-cell {
            flex: 1;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px 30px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .feat-cell:hover {
            border-color: rgba(220,38,38,0.2);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .feat-visual {
            width: 52px; height: 52px;
            background: #fef2f2;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #dc2626;
            flex-shrink: 0;
            transition: background 0.3s, color 0.3s, box-shadow 0.3s;
        }
        .feat-cell:hover .feat-visual {
            background: #dc2626;
            color: #fff;
            box-shadow: 0 4px 14px rgba(220,38,38,0.25);
        }
        .feat-visual .svg-icon { width: 24px; height: 24px; }
        .feat-body h3 { font-size: 1rem; font-weight: 700; margin-bottom: 6px; }
        .feat-body p { font-size: 0.85rem; color: #6b7280; line-height: 1.6; }
        .feat-accent {
            width: 3px;
            background: linear-gradient(180deg, #dc2626, transparent);
            border-radius: 3px;
            flex-shrink: 0;
            opacity: 0.6;
        }

        /* -- Showcase (Document ID + Real-Time) -- */
        .showcase {
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        .showcase-inner {
            display: flex;
            align-items: center;
            gap: 60px;
        }
        .showcase-inner.rev { flex-direction: row-reverse; }
        .sc-visual {
            flex: 0 0 380px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1200px;
        }
        .sc-visual svg {
            width: 100%;
            max-width: 320px;
            height: auto;
            position: relative;
            z-index: 2;
        }
        .sc-visual-glow {
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(220,38,38,0.06) 0%, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            transition: transform 0.35s ease, opacity 0.35s ease;
            opacity: 0.9;
        }
        .sc-shot {
            width: 100%;
            max-width: 340px;
            border-radius: 14px;
            position: relative;
            z-index: 2;
            box-shadow: 0 8px 32px rgba(0,0,0,0.06);
            transform: rotateX(0deg) rotateY(0deg) scale(1);
            transform-origin: center;
            transition: transform 0.38s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.38s ease;
            will-change: transform;
        }
        .sc-visual:hover .sc-shot {
            transform: rotateX(5deg) rotateY(-7deg) scale(1.08);
            box-shadow: 0 22px 55px rgba(15,23,42,0.22);
        }
        .sc-visual:hover .sc-visual-glow {
            transform: translate(-50%, -50%) scale(1.12);
            opacity: 1;
        }
        .sc-text { flex: 1; }
        .sc-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #dc2626;
            margin-bottom: 16px;
            background: rgba(220,38,38,0.06);
            padding: 6px 16px;
            border-radius: 20px;
        }
        .sc-text h2 {
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 14px;
            line-height: 1.2;
        }
        .sc-text > p {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 28px;
            max-width: 480px;
        }
        .sc-list { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 12px; }
        .sc-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.88rem;
            color: #374151;
        }
        .sc-list li .sc-ic {
            width: 22px; height: 22px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc2626;
        }
        .sc-list li .sc-ic svg { width: 18px; height: 18px; }

        /* -- Body airplane decorations -- */
        .body-plane {
            position: absolute;
            pointer-events: none;
            z-index: 0;
            color: rgba(220,38,38,0.06);
        }
        .body-plane svg { width: 100%; height: 100%; }
        .bp-1 {
            top: -40px; right: 60px;
            width: 120px;
            animation: planeFloat1 16s ease-in-out infinite;
        }
        .bp-2 {
            bottom: 20px; left: 40px;
            width: 90px;
            animation: planeFloat2 20s ease-in-out infinite;
        }
        .bp-3 {
            top: 30px; left: 30px;
            width: 70px;
            animation: planeFloat3 14s ease-in-out infinite;
        }
        .bp-4 {
            bottom: -20px; right: 80px;
            width: 100px;
            animation: planeFloat4 18s ease-in-out infinite;
        }
        .bp-5 {
            top: 50%; left: 20px;
            width: 60px;
            animation: planeFloat5 22s ease-in-out infinite;
        }
        @keyframes planeFloat1 {
            0%, 100% { transform: translateY(0) rotate(-5deg); }
            50% { transform: translateY(-18px) rotate(3deg); }
        }
        @keyframes planeFloat2 {
            0%, 100% { transform: translateY(0) rotate(8deg); }
            50% { transform: translateY(14px) rotate(-2deg); }
        }
        @keyframes planeFloat3 {
            0%, 100% { transform: translateY(0) rotate(-8deg) scale(1); }
            50% { transform: translateY(-12px) rotate(5deg) scale(1.08); }
        }
        @keyframes planeFloat4 {
            0%, 100% { transform: translateY(0) rotate(5deg); }
            50% { transform: translateY(16px) rotate(-6deg); }
        }
        @keyframes planeFloat5 {
            0%, 100% { transform: translateY(0) rotate(-12deg); opacity: 0.4; }
            50% { transform: translateY(-10px) rotate(3deg); opacity: 0.8; }
        }

        /* -- How It Works — connected timeline -- */
        .how { background: #fff; position: relative; }
        .how-visual {
            position: relative;
            display: flex;
            justify-content: center;
            gap: 0;
            margin-bottom: 56px;
            padding: 0 40px;
        }
        .how-visual::before {
            content: '';
            position: absolute;
            top: 28px; left: 60px; right: 60px;
            height: 2px;
            background: linear-gradient(90deg, #fca5a5 0%, #ef4444 50%, #fca5a5 100%);
            opacity: 0.5;
            z-index: 0;
        }
        .hv-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
            z-index: 1;
        }
        .hv-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(145deg, #ffffff, #f9fafb);
            border: 1px solid #d1d5db;
            display: flex; align-items: center; justify-content: center;
            color: #4b5563;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
            transition: background 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s, border-color 0.3s;
            box-shadow: 0 10px 24px rgba(15,23,42,0.09), inset 0 1px 0 rgba(255,255,255,0.95);
        }
        .hv-icon::before {
            content: '';
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 1px solid rgba(156,163,175,0.4);
        }
        .hv-step:hover .hv-icon {
            background: linear-gradient(145deg, #dc2626, #ef4444);
            color: #fff;
            border-color: #dc2626;
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 12px 26px rgba(220,38,38,0.28);
        }
        .hv-icon i { font-size: 1.35rem; line-height: 1; }
        .hv-label { font-size: 0.84rem; font-weight: 700; color: #374151; text-align: center; max-width: 140px; line-height: 1.35; }
        .hv-step.active .hv-icon {
            background: linear-gradient(145deg, #dc2626, #ef4444);
            color: #fff;
            box-shadow: 0 14px 30px rgba(220,38,38,0.3);
            border-color: #dc2626;
        }
        .hv-step.active .hv-label { color: #111827; }
        .steps-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .step-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px 28px;
            position: relative;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .step-card:hover {
            border-color: rgba(220,38,38,0.15);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }
        .step-card-num {
            font-size: 0.7rem; font-weight: 700; color: #dc2626;
            text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px;
        }
        .step-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
        .step-card p { font-size: 0.85rem; color: #6b7280; line-height: 1.6; }
        .for-agents {
            background: #f8f9fa;
            position: relative;
        }
        .for-agents .section-sub,
        .trust-proof .section-sub,
        .pricing .section-sub,
        .faq .section-sub {
            margin-bottom: 30px;
            max-width: 700px;
        }
        .agent-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .agent-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px 18px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .agent-card:hover {
            transform: translateY(-2px);
            border-color: rgba(220,38,38,0.2);
            box-shadow: 0 10px 24px rgba(15,23,42,0.06);
        }
        .agent-card h3 {
            font-size: 0.96rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #111827;
        }
        .agent-card p {
            font-size: 0.84rem;
            color: #6b7280;
            line-height: 1.6;
        }
        .trust-proof {
            background: #fff;
        }
        .proof-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .proof-stat {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            background: #f9fafb;
        }
        .proof-stat strong {
            display: block;
            font-size: 1.7rem;
            color: #dc2626;
            line-height: 1.1;
            margin-bottom: 4px;
            font-weight: 900;
            letter-spacing: -0.02em;
        }
        .proof-stat span {
            font-size: 0.78rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .logo-strip {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .logo-pill {
            border: 1px dashed #d1d5db;
            border-radius: 10px;
            padding: 10px 12px;
            text-align: center;
            font-size: 0.82rem;
            color: #4b5563;
            background: #fff;
            font-weight: 600;
        }
        .testi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .testi-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            background: #fff;
        }
        .testi-card p {
            font-size: 0.88rem;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        .testi-card .by {
            font-size: 0.78rem;
            color: #6b7280;
            font-weight: 600;
        }
        .pricing {
            background: #f8f9fa;
        }`r`n        .onboarding {
            background: linear-gradient(180deg, #f8fafc 0%, #f3f6fb 100%);
        }
        .onboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .onboard-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 22px 18px;
            background: #fff;
        }
        .onboard-card h3 {
            font-size: 1rem;
            color: #111827;
            margin-bottom: 8px;
        }
        .onboard-card p {
            font-size: 0.86rem;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .onboard-media {
            width: 100%;
            height: 170px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin-bottom: 12px;
        }
        .insights {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
        .insight-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .insight-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 14px;
            background: #fff;
        }
        .insight-card .tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.6px;
            color: #dc2626;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: inline-block;
        }
        .insight-card h3 {
            font-size: 0.94rem;
            margin-bottom: 8px;
            color: #111827;
            line-height: 1.35;
        }
        .insight-card p {
            font-size: 0.83rem;
            color: #6b7280;
            line-height: 1.55;
        }
        .insight-media {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 9px;
            border: 1px solid #e5e7eb;
            margin-bottom: 10px;
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .price-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            padding: 20px 18px;
        }
        .price-card.featured {
            border-color: rgba(220,38,38,0.35);
            box-shadow: 0 12px 28px rgba(220,38,38,0.08);
        }
        .price-card h3 {
            font-size: 1rem;
            margin-bottom: 8px;
            color: #111827;
        }
        .price-tag {
            color: #dc2626;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .price-card ul {
            margin: 0;
            padding-left: 18px;
            color: #4b5563;
            font-size: 0.84rem;
            line-height: 1.7;
        }
        .workflow-visual {
            margin: 6px auto 34px;
            max-width: 940px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 18px 40px rgba(15,23,42,0.08);
            background: #fff;
        }
        
        .section-visual {
            margin: 0 auto 24px;
            max-width: 940px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 14px 36px rgba(15,23,42,0.08);
            background: #fff;
        }
        .section-visual img {
            display: block;
            width: 100%;
            height: auto;
        }
        .faq { background: #f8f9fa; position: relative; }
        .faq-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .faq-item {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .faq-item summary {
            list-style: none;
            cursor: pointer;
            padding: 14px 16px;
            font-size: 0.9rem;
            font-weight: 650;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary::after {
            content: '+';
            font-size: 1.05rem;
            color: #dc2626;
            flex-shrink: 0;
        }
        .faq-item[open] summary::after { content: '-'; }
        .faq-body {
            padding: 0 16px 14px;
            color: #6b7280;
            font-size: 0.88rem;
            line-height: 1.6;
        }

        /* -- CTA -- */
        .cta-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #0b1120 0%, #1a2332 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .cta-section::before {
            content: '';
            position: absolute;
            top: -30%; left: -10%;
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(220,38,38,0.06) 0%, transparent 60%);
            pointer-events: none;
        }
        .cta-section::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 60px;
            background: #fff;
            clip-path: polygon(0 100%, 100% 0, 100% 100%, 0% 100%);
            pointer-events: none;
            z-index: 1;
        }
        .cta-section .container { position: relative; z-index: 2; }
        .cta-section h2 { font-size: 2rem; font-weight: 900; margin-bottom: 12px; color: #fff; letter-spacing: -0.025em; }
        .cta-section p { color: rgba(255,255,255,0.55); font-size: 1.05rem; margin-bottom: 32px; max-width: 500px; margin-left: auto; margin-right: auto; }
        .btn-cta {
            display: inline-flex; align-items: center; gap: 10px;
            background: #dc2626; color: #fff;
            padding: 16px 40px; border-radius: 12px;
            font-size: 0.92rem; font-weight: 600;
            font-family: inherit; border: none;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
            box-shadow: 0 4px 20px rgba(220,38,38,0.25);
        }
        .btn-cta:hover {
            background: #b91c1c; color: #fff;
            box-shadow: 0 8px 30px rgba(220,38,38,0.35);
            transform: translateY(-2px);
        }
        .btn-cta:active { transform: translateY(0); }
        .cta-contact { margin-top: 18px; font-size: 0.85rem; color: rgba(255,255,255,0.35); }
        .cta-contact a { color: #f87171; font-weight: 600; }
        .cta-contact a:hover { color: #fff; }

        /* -- Footer -- */
        footer {
            background: #fff;
            border-top: 1px solid #e5e7eb;
            padding: 56px 0 32px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 48px;
        }
        .footer-brand img { height: 32px; margin-bottom: 14px; }
        .footer-brand p { font-size: 0.82rem; color: #6b7280; max-width: 280px; line-height: 1.6; }
        .footer-col h4 {
            font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.8px; color: #9ca3af; margin-bottom: 18px;
        }
        .footer-col a {
            display: block; font-size: 0.85rem;
            color: #4b5563; margin-bottom: 10px;
            transition: color 0.15s;
        }
        .footer-col a:hover { color: #111827; }
        .footer-contact p { font-size: 0.85rem; color: #4b5563; margin-bottom: 10px; }
        .footer-contact svg { width: 16px; height: 16px; margin-right: 8px; color: #dc2626; vertical-align: middle; }
        .footer-bottom {
            border-top: 1px solid #e5e7eb;
            margin-top: 44px; padding-top: 24px;
            text-align: center; font-size: 0.8rem; color: #9ca3af;
        }

        /* -- Responsive -- */
        @media (max-width: 900px) {
            .countries-grid { grid-template-columns: 1fr 1fr; }
            .steps-cards { grid-template-columns: 1fr 1fr; }
            .feat-row { flex-direction: column; }
            .feat-row.reverse { flex-direction: column; }
            .feat-accent { display: none; }
        }
        @media (max-width: 900px) {
            .sc-visual { flex: 0 0 280px; }
            .showcase-inner, .showcase-inner.rev { gap: 36px; }
        }
        @media (max-width: 768px) {
            nav { height: auto; min-height: 68px; }
            .nav-inner { padding: 10px 14px; gap: 10px; align-items: flex-start; flex-direction: column; }
            .nav-links {
                width: 100%;
                overflow-x: auto;
                gap: 18px;
                border-radius: 14px;
                padding: 8px 12px;
                justify-content: flex-start;
            }
            .nav-links a { white-space: nowrap; }
            .btn-nav-login { margin-left: auto; }
            .hero h1 { font-size: 1.8rem; }
            .hero p { font-size: 0.95rem; }
            .hero-stats { gap: 24px; }
            .hero { padding: 130px 0 100px; }
            .search-box { flex-direction: column; border-radius: 12px; }
            .search-box button { justify-content: center; padding: 14px; }
            .visa-table th, .visa-table td { padding: 10px 12px; }
            .countries-grid { grid-template-columns: 1fr; }
            .steps-cards { grid-template-columns: 1fr; }
            .agent-cards { grid-template-columns: 1fr; }
            .proof-stats { grid-template-columns: 1fr 1fr; }
            .logo-strip { grid-template-columns: 1fr 1fr; }
            .testi-grid { grid-template-columns: 1fr; }
            .pricing-grid { grid-template-columns: 1fr; }
            .onboard-grid { grid-template-columns: 1fr; }
            .insight-grid { grid-template-columns: 1fr; }
            .faq-grid { grid-template-columns: 1fr; }
            .section-cta-row { margin-top: 16px; }
            .step { border-right: none; border-bottom: 1px solid #e5e7eb; }
            .step:last-child { border-bottom: none; }
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 28px; }
            .section { padding: 64px 0; }
            .results-header h2 { font-size: 1.2rem; }
            .trust-inner { gap: 20px; }
            .svg-visa-deco, .svg-passport-deco { display: none; }
            .how-visual { display: none; }
            .hero::after { height: 40px; clip-path: polygon(0 60%, 100% 0, 100% 100%, 0% 100%); }
            .trust-bar::before { display: none; }
            .cta-section::after { height: 30px; }
            .hero h1 span { -webkit-text-fill-color: #f87171; color: #f87171; }
            .showcase-inner, .showcase-inner.rev { flex-direction: column; gap: 32px; }
            .sc-visual { flex: 0 0 auto; width: 100%; max-width: 280px; margin: 0 auto; }
            .sc-text { text-align: center; }
            .sc-text > p { margin-left: auto; margin-right: auto; }
            .sc-list { align-items: center; }
            .body-plane { display: none; }
            .hero-cta-row { margin-bottom: 18px; }
            .hero-product-shot { margin-bottom: 20px; border-radius: 12px; z-index: 3; }
            .hero-map { top: -90px; width: 840px; opacity: 0.18; }
            .workflow-visual { margin-bottom: 24px; border-radius: 12px; }
            .cta-section [style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; text-align: center !important; }
            .cta-section h2, .cta-section p, .cta-section .cta-contact { text-align: center !important; }
            .cta-section [style*="display:flex;gap:12px"] { justify-content: center; }
        }
        @media (max-width: 480px) {
            .footer-grid { grid-template-columns: 1fr; }
            .hero-stats { flex-direction: column; gap: 14px; }
            .feat-cell { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="scroll-progress" id="scrollProgress"></div>
<div class="mouse-glow" id="mouseGlow"></div>

<nav>
    <div class="nav-inner">
         <a href="landing.php" class="nav-logo">
            <img src="../assets/ask-visa-logo-final.png" alt="AskVisa" class="nav-logo-img">
            <span class="nav-brand">AskVisa <span class="nav-brand-badge">B2B</span></span>
        </a>
        <div class="nav-links">
            <a href="#how">How It Works</a>
            <a href="#features">Features</a>
            <a href="#onboarding">Onboarding</a>
            <a href="#faq">FAQ</a>
            <a href="#contact">Contact</a>
            <a href="login.php" class="btn-nav-login">
                <svg class="svg-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Get B2B Access
            </a>
        </div>
    </div>
</nav>

<section class="hero" id="hero">
    <div class="hero-bg parallax-layer" data-speed="0.3"></div>

    <!-- Visa SVG -->
    <div class="hero-svg-deco svg-visa-deco parallax-layer" data-speed="-0.25">
        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect x="10" y="18" width="80" height="64" rx="6" fill="currentColor" fill-opacity="0.08"/>
            <rect x="18" y="28" width="64" height="8" rx="2" fill="currentColor" fill-opacity="0.15"/>
            <rect x="18" y="42" width="48" height="4" rx="2" fill="currentColor" fill-opacity="0.1"/>
            <rect x="18" y="52" width="36" height="4" rx="2" fill="currentColor" fill-opacity="0.1"/>
            <circle cx="72" cy="38" r="10" fill="none" stroke="currentColor" stroke-width="2" stroke-opacity="0.3"/>
            <path d="M65 38 l4 4 l6 -6" fill="none" stroke="currentColor" stroke-width="2" stroke-opacity="0.3" stroke-linecap="round"/>
            <rect x="36" y="62" width="30" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-opacity="0.15"/>
        </svg>
    </div>
    <!-- Passport SVG -->
    <div class="hero-svg-deco svg-passport-deco parallax-layer" data-speed="0.3">
        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect x="15" y="8" width="70" height="84" rx="8" fill="currentColor" fill-opacity="0.08"/>
            <rect x="22" y="16" width="56" height="68" rx="4" fill="currentColor" fill-opacity="0.05"/>
            <circle cx="50" cy="34" r="12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-opacity="0.2"/>
            <path d="M38 58 Q50 48 62 58" fill="none" stroke="currentColor" stroke-width="1.5" stroke-opacity="0.2"/>
            <rect x="28" y="68" width="44" height="2" rx="1" fill="currentColor" fill-opacity="0.12"/>
            <rect x="28" y="74" width="36" height="2" rx="1" fill="currentColor" fill-opacity="0.08"/>
            <circle cx="78" cy="12" r="7" fill="currentColor" fill-opacity="0.1"/>
        </svg>
    </div>

    <div class="hero-map parallax-layer" data-speed="-0.2"><img src="assets/world-map.png" alt=""></div>

    <div class="container">
        <h1>Visa Back-Office Built for <span>Travel Agents</span></h1>
        <p>Submit, track, and manage every client visa in one dashboard while AskVisa handles document checks, filing, and status updates end to end.</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-num">18+</div>
                <div class="hero-stat-label">Visa Routes</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-num">1,250+</div>
                <div class="hero-stat-label">Files / Month</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-num">24/7</div>
                <div class="hero-stat-label">Dedicated Support</div>
            </div>
        </div>
        <div class="hero-cta-row">
            <a href="login.php" class="hero-cta primary">Get B2B Access</a>
            <a href="mailto:partnerships@askvisa.in?subject=B2B%20Onboarding%20Call" class="hero-cta secondary">Book 15-min Onboarding Call</a>
        </div>
        <div class="search-wrap">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search a country (e.g. Thailand, Malaysia, Dubai...)" autocomplete="off">
                <button onclick="searchCountry()"><svg class="svg-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Search</button>
            </div>
            <div class="search-chips">
                <span class="chip" onclick="quickSearch('Thailand')">Thailand</span>
                <span class="chip" onclick="quickSearch('Malaysia')">Malaysia</span>
                <span class="chip" onclick="quickSearch('Hong Kong')">Hong Kong</span>
                <span class="chip" onclick="quickSearch('Singapore')">Singapore</span>
                <span class="chip" onclick="quickSearch('Dubai (UAE)')">Dubai</span>
                <span class="chip" onclick="quickSearch('Vietnam')">Vietnam</span>
            </div>
        </div>
    </div>
</section>

<section class="trust-bar fade-up">
    <div class="container">
        <div class="trust-inner">
            <div class="trust-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <strong>12,400+</strong> visas processed
            </div>
            <div class="trust-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                <strong>180+</strong> partner agencies
            </div>
            <div class="trust-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Avg TAT <strong>2.3 days</strong>
            </div>
            <div class="trust-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <strong>7 days/week</strong> WhatsApp support
            </div>
        </div>
    </div>
</section>


<section class="section countries" id="countries">
    <div class="countries-bg-dots"></div>
    <div class="container">
        <h2 class="section-title fade-up">Countries We Serve</h2>
        <p class="section-sub fade-up">Live visa processing for top destinations, with more launching soon.</p>
        <div class="countries-grid" id="countriesList"></div>
    </div>
</section>

<section class="container" id="resultsSection">
    <div class="results-wrap" id="resultsWrap">
        <div class="results-header" id="resultsHeader">
            <span class="flag" id="resFlag"></span>
            <div class="rh-info">
                <h2 id="resTitle"></h2>
                <p id="resSub"></p>
            </div>
            <div id="resBadge"></div>
        </div>
        <div id="resultsContent"></div>
    </div>
</section>

<section class="section how dot-pattern" id="how">
    <div class="container">
        <h2 class="section-title fade-up">How It Works</h2>
        <p class="section-sub fade-up">Submit documents once, then let AskVisa handle verification, filing, and continuous status updates.</p>

        <div class="how-visual fade-up">
            <div class="hv-step">
                <div class="hv-icon">
                    <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                </div>
                <div class="hv-label">Share Files</div>
            </div>
            <div class="hv-step">
                <div class="hv-icon">
                    <i class="fa-solid fa-file-circle-check" aria-hidden="true"></i>
                </div>
                <div class="hv-label">We Verify &amp; File</div>
            </div>
            <div class="hv-step">
                <div class="hv-icon">
                    <i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>
                </div>
                <div class="hv-label">Track in Real Time</div>
            </div>
        </div>
        <div class="workflow-visual fade-up">
            <img src="assets/saas-workflow-mock.png" alt="AskVisa B2B workflow visual">
        </div>

        <div class="steps-cards fade-up">
            <div class="step-card">
                <div class="step-card-num">Step 01</div>
                <h3>Submit Client Documents</h3>
                <p>Upload passport, itinerary, and supporting files from your portal, email, or WhatsApp in one place.</p>
            </div>
            <div class="step-card">
                <div class="step-card-num">Step 02</div>
                <h3>AskVisa Processes &amp; Files</h3>
                <p>Our team validates every file, aligns requirements by destination, and handles embassy or center submissions.</p>
            </div>
            <div class="step-card">
                <div class="step-card-num">Step 03</div>
                <h3>Track Every Update</h3>
                <p>Monitor status changes in your dashboard and get proactive alerts so your team can update travelers immediately.</p>
            </div>
        </div>
    </div>
</section>


<section class="section features dot-pattern" id="features">
    <div class="container">
        <h2 class="section-title fade-up">Platform Features</h2>
        <p class="section-sub fade-up">Everything travel agencies need to run visa operations at scale from one workflow platform.</p>
        <div class="feat-rows fade-up">
            <div class="feat-row">
                <div class="feat-cell">
<div style="display:flex;gap:20px;align-items:flex-start;width:100%;">
                        <div class="feat-visual">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        </div>
                        <div class="feat-body">
                            <h3>Dashboard & Analytics</h3>
                            <p>Multi-user overview of all files with real-time stats, pipeline health, and team-level operational visibility.</p>
                        </div>
                    </div>
                </div>
                <div class="feat-accent"></div>
                <div class="feat-cell">
<div style="display:flex;gap:20px;align-items:flex-start;width:100%;">
                        <div class="feat-visual">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                        </div>
                        <div class="feat-body">
                            <h3>Real-time Tracking</h3>
                            <p>Track each application from intake to decision with clear milestones your team and clients can follow.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="feat-row reverse">
                <div class="feat-cell">
<div style="display:flex;gap:20px;align-items:flex-start;width:100%;">
                        <div class="feat-visual">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        </div>
                        <div class="feat-body">
                            <h3>Document Management</h3>
                            <p>Country-wise checklists, auto-validation, and smart mapping reduce rework before embassy submission.</p>
                        </div>
                    </div>
                </div>
                <div class="feat-accent"></div>
                <div class="feat-cell">
<div style="display:flex;gap:20px;align-items:flex-start;width:100%;">
                        <div class="feat-visual">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="feat-body">
                            <h3>Bulk Upload</h3>
                            <p>Process group departures and high-volume batches without duplicating data entry across applicants.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="feat-row">
                <div class="feat-cell">
<div style="display:flex;gap:20px;align-items:flex-start;width:100%;">
                        <div class="feat-visual">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div class="feat-body">
                            <h3>Dedicated Support</h3>
                            <p>Fast B2B escalations via WhatsApp, email, and phone for urgent files and time-sensitive departures.</p>
                        </div>
                    </div>
                </div>
                <div class="feat-accent"></div>
                <div class="feat-cell">
<div style="display:flex;gap:20px;align-items:flex-start;width:100%;">
                        <div class="feat-visual">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </div>
                        <div class="feat-body">
                            <h3>Agent-only Pricing</h3>
                            <p>Transparent destination-wise commercials with partner benefits for higher monthly processing volume.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-cta-row fade-up">
            <a href="login.php" class="section-cta primary">Get B2B Access</a>
            <a href="mailto:partnerships@askvisa.in?subject=B2B%20Onboarding%20Call" class="section-cta secondary">Book Onboarding Call</a>
        </div>
    </div>
</section>






<!-- --- Automatic Document Identification --- -->
<section class="showcase" id="doc-id">
    <div class="container">
        <div class="showcase-inner fade-up">
            <div class="sc-visual">
                <div class="sc-visual-glow"></div>
                <img src="assets/doc-identification.png" alt="AskVisa AI Document Identification" class="sc-shot">
            </div>
            <div class="sc-text">
                <div class="sc-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    AI-Powered
                </div>
                <h2>Automatic Document<br>Identification</h2>
                <p>Upload all documents — our AI system auto-detects, categorizes, and validates every file instantly.</p>
                <ul class="sc-list">
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Smart passport detection &amp; data extraction</li>
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Auto-crop &amp; enhance passport photos</li>
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Instant validation against embassy rules</li>
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Drag-and-drop bulk upload for groups</li>
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> AES-256 encrypted document storage</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- --- Real-Time Visa Updates --- -->
<section class="showcase" id="realtime">
    <div class="container">
        <div class="showcase-inner rev fade-up">
            <div class="sc-visual">
                <div class="sc-visual-glow"></div>
                <img src="assets/realtime-tracking.png" alt="AskVisa Real-Time Visa Tracking" class="sc-shot">
            </div>
            <div class="sc-text">
                <div class="sc-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Live Tracking
                </div>
                <h2>Get Real-Time<br>Visa Updates</h2>
                <p>Stay informed at every step with instant status updates — from submission to approval.</p>
                <ul class="sc-list">
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Push notifications on status changes</li>
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Step-by-step visual progress tracker</li>
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Agent dashboard with bulk overview</li>
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Shareable tracking links for clients</li>
                    <li><span class="sc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> WhatsApp &amp; email notifications</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section trust-proof" id="proof">
    <div class="container">
        <h2 class="section-title fade-up">Trusted by Growing Travel Agencies</h2>
        <p class="section-sub fade-up">Real results from agencies already processing with AskVisa B2B.</p>
        <div class="proof-stats fade-up">
            <div class="proof-stat"><strong>12,400+</strong><span>Visas Processed</span></div>
            <div class="proof-stat"><strong>180+</strong><span>Partner Agencies</span></div>
            <div class="proof-stat"><strong>2.3 Days</strong><span>Avg Turnaround</span></div>
            <div class="proof-stat"><strong>98.7%</strong><span>Checklist-Ready Files</span></div>
        </div>
        <div class="logo-strip fade-up">
            <div class="logo-pill">SkyBridge Travels</div>
            <div class="logo-pill">TripNest Holidays</div>
            <div class="logo-pill">Voyana DMC</div>
            <div class="logo-pill">UrbanRoute Tours</div>
            <div class="logo-pill">GlobeWing Holidays</div>
        </div>
        <div class="testi-grid fade-up">
            <div class="testi-card">
                <p>"AskVisa cut our visa follow-up time by nearly half. Our ops team now handles more departures with the same headcount."</p>
                <div class="by">A. Mehta · Director, TripNest Holidays · Mumbai</div>
            </div>
            <div class="testi-card">
                <p>"For UAE and Schengen files, the process is predictable and updates are timely. That reliability helps us sell with confidence."</p>
                <div class="by">R. Sharma · Operations Lead, SkyBridge Travels · Delhi</div>
            </div>
        </div>
        <div class="section-cta-row fade-up">
            <a href="#onboarding" class="section-cta primary">View Onboarding Options</a>
            <a href="mailto:partnerships@askvisa.in?subject=B2B%20Performance%20Deck" class="section-cta secondary">Request Performance Deck</a>
        </div>
    </div>
</section>

<section class="section onboarding" id="onboarding">
    <div class="container">
        <h2 class="section-title fade-up">Start Fast with Demo or Guided Onboarding</h2>
        <p class="section-sub fade-up">Choose your preferred start path based on urgency and monthly volume, then connect with our B2B team instantly.</p>
        <div class="onboard-grid fade-up">
            <div class="onboard-card" style="border-color:rgba(220,38,38,0.3);position:relative;">
                <div style="position:absolute;top:-1px;right:20px;background:#dc2626;color:#fff;font-size:0.65rem;font-weight:700;padding:4px 14px;border-radius:0 0 8px 8px;letter-spacing:0.5px;text-transform:uppercase;">Recommended</div>
                <div style="display:flex;align-items:flex-start;gap:18px;">
                    <div style="width:48px;height:48px;background:#fef2f2;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#dc2626;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <h3>Book a 15-minute Onboarding Call</h3>
                        <p>Best for agencies that want immediate setup guidance, process walk-through, and commercial alignment.</p>
                        <a href="mailto:partnerships@askvisa.in?subject=B2B%20Onboarding%20Call" class="section-cta primary" style="margin-top:14px;">Book Onboarding Call</a>
                    </div>
                </div>
            </div>
            <div class="onboard-card">
                <div style="display:flex;align-items:flex-start;gap:18px;">
                    <div style="width:48px;height:48px;background:#f0fdf4;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#16a34a;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                    </div>
                    <div>
                        <h3>Request Demo Access</h3>
                        <p>Best for teams evaluating workflow fit before rollout. Get a guided preview of tracking, statuses, and file flow.</p>
                        <a href="mailto:partnerships@askvisa.in?subject=Request%20B2B%20Demo%20Access" class="section-cta secondary" style="margin-top:14px;">Request Demo Access</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-cta-row fade-up">
            <a href="https://wa.me/917880789486" class="section-cta primary" target="_blank" rel="noopener">Contact on WhatsApp</a>
            <a href="mailto:partnerships@askvisa.in?subject=B2B%20Quick%20Consultation" class="section-cta secondary">Email B2B Team</a>
        </div>
    </div>
</section>

<section class="section faq" id="faq">
    <div class="container">
        <h2 class="section-title fade-up">Frequently Asked Questions</h2>
        <p class="section-sub fade-up">Everything travel agents usually ask before onboarding with AskVisa B2B.</p>
        <div class="faq-grid fade-up">
            <details class="faq-item"><summary>Who is AskVisa B2B built for?</summary><div class="faq-body">AskVisa B2B is designed for travel agencies, tour operators, and corporate travel desks that manage visa files for multiple travelers.</div></details>
            <details class="faq-item"><summary>Which visa categories do you support?</summary><div class="faq-body">We support tourist, business, and selected long-stay categories for key destinations. Coverage expands continuously by route demand.</div></details>
            <details class="faq-item"><summary>How quickly can we start after signup?</summary><div class="faq-body">Most agencies can start within 24 hours after basic onboarding and account verification with our B2B operations team.</div></details>
            <details class="faq-item"><summary>Do you handle group or bulk applications?</summary><div class="faq-body">Yes. AskVisa supports group departures and bulk document handling workflows so your team can process high-volume files efficiently.</div></details>
            <details class="faq-item"><summary>How do we track each application?</summary><div class="faq-body">You can track milestone updates from submission to outcome through the agent dashboard, with status notes and follow-up flags.</div></details>
            <details class="faq-item"><summary>Do you offer WhatsApp support?</summary><div class="faq-body">Yes. Our B2B support team is available on WhatsApp and email for routine updates and urgent escalations.</div></details>
            <details class="faq-item"><summary>How is agency pricing structured?</summary><div class="faq-body">Commercials are based on destination, visa type, and monthly volume. Partner pricing and volume benefits are shared during onboarding.</div></details>
            <details class="faq-item"><summary>How do you secure sensitive documents?</summary><div class="faq-body">Documents are handled through controlled workflows with restricted access and retention policies aligned to operational and compliance needs.</div></details>
        </div>
        <div class="section-cta-row fade-up">
            <a href="login.php" class="section-cta primary">Get B2B Access</a>
            <a href="mailto:partnerships@askvisa.in?subject=B2B%20Question" class="section-cta secondary">Ask a Question</a>
        </div>
    </div>
</section>

<section class="cta-section" id="contact">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;text-align:left;" class="fade-up">
            <div>
                <h2 style="text-align:left;">Ready to streamline your visa processing?</h2>
                <p style="text-align:left;margin-left:0;">Join India's growing network of travel agents using AskVisa B2B. Start processing in under 24 hours.</p>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="mailto:partnerships@askvisa.in" class="btn-cta">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Contact Us
                    </a>
                    <a href="https://wa.me/917880789486" target="_blank" rel="noopener" class="btn-cta" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);box-shadow:none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        WhatsApp
                    </a>
                </div>
                <div class="cta-contact" style="text-align:left;">
                    Already a partner? <a href="login.php">Sign in to your dashboard</a>
                </div>
            </div>
            <div style="position:relative;">
                <img src="assets/cta-contact.png" alt="AskVisa B2B Platform" style="width:100%;">
            </div>
        </div>
    </div>

<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="../assets/ask-visa-logo-final.png" alt="AskVisa">
                <p>B2B visa processing platform for travel agencies, OTAs, and businesses across India.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="#countries">Countries</a>
                <a href="#features">Features</a>
                <a href="#how">How It Works</a>
                <a href="#faq">FAQ</a>
                <a href="../privacy_policy.php">Privacy Policy</a>
                <a href="../terms_of_use.php">Terms of Service</a>
            </div>
            <div class="footer-col">
                <h4>Agents</h4>
                <a href="login.php">Agent Login</a>
                <a href="mailto:partnerships@askvisa.in">Become a Partner</a>
                <a href="mailto:support@askvisa.in">Support</a>
            </div>
            <div class="footer-col footer-contact">
                <h4>Contact</h4>
                <p>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    partnerships@askvisa.in
                </p>
                <p>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    +91 78807 89486
                </p>
                <p>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    +91 78629 92570
                </p>
                <p style="margin-top:10px;font-size:0.8rem;color:#9ca3af;line-height:1.5;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Shop No 16, 2nd Floor, VED TransCube Plaza, Sayajiganj, Vadodara, Gujarat 390007
                </p>
            </div>
        </div>
        <div class="footer-bottom">&copy; 2026 AskVisa. All rights reserved.</div>
    </div>
</footer>

<script>
var countriesData = {
    thailand: {
        name: 'Thailand', flag: '\uD83C\uDDF9\uD83C\uDDED', key: 'Thailand', live: true,
        img: 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?auto=format&fit=crop&w=800&h=520&q=80',
        visas: [
            { type: 'Tourist Visa (30 Days)', price: '₹80', processing: '2-3 Business Days', validity: '60 Days', docs: 'Passport, Photo, Flight Itinerary, Hotel Booking' },
            { type: 'TDAC', price: '₹60', processing: '24-72 Hours', validity: '30 Days', docs: 'Valid Passport, Passport Photo' },
            { type: 'Business Visa', price: '₹250', processing: '5-7 Business Days', validity: '90 Days', docs: 'Passport, Invitation Letter, Company Docs' }
        ]
    },
    malaysia: {
        name: 'Malaysia', flag: '\uD83C\uDDF2\uD83C\uDDFE', key: 'Malaysia', live: true,
        img: 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=800&h=520&q=80',
        visas: [
            { type: 'eVisa (Tourism)', price: '₹120', processing: '24-48 Hours', validity: '30 Days Single Entry', docs: 'Passport, Photo, Return Tickets, Hotel Reservation' },
            { type: 'eNTRI', price: '₹80', processing: 'Instant - 24 Hours', validity: '15 Days Single Entry', docs: 'Valid Passport, Return Tickets' }
        ]
    },
    hongkong: {
        name: 'Hong Kong', flag: '\uD83C\uDDED\uD83C\uDDF0', key: 'Hong Kong', live: true,
        img: 'https://images.unsplash.com/photo-1506974210756-8e1b8985d348?auto=format&fit=crop&w=800&h=520&q=80',
        visas: [
            { type: 'PAR (Pre-Arrival Registration)', price: '₹150', processing: '24-48 Hours', validity: '14-90 Days', docs: 'Passport, Photo, Confirmed Tickets, Hotel Booking' },
            { type: 'Tourist Visa', price: '₹200', processing: '3-5 Business Days', validity: '14-90 Days', docs: 'Passport, Photo, Proof of Funds, Flight Itinerary' }
        ]
    },
    singapore: {
        name: 'Singapore', flag: '\uD83C\uDDF8\uD83C\uDDEC', key: 'Singapore', live: false,
        img: 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=800&h=520&q=80',
        visas: [
            { type: 'Tourist Visa', price: '₹200', processing: '5-7 Days', validity: '30 Days', docs: 'Passport, Photo, Cover Letter, Bank Statement' }
        ]
    },
    vietnam: {
        name: 'Vietnam', flag: '\uD83C\uDDFB\uD83C\uDDF3', key: 'Vietnam', live: false,
        img: 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&h=520&q=80',
        visas: [
            { type: 'e-Visa', price: '₹90', processing: '3-5 Days', validity: '30 Days', docs: 'Passport, Photo, Flight Itinerary' }
        ]
    },
    dubai: {
        name: 'Dubai (UAE)', flag: '\uD83C\uDDE6\uD83C\uDDEA', key: 'Dubai (UAE)', live: false,
        img: 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=800&h=520&q=80',
        visas: [
            { type: 'Tourist Visa (30 Days)', price: '₹180', processing: '3-4 Days', validity: '30 Days', docs: 'Passport, Photo, Bank Statement, Hotel Booking' }
        ]
    },
    srilanka: {
        name: 'Sri Lanka', flag: '\uD83C\uDDF1\uD83C\uDDF0', key: 'Sri Lanka', live: false,
        img: 'https://images.pexels.com/photos/3214958/pexels-photo-3214958.jpeg?auto=compress&cs=tinysrgb&w=800&h=520&fit=crop',
        visas: [
            { type: 'ETA', price: '₹60', processing: '24-48 Hours', validity: '30 Days', docs: 'Passport, Photo, Return Tickets' }
        ]
    },
    indonesia: {
        name: 'Indonesia', flag: '\uD83C\uDDEE\uD83C\uDDE9', key: 'Indonesia', live: false,
        img: 'https://images.unsplash.com/photo-1555400038-63f5ba517a47?auto=format&fit=crop&w=800&h=520&q=80',
        visas: [
            { type: 'e-VOA', price: '₹70', processing: 'Instant - 48 Hours', validity: '30 Days', docs: 'Passport, Photo, Return Tickets' }
        ]
    },
    japan: {
        name: 'Japan', flag: '\uD83C\uDDEF\uD83C\uDDF5', key: 'Japan', live: false,
        img: 'https://images.unsplash.com/photo-1528360983277-13d401cdc186?auto=format&fit=crop&w=800&h=520&q=80',
        visas: [
            { type: 'Tourist Visa', price: '₹350', processing: '7-10 Days', validity: '15-90 Days', docs: 'Passport, Photo, ITR, Bank Statement' }
        ]
    }
};

var countryKeys = Object.keys(countriesData);

function renderCountries() {
    var el = document.getElementById('countriesList');
    var html = '';
    for (var i = 0; i < countryKeys.length; i++) {
        var k = countryKeys[i];
        var c = countriesData[k];
        var visaList = '';
        for (var j = 0; j < c.visas.length; j++) {
            if (j > 0) visaList += ', ';
            visaList += c.visas[j].type;
        }
        var badge = c.live
            ? '<span class="badge-live">Live</span>'
            : '<span class="badge-soon">Coming Soon</span>';
        html += '<div class="country-card" onclick="quickSearch(\'' + c.name.replace(/'/g, "\\'") + '\')">'
            + '<img class="cc-img" src="' + c.img + '" alt="' + c.name + '" loading="lazy">'
            + '<div class="cc-overlay">'
            + '<span class="cc-flag">' + c.flag + '</span>'
            + '<div class="cc-info">'
            + '<div class="cc-name">' + c.name + '</div>'
            + '<div class="cc-visas">' + visaList + '</div>'
            + '</div>'
            + '<div class="cc-badge">' + badge + '</div>'
            + '</div>'
            + '</div>';
    }
    el.innerHTML = html;
}
renderCountries();

function searchCountry() {
    var input = document.getElementById('searchInput').value.trim().toLowerCase();
    if (!input) { hideResults(); return; }
    var match = null;
    for (var i = 0; i < countryKeys.length; i++) {
        if (countriesData[countryKeys[i]].name.toLowerCase().indexOf(input) !== -1) {
            match = countryKeys[i];
            break;
        }
    }
    if (!match) { showNoResults(); return; }
    showResults(match);
}

function quickSearch(name) {
    document.getElementById('searchInput').value = name;
    searchCountry();
    document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showResults(key) {
    var c = countriesData[key];
    var wrap = document.getElementById('resultsWrap');
    wrap.className = 'results-wrap visible';
    document.getElementById('resFlag').textContent = c.flag;
    document.getElementById('resTitle').textContent = c.name;
    document.getElementById('resSub').textContent = 'Visa options available for ' + c.name;
    var content = document.getElementById('resultsContent');

    if (!c.live) {
        document.getElementById('resBadge').innerHTML = '<span class="rh-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Coming Soon</span>';
        content.innerHTML = '<div class="results-none">' + c.flag + ' ' + c.name + ' visa services are coming soon. <a href="mailto:partnerships@askvisa.in">Contact us</a> for updates.</div>';
        return;
    }

    document.getElementById('resBadge').innerHTML = '<span class="rh-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Approved</span>';

    var cards = '';
    for (var i = 0; i < c.visas.length; i++) {
        var v = c.visas[i];
        cards += '<div class="visa-card">'
            + '<div class="vc-visual">'
            + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3 10 12 15 21 10"/></svg>'
            + '</div>'
            + '<div class="vc-info">'
            + '<h3>' + v.type + '</h3>'
            + '<div class="vc-meta">'
            + '<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' + v.processing + '</span>'
            + '<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>' + v.validity + '</span>'
            + '</div>'
            + '<div class="vc-docs">' + v.docs + '</div>'
            + '</div>'
            + '<div class="vc-price">' + v.price + '</div>'
            + '<div class="vc-action"><a href="../index.php?country=' + encodeURIComponent(c.name) + '&source=b2b" target="_blank">Apply <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a></div>'
            + '</div>';
    }

    content.innerHTML = '<div class="visa-cards">' + cards + '</div>';
}

function showNoResults() {
    var wrap = document.getElementById('resultsWrap');
    wrap.className = 'results-wrap visible';
    document.getElementById('resFlag').textContent = '';
    document.getElementById('resTitle').textContent = '';
    document.getElementById('resultsContent').innerHTML = '<div class="results-none">No matching country found. Try searching for Thailand, Malaysia, Hong Kong, Singapore, Dubai, Vietnam, Sri Lanka, Indonesia, or Japan.</div>';
}

function hideResults() {
    document.getElementById('resultsWrap').className = 'results-wrap';
}

document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); searchCountry(); }
});

// -- Scroll fade-up animations --
(function() {
    var els = document.querySelectorAll('.fade-up');
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    els.forEach(function(el) { observer.observe(el); });
})();

// -- Scroll Progress Bar --
(function() {
    var bar = document.getElementById('scrollProgress');
    window.addEventListener('scroll', function() {
        var scrollTop = window.scrollY;
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        var progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        bar.style.width = progress + '%';
    });
})();

// -- Mouse Glow --
(function() {
    var glow = document.getElementById('mouseGlow');
    var timer;
    document.addEventListener('mousemove', function(e) {
        glow.classList.add('visible');
        glow.style.left = e.clientX + 'px';
        glow.style.top = e.clientY + 'px';
        clearTimeout(timer);
        timer = setTimeout(function() { glow.classList.remove('visible'); }, 2000);
    });
    document.addEventListener('mouseleave', function() {
        glow.classList.remove('visible');
    });
})();

// -- Parallax on Scroll --
(function() {
    var layers = document.querySelectorAll('.parallax-layer');
    if (layers.length === 0) return;
    var ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                var scrollY = window.scrollY;
                var hero = document.getElementById('hero');
                if (!hero) { ticking = false; return; }
                var heroRect = hero.getBoundingClientRect();
                var heroTop = heroRect.top + scrollY;
                var heroHeight = hero.offsetHeight;
                var relativeScroll = Math.max(0, Math.min(heroHeight, scrollY - heroTop));
                for (var i = 0; i < layers.length; i++) {
                    var speed = parseFloat(layers[i].getAttribute('data-speed')) || 0.1;
                    var y = relativeScroll * speed;
                    layers[i].style.transform = 'translateY(' + y + 'px)';
                }
                ticking = false;
            });
            ticking = true;
        }
    });
})();

// -- Smooth anchor scroll --
(function() {
    document.querySelectorAll('a[href^="#"]').forEach(function(a) {
        a.addEventListener('click', function(e) {
            var target = document.getElementById(this.getAttribute('href').slice(1));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
</script>

</body>
</html>














