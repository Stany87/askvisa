<?php
session_start();
require 'db.php';

$order_id = $_GET['order_id'] ?? 0;

if (!$order_id) {
    // Try to get from session
    $order_id = $_SESSION['payment_success_order_id'] ?? 0;
}

if (!$order_id) {
    header('Location: index.php');
    exit;
}

// Get order details
$stmt = $pdo->prepare("
    SELECT vo.*, c.country_name 
    FROM visa_orders vo 
    JOIN countries c ON vo.country_id = c.id 
    WHERE vo.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get payment details
$stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

// Generate invoice number
$invoice_number = 'INV-' . date('Ymd') . '-' . $order_id;

// Get applicant count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM applicants WHERE order_id = ?");
$stmt->execute([$order_id]);
$applicant_count = $stmt->fetch();
$total_people = $applicant_count['total'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful - Visa Application</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #dc2626;
            --primary-hover: #b91c1c;
            --primary-light: rgba(220, 38, 38, 0.1);
            --dark: #0f172a;
            --dark-surface: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border: rgba(51, 65, 85, 0.8);
            --radius-lg: 24px;
            --radius-md: 12px;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(at 0% 0%, rgba(220, 38, 38, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(220, 38, 38, 0.05) 0px, transparent 50%);
        }

        .success-wrapper {
            width: 100%;
            max-width: 1100px;
            height: 650px;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Left Side: Celebration & Steps */
        .celebration-side {
            padding: 50px;
            background: rgba(15, 23, 42, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: left;
        }

        .success-badge {
            width: 70px;
            height: 70px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 24px;
            animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .celebration-side h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 12px; }
        .celebration-side p { color: var(--text-muted); font-size: 1rem; margin-bottom: 48px; max-width: 400px; line-height: 1.5; }

        .timeline { 
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .timeline-item {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }
        .timeline-icon {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: var(--primary);
            flex-shrink: 0;
            border: 1px solid var(--border);
        }
        .timeline-content h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 4px; }
        .timeline-content p { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0; }

        /* Right Side: Receipt & Actions */
        .receipt-side {
            padding: 50px;
            background: rgba(30, 41, 59, 0.2);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .receipt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .receipt-header h3 { font-size: 1.2rem; font-weight: 700; color: var(--primary); }
        .invoice-tag { padding: 4px 12px; background: rgba(255,255,255,0.05); border-radius: 20px; font-size: 0.75rem; color: var(--text-muted); border: 1px solid var(--border); }

        .receipt-list { margin-bottom: 32px; }
        .receipt-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .item-label { font-size: 0.9rem; color: var(--text-muted); }
        .item-value { font-size: 0.9rem; font-weight: 600; }

        .total-box {
            margin-top: auto;
            padding: 24px;
            background: rgba(255,255,255,0.03);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            margin-bottom: 32px;
        }
        .total-row { display: flex; justify-content: space-between; align-items: baseline; }
        .total-price { font-size: 2rem; font-weight: 800; color: var(--text-main); }
        .total-price span { font-size: 1rem; opacity: 0.6; margin-right: 4px; }

        .action-group { display: flex; flex-direction: column; gap: 12px; }
        .btn-primary {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.2);
        }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-3px); box-shadow: 0 15px 30px rgba(220, 38, 38, 0.3); }

        .btn-secondary {
            width: 100%;
            padding: 16px;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: var(--transition);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.05); color: var(--text-main); }

        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 900px) {
            .success-wrapper { grid-template-columns: 1fr; height: auto; max-height: 90vh; overflow-y: auto; max-width: 500px; }
            .receipt-side { border-left: none; border-top: 1px solid var(--border); }
            body { overflow: auto; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="success-wrapper">
        <!-- Left: Celebration -->
        <div class="celebration-side">
            <div class="success-badge">
                <i class="fas fa-check"></i>
            </div>
            <h1>Confirmed!</h1>
            <p>Your visa application payment has been securely processed and sent for review.</p>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-icon"><i class="fas fa-envelope"></i></div>
                    <div class="timeline-content">
                        <h4>Confirmation Email</h4>
                        <p>Check your inbox for the formal receipt and application ID.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon"><i class="fas fa-search"></i></div>
                    <div class="timeline-content">
                        <h4>Initial Screening</h4>
                        <p>Our experts will verify your documents within 24-48 hours.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon"><i class="fas fa-passport"></i></div>
                    <div class="timeline-content">
                        <h4>Decision Alert</h4>
                        <p>You'll receive a notification immediately upon approval.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Receipt -->
        <div class="receipt-side">
            <div class="receipt-header">
                <h3>Order Receipt</h3>
                <span class="invoice-tag"><?php echo $invoice_number; ?></span>
            </div>

            <div class="receipt-list">
                <div class="receipt-item">
                    <span class="item-label">Order ID</span>
                    <span class="item-value">#<?php echo $order_id; ?></span>
                </div>
                <div class="receipt-item">
                    <span class="item-label">Destination</span>
                    <span class="item-value"><?php echo htmlspecialchars($order['country_name']); ?></span>
                </div>
                <div class="receipt-item">
                    <span class="item-label">Applicants</span>
                    <span class="item-value"><?php echo $total_people; ?> Person(s)</span>
                </div>
                <div class="receipt-item">
                    <span class="item-label">Date</span>
                    <span class="item-value"><?php echo date('d M Y, h:i A'); ?></span>
                </div>
                <?php if ($payment && $payment['provider_payment_id']): ?>
                <div class="receipt-item">
                    <span class="item-label">Transaction ID</span>
                    <span class="item-value"><?php echo substr($payment['provider_payment_id'], 0, 15) . '...'; ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="total-box">
                <div class="total-row">
                    <span class="item-label">Amount Paid</span>
                    <div class="total-price">
                        <span><?php echo $order['currency']; ?></span><?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                </div>
            </div>

            <div class="action-group">
                <a href="generate_invoice.php?order_id=<?php echo $order_id; ?>" class="btn-primary" target="_blank">
                    <i class="fas fa-download"></i> Download Invoice
                </a>
                <a href="track_application.php?order_id=<?php echo $order_id; ?>" class="btn-secondary">
                    <i class="fas fa-search-location"></i> Track My Application
                </a>
                <a href="index.php" class="btn-secondary">
                    Back to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html>