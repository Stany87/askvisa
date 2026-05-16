<?php
session_start();
require 'db.php'; 
// Helper function to format bold text for initial page load
function formatBold($text) {
    return preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $text);
}

if (isset($_GET['ajax'])) {
    $msg = htmlspecialchars(trim($_POST['message'] ?? ''));
    $response = "";
    $img_path = "";
    $progress = 0;

    // 1. Handle File Uploads
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $home_dir = dirname($_SERVER['DOCUMENT_ROOT']); 
        $base_gov_id = $home_dir . '/gov_id/';

        if (!isset($_SESSION['order_folder_name'])) {
         // Generate a unique temporary name
            $_SESSION['order_folder_name'] = 'TMP_' . time() . '_' . uniqid();
        }

        $p_num = $_SESSION['current_person_num'] ?? 1;
        $sub_path = date('Y/m/d') . '/' . $_SESSION['order_folder_name'] . '/applicant_' . $p_num;
        $full_dir = $base_gov_id . $sub_path . '/';
        
        if (!is_dir($full_dir)) mkdir($full_dir, 0775, true);

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = 'file_' . time() . '_' . uniqid() . '.' . $ext;
        $target = $full_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $img_path = 'fetch_file.php?path=' . urlencode($sub_path . '/' . $filename);
        }
    }

    // 2. Main Logic
    if ($msg !== '' || $img_path !== '') {
        $p_num = $_SESSION['current_person_num'] ?? 1;
        
        if ($img_path !== '') {
            $is_pdf = (strpos(strtolower($img_path), 'pdf') !== false);
            $_SESSION['messages'][] = ['role' => 'user', 'text' => $is_pdf ? "Uploaded PDF" : "Uploaded Image", 'img' => $img_path, 'is_pdf' => $is_pdf];
        } else {
            $_SESSION['messages'][] = ['role' => 'user', 'text' => $msg];
        }

        switch ($_SESSION['step']) {
            case 'country':
                $stmt = $pdo->prepare("SELECT id, country_name FROM countries WHERE country_name LIKE ? AND is_active = 1 LIMIT 1");
                $stmt->execute(["%$msg%"]);
                $country = $stmt->fetch();
                if ($country) {
                    $_SESSION['country_id'] = $country['id'];
                    $q_stmt = $pdo->prepare("SELECT id, label, field_type FROM country_questions WHERE country_id = ? ORDER BY sort_order ASC");
                    $q_stmt->execute([$country['id']]);
                    $_SESSION['db_questions'] = $q_stmt->fetchAll();
                    $_SESSION['step'] = 'how_many';
                    $response = "Selected: **" . trim($country['country_name']) . "**. How many applicants?";
                } else { $response = "Country not found. Please type 'Thailand'."; }
                break;

            case 'how_many':
                if (is_numeric($msg) && (int)$msg > 0) {
                    $_SESSION['total_people'] = (int)$msg;
                    $_SESSION['current_person_num'] = 1; 
                    $_SESSION['q_idx'] = 0; 
                    $_SESSION['step'] = 'details';
                    $response = "Applicant #1. **" . trim($_SESSION['db_questions'][0]['label']) . "**?";
                } else { $response = "Please enter a valid number."; }
                break;

            case 'details':
                $questions = $_SESSION['db_questions'];
                $current_q = $questions[$_SESSION['q_idx']];

                if ($current_q['field_type'] === 'file' && !$img_path) {
                    $response = "I need a file for: **" . trim($current_q['label']) . "**. Please use the 📎 icon.";
                } else {
                    $_SESSION['collected_info']["applicant_$p_num"]['answers'][$current_q['id']] = $img_path ?: $msg;
                    $_SESSION['q_idx']++;

                    if ($_SESSION['q_idx'] < count($questions)) {
                        $next_q = $questions[$_SESSION['q_idx']];
                        $response = "Next for Applicant #$p_num: **" . trim($next_q['label']) . "**?";
                    } else {
                        $_SESSION['step'] = 'applicant_email';
                        $response = "Done with documents for Applicant #$p_num. What is **their email address**?";
                    }
                }
                break;

            case 'applicant_email':
                $_SESSION['collected_info']["applicant_$p_num"]['email'] = $msg;
                $_SESSION['step'] = 'applicant_phone';
                $response = "What is the **phone number** for Applicant #$p_num?";
                break;

            case 'applicant_phone':
                $_SESSION['collected_info']["applicant_$p_num"]['phone'] = $msg;
                if ($_SESSION['current_person_num'] < $_SESSION['total_people']) {
                    $_SESSION['current_person_num']++;
                    $_SESSION['q_idx'] = 0;
                    $_SESSION['step'] = 'details';
                    $p = $_SESSION['current_person_num'];
                    $response = "Next: Applicant #$p. **" . trim($_SESSION['db_questions'][0]['label']) . "**?";
                } else {
                    $_SESSION['step'] = 'order_email';
                    $response = "All applicant details captured. Now, please provide the **Primary Contact Email** for this order.";
                }
                break;

            case 'order_email':
                $_SESSION['order_contact_email'] = $msg;
                $_SESSION['step'] = 'order_phone';
                $response = "Finally, what is the **Primary Contact Phone Number** for the order?";
                break;
                
            
            case 'order_phone':
                try {
                    $pdo->beginTransaction();
                    
                    // 1. Create the Order Entry
                    $stmt = $pdo->prepare("INSERT INTO visa_orders (country_id, email, phone) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['country_id'], $_SESSION['order_contact_email'], $msg]);
                    $order_id = $pdo->lastInsertId(); // This is your REAL ID (e.g., 50)
            
                    // 2. Rename the Physical Folder from "Order_XYZ" to the "ID"
                    $home_dir = dirname($_SERVER['DOCUMENT_ROOT']); 
                    $base_path = $home_dir . '/gov_id/' . date('Y/m/d') . '/';
                    
                    $old_folder_name = $_SESSION['order_folder_name']; // e.g., Order_63829
                    $new_folder_name = "order_" . $order_id; // e.g., 50
                    
                    if (is_dir($base_path . $old_folder_name)) {
                        rename($base_path . $old_folder_name, $base_path . $new_folder_name);
                    }
            
                    // 3. Update File Paths in Database
                    // Because we renamed the folder, we must replace the old name with the new ID in our saved paths
                    for ($i = 1; $i <= $_SESSION['total_people']; $i++) {
                        $app_data = $_SESSION['collected_info']["applicant_$i"];
                        
                        // Insert applicant... (as before)
                        $stmt = $pdo->prepare("INSERT INTO applicants (order_id, applicant_no, applicant_email, applicant_phone, visa_status) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$order_id, $i, $app_data['email'], $app_data['phone'], 'submitted']);
                        $app_id = $pdo->lastInsertId();
            
                        foreach ($app_data['answers'] as $q_id => $val) {
                            // If it's a file path, replace the temporary name with the real ID
                            if (strpos($val, 'fetch_file.php') !== false) {
                                $val = str_replace($old_folder_name, $new_folder_name, $val);
                            }
                            
                            $is_file = (strpos($val, 'fetch_file.php') !== false);
                            $tbl = $is_file ? 'applicant_files' : 'applicant_answers';
                            $col = $is_file ? 'file_path' : 'answer_text';
                            
                            $pdo->prepare("INSERT INTO $tbl (order_id, applicant_id, question_id, $col) VALUES (?, ?, ?, ?)")
                                ->execute([$order_id, $app_id, $q_id, $val]);
                        }
                    }
            
                    $pdo->commit();
                    $response = "Success! ✅ Order ID: **$order_id**";
                    $_SESSION['step'] = 'finish';
                } catch (Exception $e) { $pdo->rollBack(); $response = "Error: " . $e->getMessage(); }
                break;
        }
        $_SESSION['messages'][] = ['role' => 'bot', 'text' => $response];
    }

    $allow_upload = false;
    $step_label = "";
    if ($_SESSION['step'] === 'details' && isset($_SESSION['db_questions'][$_SESSION['q_idx']])) {
        $allow_upload = ($_SESSION['db_questions'][$_SESSION['q_idx']]['field_type'] === 'file');
        $progress = round(($_SESSION['q_idx'] / count($_SESSION['db_questions'])) * 100);
        $step_label = "Document " . ($_SESSION['q_idx'] + 1) . " of " . count($_SESSION['db_questions']);
    } elseif ($_SESSION['step'] === 'country') {
        $step_label = "Country Selection";
    } elseif ($_SESSION['step'] === 'how_many') {
        $step_label = "Applicant Count";
    } elseif ($_SESSION['step'] === 'applicant_email' || $_SESSION['step'] === 'applicant_phone') {
        $step_label = "Applicant #" . ($_SESSION['current_person_num'] ?? 1) . " Details";
    } elseif ($_SESSION['step'] === 'order_email' || $_SESSION['step'] === 'order_phone') {
        $step_label = "Order Contact";
    } elseif ($_SESSION['step'] === 'finish') {
        $step_label = "Complete";
    }

    echo json_encode([
        'text' => formatBold($response), 
        'is_finished' => ($_SESSION['step'] === 'finish'), 
        'progress' => $progress, 
        'allow_upload' => $allow_upload, 
        'img_path' => $img_path,
        'step_label' => $step_label
    ]);
    exit;
}

if (isset($_POST['reset'])) { session_unset(); session_destroy(); header('Location: '.$_SERVER['PHP_SELF']); exit; }
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [['role'=>'bot','text'=>'Hello! 👋 Which country are you applying for?']];
    $_SESSION['step'] = 'country';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ask Visa Portal</title>
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
            --dark-light: #24283b;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 16px;
            --border-radius-sm: 8px;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --box-shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark {
            --light: #1a1b26;
            --dark: #f8f9fa;
            --gray-light: #24283b;
            --gray: #a9b1d6;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            --box-shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--dark);
            line-height: 1.6;
            height: 100vh;
            overflow: hidden;
            transition: var(--transition);
        }

        .app-container {
            display: flex;
            height: 100vh;
            max-width: 1600px;
            margin: 0 auto;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 320px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 30px 24px;
            display: flex;
            flex-direction: column;
            color: white;
            box-shadow: var(--box-shadow-lg);
            z-index: 10;
            position: relative;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.05)"/></svg>'), 
                        radial-gradient(circle at 20% 80%, rgba(76, 201, 240, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(67, 97, 238, 0.1) 0%, transparent 50%);
            background-size: cover, cover, cover;
            pointer-events: none;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .logo-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            backdrop-filter: blur(10px);
            animation: logoFloat 4s ease-in-out infinite;
        }

        .logo-text h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .logo-text p {
            font-size: 13px;
            opacity: 0.9;
        }

        .progress-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 24px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
        }

        .progress-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shimmerBorder 3s linear infinite;
        }

        .step-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            position: relative;
            z-index: 1;
        }

        .step-label {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .step-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            animation: countPulse 2s infinite;
        }

        .progress-container {
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4cc9f0, #4895ef, #4cc9f0);
            background-size: 200% 100%;
            border-radius: 4px;
            width: 0%;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            animation: progressPulse 2s infinite, progressShimmer 3s linear infinite;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 2s infinite;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .sidebar-actions {
            margin-top: auto;
            position: relative;
            z-index: 1;
        }

        .action-btn {
            width: 100%;
            padding: 14px;
            border-radius: var(--border-radius-sm);
            border: none;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 16px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.7s ease;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .action-btn.danger {
            background: rgba(247, 37, 133, 0.2);
        }

        .action-btn.danger:hover {
            background: rgba(247, 37, 133, 0.3);
        }

        .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 18px;
            border-radius: var(--border-radius-sm);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .theme-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        .switch {
            position: relative;
            width: 52px;
            height: 28px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: var(--transition);
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        input:checked + .slider {
            background-color: var(--success);
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        /* Chat Container */
        .chat-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .chat-header {
            padding: 20px 30px;
            background: var(--light);
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            z-index: 5;
            position: relative;
        }

        .chat-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--success), var(--primary));
            background-size: 200% 100%;
            animation: headerShimmer 3s linear infinite;
        }

        .chat-title h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }

        .chat-title p {
            font-size: 14px;
            color: var(--gray);
            margin-top: 4px;
        }

        .chat-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--success);
            font-weight: 500;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
            box-shadow: 0 0 10px var(--success);
        }

        .chat-container {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            scroll-behavior: smooth;
            position: relative;
        }

        .chat-container::-webkit-scrollbar {
            width: 6px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background: var(--gray-light);
            border-radius: 3px;
        }

        .chat-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray);
        }

        .message-row {
            display: flex;
            margin-bottom: 24px;
            animation: fadeIn 0.4s ease-out;
            position: relative;
        }

        .message-row.bot {
            justify-content: flex-start;
            animation: slideInLeft 0.5s ease-out;
        }

        .message-row.user {
            justify-content: flex-end;
            animation: slideInRight 0.5s ease-out;
        }

        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 8px 12px 0 0;
            flex-shrink: 0;
            background: var(--primary);
            color: white;
            font-weight: 600;
            font-size: 14px;
            position: relative;
        }

        .message-avatar::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: var(--primary-light);
            animation: avatarSpin 2s linear infinite;
        }

        .message-row.user .message-avatar {
            margin: 8px 0 0 12px;
            background: var(--primary-light);
        }

        .message-content {
            max-width: 70%;
            /* Reduced padding: 10px top/bottom, 16px left/right */
            padding: 10px 16px; 
            border-radius: var(--border-radius);
            position: relative;
            box-shadow: var(--box-shadow);
            /* Tighter line height for a more compact look */
            line-height: 1.4; 
            word-wrap: break-word;
        }

        .message-row.bot .message-content {
            background: var(--light);
            border: 1px solid var(--gray-light);
            border-top-left-radius: 4px;
            color: var(--dark);
        }

        .message-row.user .message-content {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-text {
            font-size: 15px;
        }

        .message-text b {
            font-weight: 700;
            color: inherit;
        }

        .message-row.bot .message-text b {
            color: var(--primary);
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 8px;
            text-align: right;
        }

        .message-attachment {
            margin-top: 12px;
        }

        .msg-img {
            max-width: 240px;
            border-radius: var(--border-radius-sm);
            margin-top: 10px;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .msg-img:hover {
            transform: scale(1.03);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .pdf-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 14px 18px;
            border-radius: var(--border-radius-sm);
            margin-top: 12px;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .pdf-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .pdf-card:hover::before {
            left: 100%;
        }

        .message-row.bot .pdf-card {
            background: var(--gray-light);
            border: 1px solid var(--gray-light);
        }

        .pdf-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(4px);
        }

        .pdf-icon {
            font-size: 24px;
            color: var(--danger);
            animation: pdfPulse 2s infinite;
        }

        .pdf-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .pdf-info p {
            font-size: 12px;
            opacity: 0.8;
        }

        /* Input Area */
        .input-section {
            padding: 20px 30px;
            background: var(--light);
            border-top: 1px solid var(--gray-light);
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
            z-index: 5;
            position: relative;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--light);
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            padding: 8px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
        }

        .input-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .file-upload-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: var(--gray);
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .file-upload-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: var(--primary);
            animation: buttonSpin 1.5s linear infinite;
        }

        .file-upload-btn.active {
            background: var(--primary-light);
            color: white;
            cursor: pointer;
        }

        .file-upload-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .file-upload-btn:hover:not(.disabled) {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .input-field {
            flex: 1;
            border: none;
            background: transparent;
            padding: 14px 0;
            font-size: 16px;
            color: var(--dark);
            font-family: 'Inter', sans-serif;
            outline: none;
        }

        .input-field::placeholder {
            color: var(--gray);
            animation: placeholderPulse 2s infinite;
        }

        .send-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
        }

        .send-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .send-btn:hover::before {
            left: 100%;
        }

        .send-btn:hover:not(:disabled) {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Preview Tray */
        #previewTray {
            position: absolute;
            bottom: 100px;
            left: 30px;
            right: 30px;
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 16px 20px;
            display: none;
            align-items: center;
            gap: 16px;
            box-shadow: var(--box-shadow-lg);
            border: 1px solid var(--gray-light);
            z-index: 100;
            animation: slideUp 0.3s ease-out;
        }

        #previewImg {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius-sm);
            border: 2px solid var(--primary-light);
            animation: previewPulse 2s infinite;
        }

        .preview-info {
            flex: 1;
        }

        .preview-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .preview-info p {
            font-size: 12px;
            color: var(--gray);
        }

        .preview-close {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: var(--gray);
        }

        .preview-close:hover {
            background: var(--danger);
            color: white;
            transform: rotate(90deg);
        }

        /* Lightbox */
        #lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            backdrop-filter: blur(10px);
        }

        #lbContainer {
            width: 90%;
            height: 90%;
            position: relative;
        }

        #lbImg, #lbPdf {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: var(--border-radius);
            display: none;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 24px;
            color: white;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 2001;
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        /* Confirmation Modal */
        #confirmOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .confirm-card {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 32px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: var(--box-shadow-lg);
            border: 1px solid var(--gray-light);
            animation: modalSlide 0.3s ease-out;
        }

        .confirm-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(247, 37, 133, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: var(--danger);
            font-size: 32px;
            animation: dangerPulse 1.5s infinite;
        }

        .confirm-card h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--dark);
        }

        .confirm-card p {
            color: var(--gray);
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .confirm-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .confirm-btn {
            padding: 12px 28px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            position: relative;
            overflow: hidden;
        }

        .confirm-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .confirm-btn:hover::before {
            left: 100%;
        }

        .confirm-btn.cancel {
            background: var(--gray-light);
            color: var(--dark);
        }

        .confirm-btn.cancel:hover {
            background: var(--gray);
            color: white;
        }

        .confirm-btn.danger {
            background: linear-gradient(135deg, #f72585, #ff4d9e);
            color: white;
            box-shadow: 0 4px 15px rgba(247, 37, 133, 0.3);
        }

        .confirm-btn.danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(247, 37, 133, 0.4);
        }

        /* Completion State */
        .completion-state {
            text-align: center;
            padding: 40px 20px;
            background: var(--light);
            border-radius: var(--border-radius);
            margin: 20px auto;
            max-width: 500px;
            box-shadow: var(--box-shadow);
            border: 2px solid var(--success);
            position: relative;
            overflow: hidden;
        }

        .completion-state::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--success), var(--primary), var(--success));
            background-size: 200% 100%;
            animation: successShimmer 2s linear infinite;
        }

        .completion-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(76, 201, 240, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: var(--success);
            font-size: 36px;
            animation: bounce 1s infinite alternate, iconGlow 2s infinite;
        }

        .completion-state h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--success);
            background: linear-gradient(90deg, var(--success), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .completion-state p {
            color: var(--gray);
            margin-bottom: 20px;
        }

        .order-id {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            background: var(--gray-light);
            padding: 10px 20px;
            border-radius: var(--border-radius-sm);
            display: inline-block;
            margin: 10px 0;
            letter-spacing: 2px;
            animation: orderIdGlow 2s infinite;
            box-shadow: 0 0 20px rgba(67, 97, 238, 0.3);
        }

        /* ENHANCED LIVE ANIMATIONS */

        /* Flying planes background */
        .plane-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .flying-plane {
            position: absolute;
            width: 40px;
            height: 40px;
            opacity: 0.3;
            filter: drop-shadow(0 0 5px rgba(67, 97, 238, 0.5));
            z-index: 0;
        }

        /* Floating passport icons */
        .floating-passport {
            position: fixed;
            width: 30px;
            height: 30px;
            opacity: 0.1;
            color: var(--primary);
            z-index: 0;
            pointer-events: none;
        }

        /* Animated background particles */
        .background-particle {
            position: fixed;
            border-radius: 50%;
            background: var(--primary);
            opacity: 0.05;
            z-index: 0;
            pointer-events: none;
        }

        /* Pulsing elements */
        .pulse-element {
            animation: elementPulse 3s infinite;
        }

        /* Shimmering text */
        .shimmer-text {
            background: linear-gradient(90deg, var(--primary), var(--success), var(--primary));
            background-size: 200% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textShimmer 3s linear infinite;
        }

        /* Animated gradients */
        .animated-gradient {
            background: linear-gradient(-45deg, var(--primary), var(--primary-light), var(--success), var(--primary));
            background-size: 400% 400%;
            animation: gradientShift 10s ease infinite;
        }

        /* Loading dots */
        .loading-dots {
            display: flex;
            gap: 4px;
        }

        .loading-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--primary);
            animation: dotBounce 1.4s infinite ease-in-out;
        }

        .loading-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        /* Enhanced Typing Indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: var(--light);
            border-radius: 20px;
            width: fit-content;
            margin: 10px 0;
            border: 1px solid var(--gray-light);
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }

        .typing-indicator::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(67, 97, 238, 0.05), transparent);
            animation: typingShimmer 2s linear infinite;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            animation: typingBounce 1.4s infinite ease-in-out;
            box-shadow: 0 0 5px var(--primary);
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes modalSlide {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }

        /* Enhanced Animations */
        @keyframes flyAcross {
            0% {
                transform: translateX(-100px) translateY(var(--start-y)) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            100% {
                transform: translateX(calc(100vw + 100px)) translateY(var(--end-y)) rotate(10deg);
                opacity: 0;
            }
        }

        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-5px) rotate(2deg);
            }
        }

        @keyframes progressShimmer {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes headerShimmer {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes avatarSpin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes buttonSpin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes countPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes progressPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(76, 201, 240, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(76, 201, 240, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(76, 201, 240, 0);
            }
        }

        @keyframes pdfPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        @keyframes previewPulse {
            0%, 100% {
                border-color: var(--primary-light);
            }
            50% {
                border-color: var(--success);
            }
        }

        @keyframes placeholderPulse {
            0%, 100% {
                opacity: 0.5;
            }
            50% {
                opacity: 1;
            }
        }

        @keyframes dangerPulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(247, 37, 133, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(247, 37, 133, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(247, 37, 133, 0);
            }
        }

        @keyframes successShimmer {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes iconGlow {
            0%, 100% {
                filter: drop-shadow(0 0 5px var(--success));
            }
            50% {
                filter: drop-shadow(0 0 15px var(--success));
            }
        }

        @keyframes orderIdGlow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(67, 97, 238, 0.3);
            }
            50% {
                box-shadow: 0 0 30px rgba(67, 97, 238, 0.6);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes shimmerBorder {
            0% {
                transform: rotate(45deg) translateX(-100%);
            }
            100% {
                transform: rotate(45deg) translateX(100%);
            }
        }

        @keyframes elementPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.7;
            }
            50% {
                transform: scale(1.05);
                opacity: 1;
            }
        }

        @keyframes textShimmer {
            0% {
                background-position: -200% 50%;
            }
            100% {
                background-position: 200% 50%;
            }
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes dotBounce {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        @keyframes typingShimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        @keyframes typingBounce {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        @keyframes floatUp {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.1;
            }
            90% {
                opacity: 0.1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        @keyframes floatPassport {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.1;
            }
            90% {
                opacity: 0.1;
            }
            100% {
                transform: translateY(-100px) rotate(720deg);
                opacity: 0;
            }
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.05;
            }
            90% {
                opacity: 0.05;
            }
            100% {
                transform: translateY(-100px) translateX(100px) rotate(180deg);
                opacity: 0;
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 280px;
            }
            
            .message-content {
                max-width: 85%;
            }
        }

        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                padding: 20px;
            }
            
            .logo {
                margin-bottom: 20px;
            }
            
            .progress-section {
                margin-bottom: 20px;
            }
            
            .chat-header {
                padding: 15px 20px;
            }
            
            .chat-container {
                padding: 20px;
            }
            
            .input-section {
                padding: 15px 20px;
            }
            
            #previewTray {
                left: 20px;
                right: 20px;
                bottom: 90px;
            }
        }
    </style>
