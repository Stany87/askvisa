<?php
require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['agent_id'])) {
  header('Location: dashboard.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if (login($email, $password)) {
    header('Location: dashboard.php');
    exit;
  }
  $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Login — AskVisa B2B</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: #f8fafc;
      color: #0f172a;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    .login-split {
      display: grid;
      grid-template-columns: 3fr 2fr;
      min-height: 100vh;
      width: 100%;
    }

    /* LEFT SIDE: Original Light Branding & Map */
    .left {
      background: #f1f4f8;
      background-image: radial-gradient(rgba(15, 23, 42, 0.03) 1px, transparent 1px);
      background-size: 24px 24px;
      padding: 48px;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow: hidden;
    }

    .left-logo {
      display: flex;
      align-items: center;
      gap: 8px;
      position: relative;
      z-index: 2;
    }

    .left-logo img {
      height: 48px;
      transition: transform 0.3s ease;
    }

    .left-logo img:hover {
      transform: scale(1.05);
    }

    .left-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      z-index: 2;
      max-width: 580px;
      pointer-events: none;
    }

    .left h1 {
      font-size: 2.8rem;
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -.03em;
      margin-bottom: 36px;
      color: #111827;
    }

    .left h1 span {
      color: #dc2626;
    }

    .left-stats {
      display: flex;
      gap: 14px;
      position: relative;
      z-index: 2;
    }

    .left-stat {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 16px 18px;
      min-width: 110px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      pointer-events: auto;
    }

    .left-stat:hover {
      transform: translateY(-5px);
      border-color: rgba(220, 38, 38, 0.3);
      box-shadow: 0 10px 15px -3px rgba(220, 38, 38, 0.1);
    }

    .left-stat-icon {
      width: 34px;
      height: 34px;
      background: rgba(220, 38, 38, .08);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #dc2626;
      font-size: .85rem;
      margin-bottom: 8px;
    }

    .left-stat-text {
      font-size: .75rem;
      font-weight: 600;
      color: #4b5563;
    }

    .left-map {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: flex-end;
      justify-content: flex-start;
      z-index: 1;
      pointer-events: none;
    }

    .left-map-wrapper {
      position: relative;
      height: 100%;
      aspect-ratio: 2108 / 2016;
      max-width: 100%;
      pointer-events: none;
    }

    .left-map-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: fill;
      display: block;
      mix-blend-mode: multiply;
    }

    .map-dot {
      position: absolute;
      width: 8px;
      height: 8px;
      background: #ef4444;
      border-radius: 50%;
      z-index: 2;
      box-shadow: 0 0 8px rgba(239, 68, 68, 0.8);
      pointer-events: auto;
      animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
      transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.2s, box-shadow 0.2s;
    }

    .map-dot:hover {
      transform: scale(1.4) !important;
      background: #ffffff;
      box-shadow: 0 0 12px rgba(239, 68, 68, 1);
    }

    .map-dot::after {
      content: '';
      position: absolute;
      top: -6px;
      left: -6px;
      right: -6px;
      bottom: -6px;
      border: 2px solid rgba(239, 68, 68, 0.85);
      border-radius: 50%;
      animation: mapPulse 2s infinite ease-out;
    }

    @keyframes mapPulse {
      0% {
        transform: scale(0.5);
        opacity: 1;
      }

      100% {
        transform: scale(2.5);
        opacity: 0;
      }
    }

    @keyframes popIn {
      0% {
        transform: scale(0);
        opacity: 0;
      }
      70% {
        transform: scale(1.25);
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }

    /* RIGHT SIDE: Interactive Login Card */
    .right {
      background: #f8fafc;
      background-image: radial-gradient(rgba(15, 23, 42, 0.04) 1px, transparent 1px);
      background-size: 24px 24px;
      padding: 60px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    .right::before {
      content: '';
      position: absolute;
      width: 320px;
      height: 320px;
      background: radial-gradient(circle, rgba(239, 68, 68, 0.06) 0%, rgba(239, 68, 68, 0) 70%);
      top: 15%;
      right: 10%;
      border-radius: 50%;
      filter: blur(40px);
      z-index: 1;
      pointer-events: none;
    }

    .right::after {
      content: '';
      position: absolute;
      width: 280px;
      height: 280px;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0) 70%);
      bottom: 15%;
      left: 10%;
      border-radius: 50%;
      filter: blur(30px);
      z-index: 1;
      pointer-events: none;
    }

    .right-wrap {
      width: 100%;
      max-width: 440px;
      background: #ffffff;
      padding: 48px;
      border-radius: 24px;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.03);
      border: 1px solid rgba(226, 232, 240, 0.8);
      position: relative;
      z-index: 2;
      overflow: hidden;
    }

    .right-wrap::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #ef4444, #f87171);
    }

    .status-badge {
      position: absolute;
      top: 24px;
      right: 24px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      padding: 6px 12px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
      color: #475569;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
      z-index: 3;
    }

    .status-dot {
      width: 6px;
      height: 6px;
      background: #10b981;
      border-radius: 50%;
      display: inline-block;
      box-shadow: 0 0 8px #10b981;
      animation: statusPulse 2s infinite;
    }

    @keyframes statusPulse {

      0%,
      100% {
        opacity: 0.6;
      }

      50% {
        opacity: 1;
      }
    }

    .r-header {
      margin-bottom: 32px;
    }

    .r-title {
      font-size: 1.85rem;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -0.03em;
      margin-bottom: 6px;
    }

    .r-subtitle {
      font-size: 0.875rem;
      color: #64748b;
      font-weight: 500;
    }

    .fg {
      margin-bottom: 20px;
    }

    .fg label {
      display: block;
      font-size: 0.813rem;
      font-weight: 600;
      color: #475569;
      margin-bottom: 6px;
      letter-spacing: 0.01em;
    }

    .fg-input {
      position: relative;
    }

    .fg-input input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #cbd5e1;
      border-radius: 12px;
      font-size: 0.938rem;
      font-family: inherit;
      outline: none;
      background: #f8fafc;
      color: #0f172a;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .fg-input input:focus {
      border-color: #ef4444;
      background: #ffffff;
      box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
    }

    .fg-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      font-size: 0.95rem;
      padding: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color 0.15s;
    }

    .fg-toggle:hover {
      color: #475569;
    }

    .fg-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 22px 0;
    }

    .fg-checkbox {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .fg-checkbox input[type=checkbox] {
      width: 16px;
      height: 16px;
      accent-color: #ef4444;
      cursor: pointer;
      border-radius: 4px;
      border: 1px solid #cbd5e1;
    }

    .fg-checkbox label {
      font-size: 0.813rem;
      font-weight: 500;
      color: #64748b;
      cursor: pointer;
      user-select: none;
    }

    .r-forgot a {
      font-size: 0.813rem;
      font-weight: 600;
      color: #ef4444;
      text-decoration: none;
      transition: color 0.15s;
    }

    .r-forgot a:hover {
      color: #b91c1c;
      text-decoration: underline;
    }

    .btn-signin {
      width: 100%;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: #ffffff;
      border: none;
      padding: 14px;
      border-radius: 12px;
      font-size: 0.938rem;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-signin:hover {
      background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
      box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
      transform: translateY(-1px);
    }

    .btn-signin:active {
      transform: translateY(1px);
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
    }

    .r-error {
      background: #fef2f2;
      color: #ef4444;
      border: 1px solid #fee2e2;
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 0.813rem;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
      box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.05);
    }

    .r-error i {
      font-size: 1rem;
    }

    .r-footer {
      text-align: center;
      margin-top: 36px;
      padding-top: 24px;
      border-top: 1px solid #f1f5f9;
    }

    .btn-back-home {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #64748b;
      text-decoration: none;
      font-size: 0.813rem;
      font-weight: 600;
      transition: color 0.15s, transform 0.15s;
      margin-bottom: 16px;
    }

    .btn-back-home:hover {
      color: #0f172a;
      transform: translateX(-2px);
    }

    .contact-help {
      font-size: 0.75rem;
      color: #94a3b8;
      font-weight: 500;
    }

    .contact-help a {
      color: #ef4444;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.15s;
    }

    .contact-help a:hover {
      color: #b91c1c;
      text-decoration: underline;
    }

    /* Enhanced Custom Tooltips */
    .map-tooltip {
      position: absolute;
      bottom: 100%;
      left: 50%;
      transform: translate(-50%, 10px);
      background: rgba(15, 23, 42, 0.95);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      color: #ffffff;
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 0.65rem;
      white-space: nowrap;
      box-shadow: 0 8px 12px -3px rgba(15, 23, 42, 0.4), 0 3px 6px -3px rgba(15, 23, 42, 0.4);
      z-index: 10;
      border: 1px solid rgba(255, 255, 255, 0.12);
      pointer-events: none;
      opacity: 0;
      visibility: hidden;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .map-tooltip::after {
      content: '';
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      border-width: 5px;
      border-style: solid;
      border-color: rgba(15, 23, 42, 0.95) transparent transparent transparent;
    }

    .map-dot:hover .map-tooltip {
      opacity: 1;
      visibility: visible;
      transform: translate(-50%, -10px);
    }

    .mt-title {
      font-weight: 700;
      color: #f8fafc;
      margin-bottom: 2px;
      font-size: 0.7rem;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .mt-title i {
      color: #ef4444;
      font-size: 0.62rem;
    }

    .mt-row {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      color: #94a3b8;
      line-height: 1.3;
    }

    .mt-row strong {
      color: #ef4444;
    }

    /* SVG Flight Paths */
    .map-flight-paths {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
      pointer-events: none;
    }

    .flight-pulse {
      fill: none;
      stroke: #ef4444;
      stroke-width: 0.25px;
      stroke-linecap: round;
      stroke-dasharray: 1.5 450;
      filter: url(#glow);
      animation: pulseFlow 65s linear infinite;
    }

    @keyframes pulseFlow {
      0% {
        stroke-dashoffset: 451.5;
      }
      100% {
        stroke-dashoffset: 0;
      }
    }

    /* RESPONSIVE DESIGN */
    @media(max-width: 1024px) {
      .login-split {
        grid-template-columns: 1fr;
      }

      .left {
        padding: 40px;
        min-height: auto;
        justify-content: center;
        gap: 30px;
      }

      .left-body {
        margin: 40px 0;
      }

      .left h1 {
        font-size: 2.5rem;
      }

      .left-map {
        display: none;
      }

      .right {
        padding: 40px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="login-split">

    <!-- LEFT: Brand Showcase -->
    <div class="left">
      <div class="left-logo">
        <img src="assets/askvisa-logo.png" alt="AskVisa">
      </div>
      <div class="left-body">
        <h1>Your trusted<br>visa processing<br><span>partner</span></h1>
        <div class="left-stats">
          <div class="left-stat">
            <div class="left-stat-icon"><i class="fa-solid fa-bolt"></i></div>
            <div class="left-stat-text">Fast Processing</div>
          </div>
          <div class="left-stat">
            <div class="left-stat-icon"><i class="fa-solid fa-headset"></i></div>
            <div class="left-stat-text">24/7 Support</div>
          </div>
          <div class="left-stat">
            <div class="left-stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="left-stat-text">Secure Platform</div>
          </div>
        </div>
      </div>
      <div class="left-map">
        <div class="left-map-wrapper">
          <img src="assets/world-map.png" alt="World Map">
          
          <!-- Animated SVG Flight Paths -->
          <svg class="map-flight-paths" viewBox="0 0 100 100" preserveAspectRatio="none" style="overflow: visible;">
            <defs>
              <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur stdDeviation="0.4" result="blur" />
                <feMerge>
                  <feMergeNode in="blur" />
                  <feMergeNode in="blur" />
                  <feMergeNode in="SourceGraphic" />
                </feMerge>
              </filter>
            </defs>
            <!-- Only one path/pulse traveling around the loop slowly without background line -->
            <path class="flight-pulse" d="M 35,22 Q 55,25 71,42 Q 52,60 30,56 Q 42,30 62,25 Q 75,40 81,63 Q 65,60 47,36 Q 63,40 74,58 Q 68,54 58,46 Q 62,45 66,49 Q 48,36 35,22" />
          </svg>

          <!-- Interactive pulsing global map hotspots with custom tooltips -->
          <div class="map-dot" style="top: 22%; left: 35%; animation-delay: 0.1s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> Greenland Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>7 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>98.1%</strong></div>
            </div>
          </div>
          <div class="map-dot" style="top: 25%; left: 62%; animation-delay: 0.2s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> Russia Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>5 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>98.5%</strong></div>
            </div>
          </div>
          <div class="map-dot" style="top: 42%; left: 71%; animation-delay: 0.3s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> China Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>3 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>99.1%</strong></div>
            </div>
          </div>
          <div class="map-dot" style="top: 36%; left: 47%; animation-delay: 0.4s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> London Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>5 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>99.2%</strong></div>
            </div>
          </div>
          <div class="map-dot" style="top: 49%; left: 66%; animation-delay: 0.5s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> New Delhi Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>4 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>99.4%</strong></div>
            </div>
          </div>
          <div class="map-dot" style="top: 46%; left: 58%; animation-delay: 0.6s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> Dubai Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>2 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>99.8%</strong></div>
            </div>
          </div>
          <div class="map-dot" style="top: 58%; left: 74%; animation-delay: 0.7s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> Singapore Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>3 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>99.7%</strong></div>
            </div>
          </div>
          <div class="map-dot" style="top: 63%; left: 81%; animation-delay: 0.8s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> Sydney Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>6 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>98.9%</strong></div>
            </div>
          </div>
          <div class="map-dot" style="top: 56%; left: 30%; animation-delay: 0.9s;">
            <div class="map-tooltip">
              <div class="mt-title"><i class="fa-solid fa-plane-arrival"></i> South America Hub</div>
              <div class="mt-row"><span>Avg. Speed:</span> <strong>6 Days</strong></div>
              <div class="mt-row"><span>Approval:</span> <strong>98.7%</strong></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Login Form Card -->
    <div class="right">
      <!-- Session Status Badge -->
      <div class="status-badge">
        <span class="status-dot"></span>
        <span>Secure B2B Portal</span>
      </div>

      <div class="right-wrap">
        <div class="r-header">
          <h2 class="r-title">Agent Login</h2>
          <p class="r-subtitle">Access the AskVisa B2B partner dashboard</p>
        </div>

        <?php if ($error): ?>
          <div class="r-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" autocomplete="on">
          <div class="fg">
            <label for="email">Email</label>
            <div class="fg-input">
              <input type="email" name="email" id="email" required autofocus placeholder="you@agency.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
          </div>
          <div class="fg">
            <label for="password">Password</label>
            <div class="fg-input">
              <input type="password" name="password" id="password" required placeholder="Your password">
              <button type="button" class="fg-toggle" onclick="togglePw()" tabindex="-1">
                <i class="fa-solid fa-eye" id="eye-icon"></i>
              </button>
            </div>
          </div>

          <div class="fg-row">
            <div class="fg-checkbox">
              <input type="checkbox" id="remember" name="remember">
              <label for="remember">Remember me</label>
            </div>
            <div class="r-forgot">
              <a href="#">Forgot password?</a>
            </div>
          </div>

          <button type="submit" class="btn-signin">Sign In</button>
        </form>

        <div class="r-footer">
          <a href="landing.php" class="btn-back-home">
            <i class="fa-solid fa-arrow-left"></i> Back to Home
          </a>
          <div class="contact-help">
            Need access? Contact <a href="mailto:partnerships@askvisa.in">partnerships@askvisa.in</a>
          </div>
        </div>
      </div>
    </div>

  </div>
  <script>
    function togglePw() {
      const p = document.getElementById('password');
      const icon = document.getElementById('eye-icon');
      if (p.type === 'password') {
        p.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
      } else {
        p.type = 'password';
        icon.className = 'fa-solid fa-eye';
      }
    }
  </script>
</body>

</html>