<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: /login/b2b?error=empty");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, company_name, password_hash, status FROM b2b_agents WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($agent && password_verify($password, $agent['password_hash'])) {
            if ($agent['status'] !== 'active') {
                header("Location: /login/b2b?error=inactive");
                exit;
            }

            // Login successful
            $_SESSION['b2b_agent_id'] = $agent['id'];
            $_SESSION['b2b_company_name'] = $agent['company_name'];

            // Redirect to dashboard
            header("Location: /agent_dashboard.php");
            exit;
        } else {
            // Invalid credentials
            header("Location: /login/b2b?error=invalid");
            exit;
        }
    } catch (\PDOException $e) {
        // Log error
        error_log("B2B Login Error: " . $e->getMessage());
        header("Location: /login/b2b?error=system");
        exit;
    }
} else {
    // If accessed directly without POST
    header("Location: /login/b2b");
    exit;
}
?>