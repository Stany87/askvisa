<?php
session_start();
require 'db.php';

// Get order ID from URL
$order_id = $_GET['order_id'] ?? $_SESSION['current_order_id'] ?? 0;
$invoice_email = $_GET['email'] ?? '';

if (!$order_id) {
    header('Location: exco.php');
    exit;
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
        vo.created_at,
        c.country_name 
        FROM visa_orders vo 
        LEFT JOIN countries c ON vo.country_id = c.id 
        WHERE vo.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: exco.php');
        exit;
    }
    
    // Fetch payment details
    $stmt = $pdo->prepare("SELECT 
        provider,
        provider_payment_id,
        amount,
        currency,
        status,
        created_at
        FROM payments 
        WHERE order_id = ? AND status = 'completed'
        ORDER BY created_at DESC 
        LIMIT 1");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch();
    
} catch (Exception $e) {
    die("Error fetching order details: " . $e->getMessage());
}

// Clear session order ID after successful payment
unset($_SESSION['current_order_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Ask Visa Portal</title>
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-lg);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        .success-header {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            padding: 40px 30px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            animation: successShimmer 3s linear infinite;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 48px;
            animation: bounce 1s ease-in-out;
        }

        .success-icon i {
            animation: checkmark 0.5s ease-out 0.5s both;
        }

        .success-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .success-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .success-content {
            padding: 30px;
        }

        .order-details {
            background: var(--gray-light);
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
            color: var(--primary);
            font-weight: 500;
            text-align: right;
        }

        .email-notice {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: var(--border-radius-sm);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .email-icon {
            font-size: 24px;
            color: #4caf50;
        }

        .email-notice p {
            flex: 1;
            color: #2e7d32;
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
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .action-btn:hover::before {
            left: 100%;
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

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-success {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .security-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
            text-align: center;
            color: var(--gray);
            font-size: 13px;
        }

        .security-footer i {
            color: var(--success);
            margin-right: 5px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }

        @keyframes checkmark {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        @keyframes successShimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }

        @media (max-width: 768px) {
            .success-container {
                max-width: 100%;
            }
            
            .success-header {
                padding: 30px 20px;
            }
            
            .success-header h1 {
                font-size: 24px;
            }
            
            .success-content {
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
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p>Your visa application payment has been processed successfully</p>
        </div>
        
        <div class="success-content">
            <div class="order-details">
                <div class="detail-item">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value">#<?php echo htmlspecialchars($order['id']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value"><?php echo $payment ? htmlspecialchars($payment['provider_payment_id']) : 'N/A'; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value">$<?php echo number_format($order['total_amount'], 2); ?> <?php echo htmlspecialchars($order['currency']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value"><?php echo $payment ? ucfirst(htmlspecialchars($payment['provider'])) : 'Credit Card'; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y g:i A', strtotime($payment ? $payment['created_at'] : $order['created_at'])); ?></span>
                </div>
                <?php if (!empty($order['country_name'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Destination:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['country_name']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($invoice_email): ?>
            <div class="email-notice">
                <i class="fas fa-paper-plane email-icon"></i>
                <p>
                    <strong>Invoice Sent!</strong> A payment confirmation and invoice has been sent to 
                    <strong><?php echo htmlspecialchars($invoice_email); ?></strong>.
                    Please check your inbox (and spam folder).
                </p>
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <a href="exco.php" class="action-btn btn-primary">
                    <i class="fas fa-home"></i> Back to Application
                </a>
                <button onclick="window.print()" class="action-btn btn-secondary">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <button onclick="downloadSummary()" class="action-btn btn-success">
                    <i class="fas fa-download"></i> Download Summary
                </button>
            </div>
            
            <div class="security-footer">
                <i class="fas fa-shield-alt"></i>
                Your payment is secured and protected. Order status: <strong><?php echo strtoupper($order['payment_status']); ?></strong>
            </div>
        </div>
    </div>

    <script>
        function downloadSummary() {
            const summary = `Payment Summary
========================
Order ID: #<?php echo $order['id']; ?>
Transaction ID: <?php echo $payment ? $payment['provider_payment_id'] : 'N/A'; ?>
Amount Paid: $<?php echo number_format($order['total_amount'], 2); ?> <?php echo $order['currency']; ?>
Payment Method: <?php echo $payment ? ucfirst($payment['provider']) : 'Credit Card'; ?>
Payment Date: <?php echo date('F j, Y g:i A', strtotime($payment ? $payment['created_at'] : $order['created_at'])); ?>
Status: PAID
Generated on: ${new Date().toLocaleString()}`;
            
            const blob = new Blob([summary], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `payment-summary-<?php echo $order['id']; ?>.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        // Confetti effect for celebration
        function celebrate() {
            if (typeof confetti === 'function') {
                confetti({
                    particleCount: 150,
                    spread: 70,
                    origin: { y: 0.6 }
                });
                
                setTimeout(() => {
                    confetti({
                        particleCount: 100,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0 }
                    });
                }, 250);
                
                setTimeout(() => {
                    confetti({
                        particleCount: 100,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1 }
                    });
                }, 400);
            }
        }
        
        // Load confetti library and trigger celebration
        setTimeout(() => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js';
            script.onload = celebrate;
            document.head.appendChild(script);
        }, 500);
        
        // Print styles
        const style = document.createElement('style');
        style.innerHTML = `
            @media print {
                body * {
                    visibility: hidden;
                }
                .success-container, .success-container * {
                    visibility: visible;
                }
                .success-container {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    max-width: 100%;
                    box-shadow: none;
                }
                .actions, .action-btn::before {
                    display: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>