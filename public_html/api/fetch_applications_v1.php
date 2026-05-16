<?php
require '../db.php';
header("Content-Type: application/json");

/* ======================
   SECURITY
====================== */
if (!isset($_GET['key']) || $_GET['key'] !== 'SUPER_SECRET_123') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

/* ======================
   FETCH ALL PAID ORDERS
====================== */
$orderStmt = $pdo->prepare("
    SELECT vo.*, c.country_name
    FROM visa_orders vo
    JOIN countries c ON c.id = vo.country_id
    WHERE vo.payment_status = 'paid'
    ORDER BY vo.created_at DESC
");
$orderStmt->execute();
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    echo json_encode(["orders" => []]);
    exit;
}

/* ======================
   COLLECT ORDER IDS
====================== */
$orderIds = array_column($orders, 'id');

/* ======================
   FETCH ALL APPLICANTS (1 QUERY)
====================== */
$appStmt = $pdo->prepare("
    SELECT *
    FROM applicants
    WHERE order_id IN (" . implode(',', array_fill(0, count($orderIds), '?')) . ")
    ORDER BY applicant_no ASC
");
$appStmt->execute($orderIds);
$applicants = $appStmt->fetchAll(PDO::FETCH_ASSOC);

/* ======================
   COLLECT APPLICANT IDS
====================== */
$appIds = array_column($applicants, 'id');

$answers = [];
$files = [];

if (!empty($appIds)) {

    /* ======================
       FETCH ALL ANSWERS (1 QUERY)
    ====================== */
    $ansStmt = $pdo->prepare("
        SELECT aa.applicant_id, cq.label, aa.answer_text
        FROM applicant_answers aa
        JOIN country_questions cq ON cq.id = aa.question_id
        WHERE aa.applicant_id IN (" . implode(',', array_fill(0, count($appIds), '?')) . ")
        ORDER BY cq.sort_order ASC
    ");
    $ansStmt->execute($appIds);
    $answers = $ansStmt->fetchAll(PDO::FETCH_ASSOC);

    /* ======================
       FETCH ALL FILES (1 QUERY)
    ====================== */
    $fileStmt = $pdo->prepare("
        SELECT af.applicant_id, cq.label, af.file_path, af.file_type, af.uploaded_at
        FROM applicant_files af
        JOIN country_questions cq ON cq.id = af.question_id
        WHERE af.applicant_id IN (" . implode(',', array_fill(0, count($appIds), '?')) . ")
    ");
    $fileStmt->execute($appIds);
    $files = $fileStmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ======================
   BUILD MAPS (FAST AF)
====================== */

/* Applicants map */
$appMap = [];
foreach ($applicants as $app) {
    $app['answers'] = [];
    $app['files'] = [];
    $appMap[$app['id']] = $app;
}

/* Attach answers */
foreach ($answers as $ans) {
    if (isset($appMap[$ans['applicant_id']])) {
        $appMap[$ans['applicant_id']]['answers'][] = [
            "label" => $ans['label'],
            "answer_text" => $ans['answer_text']
        ];
    }
}

/* Attach files */
foreach ($files as $file) {
    if (isset($appMap[$file['applicant_id']])) {
        $appMap[$file['applicant_id']]['files'][] = [
            "label" => $file['label'],
            "file_path" => $file['file_path'],
            "file_type" => $file['file_type'],
            "uploaded_at" => $file['uploaded_at']
        ];
    }
}

/* Group applicants by order */
$orderMap = [];
foreach ($orders as $order) {
    $order['applicants'] = [];
    $orderMap[$order['id']] = $order;
}

foreach ($appMap as $app) {
    if (isset($orderMap[$app['order_id']])) {
        $orderMap[$app['order_id']]['applicants'][] = $app;
    }
}

/* ======================
   OUTPUT
====================== */
echo json_encode([
    "orders" => array_values($orderMap)
]);