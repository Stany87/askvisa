<?php
require_once __DIR__ . '/../auth.php';
requireLogin();
$agent = getAgent();
$countries = getCountries();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Application — <?= htmlspecialchars($agent['agency']) ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="layout">
    <aside class="sidebar">
      <div class="sb-logo">AskVisa <span class="badge">B2B</span></div>
      <nav class="sb-nav">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="new-order.php" class="active">📝 New Application</a>
        <a href="orders.php">📋 All Orders</a>
        <a href="orders.php?search=">🔍 Track Order</a>
        <a href="#pricing">💰 Pricing</a>
        <a href="#support">📞 Support</a>
      </nav>
      <div class="sb-footer">
        <div class="sb-agent"><?= htmlspecialchars($agent['name']) ?></div>
        <div class="sb-agency"><?= htmlspecialchars($agent['agency']) ?></div>
        <a href="dashboard.php?logout=1" class="sb-logout">Logout</a>
      </div>
    </aside>

    <main class="main">
      <div class="topbar"><h2>New Visa Application</h2></div>

      <div class="new-order-wrap">
        <div class="card" style="max-width:560px">
          <h3 style="margin-bottom:4px">Select Country</h3>
          <p class="card-sub">Choose a destination to start the visa application process.</p>

          <div class="country-list">
            <?php foreach ($countries as $c): ?>
            <a href="../../index.php?country=<?= urlencode($c['country_name']) ?>&source=b2b&agent_id=<?= $agent['id'] ?>&auto_open=chat"
               target="_blank" class="country-option">
              <span class="co-name"><?= htmlspecialchars($c['country_name']) ?></span>
              <span class="co-arrow">→</span>
            </a>
            <?php endforeach; ?>
          </div>

          <?php if (empty($countries)): ?>
            <div class="empty">No active countries available.</div>
          <?php endif; ?>
        </div>

        <div class="card" style="max-width:560px">
          <h3 style="margin-bottom:4px">How it works</h3>
          <p class="card-sub">Quick steps to process a visa application.</p>
          <ol class="steps-list">
            <li><strong>Select a country</strong> — Opens the visa application form</li>
            <li><strong>Fill applicant details</strong> — Name, passport, travel info</li>
            <li><strong>Upload documents</strong> — Passport scans, photos</li>
            <li><strong>Pay & submit</strong> — Payment processes the application</li>
            <li><strong>Track here</strong> — Order appears in your dashboard</li>
          </ol>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
