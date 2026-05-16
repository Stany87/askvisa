<?php
// WARNING: This file should be protected or removed after use in production!
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = $_POST['company_name'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if ($company_name && $contact_person && $email && $password) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO b2b_agents (company_name, contact_person, email, password_hash, phone) VALUES (:company, :contact, :email, :pass, :phone)");
            
            $stmt->execute([
                ':company' => $company_name,
                ':contact' => $contact_person,
                ':email' => $email,
                ':pass' => $password_hash,
                ':phone' => $phone
            ]);
            
            $message = "<div class='alert alert-success'>Agent account created successfully!</div>";
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $message = "<div class='alert alert-error'>Error: Email already exists.</div>";
            } else {
                $message = "<div class='alert alert-error'>Error creating account: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-error'>Please fill all required fields.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Create B2B Agent</title>
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
            background-image: radial-gradient(circle at top left, #1a1a2e 0%, #050505 60%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
            padding: 20px;
        }
        .admin-container {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 500px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h2 {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
        }
        .header p {
            color: #888;
            font-size: 14px;
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
            padding: 12px 16px;
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
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #10a37f 0%, #0d8a6a 100%);
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
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(16, 163, 127, 0.3);
        }
        .btn-submit:active {
            transform: translateY(1px);
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .alert-success {
            color: #4ade80;
            background: rgba(74, 222, 128, 0.1);
            border: 1px solid rgba(74, 222, 128, 0.2);
        }
        .alert-error {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.2);
        }
        .warning-banner {
            background: rgba(245, 158, 11, 0.1);
            border-left: 4px solid #f59e0b;
            color: #fcd34d;
            padding: 10px 15px;
            font-size: 12px;
            margin-bottom: 25px;
            border-radius: 0 4px 4px 0;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="warning-banner">
            <strong>Security Notice:</strong> Remove or password-protect this file before going live to the public.
        </div>
        
        <div class="header">
            <h2>Create Agent Account</h2>
            <p>Generate a new B2B partner credential</p>
        </div>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label>Company Name *</label>
                <input type="text" name="company_name" required placeholder="e.g. Travel World Inc.">
            </div>
            <div class="form-group">
                <label>Contact Person *</label>
                <input type="text" name="contact_person" required placeholder="e.g. John Doe">
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required placeholder="john@travelworld.com">
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="text" name="password" required placeholder="Temporary Password">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="+1 234 567 8900">
            </div>
            <button type="submit" class="btn-submit">Create Agent</button>
        </form>
    </div>
</body>
</html>
