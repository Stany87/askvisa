<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$agent = getAgent();
$stats = getAgentStats($agent['id']);
$recent = getRecentOrders($agent['id'], 10);
$countries = getCountries();
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — <?= htmlspecialchars($agent['agency']) ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sb-logo">AskVisa <span class="badge">B2B</span></div>
      <nav class="sb-nav">
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="new-order.php">📝 New Application</a>
        <a href="orders.php">📋 All Orders</a>
        <a href="orders.php?search=">🔍 Track Order</a>
        <a href="#pricing">💰 Pricing</a>
        <a href="#support">📞 Support</a>
      </nav>
      <div class="sb-footer">
        <div class="sb-agent"><?= htmlspecialchars($agent['name']) ?></div>
        <div class="sb-agency"><?= htmlspecialchars($agent['agency']) ?></div>
        <a href="?logout=1" class="sb-logout">Logout</a>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <div class="topbar">
        <h2>Dashboard</h2>
        <a href="new-order.php" class="btn-primary">+ New Application</a>
      </div>

      <!-- STATS -->
      <div class="stat-row">
        <div class="stat-card">
          <div class="stat-num"><?= $stats['total'] ?></div>
          <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card stat-yellow">
          <div class="stat-num"><?= $stats['processing'] ?></div>
          <div class="stat-label">Processing</div>
        </div>
        <div class="stat-card stat-green">
          <div class="stat-num"><?= $stats['approved'] ?></div>
          <div class="stat-label">Approved</div>
        </div>
        <div class="stat-card stat-red">
          <div class="stat-num"><?= $stats['rejected'] ?></div>
          <div class="stat-label">Rejected</div>
        </div>
      </div>

      <!-- RECENT ORDERS -->
      <div class="card">
        <div class="card-head">
          <h3>Recent Orders</h3>
          <a href="orders.php" class="link">View All →</a>
        </div>
        <?php if (empty($recent)): ?>
          <div class="empty">No orders yet. <a href="new-order.php">Create your first application</a>.</div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Country</th>
                <th>Pax</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $o): ?>
              <tr>
                <td><strong>#<?= $o['id'] ?></strong></td>
                <td><?= htmlspecialchars($o['customer_name'] ?: $o['email']) ?></td>
                <td><?= htmlspecialchars($o['country_name']) ?></td>
                <td><?= $o['applicant_count'] ?></td>
                <td><?= statusBadge($o['visa_status']) ?></td>
                <td><?= $o['currency'] ?> <?= number_format($o['total_amount'], 2) ?></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td><a href="order-detail.php?id=<?= $o['id'] ?>" class="btn-sm">View</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

    </main>
  </div>

<?php
// Handle logout
if (isset($_GET['logout'])) { logout(); }
?>
</body>
</html>
