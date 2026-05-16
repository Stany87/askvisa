<?php
require '../db.php';

header("Content-Type: application/json");

// ===== CONFIG =====
$PASSWORD = "AuRum@16092003!^)(@))#";
$ALLOWED_IP = null; // put your IP if you want lock

// ===== METHOD CHECK =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "POST only"]);
    exit;
}

// ===== IP LOCK =====
if ($ALLOWED_IP && $_SERVER['REMOTE_ADDR'] !== $ALLOWED_IP) {
    http_response_code(403);
    echo json_encode(["error" => "IP not allowed"]);
    exit;
}

// ===== READ JSON =====
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['password']) || $input['password'] !== $PASSWORD) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$query = trim($input['query'] ?? '');

if (!$query) {
    echo json_encode(["error" => "Empty query"]);
    exit;
}

// ===== BASIC SAFETY FILTER =====
$blocked = ['DROP DATABASE', 'TRUNCATE', 'GRANT', 'REVOKE'];

foreach ($blocked as $word) {
    if (stripos($query, $word) !== false) {
        echo json_encode(["error" => "Blocked dangerous query"]);
        exit;
    }
}

// ===== EXECUTE =====
try {
    $stmt = $pdo->query($query);

    // SELECT queries
    if (stripos($query, 'SELECT') === 0 || stripos($query, 'SHOW') === 0 || stripos($query, 'DESCRIBE') === 0) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } else {
        // UPDATE / INSERT / DELETE
        echo json_encode([
            "success" => true,
            "affected_rows" => $stmt->rowCount()
        ]);
    }

    // ===== LOG QUERY =====
    file_put_contents(
        "query.log",
        "[" . date("Y-m-d H:i:s") . "] " . $query . PHP_EOL,
        FILE_APPEND
    );

} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}