</head>
<body id="body">
<div class="app-container">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-passport"></i>
            </div>
            <div class="logo-text">
                <h1>Ask Visa</h1>
                <p>Intelligent Application Assistant</p>
            </div>
        </div>
        
        <div class="progress-section">
            <div class="step-info">
                <span class="step-label" id="stepLabel">Country Selection</span>
                <span class="step-count" id="stepCount">Step 1/8</span>
            </div>
            <div class="progress-container">
                <div id="pBar" class="progress-bar"></div>
            </div>
            <div class="stats">
                <span id="applicantCount">Applicants: 0</span>
                <span id="progressPercent">0%</span>
            </div>
        </div>
        
        <div class="sidebar-actions">
            <button class="action-btn" onclick="toggleConfirm(true)">
                <i class="fas fa-plus-circle"></i>
                New Application
            </button>
            <button class="action-btn danger" onclick="downloadSummary()">
                <i class="fas fa-download"></i>
                Download Summary
            </button>
            
            <div class="theme-toggle">
                <div class="theme-label">
                    <i class="fas fa-moon"></i>
                    Dark Mode
                </div>
                <label class="switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- Main Chat Section -->
    <div class="chat-section">
        <div class="chat-header">
            <div class="chat-title">
                <h2>Visa Application Assistant</h2>
                <p>Your personal guide through the visa application process</p>
            </div>
            <div class="chat-status">
                <div class="status-dot"></div>
                <span>Assistant is online</span>
            </div>
        </div>
        
        <div class="chat-container" id="chat">
            <?php foreach($_SESSION['messages'] as $index => $m): ?>
                <div class="message-row <?= $m['role'] === 'user' ? 'user' : 'bot' ?>">
                    <?php if($m['role'] === 'bot'): ?>
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="message-content">
                        <div class="message-text">
                            <?= formatBold($m['text']) ?>
                        </div>
                        
                        <?php if(isset($m['img'])): ?>
                            <div class="message-attachment">
                                <?php if(isset($m['is_pdf']) && $m['is_pdf']): ?>
                                    <div class="pdf-card" onclick="openLightbox('<?= $m['img'] ?>')">
                                        <div class="pdf-icon">
                                            <i class="fas fa-file-pdf"></i>
                                        </div>
                                        <div class="pdf-info">
                                            <h4>Uploaded Document</h4>
                                            <p>Click to preview PDF</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= $m['img'] ?>" class="msg-img" onclick="openLightbox(this.src)">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-time">
                            <?= date('H:i') ?>
                        </div>
                    </div>
                    
                    <?php if($m['role'] === 'user'): ?>
                        <div class="message-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <!-- Completion State (hidden by default) -->
            <div id="completionState" style="display: none;">
                <div class="completion-state">
                    <div class="completion-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Application Complete!</h3>
                    <p>Your visa application has been successfully submitted.</p>
                    <div class="order-id" id="finalOrderId">#0000</div>
                    <p>You will receive a confirmation email shortly.</p>
                </div>
            </div>
        </div>
        
        <!-- Preview Tray -->
        <div id="previewTray">
            <img id="previewImg" src="">
            <div class="preview-info">
                <h4 id="previewFileName">File Preview</h4>
                <p id="previewFileSize">Ready to upload</p>
            </div>
            <div class="preview-close" onclick="clearPreview()">
                <i class="fas fa-times"></i>
            </div>
        </div>
        
        <!-- Input Area -->
        <div class="input-section">
            <div class="input-wrapper">
                <label id="attachBtn" class="file-upload-btn disabled">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" id="fileInput" hidden accept="image/*,application/pdf" disabled onchange="handlePreview(this)">
                </label>
                <input type="text" id="msgInput" class="input-field" placeholder="Type your response here..." autocomplete="off">
                <button id="sendBtn" class="send-btn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ENHANCED ANIMATION ELEMENTS -->

<!-- Plane Background -->
<div class="plane-background" id="planeBackground"></div>

<!-- Floating Passports -->
<div id="floatingPassports"></div>

<!-- Background Particles -->
<div id="backgroundParticles"></div>

<!-- Enhanced Typing Indicator -->
<div class="typing-indicator" id="enhancedTyping" style="display: none;">
    <div class="typing-dot"></div>
    <div class="typing-dot"></div>
    <div class="typing-dot"></div>
    <span>Assistant is typing...</span>
</div>

<!-- Lightbox -->
<div id="lightbox" onclick="closeLightbox()">
    <div class="lightbox-close" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
    </div>
    <div id="lbContainer" onclick="event.stopPropagation()">
        <img id="lbImg">
        <iframe id="lbPdf"></iframe>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmOverlay">
    <div class="confirm-card">
        <div class="confirm-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Reset Application?</h3>
        <p>This will clear all current progress and start a new application. This action cannot be undone.</p>
        <div class="confirm-actions">
            <button class="confirm-btn cancel" onclick="toggleConfirm(false)">Cancel</button>
            <form method="POST" style="display:inline;">
                <button type="submit" name="reset" class="confirm-btn danger">Reset Application</button>
            </form>
        </div>
    </div>
</div>

<script>
    const chat = document.getElementById('chat');
    const msgInput = document.getElementById('msgInput');
    const fileInput = document.getElementById('fileInput');
    const attachBtn = document.getElementById('attachBtn');
    const sendBtn = document.getElementById('sendBtn');
    const pBar = document.getElementById('pBar');
    const stepLabel = document.getElementById('stepLabel');
    const stepCount = document.getElementById('stepCount');
    const applicantCount = document.getElementById('applicantCount');
    const progressPercent = document.getElementById('progressPercent');
    const themeToggle = document.getElementById('themeToggle');
    const completionState = document.getElementById('completionState');
    const finalOrderId = document.getElementById('finalOrderId');
    
    let isProcessing = false;
    let currentOrderId = null;

    // Create continuous flying planes
    function createContinuousPlanes() {
        const planeBackground = document.getElementById('planeBackground');
        const planeCount = 5;
        
        for (let i = 0; i < planeCount; i++) {
            setTimeout(() => {
                createFlyingPlane(i);
            }, i * 2000);
        }
        
        // Keep creating planes
        setInterval(() => {
            createFlyingPlane(Math.floor(Math.random() * 5));
        }, 3000);
    }
    
    function createFlyingPlane(id) {
        const planeBackground = document.getElementById('planeBackground');
        const plane = document.createElement('div');
        plane.className = 'flying-plane';
        plane.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 9L21 3L17 12L21 21L10 15V19L7 21L6 15L3 13L4 10L6 8L3 6L4 3L7 5H10V9Z" fill="var(--primary)"/>
            </svg>
        `;
        
        const startY = Math.random() * 80 + 10; // 10% to 90%
        const endY = Math.random() * 80 + 10;
        const duration = Math.random() * 20000 + 15000; // 15-35 seconds
        const size = Math.random() * 20 + 20; // 20-40px
        
        plane.style.setProperty('--start-y', `${startY}%`);
        plane.style.setProperty('--end-y', `${endY}%`);
        plane.style.width = `${size}px`;
        plane.style.height = `${size}px`;
        plane.style.top = `${startY}%`;
        plane.style.left = '-50px';
        plane.style.animation = `flyAcross ${duration}ms linear`;
        plane.style.opacity = '0.2';
        
        planeBackground.appendChild(plane);
        
        // Remove plane after animation
        setTimeout(() => {
            if (plane.parentNode) {
                plane.parentNode.removeChild(plane);
            }
        }, duration);
    }

    // Create floating passports
    function createFloatingPassports() {
        const container = document.getElementById('floatingPassports');
        
        setInterval(() => {
            if (Math.random() > 0.7) { // 30% chance to create a passport
                const passport = document.createElement('div');
                passport.className = 'floating-passport';
                passport.innerHTML = '<i class="fas fa-passport"></i>';
                
                const startX = Math.random() * 100;
                const duration = Math.random() * 30000 + 20000; // 20-50 seconds
                const size = Math.random() * 30 + 20; // 20-50px
                
                passport.style.left = `${startX}%`;
                passport.style.bottom = '-50px';
                passport.style.fontSize = `${size}px`;
                passport.style.animation = `floatPassport ${duration}ms linear`;
                
                container.appendChild(passport);
                
                // Remove after animation
                setTimeout(() => {
                    if (passport.parentNode) {
                        passport.parentNode.removeChild(passport);
                    }
                }, duration);
            }
        }, 2000);
    }

    // Create background particles
    function createBackgroundParticles() {
        const container = document.getElementById('backgroundParticles');
        
        // Initial particles
        for (let i = 0; i < 20; i++) {
            createParticle();
        }
        
        // Add new particles occasionally
        setInterval(() => {
            if (Math.random() > 0.8) { // 20% chance to add a particle
                createParticle();
            }
        }, 1000);
    }
    
    function createParticle() {
        const container = document.getElementById('backgroundParticles');
        const particle = document.createElement('div');
        particle.className = 'background-particle';
        
        const size = Math.random() * 10 + 5; // 5-15px
        const startX = Math.random() * 100;
        const duration = Math.random() * 20000 + 10000; // 10-30 seconds
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${startX}%`;
        particle.style.bottom = '-20px';
        particle.style.animation = `particleFloat ${duration}ms linear`;
        
        // Random color
        const colors = ['#4361ee', '#4895ef', '#4cc9f0', '#f72585', '#f8961e'];
        particle.style.background = colors[Math.floor(Math.random() * colors.length)];
        
        container.appendChild(particle);
        
        // Remove after animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, duration);
    }

    // Enhanced typing indicator
    function showEnhancedTyping() {
        const typingIndicator = document.getElementById('enhancedTyping');
        typingIndicator.style.display = 'flex';
        chat.appendChild(typingIndicator);
        chat.scrollTop = chat.scrollHeight;
    }

    function hideEnhancedTyping() {
        const typingIndicator = document.getElementById('enhancedTyping');
        typingIndicator.style.display = 'none';
    }

    // Create celebration confetti
    function createConfetti() {
        const colors = ['#4361ee', '#4895ef', '#4cc9f0', '#f72585', '#f8961e'];
        
        for (let i = 0; i < 30; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = `${Math.random() * 10 + 5}px`;
                confetti.style.height = confetti.style.width;
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                confetti.style.left = `${Math.random() * 100}%`;
                confetti.style.top = '-20px';
                confetti.style.opacity = '0.7';
                confetti.style.zIndex = '9999';
                confetti.style.pointerEvents = 'none';
                
                document.body.appendChild(confetti);
                
                // Animate confetti
                const animation = confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 0.7 },
                    { transform: `translateY(${window.innerHeight + 100}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                ], {
                    duration: Math.random() * 2000 + 1000,
                    easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
                });
                
                // Remove after animation
                animation.onfinish = () => {
                    if (confetti.parentNode) {
                        confetti.parentNode.removeChild(confetti);
                    }
                };
            }, i * 50);
        }
    }

    // Progress milestone checker
    function checkProgressMilestone(progress) {
        if (progress >= 25 && progress < 30) {
            createConfetti();
        } else if (progress >= 50 && progress < 55) {
            createConfetti();
            // Add extra planes
            for (let i = 0; i < 3; i++) {
                setTimeout(() => createFlyingPlane(i), i * 300);
            }
        } else if (progress >= 75 && progress < 80) {
            createConfetti();
        } else if (progress >= 100) {
            // Big celebration
            for (let i = 0; i < 5; i++) {
                setTimeout(() => createConfetti(), i * 200);
            }
            // Add many planes
            for (let i = 0; i < 10; i++) {
                setTimeout(() => createFlyingPlane(i), i * 100);
            }
        }
    }

    // Lightbox Logic
    function openLightbox(src) { 
        const isPdf = src.toLowerCase().includes('pdf') || src.startsWith('blob:');
        document.getElementById('lbImg').style.display = isPdf ? 'none' : 'block';
        document.getElementById('lbPdf').style.display = isPdf ? 'block' : 'none';
        if(isPdf) {
            document.getElementById('lbPdf').src = src + "#toolbar=0";
        } else {
            document.getElementById('lbImg').src = src;
        }
        document.getElementById('lightbox').style.display = 'flex'; 
    }
    
    function closeLightbox() { 
        document.getElementById('lightbox').style.display = 'none'; 
        document.getElementById('lbPdf').src = ''; 
    }
    
    function toggleConfirm(show) { 
        document.getElementById('confirmOverlay').style.display = show ? 'flex' : 'none'; 
    }

    // File Preview
    function handlePreview(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = (e) => { 
                const isPdf = file.type === "application/pdf";
                document.getElementById('previewImg').src = isPdf 
                    ? "https://cdn-icons-png.flaticon.com/512/337/337946.png" 
                    : e.target.result;
                
                document.getElementById('previewFileName').textContent = file.name;
                document.getElementById('previewFileSize').textContent = 
                    `${(file.size / 1024 / 1024).toFixed(2)} MB • ${isPdf ? 'PDF Document' : 'Image'}`;
                
                document.getElementById('previewTray').style.display = 'flex'; 
            };
            reader.readAsDataURL(file);
        }
    }
    
    function clearPreview() { 
        fileInput.value = ""; 
        document.getElementById('previewTray').style.display = 'none'; 
    }

    // Update progress display
    function updateProgressDisplay(data) {
        // Update progress bar
        if (pBar) {
            pBar.style.width = data.progress + '%';
        }
        
        // Update progress percentage
        if (progressPercent) {
            progressPercent.textContent = data.progress + '%';
        }
        
        // Update step label
        if (stepLabel && data.step_label) {
            stepLabel.textContent = data.step_label;
        }
        
        // Update applicant count
        if (applicantCount && data.current_person && data.total_people) {
            applicantCount.textContent = `Applicant ${data.current_person}/${data.total_people}`;
        }
    }

    // Modified Message Sending function
    async function sendMessage() {
        const file = fileInput.files[0];
        const text = msgInput.value.trim();
        
        if (isProcessing || (!text && !file)) return;
        
        // Show enhanced typing indicator
        showEnhancedTyping();
        
        isProcessing = true;
        msgInput.disabled = true;
        sendBtn.disabled = true;

        const formData = new FormData();
        formData.append('message', text);
        if (file) formData.append('image', file);

        // Add user message to UI immediately
        if (text || file) {
            const userRow = document.createElement('div');
            userRow.className = 'message-row user';
            
            userRow.innerHTML = `
                <div class="message-content">
                    <div class="message-text">${text || (file.type === "application/pdf" ? "Uploaded PDF document" : "Uploaded image")}</div>
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
                <div class="message-avatar">
                    <i class="fas fa-user"></i>
                </div>
            `;
            chat.appendChild(userRow);
        }

        msgInput.value = ''; 
        clearPreview();
        chat.scrollTop = chat.scrollHeight;

        try {
            const response = await fetch('?ajax=1', { method: 'POST', body: formData });
            const data = await response.json();

            // Hide typing indicator
            hideEnhancedTyping();

            // Update progress display
            updateProgressDisplay(data);

            // Check for progress milestones
            if (data.progress) {
                checkProgressMilestone(data.progress);
            }

            // Add bot response to UI
            const botRow = document.createElement('div');
            botRow.className = 'message-row bot';
            
            const formattedText = data.text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
            
            let attachmentHtml = '';
            if (data.img_path) {
                const isPdf = data.img_path.toLowerCase().includes('pdf');
                if (isPdf) {
                    attachmentHtml = `
                        <div class="message-attachment">
                            <div class="pdf-card" onclick="openLightbox('${data.img_path}')">
                                <div class="pdf-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="pdf-info">
                                    <h4>Uploaded Document</h4>
                                    <p>Click to preview PDF</p>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    attachmentHtml = `
                        <div class="message-attachment">
                            <img src="${data.img_path}" class="msg-img" onclick="openLightbox(this.src)">
                        </div>
                    `;
                }
            }
            
            botRow.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-text">${formattedText}</div>
                    ${attachmentHtml}
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
            `;
            
            chat.appendChild(botRow);

            // Update file upload button
            if (data.allow_upload) {
                attachBtn.classList.remove('disabled');
                attachBtn.classList.add('active');
                fileInput.disabled = false;
            } else {
                attachBtn.classList.remove('active');
                attachBtn.classList.add('disabled');
                fileInput.disabled = true;
            }

            // Handle finish state
            if (data.is_finished) {
                msgInput.placeholder = "Application Complete";
                msgInput.disabled = true;
                sendBtn.disabled = true;
                
                // Extract order ID from response
                const orderMatch = data.text.match(/Order ID:\s*\*\*(\d+)\*\*/);
                if (orderMatch) {
                    currentOrderId = orderMatch[1];
                    finalOrderId.textContent = `#${currentOrderId}`;
                    
                    // Show completion state
                    setTimeout(() => {
                        completionState.style.display = 'block';
                        chat.scrollTop = chat.scrollHeight;
                    }, 500);
                }
            }

        } catch (error) {
            console.error("Error sending message:", error);
            
            // Hide typing indicator on error
            hideEnhancedTyping();
            
            // Show error message
            const errorRow = document.createElement('div');
            errorRow.className = 'message-row bot';
            errorRow.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-text">Sorry, an error occurred. Please try again.</div>
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
            `;
            chat.appendChild(errorRow);
        } finally {
            isProcessing = false;
            msgInput.disabled = false;
            sendBtn.disabled = false;
            msgInput.focus();
            chat.scrollTop = chat.scrollHeight;
        }
    }

    // Theme Toggle Handler
    themeToggle.addEventListener('change', () => {
        document.body.classList.toggle('dark');
        localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    });

    // Enter key to send message
    msgInput.addEventListener('keypress', (e) => { 
        if(e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Download summary function
    function downloadSummary() {
        if (!currentOrderId) {
            alert('No application data available to download.');
            return;
        }
        
        // Create a simple summary
        const summary = `
Visa Application Summary
=======================
Order ID: ${currentOrderId}
Date: ${new Date().toLocaleDateString()}
Status: Submitted

This is a placeholder for the actual summary.
In a real implementation, this would generate a PDF with all application details.
        `;
        
        const blob = new Blob([summary], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `visa-application-${currentOrderId}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // Initialize enhanced animations
    window.addEventListener('load', () => {
        // Restore Theme
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            themeToggle.checked = true;
        }

        chat.scrollTop = chat.scrollHeight;
        
        // Initial Fetch for Progress and Upload status
        fetch('?ajax=1', { method: 'POST', body: new FormData() })
            .then(r => r.json())
            .then(data => {
                updateProgressDisplay(data);
                if (data.allow_upload) {
                    attachBtn.classList.remove('disabled');
                    attachBtn.classList.add('active');
                    fileInput.disabled = false;
                }
            });
            
        msgInput.focus();
        
        // Start continuous animations
        createContinuousPlanes();
        createFloatingPassports();
        createBackgroundParticles();
    });

    // Add keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ctrl + P to add extra planes
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            for (let i = 0; i < 3; i++) {
                createFlyingPlane(i);
            }
        }
        
        // Ctrl + C for confetti
        if (e.ctrlKey && e.key === 'c') {
            e.preventDefault();
            createConfetti();
        }
    });
</script>
</body>
</html>