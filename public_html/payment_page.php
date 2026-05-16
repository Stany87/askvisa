<?php
session_start();
require 'db.php';
require_once 'csrf_helper.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();

$temp_order_id = $_GET['temp_order_id'] ?? '';

if (!$temp_order_id || !isset($_SESSION['temp_application_data'])) {
    header('Location: index.php');
    exit;
}

// Get order details from session (NO database query)
$order_data = $_SESSION['temp_application_data'];
$country_name = $order_data['country_name'] ?? '';
$payment_amount = $order_data['payment_amount'] ?? 0;
$currency = $order_data['currency'] ?? 'INR';
$order_contact_email = $order_data['order_contact_email'] ?? '';
$order_contact_phone = $order_data['order_contact_phone'] ?? '';
$total_people = $order_data['total_people'] ?? 1;
$country_id = $order_data['country_id'] ?? 0;

// Store temp order ID in session for payment callback
$_SESSION['temp_order_id'] = $temp_order_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment - Visa Application</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root {
            --primary: #dc2626;
            --primary-hover: #b91c1c;
            --primary-glow: rgba(220, 38, 38, 0.4);
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
                radial-gradient(at 0% 0%, rgba(220, 38, 38, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(220, 38, 38, 0.1) 0px, transparent 50%);
        }

        .payment-wrapper {
            width: 100%;
            max-width: 1000px;
            height: 600px;
            display: grid;
            grid-template-columns: 1fr 400px;
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Left Side: Summary */
        .summary-side {
            padding: 60px;
            background: rgba(15, 23, 42, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .logo-box img { height: 40px; margin-bottom: 40px; }

        .transaction-meta h2 { font-size: 2rem; font-weight: 800; margin-bottom: 8px; }
        .transaction-meta p { color: var(--text-muted); font-size: 0.9rem; }

        .details-list { margin: 40px 0; }
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .detail-label { color: var(--text-muted); font-size: 0.85rem; font-weight: 500; }
        .detail-value { font-weight: 600; color: var(--text-main); }

        .total-display { margin-top: auto; }
        .total-label { color: var(--text-muted); font-size: 0.9rem; font-weight: 600; margin-bottom: 4px; display: block; }
        .total-amount { font-size: 3.5rem; font-weight: 800; color: var(--primary); letter-spacing: -1px; }
        .total-currency { font-size: 1.5rem; color: var(--text-main); opacity: 0.7; }

        /* Right Side: Action */
        .action-side {
            padding: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(30, 41, 59, 0.3);
            border-left: 1px solid var(--border);
            text-align: center;
        }

        .payment-status-icon {
            width: 100px;
            height: 100px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            color: var(--primary);
            font-size: 2.5rem;
            position: relative;
        }
        
        .payment-status-icon::after {
            content: '';
            position: absolute;
            inset: -10px;
            border: 2px dashed var(--primary);
            border-radius: 50%;
            opacity: 0.3;
            animation: rotate 10s linear infinite;
        }

        .action-text h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 12px; }
        .action-text p { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 40px; line-height: 1.5; }

        .pay-btn {
            width: 100%;
            padding: 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 10px 20px var(--primary-glow);
        }

        .pay-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-4px);
            box-shadow: 0 15px 30px var(--primary-glow);
        }

        .pay-btn:active { transform: translateY(0); }

        .back-link {
            margin-top: 24px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: var(--transition);
        }
        .back-link:hover { color: var(--text-main); }

        .security-badges {
            margin-top: 60px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .badge-list { display: flex; gap: 15px; opacity: 0.6; margin-top: 5px; font-size: 1rem; }

        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Loading */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .spinner {
            width: 60px; height: 60px;
            border: 4px solid var(--border);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 900px) {
            .payment-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
                height: auto;
                max-height: 90vh;
                overflow-y: auto;
            }
            .summary-side { padding: 40px; }
            .action-side { padding: 40px; border-left: none; border-top: 1px solid var(--border); }
            body { overflow: auto; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="payment-wrapper">
        <!-- Left: Summary -->
        <div class="summary-side">
            <div class="top-section">
                <div class="logo-box">
                    <img src="assets/ask-visa-logo-final.png" alt="Ask Visa">
                </div>
                <div class="transaction-meta">
                    <h2>Payment Summary</h2>
                    <p>Transaction ID: #<?php echo $temp_order_id; ?></p>
                </div>
                
                <div class="details-list">
                    <div class="detail-item">
                        <span class="detail-label">Destination</span>
                        <span class="detail-value"><?php echo htmlspecialchars($country_name); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Applicants Count</span>
                        <span class="detail-value"><?php echo $total_people; ?> Person(s)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Billing Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order_contact_email); ?></span>
                    </div>
                </div>
            </div>

            <div class="total-display">
                <span class="total-label">Payable Amount</span>
                <div class="total-amount-box">
                    <span class="total-currency"><?php echo $currency; ?></span>
                    <span class="total-amount"><?php echo number_format($payment_amount, 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Right: Action -->
        <div class="action-side">
            <div class="payment-status-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            
            <div class="action-text">
                <h3>Authorize Payment</h3>
                <p>Verify your details and click below to proceed with the secure transaction.</p>
            </div>

            <button class="pay-btn" onclick="initiateRazorpayPayment()">
                Verify & Pay Now
                <i class="fas fa-arrow-right"></i>
            </button>

            <a href="index.php?return_from_payment=1" class="back-link">
                <i class="fas fa-times-circle"></i> Cancel Application
            </a>

            <div class="security-badges">
                <div class="badge-list">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-google-pay"></i>
                    <i class="fab fa-apple-pay"></i>
                </div>
                <p><i class="fas fa-lock"></i> AES-256 SSL Encryption</p>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        async function initiateRazorpayPayment() {
            showLoading();
            
            try {
                const response = await fetch('create_razorpay_order_new.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        temp_order_id: <?php echo json_encode($temp_order_id); ?>,
                        amount: <?php echo $payment_amount; ?>,
                        currency: <?php echo json_encode($currency); ?>,
                        email: <?php echo json_encode($order_contact_email); ?>,
                        phone: <?php echo json_encode($order_contact_phone); ?>,
                        country_id: <?php echo $country_id; ?>,
                        csrf_token: '<?php echo $csrf_token; ?>'
                    })
                });

                const data = await response.json();
                
                if (!data.success) {
                    hideLoading();
                    alert(data.message || 'Failed to create payment order');
                    return;
                }

                const options = {
                    key: data.key,
                    amount: data.amount * 100,
                    currency: data.currency,
                    name: 'Ask Visa',
                    description: 'Visa Application Fee',
                    order_id: data.razorpay_order_id,
                    handler: function(response) {
                        handlePaymentResponse(response, true);
                    },
                    prefill: {
                        name: 'Applicant',
                        email: data.customer_email,
                        contact: data.customer_phone
                    },
                    notes: { order_id: data.temp_order_id },
                    theme: { color: '#dc2626' }
                };

                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function(response) {
                    handlePaymentResponse(response, false);
                });

                hideLoading();
                rzp.open();
                
            } catch (error) {
                hideLoading();
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }

        function handlePaymentResponse(response, isSuccess) {
            showLoading();
            
            const formData = new FormData();
            formData.append('razorpay_payment_id', response.razorpay_payment_id || '');
            formData.append('razorpay_order_id', response.razorpay_order_id || (response.error && response.error.metadata ? response.error.metadata.order_id : ''));
            formData.append('razorpay_signature', response.razorpay_signature || '');
            formData.append('temp_order_id', <?php echo json_encode($temp_order_id); ?>);
            formData.append('is_success', isSuccess ? '1' : '0');
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            
            fetch('verify_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                hideLoading();
                if (isSuccess && data.success) {
                    window.location.href = 'payment_successfull.php?order_id=' + data.order_id;
                } else {
                    window.location.href = 'payment_failed.php';
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                alert('Payment verification failed. Please contact support.');
            });
        }
    </script>
</body>
</html>