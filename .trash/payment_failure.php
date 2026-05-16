<?php
session_start();
require 'db.php';

// Get order ID from URL
$order_id = $_GET['order_id'] ?? $_SESSION['current_order_id'] ?? 0;
$error_message = $_GET['error'] ?? '';

if (!$order_id) {
    header('Location: exco.php');
    exit;
}

// Fetch order details
try {
    $stmt = $pdo->prepare("SELECT 
        vo.id, 
        vo.email, 
        vo.total_amount,
        vo.currency,
        vo.created_at,
        vo.payment_status
        FROM visa_orders vo 
        WHERE vo.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
} catch (Exception $e) {
    $order = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Ask Visa Portal</title>
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
            --dark-light: #24283b;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 16px;
            --border-radius-sm: 8px;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --box-shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fdf2f2 0%, #fde8e8 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .failed-container {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-lg);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        .failed-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 40px 30px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .failed-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        }

        .failed-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 48px;
            animation: shake 0.5s ease-in-out;
        }

        .failed-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .failed-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .failed-content {
            padding: 30px;
        }

        .error-notice {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: var(--border-radius-sm);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .error-icon {
            font-size: 24px;
            color: #dc2626;
        }

        .error-notice p {
            flex: 1;
            color: #991b1b;
        }

        .order-details {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: var(--border-radius-sm);
            padding: 25px;
            margin-bottom: 30px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: var(--dark);
        }

        .detail-value {
            color: var(--danger);
            font-weight: 500;
            text-align: right;
        }

        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .action-btn {
            flex: 1;
            min-width: 200px;
            padding: 16px 24px;
            border-radius: var(--border-radius-sm);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .support-info {
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: var(--border-radius-sm);
            text-align: center;
        }

        .support-info h3 {
            color: var(--dark);
            margin-bottom: 10px;
            font-size: 18px;
        }

        .support-info p {
            color: var(--gray);
            margin-bottom: 5px;
        }

        .error-details {
            background: #f1f5f9;
            border-radius: var(--border-radius-sm);
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 14px;
            color: #64748b;
            display: <?php echo $error_message ? 'block' : 'none'; ?>;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @media (max-width: 768px) {
            .failed-container {
                max-width: 100%;
            }
            
            .failed-header {
                padding: 30px 20px;
            }
            
            .failed-header h1 {
                font-size: 24px;
            }
            
            .failed-content {
                padding: 20px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .action-btn {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="failed-container">
        <div class="failed-header">
            <div class="failed-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1>Payment Failed</h1>
            <p>There was an issue processing your payment</p>
        </div>
        
        <div class="failed-content">
            <div class="error-notice">
                <i class="fas fa-exclamation-triangle error-icon"></i>
                <p>
                    <strong>Important:</strong> Your payment was not processed successfully. 
                    No charges have been made to your account. Your order has NOT been confirmed.
                </p>
            </div>
            
            <?php if ($order): ?>
            <div class="order-details">
                <div class="detail-item">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value">#<?php echo htmlspecialchars($order['id']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value">$<?php echo number_format($order['total_amount'], 2); ?> <?php echo htmlspecialchars($order['currency']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #dc2626; font-weight: 600;"><?php echo strtoupper($order['payment_status']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="error-details">
                <strong>Error Details:</strong><br>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <a href="exco.php" class="action-btn btn-primary">
                    <i class="fas fa-redo"></i> Try Payment Again
                </a>
                <button onclick="contactSupport()" class="action-btn btn-danger">
                    <i class="fas fa-headset"></i> Contact Support
                </button>
                <a href="exco.php" class="action-btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
            
            <div class="support-info">
                <h3><i class="fas fa-life-ring"></i> Need Help?</h3>
                <p>Email: support@askvisa.com</p>
                <p>Phone: +1 (555) 123-4567</p>
                <p>Available 24/7</p>
            </div>
        </div>
    </div>

    <script>
        function contactSupport() {
            const orderId = '<?php echo $order_id; ?>';
            const subject = encodeURIComponent(`Payment Failed - Order #${orderId}`);
            const body = encodeURIComponent(`Hello Support Team,\n\nMy payment for Order #${orderId} has failed. Please assist me with this issue.\n\nThank you.`);
            window.location.href = `mailto:support@askvisa.com?subject=${subject}&body=${body}`;
        }
        
        // Store order ID in session for retry
        sessionStorage.setItem('failed_order_id', '<?php echo $order_id; ?>');
    </script>
</body>
</html>