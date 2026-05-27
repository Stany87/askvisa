<?php
require_once __DIR__ . '/../auth.php';
requireLogin();
$agent = getAgent();
$countries = getCountries();

$filters = [
    'country' => $_GET['country'] ?? '',
    'status'  => $_GET['status'] ?? '',
    'search'  => $_GET['search'] ?? '',
    'page'    => $_GET['page'] ?? 1,
];
$result = getAllOrders($agent['id'], $filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Orders — <?= htmlspecialchars($agent['agency']) ?></title>
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
        <h2>All Orders <span class="count">(<?= $result['total'] ?>)</span></h2>
        <a href="new-order.php" class="btn-primary">+ New Application</a>
      </div>

      <!-- FILTERS -->
      <div class="filter-bar">
        <form method="GET" class="filter-form">
          <input type="text" name="search" placeholder="Search by Order # or Email..." value="<?= htmlspecialchars($filters['search']) ?>" class="filter-input">
          <select name="country" class="filter-select">
            <option value="">All Countries</option>
            <?php foreach ($countries as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $filters['country'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['country_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="status" class="filter-select">
            <option value="">All Statuses</option>
            <option value="initiated" <?= $filters['status'] === 'initiated' ? 'selected' : '' ?>>Initiated</option>
            <option value="in_review" <?= $filters['status'] === 'in_review' ? 'selected' : '' ?>>In Review</option>
            <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
          </select>
          <button type="submit" class="btn-filter">Filter</button>
          <?php if ($filters['search'] || $filters['country'] || $filters['status']): ?>
            <a href="orders.php" class="btn-clear">Clear</a>
          <?php endif; ?>
        </form>
      </div>

      <!-- TABLE -->
      <div class="card" style="padding:0">
        <?php if (empty($result['orders'])): ?>
          <div class="empty" style="padding:32px">No orders found.</div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Country</th>
                <th>Pax</th>
                <th>Visa Status</th>
                <th>Payment</th>
                <th>Amount</th>
                <th>Date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($result['orders'] as $o): ?>
              <tr>
                <td><strong>#<?= $o['id'] ?></strong></td>
                <td><?= htmlspecialchars($o['customer_name'] ?: $o['email']) ?></td>
                <td><?= htmlspecialchars($o['country_name']) ?></td>
                <td><?= $o['applicant_count'] ?></td>
                <td><?= statusBadge($o['visa_status']) ?></td>
                <td><?= statusBadge($o['payment_status']) ?></td>
                <td><?= $o['currency'] ?> <?= number_format($o['total_amount'], 2) ?></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td><a href="order-detail.php?id=<?= $o['id'] ?>" class="btn-sm">View</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <!-- PAGINATION -->
          <?php if ($result['pages'] > 1): ?>
          <div class="pagination">
            <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
              <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>" class="pg-btn <?= $i == $result['page'] ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
