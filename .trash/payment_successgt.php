<?php
session_start();
require 'db.php';

// Check if we have a completed order
if (!isset($_SESSION['completed_order_id'])) {
    header('Location: gt.php');
    exit;
}

$order_id = $_SESSION['completed_order_id'];
$transaction_id = $_SESSION['transaction_id'] ?? '';

// Fetch order details from database
try {
    $stmt = $pdo->prepare("
        SELECT 
            vo.*,
            c.country_name,
            p.provider_payment_id,
            p.created_at as payment_date
        FROM visa_orders vo
        LEFT JOIN countries c ON vo.country_id = c.id
        LEFT JOIN payments p ON vo.id = p.order_id
        WHERE vo.id = ?
        ORDER BY p.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order_details) {
        header('Location: gt.php');
        exit;
    }
    
    // Get applicant count for this order
    $stmt = $pdo->prepare("SELECT COUNT(*) as applicant_count FROM applicants WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $applicant_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $applicant_count = $applicant_data['applicant_count'] ?? 0;
    
} catch (Exception $e) {
    // If there's an error, still show the page but with limited info
    $order_details = ['country_name' => 'Unknown', 'total_amount' => 0, 'currency' => 'USD'];
    $applicant_count = 0;
    $error_message = "Unable to fetch complete order details.";
}

// Clear the session variables
unset($_SESSION['completed_order_id']);
unset($_SESSION['transaction_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful - Ask Visa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #4cc9f0;
            --danger: #f72585;
            --dark: #1a1b26;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border-radius: 16px;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
            text-align: center;
            animation: fadeIn 1s ease;
        }
        
        .success-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 50px 40px;
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }
        
        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--success), var(--primary));
            background-size: 200% 100%;
            animation: shimmer 2s linear infinite;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--success) 0%, var(--primary-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            font-size: 48px;
            animation: bounce 1s infinite alternate;
            box-shadow: 0 10px 30px rgba(76, 201, 240, 0.3);
        }
        
        .success-content h1 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 32px;
        }
        
        .success-content p {
            color: var(--gray);
            margin-bottom: 25px;
            font-size: 18px;
        }
        
        .order-details {
            background: var(--light);
            padding: 25px;
            border-radius: var(--border-radius);
            margin: 30px 0;
            border-left: 4px solid var(--success);
            text-align: left;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .detail-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .detail-value {
            color: var(--primary);
            font-weight: 600;
        }
        
        .detail-value.paid {
            color: var(--success);
        }
        
        .order-id {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
            background: var(--light);
            padding: 15px 25px;
            border-radius: var(--border-radius);
            display: inline-block;
            margin: 20px 0;
            letter-spacing: 2px;
            animation: pulse 2s infinite;
            box-shadow: 0 0 20px rgba(67, 97, 238, 0.2);
        }
        
        .next-steps {
            background: #e8f4fd;
            padding: 20px;
            border-radius: var(--border-radius);
            margin: 30px 0;
            text-align: left;
        }
        
        .next-steps h3 {
            color: #036;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .next-steps ul {
            list-style: none;
            padding-left: 0;
        }
        
        .next-steps li {
            margin-bottom: 10px;
            padding-left: 30px;
            position: relative;
        }
        
        .next-steps li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success);
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
        .action-btn {
            padding: 15px 30px;
            border-radius: var(--border-radius);
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .action-btn.primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }
        
        .action-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .action-btn.secondary {
            background: var(--light);
            color: var(--dark);
            border: 1px solid var(--gray-light);
        }
        
        .action-btn.secondary:hover {
            background: var(--gray-light);
        }
        
        .email-notice {
            margin-top: 30px;
            padding: 15px;
            background: #e8f5e8;
            border-radius: var(--border-radius);
            color: #2e7d32;
            font-size: 14px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(67, 97, 238, 0.2); }
            50% { box-shadow: 0 0 30px rgba(67, 97, 238, 0.4); }
        }
        
        @keyframes shimmer {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @media (max-width: 768px) {
            .success-card {
                padding: 30px 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <div class="success-content">
                <h1>Payment Successful!</h1>
                <p>Your visa application has been submitted successfully.</p>
                
                <?php if (isset($error_message)): ?>
                <div style="background: #fff3cd; padding: 15px; border-radius: var(--border-radius); margin: 20px 0; border-left: 4px solid #ffc107;">
                    <p style="color: #856404; margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <?php echo htmlspecialchars($error_message); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="order-id">
                    Order #<?php echo htmlspecialchars($order_id); ?>
                </div>
                
                <div class="order-details">
                    <div class="detail-item">
                        <span class="detail-label">Transaction ID</span>
                        <span class="detail-value"><?php echo htmlspecialchars($transaction_id ?: $order_details['provider_payment_id'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Country</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order_details['country_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Applicants</span>
                        <span class="detail-value"><?php echo htmlspecialchars($applicant_count); ?> person(s)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Amount</span>
                        <span class="detail-value">$<?php echo number_format($order_details['total_amount'] ?? 0, 2); ?> <?php echo htmlspecialchars($order_details['currency'] ?? 'USD'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Payment Status</span>
                        <span class="detail-value paid">
                            <i class="fas fa-check-circle"></i> 
                            <?php echo strtoupper($order_details['payment_status'] ?? 'PAID'); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date & Time</span>
                        <span class="detail-value"><?php echo date('F j, Y, g:i a', strtotime($order_details['payment_date'] ?? 'now')); ?></span>
                    </div>
                </div>
                
                <div class="email-notice">
                    <i class="fas fa-envelope"></i>
                    A confirmation email has been sent to your registered email address.
                </div>
                
                <div class="next-steps">
                    <h3><i class="fas fa-list-check"></i> What Happens Next?</h3>
                    <ul>
                        <li>Your application is now being processed by our team</li>
                        <li>You will receive status updates via email</li>
                        <li>Our support team may contact you if additional information is needed</li>
                        <li>Processing typically takes 5-7 business days</li>
                    </ul>
                </div>
                
                <div class="action-buttons">
                    <a href="gt.php" class="action-btn secondary">
                        <i class="fas fa-home"></i>
                        Back to Home
                    </a>
                    <button onclick="downloadSummary()" class="action-btn primary">
                        <i class="fas fa-download"></i>
                        Download Summary
                    </button>
                </div>
                
                <div style="margin-top: 30px; font-size: 14px; color: var(--gray);">
                    <p><i class="fas fa-info-circle"></i> Need help? <a href="mailto:support@askvisa.com" style="color: var(--primary);">Contact our support team</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadSummary() {
            // Create a simple summary text
            const summary = `
Payment Confirmation Summary
============================

Order ID: #<?php echo $order_id; ?>
Transaction ID: <?php echo htmlspecialchars($transaction_id ?: $order_details['provider_payment_id'] ?? 'N/A'); ?>
Date: <?php echo date('F j, Y, g:i a'); ?>

Application Details:
-------------------
Country: <?php echo htmlspecialchars($order_details['country_name']); ?>
Number of Applicants: <?php echo htmlspecialchars($applicant_count); ?>
Total Amount: $<?php echo number_format($order_details['total_amount'] ?? 0, 2); ?> <?php echo htmlspecialchars($order_details['currency'] ?? 'USD'); ?>
Payment Status: <?php echo strtoupper($order_details['payment_status'] ?? 'PAID'); ?>

What Happens Next:
------------------
1. Your application is now being processed by our team
2. You will receive status updates via email
3. Our support team may contact you if additional information is needed
4. Processing typically takes 5-7 business days

Contact Information:
--------------------
Email: support@askvisa.com
Website: www.askvisa.com

Thank you for choosing Ask Visa!
            `;
            
            // Create a blob and download link
            const blob = new Blob([summary], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Visa_Application_Summary_<?php echo $order_id; ?>.txt`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Show confirmation
            alert('Summary downloaded successfully!');
        }
        
        // Auto-redirect to main page after 30 seconds
        setTimeout(function() {
            if (confirm('You will be redirected to the home page. Continue?')) {
                window.location.href = 'gt.php';
            }
        }, 30000);
    </script>
</body>
</html>