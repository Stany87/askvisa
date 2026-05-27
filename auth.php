<?php
session_start();
require_once __DIR__ . '/../config.php';

function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    return $pdo;
}

function login($email, $password)
{
    $db = getDB();
    $email = trim((string) $email);
    if ($email === '' || $password === '') {
        return false;
    }

    // Primary schema used in existing production flow.
    try {
        $stmt = $db->prepare('SELECT id, company_name, contact_person, email, password_hash, status FROM b2b_agents WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $agent = $stmt->fetch();
        if ($agent && password_verify($password, $agent['password_hash']) && ($agent['status'] ?? '') === 'active') {
            $_SESSION['agent_id'] = (int) $agent['id'];
            $_SESSION['agent_name'] = $agent['contact_person'] ?: $agent['company_name'];
            $_SESSION['agency_name'] = $agent['company_name'];
            return true;
        }
    } catch (PDOException $e) {
        // Fall through to legacy schema below.
    }

    // Legacy/alternate schema fallback.
    try {
        $stmt = $db->prepare('SELECT * FROM agents WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $agent = $stmt->fetch();
        if ($agent && password_verify($password, $agent['password_hash'])) {
            $_SESSION['agent_id'] = (int) $agent['id'];
            $_SESSION['agent_name'] = $agent['contact_name'] ?? ($agent['agency_name'] ?? '');
            $_SESSION['agency_name'] = $agent['agency_name'] ?? '';
            return true;
        }
    } catch (PDOException $e) {
        return false;
    }

    return false;
}

function logout()
{
    session_destroy();
    header('Location: login.php');
    exit;
}

function requireLogin()
{
    if (empty($_SESSION['agent_id'])) {
        header('Location: login.php');
        exit;
    }
}

function getAgent()
{
    return [
        'id' => $_SESSION['agent_id'] ?? null,
        'name' => $_SESSION['agent_name'] ?? '',
        'agency' => $_SESSION['agency_name'] ?? ''
    ];
}

function getAgentStats($agentId)
{
    $db = getDB();
    $stats = ['total' => 0, 'processing' => 0, 'approved' => 0, 'rejected' => 0];
    $stmt = $db->prepare('SELECT visa_status, COUNT(*) as cnt FROM visa_orders WHERE agent_id = ? GROUP BY visa_status');
    $stmt->execute([$agentId]);
    while ($row = $stmt->fetch()) {
        $stats['total'] += $row['cnt'];
        if ($row['visa_status'] === 'initiated' || $row['visa_status'] === 'in_review') {
            $stats['processing'] += $row['cnt'];
        } elseif ($row['visa_status'] === 'approved') {
            $stats['approved'] = $row['cnt'];
        } elseif ($row['visa_status'] === 'rejected') {
            $stats['rejected'] = $row['cnt'];
        }
    }
    return $stats;
}

function getRecentOrders($agentId, $limit = 10)
{
    $db = getDB();
    $limit = intval($limit);
    $stmt = $db->prepare("
        SELECT vo.*, c.country_name,
               (SELECT CONCAT(aa_fn.answer_text, ' ', aa_ln.answer_text)
                FROM applicants ap
                JOIN applicant_answers aa_fn ON aa_fn.applicant_id = ap.id AND aa_fn.question_id = (SELECT id FROM country_questions WHERE country_id = vo.country_id AND field_key = 'first_name' LIMIT 1)
                JOIN applicant_answers aa_ln ON aa_ln.applicant_id = ap.id AND aa_ln.question_id = (SELECT id FROM country_questions WHERE country_id = vo.country_id AND field_key = 'last_name' LIMIT 1)
                WHERE ap.order_id = vo.id
                LIMIT 1) as customer_name,
               (SELECT COUNT(*) FROM applicants WHERE order_id = vo.id) as applicant_count
        FROM visa_orders vo
        JOIN countries c ON c.id = vo.country_id
        WHERE vo.agent_id = ?
        ORDER BY vo.created_at DESC
        LIMIT $limit
    ");
    $stmt->execute([$agentId]);
    return $stmt->fetchAll();
}

function getAllOrders($agentId, $filters = [])
{
    $db = getDB();
    $where = ['vo.agent_id = ?'];
    $params = [$agentId];

    if (!empty($filters['country'])) {
        $where[] = 'vo.country_id = ?';
        $params[] = $filters['country'];
    }
    if (!empty($filters['status'])) {
        $where[] = 'vo.visa_status = ?';
        $params[] = $filters['status'];
    }
    if (!empty($filters['search'])) {
        $where[] = '(vo.id LIKE ? OR vo.email LIKE ?)';
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }

    $page = max(1, intval($filters['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    $whereStr = implode(' AND ', $where);

    // Count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM visa_orders vo WHERE $whereStr");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Data
    $stmt = $db->prepare("
        SELECT vo.*, c.country_name,
               (SELECT CONCAT(aa_fn.answer_text, ' ', aa_ln.answer_text)
                FROM applicants ap
                JOIN applicant_answers aa_fn ON aa_fn.applicant_id = ap.id AND aa_fn.question_id = (SELECT id FROM country_questions WHERE country_id = vo.country_id AND field_key = 'first_name' LIMIT 1)
                JOIN applicant_answers aa_ln ON aa_ln.applicant_id = ap.id AND aa_ln.question_id = (SELECT id FROM country_questions WHERE country_id = vo.country_id AND field_key = 'last_name' LIMIT 1)
                WHERE ap.order_id = vo.id
                LIMIT 1) as customer_name,
               (SELECT COUNT(*) FROM applicants WHERE order_id = vo.id) as applicant_count
        FROM visa_orders vo
        JOIN countries c ON c.id = vo.country_id
        WHERE $whereStr
        ORDER BY vo.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);

    return [
        'orders' => $stmt->fetchAll(),
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $perPage)
    ];
}

function getOrderDetail($orderId, $agentId)
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT vo.*, c.country_name
        FROM visa_orders vo
        JOIN countries c ON c.id = vo.country_id
        WHERE vo.id = ? AND vo.agent_id = ?
    ');
    $stmt->execute([$orderId, $agentId]);
    $order = $stmt->fetch();
    if (!$order)
        return null;

    // Applicants
    $appStmt = $db->prepare('SELECT * FROM applicants WHERE order_id = ?');
    $appStmt->execute([$orderId]);
    $order['applicants'] = $appStmt->fetchAll();

    // Answers per applicant
    foreach ($order['applicants'] as &$app) {
        $ansStmt = $db->prepare('
            SELECT aa.*, cq.label, cq.field_key, cq.field_type
            FROM applicant_answers aa
            JOIN country_questions cq ON cq.id = aa.question_id
            WHERE aa.applicant_id = ?
            ORDER BY cq.sort_order
        ');
        $ansStmt->execute([$app['id']]);
        $app['answers'] = $ansStmt->fetchAll();

        $fileStmt = $db->prepare('SELECT * FROM applicant_files WHERE applicant_id = ?');
        $fileStmt->execute([$app['id']]);
        $app['files'] = $fileStmt->fetchAll();
    }

    // Payment
    $payStmt = $db->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC LIMIT 1');
    $payStmt->execute([$orderId]);
    $order['payment'] = $payStmt->fetch();

    return $order;
}

function getCountries()
{
    $db = getDB();
    return $db->query('SELECT * FROM countries WHERE is_active = 1 ORDER BY country_name')->fetchAll();
}

function statusBadge($status)
{
    $map = [
        'initiated' => ['⏳', '#f59e0b', '#fffbeb'],
        'in_review' => ['🔍', '#3b82f6', '#eff6ff'],
        'approved' => ['✅', '#16a34a', '#f0fdf4'],
        'rejected' => ['❌', '#dc2626', '#fef2f2'],
        'pending' => ['⏳', '#f59e0b', '#fffbeb'],
        'paid' => ['✅', '#16a34a', '#f0fdf4'],
        'success' => ['✅', '#16a34a', '#f0fdf4'],
        'failed' => ['❌', '#dc2626', '#fef2f2'],
    ];
    $s = $map[$status] ?? ['•', '#6b7280', '#f3f4f6'];
    return '<span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;font-size:.75rem;font-weight:600;background:' . $s[2] . ';color:' . $s[1] . '">' . $s[0] . ' ' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
}
