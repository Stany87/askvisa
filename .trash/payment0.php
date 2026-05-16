<?php
// payment.php
session_start();
if (isset($_SESSION['total_people'])) {
    $num_applicants = $_SESSION['total_people'];
    $country = $_SESSION['country_name'] ?? '';
    $order_id = $_SESSION['current_order_id'] ?? '';
    $total_amount = $num_applicants * 100;
} else {
    // Fallback to URL parameters
    $num_applicants = $_GET['applicants'] ?? 1;
    $country = $_GET['country'] ?? '';
    $order_id = $_GET['order_id'] ?? 'CHAT_' . time();
    $total_amount = $_GET['amount'] ?? ($num_applicants * 100);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Visa Fee - Secure Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        :root {
            --primary: #3a36e0;
            --primary-light: #6d69f2;
            --secondary: #10b981;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --border-radius: 16px;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--dark);
        }

        .payment-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-hover);
            width: 100%;
            max-width: 1000px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 30px 40px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .content {
            display: flex;
            min-height: 550px;
        }

        .application-summary {
            flex: 1;
            padding: 40px 30px;
            background: var(--light);
            display: flex;
            flex-direction: column;
        }

        .payment-form {
            flex: 1.2;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 25px;
            color: var(--dark);
            position: relative;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }

        .summary-label {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }

        .total-amount {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin-top: auto;
            box-shadow: var(--shadow);
        }

        .total-label {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .total-value {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .total-note {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 10px;
        }

        .payment-methods {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .payment-method {
            flex: 1;
            padding: 20px;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: var(--primary-light);
            transform: translateY(-3px);
        }

        .payment-method.active {
            border-color: var(--primary);
            background-color: rgba(58, 54, 224, 0.05);
        }

        .payment-method i {
            font-size: 32px;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(58, 54, 224, 0.1);
        }

        .card-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        #cardElementContainer {
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            padding: 18px;
            transition: all 0.3s;
            background: white;
        }

        #cardElementContainer.focused {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(58, 54, 224, 0.1);
        }

        #payButton {
            width: 100%;
            padding: 20px;
            background: linear-gradient(135deg, var(--secondary) 0%, #0ca678 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }

        #payButton:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.3);
        }

        #payButton:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .secure-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid var(--gray-light);
            color: var(--gray);
            font-size: 14px;
        }

        .progress-indicator {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 2px solid var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 8px;
            transition: all 0.3s;
        }

        .progress-step.active .step-number {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 5px 15px rgba(58, 54, 224, 0.3);
        }

        .progress-step.completed .step-number {
            background: var(--secondary);
            border-color: var(--secondary);
            color: white;
        }

        .step-label {
            font-size: 14px;
            color: var(--gray);
            font-weight: 500;
        }

        .progress-step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }

        .progress-bar {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gray-light);
            z-index: 1;
        }

        .progress-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: var(--primary);
            width: 50%;
            transition: width 0.5s ease;
        }

        .success-screen {
            text-align: center;
            padding: 50px 30px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--secondary) 0%, #0ca678 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 48px;
            color: white;
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.3);
        }

        .success-details {
            background: var(--light);
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--gray);
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark);
        }

        .status-badge {
            background: rgba(16, 185, 129, 0.1);
            color: var(--secondary);
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-primary,
        .btn-secondary {
            flex: 1;
            padding: 18px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 10px 20px rgba(58, 54, 224, 0.2);
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(58, 54, 224, 0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: rgba(58, 54, 224, 0.05);
            transform: translateY(-3px);
        }

        .back-btn {
            background: none;
            border: none;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            margin-top: 30px;
            padding: 12px 0;
            transition: color 0.3s;
        }

        .back-btn:hover {
            color: var(--primary);
        }

        .country-flag {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }

        @media (max-width: 900px) {
            .content {
                flex-direction: column;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .card-details-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .progress-indicator {
                margin-bottom: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="payment-container">
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-globe-americas"></i> Visa Fee Payment</h1>
                <p>Complete your visa application with our secure payment gateway</p>
            </div>
        </div>

        <div class="content">
            <!-- Left: Application Summary -->
            <div class="application-summary">
                <h2 class="section-title">Application Details</h2>

                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Application ID</div>
                        <div class="summary-value" id="order_Id"><?php echo time(); ?>_<?php echo $order_id; ?></div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Destination Country</div>
                        <div class="summary-value" id="countryDisplay">
                            <?php
                            echo $country ?? 'United States';
                            ?>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Visa Type</div>
                        <div class="summary-value" id="visaTypeDisplay"> Demo_Type</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Processing Time</div>
                        <div class="summary-value" id="processingDisplay">10-15 Business Days</div>
                    </div>
                </div>

                <div class="total-amount">
                    <div class="total-label">Total Amount to Pay</div>
                    <div class="total-value" id="total_amount"><?php echo '₹' . $total_amount ?></div>
                    <div class="total-note">Includes all processing fees</div>
                </div>

                <div style="margin-top: 30px;">
                    <h4 style="color: var(--primary); margin-bottom: 15px; font-size: 16px;"><i class="fas fa-check-circle"></i> What's Included</h4>
                    <ul style="color: var(--gray); line-height: 1.8; padding-left: 20px;">
                        <li>Visa application processing fee</li>
                        <li>Embassy submission service</li>
                        <li>Application tracking dashboard</li>
                        <li>Email support for 30 days</li>
                        <li>Document verification</li>
                    </ul>
                </div>
            </div>

            <!-- Right: Payment Form -->
            <div class="payment-form">
                <div class="progress-indicator">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="progress-step completed">
                        <div class="step-number"><i class="fas fa-check"></i></div>
                        <div class="step-label">Application</div>
                    </div>
                    <div class="progress-step active">
                        <div class="step-number">2</div>
                        <div class="step-label">Payment</div>
                    </div>
                    <div class="progress-step">
                        <div class="step-number">3</div>
                        <div class="step-label">Confirmation</div>
                    </div>
                </div>

                <h2 class="section-title">Payment Method</h2>

                <div class="payment-methods">
                    <div class="payment-method active" onclick="selectPaymentMethod('card')">
                        <i class="fas fa-credit-card"></i>
                        <div>Credit/Debit Card</div>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod('paypal')">
                        <i class="fab fa-paypal"></i>
                        <div>PayPal</div>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod('bank')">
                        <i class="fas fa-university"></i>
                        <div>Bank Transfer</div>
                    </div>
                </div>

                <div id="paymentSection">
                    <div class="form-group">
                        <label class="form-label">Cardholder Name</label>
                        <input type="text" class="form-input" placeholder="John Doe" id="cardName">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Card Details</label>
                        <div id="cardElementContainer">
                            <!-- Stripe card element will be inserted here -->
                        </div>
                    </div>

                    <div class="card-details-grid">
                        <div class="form-group">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" class="form-input" placeholder="MM/YY" id="cardExpiry">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CVC</label>
                            <input type="text" class="form-input" placeholder="123" id="cardCvc">
                        </div>
                    </div>

                    <button id="payButton" onclick="processPayment()">
                        <i class="fas fa-lock"></i> <?php echo 'Pay ₹' . $total_amount; ?>
                    </button>

                    <div class="secure-info">
                        <i class="fas fa-shield-alt" style="color: var(--secondary);"></i>
                        <span>Your payment is secured with 256-bit SSL encryption</span>
                    </div>

                    <div id="paymentError" style="color: #dc2626; margin-top: 15px; padding: 15px; background: #fef2f2; border-radius: 8px; display: none;"></div>
                </div>

                <div id="successSection" style="display: none;">
                    <div class="success-screen">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h2 style="color: var(--secondary); margin-bottom: 15px; font-size: 28px;">Payment Successful!</h2>
                        <p style="color: var(--gray); margin-bottom: 25px; font-size: 18px;">
                            Your visa application has been submitted successfully.
                        </p>

                        <div class="success-details">
                            <div class="detail-row">
                                <span class="detail-label">Application ID</span>
                                <span class="detail-value" id="successAppId"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Reference</span>
                                <span class="detail-value" id="successPaymentId">PAY_<?php echo rand(100000, 999999); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Amount Paid</span>
                                <span class="detail-value" id="successAmount"><?php echo 'inr' . $total_amount ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Date</span>
                                <span class="detail-value"><?php echo date('F j, Y'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status</span>
                                <span class="status-badge">Submitted for Processing</span>
                            </div>
                        </div>

                        <div class="button-group">
                            <button class="btn-primary" onclick="goToDashboard()">
                                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                            </button>
                            <button class="btn-secondary" onclick="printReceipt()">
                                <i class="fas fa-print"></i> Print Receipt
                            </button>
                        </div>

                        <p style="color: var(--gray); margin-top: 30px; font-size: 14px;">
                            A confirmation email has been sent to your registered email address.
                        </p>
                    </div>
                </div>

                <button class="back-btn" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i> Back to Application Form
                </button>
            </div>
        </div>
    </div>
    <script>
        // Mock Stripe - In production, use real Stripe.js
        const stripe = {
            elements: () => ({
                create: () => ({
                    mount: () => {}
                })
            }),
            confirmCardPayment: () => Promise.resolve({})
        };

        let cardElement;
        let selectedPaymentMethod = 'card';

        const urlParams = new URLSearchParams(window.location.search);
        const country = urlParams.get('country') || 'us';

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateFeeDisplay();
            initStripe();
        });

        // Show fee based on country
        function updateFeeDisplay() {
            const fees = {
                'us': {
                    symbol: '$',
                    amount: 185.00,
                    currency: 'USD'
                },
                'uk': {
                    symbol: '£',
                    amount: 115.00,
                    currency: 'GBP'
                },
                'fr': {
                    symbol: '€',
                    amount: 80.00,
                    currency: 'EUR'
                }
            };

            const fee = fees[country] || fees['us'];
            document.getElementById('feeAmount').textContent = `${fee.symbol}${fee.amount.toFixed(2)}`;
            document.getElementById('successAmount').textContent = `${fee.symbol}${fee.amount.toFixed(2)}`;

            // Update pay button text
            const payBtn = document.getElementById('payButton');
            payBtn.innerHTML = `<i class="fas fa-lock"></i> Pay ${fee.symbol}${fee.amount.toFixed(2)} Now`;
        }

        // Select payment method
        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;

            // Update UI
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('active');
            });
            event.currentTarget.classList.add('active');

            // Show/hide relevant fields (simplified for demo)
            if (method === 'card') {
                document.getElementById('cardElementContainer').style.display = 'block';
            } else {
                document.getElementById('cardElementContainer').style.display = 'none';
            }
        }

        // Initialize Stripe card input
        function initStripe() {
            // In a real implementation, this would initialize Stripe Elements
            console.log('Stripe initialized');
        }

        // Process payment demo 
        async function processPayment() {
            const payBtn = document.getElementById('payButton');
            const originalText = payBtn.innerHTML;
            payBtn.disabled = true;
            payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';

            // Simulate payment processing
            setTimeout(() => {
                // Show success
                document.getElementById('paymentSection').style.display = 'none';
                document.getElementById('successSection').style.display = 'block';

                // Update progress indicator
                document.querySelectorAll('.progress-step').forEach((step, index) => {
                    step.classList.remove('active');
                    if (index < 2) step.classList.add('completed');
                    if (index === 2) step.classList.add('active');
                });
                document.querySelector('.progress-fill').style.width = '100%';

                // Fill in success details
                document.getElementById('successAppId').textContent = document.getElementById('applicationId').textContent;

            }, 2000);
        }

        // Print receipt
        function printReceipt() {
            const fee = document.getElementById('feeAmount').textContent;
            const appId = document.getElementById('applicationId').textContent;
            const paymentId = document.getElementById('successPaymentId').textContent;

            const content = `
                <html>
                <head>
                    <title>Visa Payment Receipt</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 40px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .details { margin: 30px 0; }
                        .detail-row { display: flex; justify-content: space-between; margin: 10px 0; }
                        .thank-you { margin-top: 40px; text-align: center; font-style: italic; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Payment Receipt</h1>
                        <p>Visa Application Fee</p>
                    </div>
                    <div class="details">
                        <div class="detail-row">
                            <strong>Application ID:</strong> ${appId}
                        </div>
                        <div class="detail-row">
                            <strong>Payment Reference:</strong> ${paymentId}
                        </div>
                        <div class="detail-row">
                            <strong>Amount Paid:</strong> ${fee}
                        </div>
                        <div class="detail-row">
                            <strong>Date:</strong> ${new Date().toLocaleDateString()}
                        </div>
                        <div class="detail-row">
                            <strong>Time:</strong> ${new Date().toLocaleTimeString()}
                        </div>
                        <div class="detail-row">
                            <strong>Status:</strong> <span style="color: green;">Paid</span>
                        </div>
                    </div>
                    <div class="thank-you">
                        <p>Thank you for your payment. Your visa application is now being processed.</p>
                        <p>You will receive updates via email.</p>
                    </div>
                </body>
                </html>
            `;

            const win = window.open('', '_blank');
            win.document.write(content);
            win.document.close();
            win.print();
        }

        // Navigation functions
        function goToDashboard() {
            alert('Redirecting to dashboard...');
            // In real implementation: window.location.href = '/dashboard';
        }

        function goBack() {
            if (confirm('Are you sure you want to go back? Your payment information will not be saved.')) {
                // In real implementation: window.history.back();
                alert('Going back to application form...');
            }
        }
    </script>
</body>

</html>