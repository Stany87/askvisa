<?php
session_start();
if (isset($_SESSION['b2b_agent_id'])) {
    header("Location: /agent_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Partner Portal | ASKVISA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        body {
            background-color: #050505;
            background-image: radial-gradient(circle at top right, #1a1a2e 0%, #050505 50%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 420px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-container h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            background: linear-gradient(90deg, #fff, #a0a0a0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logo-container p {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #bbb;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        .form-group input::placeholder {
            color: #555;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4a90e2 0%, #0056b3 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 10px;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(74, 144, 226, 0.3);
        }
        .btn-login:active {
            transform: translateY(1px);
        }
        .error-message {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.2);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            display: <?php echo isset($_GET['error']) ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo-container">
        <h1>ASKVISA</h1>
        <p>B2B Partner Portal</p>
    </div>
    
    <div class="error-message">
        <?php 
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'invalid') echo "Invalid email or password.";
            else if ($_GET['error'] == 'inactive') echo "Your account is inactive. Please contact support.";
            else echo "An error occurred. Please try again.";
        }
        ?>
    </div>
    
    <form action="/process_b2b_login.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="name@company.com">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-login">Sign In</button>
    </form>
</div>

</body>
</html>
