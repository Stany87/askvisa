<?php
session_start();
require 'db.php';

$order_id_param = $_GET['order_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Your Application - Ask Visa</title>
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
                radial-gradient(at 0% 0%, rgba(220, 38, 38, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(220, 38, 38, 0.1) 0px, transparent 50%);
        }

        .track-wrapper {
            width: 100%;
            max-width: 1100px;
            height: 650px;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Left Side: Illustration */
        .track-hero {
            padding: 60px;
            background: rgba(15, 23, 42, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logo-box img { height: 40px; margin-bottom: 40px; }

        .hero-text h1 { font-size: 2.8rem; font-weight: 800; margin-bottom: 16px; line-height: 1.1; }
        .hero-text p { color: var(--text-muted); font-size: 1rem; line-height: 1.6; max-width: 400px; }

        .back-home {
            margin-top: auto;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
        }
        .back-home:hover { color: var(--primary); }

        /* Right Side: Search & Result */
        .track-interface {
            padding: 50px;
            display: flex;
            flex-direction: column;
            border-left: 1px solid var(--border);
            position: relative;
        }

        .search-box {
            margin-bottom: 40px;
        }

        .input-group {
            position: relative;
            display: flex;
            gap: 12px;
        }

        .track-input {
            flex: 1;
            padding: 18px 24px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            outline: none;
        }

        .track-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }

        .search-btn {
            padding: 18px 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 800;
            cursor: pointer;
            transition: var(--transition);
        }
        .search-btn:hover { background: var(--primary-hover); transform: translateY(-2px); }

        /* Result Section */
        .status-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 40px;
            display: none; /* Shown after search */
            flex-direction: column;
        }

        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .status-title h4 { font-size: 1.2rem; font-weight: 800; margin-bottom: 4px; }
        .status-title span { color: var(--text-muted); font-size: 0.85rem; }

        .status-badge {
            padding: 6px 16px;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-pending { background: rgba(234, 179, 8, 0.1); color: #eab308; }
        .badge-processing { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .badge-completed { background: rgba(34, 197, 94, 0.1); color: #22c55e; }

        /* Progress Stepper */
        .stepper {
            margin-top: 30px;
            position: relative;
        }

        .step-item {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            position: relative;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 17px;
            top: 40px;
            bottom: -20px;
            width: 2px;
            background: var(--border);
        }

        .step-circle {
            width: 36px;
            height: 36px;
            background: var(--dark-surface);
            border: 2px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: var(--text-muted);
            z-index: 1;
            transition: var(--transition);
        }

        .step-item.active .step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 0 15px var(--primary-glow);
        }

        .step-item.completed .step-circle {
            background: #22c55e;
            border-color: #22c55e;
            color: white;
        }

        .step-info h5 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
        .step-info p { font-size: 0.8rem; color: var(--text-muted); }

        /* Loading */
        .loader {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-md);
            z-index: 10;
        }
        .spinner { width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s infinite linear; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 900px) {
            .track-wrapper { grid-template-columns: 1fr; height: auto; max-height: 90vh; }
            .track-hero { display: none; }
            .track-interface { border-left: none; padding: 30px; }
            body { padding: 20px; overflow: auto; }
        }
    </style>
</head>
<body>
    <div class="track-wrapper">
        <div class="track-hero">
            <div class="logo-box">
                <img src="assets/ask-visa-logo-final.png" alt="Ask Visa">
            </div>
            <div class="hero-text">
                <h1>Track Your Visa Journey</h1>
                <p>Stay updated on every milestone. Enter your reference number below to see the real-time status of your application.</p>
            </div>
            <a href="index.php" class="back-home">
                <i class="fas fa-arrow-left"></i> Application Dashboard
            </a>
        </div>

        <div class="track-interface">
            <div class="search-box">
                <div class="input-group">
                    <input type="text" id="trackInput" class="track-input" placeholder="Enter Order ID (e.g. #123)" value="<?php echo htmlspecialchars($order_id_param); ?>">
                    <button class="search-btn" onclick="performTrack()">Track Status</button>
                </div>
            </div>

            <div id="statusCard" class="status-card">
                <div class="status-header">
                    <div class="status-title">
                        <h4 id="resOrder">Order #---</h4>
                        <span id="resDate">Submitted: ---</span>
                    </div>
                    <div id="resBadge" class="status-badge badge-processing">Processing</div>
                </div>

                <div class="stepper">
                    <div class="step-item completed" id="step1">
                        <div class="step-circle"><i class="fas fa-check"></i></div>
                        <div class="step-info">
                            <h5>Payment Received</h5>
                            <p>Transaction has been successfully verified.</p>
                        </div>
                    </div>
                    <div class="step-item" id="step2">
                        <div class="step-circle">2</div>
                        <div class="step-info">
                            <h5>Visa Initiated</h5>
                            <p>Your application has been received and queued.</p>
                        </div>
                    </div>
                    <div class="step-item" id="step3">
                        <div class="step-circle">3</div>
                        <div class="step-info">
                            <h5>Expert Review</h5>
                            <p>Our specialists are checking your documents for accuracy.</p>
                        </div>
                    </div>
                    <div class="step-item" id="step4">
                        <div class="step-circle">4</div>
                        <div class="step-info">
                            <h5>Decision Outcome</h5>
                            <p>Your visa decision will be updated here once available.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="emptyResult" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: var(--text-muted);">
                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.1;"></i>
                <p>Waiting for application ID...</p>
            </div>

            <div id="loader" class="loader">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <script>
        async function performTrack() {
            const input = document.getElementById('trackInput').value.trim().replace('#', '');
            if (!input) return;

            document.getElementById('loader').style.display = 'flex';
            document.getElementById('emptyResult').style.display = 'none';

            try {
                const response = await fetch('check_status.php?order_id=' + input);
                const data = await response.json();

                document.getElementById('loader').style.display = 'none';

                if (data.success) {
                    showStatus(data);
                } else {
                    alert('Order not found. Please check your ID and try again.');
                    document.getElementById('emptyResult').style.display = 'flex';
                    document.getElementById('statusCard').style.display = 'none';
                }
            } catch (error) {
                document.getElementById('loader').style.display = 'none';
                console.error(error);
                alert('An error occurred while tracking. Please try again.');
            }
        }

        function showStatus(data) {
            const card = document.getElementById('statusCard');
            card.style.display = 'flex';

            document.getElementById('resOrder').innerText = '#' + data.order_id;
            document.getElementById('resDate').innerText = 'Last Update: ' + data.updated_date;

            const badge = document.getElementById('resBadge');
            const vStatus = data.visa_status.toLowerCase();
            const pStatus = data.payment_status.toLowerCase();
            
            badge.innerText = vStatus.replace('_', ' ');

            // Logic for stepper
            const steps = [
                document.getElementById('step1'),
                document.getElementById('step2'),
                document.getElementById('step3'),
                document.getElementById('step4')
            ];

            // Reset all steps
            steps.forEach((s, idx) => {
                s.className = 'step-item';
                const circle = s.querySelector('.step-circle');
                circle.innerHTML = idx + 1;
                circle.style.background = '';
                circle.style.borderColor = '';
            });

            // Status Mapping logic
            // Step 1: Payment
            if (pStatus === 'paid') {
                steps[0].className = 'step-item completed';
                steps[0].querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
            } else {
                steps[0].className = 'step-item active';
                badge.className = 'status-badge badge-pending';
            }

            // Step 2: Initiated
            if (['initiated', 'in_review', 'approved', 'rejected'].includes(vStatus)) {
                if (vStatus !== 'initiated') {
                    steps[1].className = 'step-item completed';
                    steps[1].querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
                } else {
                    steps[1].className = 'step-item active';
                    badge.className = 'status-badge badge-processing';
                }
            }

            // Step 3: In Review
            if (['in_review', 'approved', 'rejected'].includes(vStatus)) {
                if (['approved', 'rejected'].includes(vStatus)) {
                    steps[2].className = 'step-item completed';
                    steps[2].querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
                } else {
                    steps[2].className = 'step-item active';
                    badge.className = 'status-badge badge-processing';
                }
            }

            // Step 4: Final Outcome
            if (vStatus === 'approved') {
                steps[3].className = 'step-item completed';
                steps[3].querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
                steps[3].querySelector('.step-circle').style.background = '#22c55e';
                steps[3].querySelector('.step-circle').style.borderColor = '#22c55e';
                steps[3].querySelector('.step-info h5').innerText = 'Visa Approved';
                steps[3].querySelector('.step-info p').innerText = 'Congratulations! Your visa has been granted.';
                badge.className = 'status-badge badge-completed';
            } else if (vStatus === 'rejected') {
                steps[3].className = 'step-item active'; // Keep it active to show failure
                steps[3].querySelector('.step-circle').innerHTML = '<i class="fas fa-times"></i>';
                steps[3].querySelector('.step-circle').style.background = '#ef4444';
                steps[3].querySelector('.step-circle').style.borderColor = '#ef4444';
                steps[3].querySelector('.step-info h5').innerText = 'Visa Rejected';
                steps[3].querySelector('.step-info p').innerText = 'Unfortunately, your application was not successful.';
                badge.className = 'status-badge badge-pending'; 
                badge.style.background = 'rgba(239, 68, 68, 0.1)';
                badge.style.color = '#ef4444';
            }
        }

        // Auto-run if order_id is in URL
        if(document.getElementById('trackInput').value) {
            performTrack();
        }
    </script>
</body>
</html>
