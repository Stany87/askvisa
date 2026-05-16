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
  <title>Agent Login â€” AskVisa B2B</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #eef1f5;
      color: #111827;
      -webkit-font-smoothing: antialiased
    }

    .login-split {
      display: grid;
      grid-template-columns: 3fr 2fr;
      height: 100vh
    }

    /* LEFT */
    .left {
      background: #f1f4f8;
      padding: 48px;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow: hidden
    }

    .left-logo {
      display: flex;
      align-items: center;
      gap: 8px;
      position: relative;
      z-index: 1
    }

    .left-logo img {
      height: 48px
    }

    .left-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      z-index: 1
    }

    .left h1 {
      font-size: 2.4rem;
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -.03em;
      margin-bottom: 36px
    }

    .left-stats {
      display: flex;
      gap: 14px;
      position: relative;
      z-index: 2
    }

    .left-stat {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 16px 18px;
      min-width: 110px
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
      margin-bottom: 8px
    }

    .left-stat-text {
      font-size: .75rem;
      font-weight: 600;
      color: #4b5563
    }

    .left-map {
      position: absolute;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 0;
      pointer-events: none
    }

    .left-map img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      object-position: bottom left;
      display: block;
      mix-blend-mode: multiply
    }

    /* RIGHT */
    .right {
      background: #fff;
      padding: 48px 60px 48px 48px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-end
    }

    .right-wrap {
      width: 100%;
      max-width: 420px
    }

    .r-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 6px;
      padding-bottom: 10px;
      border-bottom: 2px solid #dc2626;
      display: inline-block;
      margin-bottom: 32px
    }

    .fg {
      margin-bottom: 18px
    }

    .fg label {
      display: block;
      font-size: .8rem;
      font-weight: 600;
      margin-bottom: 5px
    }

    .fg-input {
      position: relative
    }

    .fg-input input {
      width: 100%;
      padding: 11px 14px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      font-size: .88rem;
      font-family: inherit;
      outline: none;
      transition: border .15s
    }

    .fg-input input:focus {
      border-color: #dc2626
    }

    .fg-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #9ca3af;
      cursor: pointer;
      font-size: .85rem
    }

    .fg-toggle:hover {
      color: #4b5563
    }

    .fg-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 22px
    }

    .fg-row input[type=checkbox] {
      width: 15px;
      height: 15px;
      accent-color: #dc2626;
      cursor: pointer
    }

    .fg-row label {
      font-size: .8rem;
      color: #4b5563;
      cursor: pointer
    }

    .btn-signin {
      width: 100%;
      background: #dc2626;
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 24px;
      font-size: .88rem;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: background .15s
    }

    .btn-signin:hover {
      background: #b91c1c
    }

    .r-forgot {
      text-align: center;
      margin-top: 16px
    }

    .r-forgot a {
      font-size: .8rem;
      color: #4b5563;
      text-decoration: none
    }

    .r-forgot a:hover {
      color: #111827
    }

    .r-contact {
      text-align: center;
      margin-top: 28px;
      font-size: .76rem;
      color: #9ca3af
    }

    .r-contact a {
      color: #dc2626;
      text-decoration: none;
      font-weight: 500
    }

    .r-error {
      background: #fef2f2;
      color: #dc2626;
      border: 1px solid #fecaca;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: .82rem;
      margin-bottom: 18px
    }

    @media(max-width:768px) {
      .login-split {
        grid-template-columns: 1fr
      }

      .left {
        display: none
      }

      .right {
        padding: 32px 24px;
        max-width: 100%
      }
    }
  </style>
</head>

<body>
  <div class="login-split">

    <!-- LEFT -->
    <div class="left">
      <div class="left-logo">
        <img src="assets/askvisa-logo.png" alt="AskVisa">
      </div>
      <div class="left-body">
        <h1>Your trusted<br>visa processing<br>partner</h1>
        <div class="left-stats">
          <div class="left-stat">
            <div class="left-stat-icon"><i class="fas fa-bolt"></i></div>
            <div class="left-stat-text">Fast Processing</div>
          </div>
          <div class="left-stat">
            <div class="left-stat-icon"><i class="fas fa-headset"></i></div>
            <div class="left-stat-text">24/7 Support</div>
          </div>
          <div class="left-stat">
            <div class="left-stat-icon"><i class="fas fa-shield-halved"></i></div>
            <div class="left-stat-text">Secure Platform</div>
          </div>
        </div>
      </div>
      <div class="left-map">
        <img src="assets/world-map.png" alt="World Map">
      </div>
    </div>

    <!-- RIGHT -->
    <div class="right">
      <div class="right-wrap">
        <h2 class="r-title">Agent Login</h2>

        <?php if ($error): ?>
          <div class="r-error"><?= htmlspecialchars($error) ?></div>
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
              <button type="button" class="fg-toggle" onclick="togglePw()" tabindex="-1"><i
                  class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="fg-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
          </div>
          <button type="submit" class="btn-signin">Sign In</button>
        </form>

        <div class="r-forgot"><a href="#">Forgot password?</a></div>
        <div class="r-contact"><a href="landing.php" style="color:#dc2626;text-decoration:none;font-weight:500;font-size:.76rem;">← Back to Home</a><div style="margin-top:8px;"></div>Need access? Contact <a href="mailto:partnerships@askvisa.in">partnerships@askvisa.in</a>
        </div>
      </div>
    </div>

  </div>
  <script>
    function togglePw() { const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password' }
  </script>
</body>

</html>

