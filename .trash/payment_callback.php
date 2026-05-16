<?php
session_start();
require 'db.php';

// Get parameters
$order_id = $_GET['order_id'] ?? null;
$status = $_GET['status'] ?? null;
$transaction_id = $_GET['transaction_id'] ?? null;

if (!$order_id || !$status) {
    die('Invalid callback parameters');
}

// Fetch order details
try {
    $stmt = $pdo->prepare("SELECT 
        vo.id, 
        vo.email, 
        vo.phone,
        vo.payment_status,
        vo.total_amount,
        vo.currency,
        c.country_name
        FROM visa_orders vo 
        JOIN countries c ON vo.country_id = c.id 
        WHERE vo.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        die('Order not found');
    }
} catch (Exception $e) {
    die('Error fetching order: ' . $e->getMessage());
}

// Get payment details
$payment = null;
if ($transaction_id) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE provider_payment_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$transaction_id]);
    $payment = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Complete - Ask Visa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --dark: #1a1b26;
            --light: #f8f9fa;
            --border-radius: 16px;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        body.dark {
            --light: #1a1b26;
            --dark: #f8f9fa;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .callback-container {
            width: 100%;
            max-width: 500px;
            background: var(--light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            text-align: center;
        }

        .callback-header {
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 48px;
            position: relative;
        }

        .status-icon.success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border: 3px solid var(--success);
            animation: iconPulse 2s infinite;
        }

        .status-icon.processing {
            background: rgba(248, 150, 30, 0.1);
            color: var(--warning);
            border: 3px solid var(--warning);
            animation: spin 2s linear infinite;
        }

        .status-icon.failed {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border: 3px solid var(--danger);
        }

        .callback-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .callback-header p {
            font-size: 16px;
            opacity: 0.8;
            margin-bottom: 20px;
        }

        .transaction-id {
            background: rgba(67, 97, 238, 0.1);
            padding: 10px 20px;
            border-radius: 20px;
            display: inline-block;
            font-family: monospace;
            font-size: 14px;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .callback-content {
            padding: 30px;
            background: rgba(0, 0, 0, 0.02);
        }

        .order-details {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            color: var(--dark);
            opacity: 0.7;
        }

        .detail-value {
            font-weight: 600;
            color: var(--primary);
        }

        .amount-display {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary);
            margin: 20px 0;
        }

        .currency {
            font-size: 24px;
            color: var(--primary-light);
        }

        .callback-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .callback-btn {
            flex: 1;
            padding: 16px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .callback-btn.primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .callback-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .callback-btn.secondary {
            background: var(--light);
            border: 2px solid var(--primary-light);
            color: var(--primary);
        }

        .callback-btn.secondary:hover {
            background: rgba(67, 97, 238, 0.05);
        }

        .status-message {
            padding: 15px;
            border-radius: var(--border-radius);
            margin: 20px 0;
            font-size: 14px;
        }

        .status-message.success {
            background: rgba(76, 201, 240, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .status-message.processing {
            background: rgba(248, 150, 30, 0.1);
            border: 1px solid var(--warning);
            color: var(--warning);
        }

        .status-message.failed {
            background: rgba(247, 37, 133, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 600px) {
            .callback-actions {
                flex-direction: column;
            }
            
            .amount-display {
                font-size: 28px;
            }
        }
    </style>
</head>
<body id="body">
    <div class="callback-container">
        <div class="callback-header">
            <div class="status-icon <?php echo $status; ?>">
                <?php if ($status === 'success'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php elseif ($status === 'processing'): ?>
                    <i class="fas fa-spinner"></i>
                <?php else: ?>
                    <i class="fas fa-times-circle"></i>
                <?php endif; ?>
            </div>
            
            <h1>
                <?php if ($status === 'success'): ?>
                    Payment Successful!
                <?php elseif ($status === 'processing'): ?>
                    Payment Processing
                <?php else: ?>
                    Payment Failed
                <?php endif; ?>
            </h1>
            
            <p>
                <?php if ($status === 'success'): ?>
                    Your payment has been processed successfully.
                <?php elseif ($status === 'processing'): ?>
                    Your payment is being processed.
                <?php else: ?>
                    We couldn't process your payment.
                <?php endif; ?>
            </p>
            
            <?php if ($transaction_id): ?>
                <div class="transaction-id"><?php echo $transaction_id; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="callback-content">
            <div class="status-message <?php echo $status; ?>">
                <?php if ($status === 'success'): ?>
                    <i class="fas fa-check-circle"></i> Your visa application is now complete and will be processed.
                <?php elseif ($status === 'processing'): ?>
                    <i class="fas fa-spinner"></i> Please wait while we confirm your payment.
                <?php else: ?>
                    <i class="fas fa-exclamation-circle"></i> Please try again or contact support.
                <?php endif; ?>
            </div>
            
            <div class="order-details">
                <div class="detail-item">
                    <span class="detail-label">Order ID</span>
                    <span class="detail-value">#<?php echo $order['id']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Country</span>
                    <span class="detail-value"><?php echo $order['country_name']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Status</span>
                    <span class="detail-value" style="color: 
                        <?php echo $status === 'success' ? 'var(--success)' : 
                              ($status === 'processing' ? 'var(--warning)' : 'var(--danger)'); ?>">
                        <?php echo strtoupper($status); ?>
                    </span>
                </div>
                
                <div class="amount-display">
                    $<?php echo number_format($order['total_amount'], 2); ?>
                    <span class="currency"><?php echo $order['currency']; ?></span>
                </div>
                
                <?php if ($payment && isset($payment['payment_method'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Payment Method</span>
                        <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Payment Date</span>
                        <span class="detail-value"><?php echo date('d-m-Y H:i', strtotime($payment['created_at'])); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="callback-actions">
                <?php if ($status === 'success'): ?>
                    <a href="index.php?payment_return=1" class="callback-btn primary">
                        <i class="fas fa-home"></i>
                        Return to Chat
                    </a>
                    <a href="index.php?get_summary=1" class="callback-btn secondary">
                        <i class="fas fa-download"></i>
                        Download Summary
                    </a>
                <?php elseif ($status === 'processing'): ?>
                    <a href="index.php?payment_return=1" class="callback-btn primary">
                        <i class="fas fa-home"></i>
                        Return to Chat
                    </a>
                    <a href="payment.php?order_id=<?php echo $order_id; ?>" class="callback-btn secondary">
                        <i class="fas fa-redo"></i>
                        Check Status
                    </a>
                <?php else: ?>
                    <a href="payment.php?order_id=<?php echo $order_id; ?>" class="callback-btn primary">
                        <i class="fas fa-redo"></i>
                        Try Again
                    </a>
                    <a href="index.php?payment_return=1" class="callback-btn secondary">
                        <i class="fas fa-home"></i>
                        Return to Chat
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect for processing status after 5 seconds
        <?php if ($status === 'processing'): ?>
        setTimeout(() => {
            window.location.href = 'payment.php?order_id=<?php echo $order_id; ?>';
        }, 5000);
        <?php endif; ?>
        
        // Store order ID in localStorage for chat page
        localStorage.setItem('last_order_id', '<?php echo $order_id; ?>');
        localStorage.setItem('last_payment_status', '<?php echo $status; ?>');
    </script>
</body>
</html>