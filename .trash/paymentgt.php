<?php
session_start();
require 'db.php';

// Check if payment data exists in session
if (!isset($_SESSION['payment_data']) || !isset($_SESSION['temp_application_id'])) {
    header('Location: gt.php');
    exit;
}

$payment_data = $_SESSION['payment_data'];
$temp_application_id = $_SESSION['temp_application_id'];
$country_name = $_SESSION['country_name'] ?? '';
$order_email = $_SESSION['order_contact_email'] ?? '';

// Process payment when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    $card_name = $_POST['card_name'] ?? '';
    $card_number = $_POST['card_number'] ?? '';
    $expiry_month = $_POST['expiry_month'] ?? '';
    $expiry_year = $_POST['expiry_year'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    
    // Validate payment details
    $errors = [];
    
    if (empty($payment_method)) {
        $errors[] = "Please select a payment method";
    }
    
    if ($payment_method === 'card') {
        if (empty($card_name)) {
            $errors[] = "Cardholder name is required";
        }
        if (empty($card_number) || !preg_match('/^\d{16}$/', str_replace(' ', '', $card_number))) {
            $errors[] = "Valid 16-digit card number is required";
        }
        if (empty($expiry_month) || empty($expiry_year)) {
            $errors[] = "Card expiry date is required";
        }
        if (empty($cvv) || !preg_match('/^\d{3,4}$/', $cvv)) {
            $errors[] = "Valid CVV is required";
        }
        
        $current_year = date('Y');
        $current_month = date('m');
        if ($expiry_year < $current_year || ($expiry_year == $current_year && $expiry_month < $current_month)) {
            $errors[] = "Card has expired";
        }
    }
    
    if (empty($errors)) {
        $transaction_id = 'TXN_' . strtoupper(uniqid()) . '_' . date('YmdHis');
        $provider_payment_id = 'PAY_' . strtoupper(uniqid());
        
        try {
            $pdo->beginTransaction();
            
            // 1. Create the Order Entry (REMOVED visa_type since column doesn't exist)
            $stmt = $pdo->prepare("INSERT INTO visa_orders (country_id, email, phone, payment_status, total_amount, currency) VALUES (?, ?, ?, 'paid', ?, ?)");
            $stmt->execute([
                $payment_data['country_id'], 
                $payment_data['order_contact_email'], 
                $payment_data['order_contact_phone'],
                $payment_data['order_amount'],
                $payment_data['order_currency']
            ]);
            $order_id = $pdo->lastInsertId();
            
            // 2. Insert payment record (REMOVED payment_method since column doesn't exist)
            $stmt = $pdo->prepare("INSERT INTO payments (order_id, provider, provider_payment_id, amount, currency, status) VALUES (?, 'online', ?, ?, ?, 'success')");
            $stmt->execute([
                $order_id,
                $provider_payment_id,
                $payment_data['order_amount'],
                $payment_data['order_currency']
            ]);
            
            // 3. Rename the Physical Folder if exists
            if (isset($_SESSION['order_folder_name'])) {
                $home_dir = dirname($_SERVER['DOCUMENT_ROOT']); 
                $base_path = $home_dir . '/gov_id/' . date('Y/m/d') . '/';
                
                $old_folder_name = $_SESSION['order_folder_name'];
                $new_folder_name = "order_" . $order_id;
                
                if (is_dir($base_path . $old_folder_name)) {
                    rename($base_path . $old_folder_name, $base_path . $new_folder_name);
                }
            }
            
            // 4. Save all applicant data to database
            if (isset($payment_data['collected_info'])) {
                for ($i = 1; $i <= $payment_data['total_people']; $i++) {
                    if (isset($payment_data['collected_info']["applicant_$i"])) {
                        $app_data = $payment_data['collected_info']["applicant_$i"];
                        
                        // Insert applicant
                        $stmt = $pdo->prepare("INSERT INTO applicants (order_id, applicant_no, applicant_email, applicant_phone, visa_status) VALUES (?, ?, ?, ?, 'submitted')");
                        $stmt->execute([$order_id, $i, $app_data['email'], $app_data['phone']]);
                        $app_id = $pdo->lastInsertId();
                        
                        // Save answers and files
                        if (isset($app_data['answers'])) {
                            foreach ($app_data['answers'] as $q_id => $val) {
                                if (strpos($val, 'fetch_file.php') !== false) {
                                    // Extract the old path from the URL
                                    $url_parts = parse_url($val);
                                    parse_str($url_parts['query'], $query_params);
                                    $old_path = $query_params['path'] ?? '';
                                    
                                    if ($old_path && isset($_SESSION['order_folder_name'])) {
                                        // Update path with new folder name
                                        $new_path = str_replace($_SESSION['order_folder_name'], "order_" . $order_id, $old_path);
                                        
                                        // Create new URL with encoded path
                                        $new_file_url = '/fetch_file.php?path=' . urlencode($new_path);
                                        
                                        // Get field type for this question
                                        $field_type = 'file';
                                        if (isset($payment_data['question_data'][$q_id])) {
                                            $field_type = $payment_data['question_data'][$q_id]['field_type'];
                                        }
                                        
                                        // Save to applicant_files table
                                        $pdo->prepare("INSERT INTO applicant_files (order_id, applicant_id, question_id, file_path, file_type) VALUES (?, ?, ?, ?, ?)")
                                            ->execute([$order_id, $app_id, $q_id, $new_file_url, 'image/jpeg']);
                                        
                                        // Also save to applicant_answers for consistency
                                        $pdo->prepare("INSERT INTO applicant_answers (order_id, applicant_id, question_id, answer_type, answer_text) VALUES (?, ?, ?, ?, ?)")
                                            ->execute([$order_id, $app_id, $q_id, $field_type, $new_file_url]);
                                    }
                                } else {
                                    // Get field type for this question
                                    $field_type = 'text';
                                    if (isset($payment_data['question_data'][$q_id])) {
                                        $field_type = $payment_data['question_data'][$q_id]['field_type'];
                                    }
                                    
                                    // Save text answers to applicant_answers table
                                    $pdo->prepare("INSERT INTO applicant_answers (order_id, applicant_id, question_id, answer_type, answer_text) VALUES (?, ?, ?, ?, ?)")
                                        ->execute([$order_id, $app_id, $q_id, $field_type, $val]);
                                }
                            }
                        }
                    }
                }
            }
            
            $pdo->commit();
            
            // Send confirmation email
            sendConfirmationEmail($order_id, $payment_data['order_contact_email'], $payment_data, $transaction_id);
            
            // Clear payment session data
            unset($_SESSION['payment_data']);
            unset($_SESSION['temp_application_id']);
            unset($_SESSION['visa_type']); // Still unset even if not used
            
            // Store order ID for confirmation page
            $_SESSION['completed_order_id'] = $order_id;
            $_SESSION['transaction_id'] = $transaction_id;
            
            header('Location: payment_successgt.php');
            exit;
            
        } catch (Exception $e) { 
            $pdo->rollBack(); 
            $errors[] = "Payment processing error: " . $e->getMessage();
        }
    }
}

