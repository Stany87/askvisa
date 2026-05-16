<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$agent = getAgent();

$orderId = intval($_GET['id'] ?? 0);
$order = getOrderDetail($orderId, $agent['id']);
if (!$order) {
    header('Location: orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order #<?= $order['id'] ?> — <?= htmlspecialchars($agent['agency']) ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="layout">
    <aside class="sidebar">
      <div class="sb-logo">AskVisa <span class="badge">B2B</span></div>
      <nav class="sb-nav">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="new-order.php">📝 New Application</a>
        <a href="orders.php" class="active">📋 All Orders</a>
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
      <div class="topbar">
        <div>
          <a href="orders.php" class="back-link">← Back to Orders</a>
          <h2>Order #<?= $order['id'] ?></h2>
        </div>
      </div>

      <!-- ORDER HEADER -->
      <div class="detail-header">
        <div class="dh-item"><span class="dh-label">Country</span><span class="dh-value"><?= htmlspecialchars($order['country_name']) ?></span></div>
        <div class="dh-item"><span class="dh-label">Visa Status</span><span class="dh-value"><?= statusBadge($order['visa_status']) ?></span></div>
        <div class="dh-item"><span class="dh-label">Payment</span><span class="dh-value"><?= statusBadge($order['payment_status']) ?></span></div>
        <div class="dh-item"><span class="dh-label">Amount</span><span class="dh-value"><?= $order['currency'] ?> <?= number_format($order['total_amount'], 2) ?></span></div>
        <div class="dh-item"><span class="dh-label">Date</span><span class="dh-value"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span></div>
        <div class="dh-item"><span class="dh-label">Email</span><span class="dh-value"><?= htmlspecialchars($order['email']) ?></span></div>
      </div>

      <!-- APPLICANTS -->
      <?php foreach ($order['applicants'] as $app): ?>
      <div class="card">
        <div class="card-head">
          <h3>Applicant #<?= $app['applicant_no'] ?></h3>
          <?= statusBadge($app['visa_status']) ?>
        </div>
        <table class="detail-table">
          <?php foreach ($app['answers'] as $ans): ?>
          <tr>
            <td class="dt-label"><?= htmlspecialchars($ans['label']) ?></td>
            <td>
              <?php if ($ans['field_type'] === 'file'): ?>
                <em>(File uploaded)</em>
              <?php else: ?>
                <?= htmlspecialchars($ans['answer_text']) ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>

        <?php if (!empty($app['files'])): ?>
        <div class="files-section">
          <h4>Uploaded Documents</h4>
          <div class="files-list">
            <?php foreach ($app['files'] as $file): ?>
              <div class="file-item">📎 <?= basename($file['file_path']) ?> <span class="file-type">(<?= $file['file_type'] ?>)</span></div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>

      <!-- PAYMENT -->
      <?php if ($order['payment']): ?>
      <div class="card">
        <div class="card-head"><h3>Payment Details</h3></div>
        <table class="detail-table">
          <tr><td class="dt-label">Provider</td><td><?= htmlspecialchars($order['payment']['provider']) ?></td></tr>
          <tr><td class="dt-label">Payment ID</td><td><?= htmlspecialchars($order['payment']['provider_payment_id']) ?></td></tr>
          <tr><td class="dt-label">Amount</td><td><?= $order['payment']['currency'] ?> <?= number_format($order['payment']['amount'], 2) ?></td></tr>
          <tr><td class="dt-label">Status</td><td><?= statusBadge($order['payment']['status']) ?></td></tr>
          <tr><td class="dt-label">Date</td><td><?= date('d M Y, h:i A', strtotime($order['payment']['created_at'])) ?></td></tr>
        </table>
      </div>
      <?php endif; ?>

    </main>
  </div>
</body>
</html>
