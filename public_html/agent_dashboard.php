<?php
session_start();
require_once 'db.php';

// Check if agent is logged in
if (!isset($_SESSION['b2b_agent_id'])) {
    header("Location: /login/b2b");
    exit;
}

$agent_id = $_SESSION['b2b_agent_id'];
$company_name = $_SESSION['b2b_company_name'];

// Fetch agent's applications
$applications = [];
try {
    // Check if agent_id column exists in visa_orders
    $stmt = $pdo->prepare("SHOW COLUMNS FROM visa_orders LIKE 'agent_id'");
    $stmt->execute();
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("SELECT v.*, a.applicant_email, a.applicant_phone FROM visa_orders v LEFT JOIN applicants a ON v.id = a.order_id WHERE v.agent_id = :agent_id GROUP BY v.id ORDER BY v.id DESC");
        $stmt->execute([':agent_id' => $agent_id]);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (\PDOException $e) {
    // Column might not exist yet or other DB error
    $error_msg = "Database error fetching applications.";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - <?php echo htmlspecialchars($company_name); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .header {
            background-color: #343a40;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: #fff;
            text-decoration: none;
            background: #dc3545;
            padding: 8px 15px;
            border-radius: 4px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #e9ecef;
        }

        .btn-new {
            display: inline-block;
            background: #28a745;
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div>
            <h2><?php echo htmlspecialchars($company_name); ?> - Partner Portal</h2>
        </div>
        <div><a href="/agent_logout.php">Logout</a></div>
    </div>

    <div class="container">
        <h2>Your Applications</h2>

        <a href="/index.php?agent_token=<?php echo session_id(); ?>" class="btn-new">+ Submit New Application</a>

        <?php if (isset($error_msg)): ?>
            <p style="color: red;"><?php echo $error_msg; ?></p>
        <?php endif; ?>

        <?php if (empty($applications)): ?>
            <p>No applications found. Click 'Submit New Application' to start.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Applicant Name</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($app['id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($app['email'] ?? $app['applicant_email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($app['visa_type'] ?? 'N/A'); ?></td>
                            <td>
                                <span style="padding: 5px 10px; border-radius: 20px; background: #e9ecef; font-size: 14px;">
                                    <?php echo htmlspecialchars($app['visa_status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($app['created_at'] ?? 'now'))); ?></td>
                            <td>
                                <a href="/view_applicant_detail.php?id=<?php echo htmlspecialchars($app['id'] ?? ''); ?>"
                                    target="_blank" style="color: #007bff; text-decoration: none;">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>

</html>