// Function to send confirmation email
function sendConfirmationEmail($order_id, $email, $payment_data, $transaction_id) {
    $subject = "Visa Application Payment Confirmation - Order #" . $order_id;
    
    $message = "
    <html>
    <head>
        <title>Visa Application Payment Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #4361ee; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #ddd; }
            .order-info { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4361ee; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Payment Confirmation</h1>
                <p>Your visa application payment has been successfully processed</p>
            </div>
            <div class='content'>
                <h2>Thank you for your payment!</h2>
                <p>Your visa application has been submitted successfully. Here are your payment details:</p>
                
                <div class='order-info'>
                    <h3>Order Details</h3>
                    <p><strong>Order ID:</strong> #$order_id</p>
                    <p><strong>Transaction ID:</strong> $transaction_id</p>
                    <p><strong>Country:</strong> {$payment_data['country_name']}</p>
                    <p><strong>Number of Applicants:</strong> {$payment_data['total_people']}</p>
                    <p><strong>Total Amount:</strong> \${$payment_data['order_amount']} {$payment_data['order_currency']}</p>
                    <p><strong>Payment Status:</strong> <span style='color: green; font-weight: bold;'>PAID</span></p>
                    <p><strong>Payment Date:</strong> " . date('F j, Y, g:i a') . "</p>
                </div>
                
                <p>Your application is now being processed. We will notify you once there are any updates.</p>
                <p>You can check your application status at any time by visiting our website.</p>
                
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                    <p>© " . date('Y') . " Ask Visa. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Ask Visa <noreply@askvisa.com>" . "\r\n";
    $headers .= "Reply-To: support@askvisa.com" . "\r\n";
    
    error_log("Email would be sent to: $email with subject: $subject");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment - Ask Visa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 16px;
            --border-radius-sm: 8px;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
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
        
        .payment-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-header h1 {
            color: var(--primary);
            font-size: 32px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .payment-header p {
            color: var(--gray);
            font-size: 16px;
        }
        
        .payment-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .payment-tabs {
            display: flex;
            background: var(--light);
            border-bottom: 1px solid var(--gray-light);
        }
        
        .tab-btn {
            flex: 1;
            padding: 20px;
            background: none;
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .tab-btn.active {
            color: var(--primary);
            background: white;
            border-bottom: 3px solid var(--primary);
        }
        
        .tab-btn:hover:not(.active) {
            background: rgba(67, 97, 238, 0.05);
        }
        
        .tab-content {
            padding: 30px;
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .order-summary {
            background: var(--light);
            padding: 20px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 30px;
            border-left: 4px solid var(--primary);
        }
        
        .order-summary h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .summary-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .summary-label {
            font-size: 13px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .total-amount {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-radius: var(--border-radius-sm);
            margin: 20px 0;
        }
        
        .total-amount .amount {
            font-size: 36px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .payment-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            padding: 12px 16px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .card-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .card-details .form-group:nth-child(1) {
            grid-column: 1 / -1;
        }
        
        .card-details .form-group:nth-child(2) {
            grid-column: 1 / -1;
        }
        
        .expiry-cvv {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .error-message {
            color: var(--danger);
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        
        .security-notice {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #e8f4fd;
            padding: 15px;
            border-radius: var(--border-radius-sm);
            margin: 20px 0;
            color: #036;
        }
        
        .payment-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .payment-btn {
            flex: 1;
            padding: 16px;
            border-radius: var(--border-radius-sm);
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
        }
        
        .payment-btn.primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }
        
        .payment-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .payment-btn.secondary {
            background: var(--gray-light);
            color: var(--dark);
        }
        
        .payment-btn.secondary:hover {
            background: var(--gray);
            color: white;
        }
        
        .payment-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .payment-footer {
            text-align: center;
            margin-top: 30px;
            color: var(--gray);
            font-size: 14px;
        }
        
        .payment-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .payment-footer a:hover {
            text-decoration: underline;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .card-details {
                grid-template-columns: 1fr;
            }
            
            .payment-tabs {
                flex-direction: column;
            }
            
            .payment-actions {
                flex-direction: column;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>
                <i class="fas fa-lock"></i>
                Secure Payment Gateway
            </h1>
            <p>Complete your visa application with secure payment</p>
        </div>
        
        <div class="payment-card">
            <div class="payment-tabs">
                <button class="tab-btn active" onclick="switchTab('card')">
                    <i class="fas fa-credit-card"></i>
                    Credit/Debit Card
                </button>
                <button class="tab-btn" onclick="switchTab('paypal')">
                    <i class="fab fa-paypal"></i>
                    PayPal
                </button>
                <button class="tab-btn" onclick="switchTab('bank')">
                    <i class="fas fa-university"></i>
                    Bank Transfer
                </button>
            </div>
            
            <div class="order-summary">
                <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label">Application ID</span>
                        <span class="summary-value"><?php echo htmlspecialchars($temp_application_id); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Country</span>
                        <span class="summary-value"><?php echo htmlspecialchars($country_name); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Applicants</span>
                        <span class="summary-value"><?php echo htmlspecialchars($payment_data['total_people']); ?> person(s)</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Invoice Email</span>
                        <span class="summary-value"><?php echo htmlspecialchars($order_email); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="total-amount">
                <div>Total Amount Due</div>
                <div class="amount">$<?php echo number_format($payment_data['order_amount'], 2); ?></div>
                <div><?php echo htmlspecialchars($payment_data['order_currency']); ?></div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div style="background: #fee; padding: 15px; border-radius: var(--border-radius-sm); margin: 20px 30px; border-left: 4px solid var(--danger);">
                    <h4 style="color: var(--danger); margin-bottom: 10px;">
                        <i class="fas fa-exclamation-triangle"></i> Payment Errors
                    </h4>
                    <ul style="color: #c00; margin-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="payment-form" id="paymentForm">
                <input type="hidden" name="process_payment" value="1">
                <input type="hidden" name="payment_method" value="card">
                
                <!-- Card Payment Tab -->
                <div id="card-tab" class="tab-content active">
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Secure Payment</strong>
                            <p style="margin: 5px 0 0 0; font-size: 13px;">Your payment information is encrypted and secure</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_name">Cardholder Name</label>
                        <input type="text" id="card_name" name="card_name" placeholder="John Doe" required>
                        <div class="error-message" id="card_name_error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required maxlength="19">
                        <div class="error-message" id="card_number_error"></div>
                    </div>
                    
                    <div class="card-details">
                        <div class="expiry-cvv">
                            <div class="form-group">
                                <label for="expiry_month">Expiry Month</label>
                                <select id="expiry_month" name="expiry_month" required>
                                    <option value="">Month</option>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <div class="error-message" id="expiry_month_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="expiry_year">Expiry Year</label>
                                <select id="expiry_year" name="expiry_year" required>
                                    <option value="">Year</option>
                                    <?php
                                    $current_year = date('Y');
                                    for ($i = $current_year; $i <= $current_year + 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <div class="error-message" id="expiry_year_error"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" required maxlength="4">
                            <div class="error-message" id="cvv_error"></div>
                        </div>
                    </div>
                </div>
                
                <!-- PayPal Tab -->
                <div id="paypal-tab" class="tab-content">
                    <div class="security-notice">
                        <i class="fab fa-paypal"></i>
                        <div>
                            <strong>PayPal Secure Payment</strong>
                            <p style="margin: 5px 0 0 0; font-size: 13px;">You will be redirected to PayPal to complete your payment</p>
                        </div>
                    </div>
                    <p>Click "Pay with PayPal" to be redirected to PayPal's secure payment page.</p>
                </div>
                
                <!-- Bank Transfer Tab -->
                <div id="bank-tab" class="tab-content">
                    <div class="security-notice">
                        <i class="fas fa-university"></i>
                        <div>
                            <strong>Bank Transfer</strong>
                            <p style="margin: 5px 0 0 0; font-size: 13px;">Transfer funds directly to our bank account</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Bank Account Details</label>
                        <div style="background: var(--light); padding: 15px; border-radius: var(--border-radius-sm); margin-top: 10px;">
                            <p><strong>Bank Name:</strong> Ask Visa International Bank</p>
                            <p><strong>Account Name:</strong> Ask Visa Services Inc.</p>
                            <p><strong>Account Number:</strong> 1234567890</p>
                            <p><strong>Routing Number:</strong> 021000021</p>
                            <p><strong>SWIFT/BIC:</strong> ASKVUS33</p>
                            <p><strong>Reference:</strong> <?php echo htmlspecialchars($temp_application_id); ?></p>
                        </div>
                    </div>
                    <p>Please include the Application ID as reference when making the transfer.</p>
                </div>
                
                <div class="payment-actions">
                    <button type="button" class="payment-btn secondary" onclick="window.location.href='gt.php'">
                        <i class="fas fa-arrow-left"></i>
                        Back to Application
                    </button>
                    <button type="submit" class="payment-btn primary" id="submitBtn">
                        <i class="fas fa-lock"></i>
                        Pay $<?php echo number_format($payment_data['order_amount'], 2); ?> Now
                    </button>
                </div>
            </form>
        </div>
        
        <div class="payment-footer">
            <p>
                <i class="fas fa-lock"></i>
                All transactions are secure and encrypted
                | 
                <a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a>
                | 
                <a href="#"><i class="fas fa-file-contract"></i> Terms of Service</a>
            </p>
            <p style="margin-top: 10px; font-size: 12px;">
                By completing this payment, you agree to our terms and conditions
            </p>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Update active tab button
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update active tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Update payment method hidden field
            document.querySelector('input[name="payment_method"]').value = tabName;
        }
        
        // Format card number with spaces
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formatted = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formatted += ' ';
                }
                formatted += value[i];
            }
            
            e.target.value = formatted.substring(0, 19);
        });
        
        // Format CVV (only numbers)
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 4);
        });
        
        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            let isValid = true;
            const paymentMethod = document.querySelector('input[name="payment_method"]').value;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });
            
            if (paymentMethod === 'card') {
                // Validate card name
                const cardName = document.getElementById('card_name').value.trim();
                if (!cardName) {
                    document.getElementById('card_name_error').textContent = 'Cardholder name is required';
                    document.getElementById('card_name_error').style.display = 'block';
                    isValid = false;
                }
                
                // Validate card number
                const cardNumber = document.getElementById('card_number').value.replace(/\s+/g, '');
                if (!cardNumber || cardNumber.length !== 16) {
                    document.getElementById('card_number_error').textContent = 'Valid 16-digit card number is required';
                    document.getElementById('card_number_error').style.display = 'block';
                    isValid = false;
                }
                
                // Validate expiry
                const expiryMonth = document.getElementById('expiry_month').value;
                const expiryYear = document.getElementById('expiry_year').value;
                if (!expiryMonth) {
                    document.getElementById('expiry_month_error').textContent = 'Expiry month is required';
                    document.getElementById('expiry_month_error').style.display = 'block';
                    isValid = false;
                }
                if (!expiryYear) {
                    document.getElementById('expiry_year_error').textContent = 'Expiry year is required';
                    document.getElementById('expiry_year_error').style.display = 'block';
                    isValid = false;
                }
                
                // Validate CVV
                const cvv = document.getElementById('cvv').value;
                if (!cvv || cvv.length < 3) {
                    document.getElementById('cvv_error').textContent = 'Valid CVV is required (3-4 digits)';
                    document.getElementById('cvv_error').style.display = 'block';
                    isValid = false;
                }
                
                // Check if card is expired
                if (expiryMonth && expiryYear) {
                    const currentDate = new Date();
                    const currentYear = currentDate.getFullYear();
                    const currentMonth = currentDate.getMonth() + 1;
                    
                    if (parseInt(expiryYear) < currentYear || 
                        (parseInt(expiryYear) === currentYear && parseInt(expiryMonth) < currentMonth)) {
                        document.getElementById('expiry_month_error').textContent = 'Card has expired';
                        document.getElementById('expiry_month_error').style.display = 'block';
                        isValid = false;
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                const firstError = document.querySelector('.error-message[style*="display: block"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
                
                // For PayPal, redirect to PayPal (simulated)
                if (paymentMethod === 'paypal') {
                    e.preventDefault();
                    alert('In a real implementation, this would redirect to PayPal. For demo, please use card payment.');
                    document.getElementById('submitBtn').disabled = false;
                    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-lock"></i> Pay Now';
                }
            }
        });
        
        // Initialize the form with card tab active
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="payment_method"]').value = 'card';
        });
    </script>
</body>
</html>