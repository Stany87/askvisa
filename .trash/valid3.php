<?php
session_start();
require 'db.php';

// Handle summary data request
if (isset($_GET['get_summary'])) {
    $data = [];
    
    if (isset($_SESSION['collected_info']) && !empty($_SESSION['collected_info'])) {
        $data = $_SESSION['collected_info'];
        
        // Add question labels for display
        $question_labels = [];
        if (isset($_SESSION['question_data'])) {
            foreach ($_SESSION['question_data'] as $q_id => $q_data) {
                $question_labels[$q_id] = $q_data['label'];
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'country_name' => $_SESSION['country_name'] ?? '',
            'order_info' => [
                'email' => $_SESSION['order_contact_email'] ?? '',
                'phone' => isset($_SESSION['order_contact_phone']) ? $_SESSION['order_contact_phone'] : '',
                'id' => $_SESSION['current_order_id'] ?? ''
            ],
            'question_labels' => $question_labels
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No data available']);
    }
    exit;
}

// Initialize session variables if they don't exist
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [['role'=>'bot','text'=>'Hello! 👋 Which country are you applying for?']];
    $_SESSION['step'] = 'country';
}

if (!isset($_SESSION['collected_info'])) {
    $_SESSION['collected_info'] = [];
}

if (!isset($_SESSION['q_idx'])) {
    $_SESSION['q_idx'] = 0;
}

if (!isset($_SESSION['current_person_num'])) {
    $_SESSION['current_person_num'] = 1;
}

// Helper function to format bold text for initial page load
function formatBold($text) {
    return preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $text);
}

// Function to parse and apply validation rules from database
function applyValidationRules($value, $validation_rules, $question_label = '', $field_key = '', $context_data = [], $country_name = '') {
    $errors = [];
    
    // If no validation rules or empty JSON, return empty errors
    if (empty($validation_rules) || $validation_rules === '[]' || $validation_rules === '{}') {
        return $errors;
    }
    
    // Decode JSON validation rules
    $rules = json_decode($validation_rules, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $errors;
    }
    
    // Check required field
    if (isset($rules['required']) && $rules['required'] === true) {
        if (empty(trim($value))) {
            $errors[] = "This field is required.";
        }
    }
    
    // Check min_length
    if (isset($rules['min_length']) && strlen(trim($value)) < $rules['min_length']) {
        $errors[] = sprintf("Minimum %d characters required.", $rules['min_length']);
    }
    
    // Check max_length
    if (isset($rules['max_length']) && strlen(trim($value)) > $rules['max_length']) {
        $errors[] = sprintf("Maximum %d characters allowed.", $rules['max_length']);
    }
    
    // Check regex pattern
    if (isset($rules['regex']) && !empty($value)) {
        // Clean the regex pattern (remove escaping)
        $regex_pattern = str_replace('\\\\', '\\', $rules['regex']);
        if (!preg_match('/' . $regex_pattern . '/', $value)) {
            // Provide specific hints based on field type
            if ($field_key === 'passport_number') {
                $errors[] = "Passport number must contain only uppercase letters and numbers (no spaces or special characters).";
            } elseif (in_array($field_key, ['first_name', 'last_name'])) {
                $errors[] = "Name can only contain letters, spaces, apostrophes and hyphens.";
            } elseif ($field_key === 'arrival_flight') {
                $errors[] = "Flight number must contain only letters and numbers.";
            } else {
                $errors[] = "Invalid format.";
            }
        }
    }
    
    // Check date format
    if (isset($rules['date_format'])) {
        $date_error = validateDateWithRules($value, $rules, $context_data, $field_key);
        if ($date_error) {
            $errors[] = $date_error;
        }
    }
    
    return $errors;
}

// Function to validate dates based on rules
function validateDateWithRules($date, $rules, $context_data = [], $field_key = '') {
    if (empty($date)) {
        return null;
    }
    
    // Check date format - always DD-MM-YYYY for your system
    $d = DateTime::createFromFormat('d-m-Y', $date);
    if (!$d || $d->format('d-m-Y') !== $date) {
        return "Date must be in DD-MM-YYYY format (e.g., 31-12-2024).";
    }
    
    $date_obj = DateTime::createFromFormat('d-m-Y', $date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    // Check min_date
    if (isset($rules['min_date'])) {
        $min_date_str = $rules['min_date'];
        if ($min_date_str === 'TODAY') {
            $min_date = clone $today;
        } elseif (preg_match('/^\d{2}-\d{2}-\d{4}$/', $min_date_str)) {
            $min_date = DateTime::createFromFormat('d-m-Y', $min_date_str);
            $min_date->setTime(0, 0, 0);
        } else {
            return null;
        }
        
        if ($date_obj < $min_date) {
            if ($rules['min_date'] === 'TODAY') {
                return "Date cannot be in the past.";
            } else {
                return sprintf("Date must be on or after %s.", $min_date_str);
            }
        }
    }
    
    // Check max_date
    if (isset($rules['max_date'])) {
        $max_date_str = $rules['max_date'];
        if ($max_date_str === 'TODAY') {
            $max_date = clone $today;
        } elseif (preg_match('/^\d{2}-\d{2}-\d{4}$/', $max_date_str)) {
            $max_date = DateTime::createFromFormat('d-m-Y', $max_date_str);
            $max_date->setTime(0, 0, 0);
        } else {
            return null;
        }
        
        if ($date_obj > $max_date) {
            if ($rules['max_date'] === 'TODAY') {
                return "Date cannot be in the future.";
            } else {
                return sprintf("Date must be on or before %s.", $max_date_str);
            }
        }
    }
    
    // Check min_validity_days (for expiry dates)
    if (isset($rules['min_validity_days'])) {
        $days_required = (int)$rules['min_validity_days'];
        $future_date = clone $today;
        $future_date->modify("+$days_required days");
        
        if ($date_obj < $future_date) {
            return sprintf("Passport must be valid for at least %d more days.", $days_required);
        }
    }
    
    // Validate passport expiry > issue date
    if ($field_key === 'passport_expiry_date') {
        // Look for issue date in context
        foreach ($context_data as $key => $val) {
            if (strpos(strtolower($key), 'issue') !== false && preg_match('/^\d{2}-\d{2}-\d{4}$/', $val)) {
                $issue_date = DateTime::createFromFormat('d-m-Y', $val);
                if ($issue_date && $date_obj <= $issue_date) {
                    return "Expiry date must be after issue date.";
                }
            }
        }
    }
    
    // Validate issue date < expiry date
    if ($field_key === 'passport_issue_date') {
        // Look for expiry date in context
        foreach ($context_data as $key => $val) {
            if ((strpos(strtolower($key), 'expiry') !== false || strpos(strtolower($key), 'expiration') !== false) && 
                preg_match('/^\d{2}-\d{2}-\d{4}$/', $val)) {
                $expiry_date = DateTime::createFromFormat('d-m-Y', $val);
                if ($expiry_date && $date_obj >= $expiry_date) {
                    return "Issue date must be before expiry date.";
                }
            }
        }
    }
    
    return null;
}

// Function to validate file uploads based on rules
function validateFileUpload($file, $validation_rules, $field_key = '') {
    $errors = [];
    
    if (empty($validation_rules)) {
        return $errors;
    }
    
    $rules = json_decode($validation_rules, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $errors;
    }
    
    // Check required
    if (isset($rules['required']) && $rules['required'] === true && empty($file['name'])) {
        $errors[] = "This file is required.";
        return $errors;
    }
    
    if (empty($file['name'])) {
        return $errors;
    }
    
    // Check file types
    if (isset($rules['file_types']) && is_array($rules['file_types'])) {
        $file_type = mime_content_type($file['tmp_name']);
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        $allowed = false;
        foreach ($rules['file_types'] as $allowed_type) {
            if ($file_type === $allowed_type) {
                $allowed = true;
                break;
            }
            if ($allowed_type === 'image/jpeg' && in_array($file_extension, ['jpg', 'jpeg'])) {
                $allowed = true;
                break;
            }
            if ($allowed_type === 'image/png' && $file_extension === 'png') {
                $allowed = true;
                break;
            }
            if ($allowed_type === 'application/pdf' && $file_extension === 'pdf') {
                $allowed = true;
                break;
            }
        }
        
        if (!$allowed) {
            $allowed_types = [];
            foreach ($rules['file_types'] as $type) {
                if ($type === 'image/jpeg') $allowed_types[] = 'JPG';
                if ($type === 'image/png') $allowed_types[] = 'PNG';
                if ($type === 'application/pdf') $allowed_types[] = 'PDF';
            }
            $errors[] = sprintf("Invalid file type. Allowed formats: %s.", implode(', ', array_unique($allowed_types)));
        }
    }
    
    // Check file size
    if (isset($rules['max_size'])) {
        $max_size = (int)$rules['max_size']; // in bytes
        if ($file['size'] > $max_size) {
            $mb_size = round($max_size / 1024 / 1024, 1);
            $errors[] = sprintf("File too large. Maximum size: %dMB.", $mb_size);
        }
    }
    
    return $errors;
}

// Function to get select field options from database
function getSelectOptions($question_id, $pdo) {
    $options = [];
    $stmt = $pdo->prepare("SELECT option_value, option_label FROM question_options WHERE question_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$question_id]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $options;
}

// Function to validate select field answer
function validateSelectAnswer($value, $question_id, $pdo) {
    if (empty($value)) {
        return "Please select an option.";
    }
    
    // Get valid options from database
    $options = getSelectOptions($question_id, $pdo);
    $valid_values = [];
    foreach ($options as $option) {
        $valid_values[] = strtolower($option['option_value']);
    }
    
    if (!in_array(strtolower($value), $valid_values)) {
        return "Please select a valid option from the list.";
    }
    
    return null;
}

// Function to detect if question is asking for a date
function isDateQuestion($validation_rules) {
    if (empty($validation_rules)) {
        return false;
    }
    
    $rules = json_decode($validation_rules, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }
    
    return isset($rules['date_format']);
}

// Function to get context data for dependent validations
function getContextData($p_num, $collected_info, $question_data, $current_q_id) {
    $context = [];
    
    if (isset($collected_info["applicant_$p_num"]['answers'])) {
        foreach ($collected_info["applicant_$p_num"]['answers'] as $q_id => $answer) {
            if ($q_id != $current_q_id && isset($question_data[$q_id])) {
                $context[$question_data[$q_id]['field_key']] = $answer;
                $context[$question_data[$q_id]['label']] = $answer;
            }
        }
    }
    
    return $context;
}

// Phone number validation by country
function validatePhoneNumber($phone, $country_name) {
    if (empty($phone)) {
        return "Phone number is required.";
    }
    
    $cleaned_phone = preg_replace('/[^0-9\+]/', '', $phone);
    $country_lower = strtolower($country_name);
    
    if (strpos($country_lower, 'india') !== false) {
        if (!preg_match('/^[6-9][0-9]{9}$/', $cleaned_phone)) {
            return "Indian phone number must be 10 digits starting with 6, 7, 8, or 9.";
        }
    } elseif (strpos($country_lower, 'usa') !== false || strpos($country_lower, 'united states') !== false) {
        if (!preg_match('/^[0-9]{10}$/', $cleaned_phone)) {
            return "US phone number must be 10 digits.";
        }
    } elseif (strpos($country_lower, 'uk') !== false || strpos($country_lower, 'united kingdom') !== false) {
        if (!preg_match('/^(07[0-9]{9}|447[0-9]{9})$/', $cleaned_phone)) {
            return "UK phone number must start with 07 or +447 and be 10-11 digits.";
        }
    } elseif (strpos($country_lower, 'china') !== false) {
        if (!preg_match('/^1[0-9]{10}$/', $cleaned_phone)) {
            return "Chinese phone number must be 11 digits starting with 1.";
        }
    } elseif (strpos($country_lower, 'germany') !== false) {
        if (!preg_match('/^[0-9]{10,13}$/', $cleaned_phone)) {
            return "German phone number must be 10-13 digits.";
        }
    } elseif (strpos($country_lower, 'france') !== false) {
        if (!preg_match('/^[0-9]{9}$/', $cleaned_phone)) {
            return "French phone number must be 9 digits.";
        }
    } elseif (strpos($country_lower, 'thailand') !== false) {
        if (!preg_match('/^[0-9]{9,10}$/', $cleaned_phone)) {
            return "Thai phone number must be 9-10 digits.";
        }
    } else {
        if (!preg_match('/^\+?[0-9]{8,15}$/', $cleaned_phone)) {
            return "Phone number must be 8-15 digits, may start with +.";
        }
    }
    
    return null;
}

if (isset($_GET['ajax'])) {
    $msg = htmlspecialchars(trim($_POST['message'] ?? ''));
    $response = "";
    $img_path = "";
    $progress = 0;

    // Check if this is a select field selection
    $is_select_selection = isset($_POST['select_value']) && $_POST['select_value'] !== '';
    if ($is_select_selection) {
        $msg = $_POST['select_value'];
    }

    // 1. Handle File Uploads with validation
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $home_dir = dirname($_SERVER['DOCUMENT_ROOT']); 
        $base_gov_id = $home_dir . '/gov_id/';

        if (!isset($_SESSION['order_folder_name'])) {
            $_SESSION['order_folder_name'] = 'TMP_' . time() . '_' . uniqid();
        }

        $p_num = $_SESSION['current_person_num'] ?? 1;
        $sub_path = date('Y/m/d') . '/' . $_SESSION['order_folder_name'] . '/applicant_' . $p_num;
        $full_dir = $base_gov_id . $sub_path . '/';
        
        if (!is_dir($full_dir)) mkdir($full_dir, 0775, true);

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = 'file_' . time() . '_' . uniqid() . '.' . $ext;
        $target = $full_dir . $filename;
        
        // Get validation rules for current question if it's a file field
        $upload_errors = [];
        if ($_SESSION['step'] === 'details' && isset($_SESSION['db_questions'][$_SESSION['q_idx']])) {
            $current_q = $_SESSION['db_questions'][$_SESSION['q_idx']];
            if ($current_q['field_type'] === 'file') {
                $validation_rules = $current_q['validation_rules'] ?? '';
                $upload_errors = validateFileUpload($_FILES['image'], $validation_rules, $current_q['field_key'] ?? '');
            }
        }
        
        if (empty($upload_errors)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $img_path = '/fetch_file.php?path=' . urlencode($sub_path . '/' . $filename);
            }
        } else {
            $response = implode("\n", $upload_errors);
        }
    }

    // 2. Main Logic
    if ($msg !== '' || $img_path !== '' || $is_select_selection) {
        $p_num = $_SESSION['current_person_num'] ?? 1;
        
        if ($img_path !== '') {
            $is_pdf = (strpos(strtolower($img_path), 'pdf') !== false);
            $_SESSION['messages'][] = [
                'role' => 'user', 
                'text' => $is_pdf ? "Uploaded PDF" : "Uploaded Image", 
                'img' => $img_path, 
                'is_pdf' => $is_pdf
            ];
        } else if ($msg !== '') {
            if ($is_select_selection) {
                $_SESSION['messages'][] = ['role' => 'user', 'text' => "Selected: " . ucfirst($msg)];
            } else {
                $_SESSION['messages'][] = ['role' => 'user', 'text' => $msg];
            }
        }

        switch ($_SESSION['step']) {
            case 'country':
                $stmt = $pdo->prepare("SELECT id, country_name FROM countries WHERE country_name LIKE ? AND is_active = 1 LIMIT 1");
                $stmt->execute(["%$msg%"]);
                $country = $stmt->fetch();
                if ($country) {
                    $_SESSION['country_id'] = $country['id'];
                    $_SESSION['country_name'] = $country['country_name'];
                    
                    // Fetch questions with validation rules
                    $q_stmt = $pdo->prepare("SELECT id, label, field_type, validation_rules, field_key FROM country_questions WHERE country_id = ? ORDER BY sort_order ASC");
                    $q_stmt->execute([$country['id']]);
                    $_SESSION['db_questions'] = $q_stmt->fetchAll();
                    
                    // Store question data for validation lookups
                    $_SESSION['question_data'] = [];
                    foreach ($_SESSION['db_questions'] as $q) {
                        $_SESSION['question_data'][$q['id']] = [
                            'label' => $q['label'],
                            'field_key' => $q['field_key'],
                            'field_type' => $q['field_type'],
                            'validation_rules' => $q['validation_rules']
                        ];
                        
                        // If it's a select field, fetch options now
                        if ($q['field_type'] === 'select') {
                            $_SESSION['question_data'][$q['id']]['options'] = getSelectOptions($q['id'], $pdo);
                        }
                    }
                    
                    $_SESSION['step'] = 'how_many';
                    $response = "Selected: **" . trim($country['country_name']) . "**. How many applicants?";
                } else { 
                    $response = "Sorry we currently don't support " . $msg . ". Please try another Country."; 
                }
                break;

            case 'how_many':
                if (is_numeric($msg) && (int)$msg > 0 && (int)$msg <= 20) {
                    $_SESSION['total_people'] = (int)$msg;
                    $_SESSION['current_person_num'] = 1; 
                    $_SESSION['q_idx'] = 0; 
                    $_SESSION['step'] = 'details';
                    
                    // Get first question
                    $first_q = $_SESSION['db_questions'][0];
                    $first_q_id = $first_q['id'];
                    $first_q_label = $first_q['label'];
                    $first_field_type = $first_q['field_type'];
                    
                    // Check if first question is select field
                    if ($first_field_type === 'select' && isset($_SESSION['question_data'][$first_q_id]['options'])) {
                        $options = $_SESSION['question_data'][$first_q_id]['options'];
                        $response = "json_select:" . $first_q_id . ":Applicant #1: **" . trim($first_q_label) . "**?";
                    } else {
                        $response = "Applicant #1. **" . trim($first_q_label) . "**?";
                    }
                } else { 
                    $response = "Please enter a valid number between 1 and 20."; 
                }
                break;

            case 'details':
                $questions = $_SESSION['db_questions'];
                $current_q = $questions[$_SESSION['q_idx']];
                $current_q_id = $current_q['id'];
                $current_q_label = $current_q['label'];
                $current_field_type = $current_q['field_type'];
                $validation_rules = $current_q['validation_rules'] ?? '';
                $field_key = $current_q['field_key'] ?? '';

                if ($current_field_type === 'file' && !$img_path) {
                    $response = "I need a file for: **" . trim($current_q_label) . "**. Please use the 📎 icon.";
                } else {
                    $validation_errors = [];
                    
                    if ($img_path === '') {
                        // If it's a select field, validate against options
                        if ($current_field_type === 'select') {
                            $select_error = validateSelectAnswer($msg, $current_q_id, $pdo);
                            if ($select_error) {
                                $validation_errors[] = $select_error;
                            }
                        }
                        
                        // Apply database validation rules (for text, date, etc.)
                        if (!empty($validation_rules)) {
                            $context_data = getContextData(
                                $p_num, 
                                $_SESSION['collected_info'], 
                                $_SESSION['question_data'],
                                $current_q_id
                            );
                            
                            $db_errors = applyValidationRules(
                                $msg, 
                                $validation_rules, 
                                $current_q_label,
                                $field_key,
                                $context_data,
                                $_SESSION['country_name'] ?? ''
                            );
                            
                            if (!empty($db_errors)) {
                                $validation_errors = array_merge($validation_errors, $db_errors);
                            }
                        }
                    }
                    
                    if (!empty($validation_errors)) {
                        $response = implode("\n", $validation_errors);
                    } else {
                        // Store the answer
                        $_SESSION['collected_info']["applicant_$p_num"]['answers'][$current_q_id] = $img_path ?: $msg;
                        $_SESSION['q_idx']++;

                        if ($_SESSION['q_idx'] < count($questions)) {
                            $next_q = $questions[$_SESSION['q_idx']];
                            $next_q_id = $next_q['id'];
                            $next_q_label = $next_q['label'];
                            $next_field_type = $next_q['field_type'];
                            
                            // Check if next question is select field
                            if ($next_field_type === 'select') {
                                $response = "json_select:" . $next_q_id . ":Applicant #$p_num: **" . trim($next_q_label) . "**?";
                            } else {
                                $response = "Next for Applicant #$p_num: **" . trim($next_q_label) . "**?";
                            }
                        } else {
                            $_SESSION['step'] = 'applicant_email';
                            $response = "Done with documents for Applicant #$p_num. What is **their email address**?";
                        }
                    }
                }
                break;

            case 'applicant_email':
                // Email validation
                if (!filter_var($msg, FILTER_VALIDATE_EMAIL)) {
                    $response = "Please enter a valid email address for Applicant #$p_num.";
                } else {
                    $_SESSION['collected_info']["applicant_$p_num"]['email'] = $msg;
                    $_SESSION['step'] = 'applicant_phone';
                    $response = "What is the **phone number** for Applicant #$p_num?";
                }
                break;

            case 'applicant_phone':
                // Phone validation based on country
                if (isset($_SESSION['country_name'])) {
                    $validation_error = validatePhoneNumber($msg, $_SESSION['country_name']);
                } else {
                    $validation_error = "Please enter a valid phone number for Applicant #$p_num.";
                }
                
                if ($validation_error) {
                    $response = $validation_error;
                } else {
                    $_SESSION['collected_info']["applicant_$p_num"]['phone'] = $msg;
                    if ($_SESSION['current_person_num'] < $_SESSION['total_people']) {
                        $_SESSION['current_person_num']++;
                        $_SESSION['q_idx'] = 0;
                        $_SESSION['step'] = 'details';
                        $p = $_SESSION['current_person_num'];
                        
                        $first_q = $_SESSION['db_questions'][0];
                        $first_q_id = $first_q['id'];
                        $first_q_label = $first_q['label'];
                        $first_field_type = $first_q['field_type'];
                        
                        // Check if first question is select field
                        if ($first_field_type === 'select') {
                            $response = "json_select:" . $first_q_id . ":Next: Applicant #$p. **" . trim($first_q_label) . "**?";
                        } else {
                            $response = "Next: Applicant #$p. **" . trim($first_q_label) . "**?";
                        }
                    } else {
                        $_SESSION['step'] = 'order_email';
                        $response = "All applicant details captured. Now, please provide the **Primary Contact Email** for this order.";
                    }
                }
                break;

            case 'order_email':
                if (!filter_var($msg, FILTER_VALIDATE_EMAIL)) {
                    $response = "Please enter a valid email address for the primary contact.";
                } else {
                    $_SESSION['order_contact_email'] = $msg;
                    $_SESSION['step'] = 'order_phone';
                    $response = "Finally, what is the **Primary Contact Phone Number** for the order?";
                }
                break;
                
            case 'order_phone':
                // Phone validation for order contact based on country
                if (isset($_SESSION['country_name'])) {
                    $validation_error = validatePhoneNumber($msg, $_SESSION['country_name']);
                } else {
                    $validation_error = "Please enter a valid phone number for the primary contact.";
                }
                
                if ($validation_error) {
                    $response = $validation_error;
                } else {
                    try {
                        $pdo->beginTransaction();
                        
                        // 1. Create the Order Entry
                        $stmt = $pdo->prepare("INSERT INTO visa_orders (country_id, email, phone) VALUES (?, ?, ?)");
                        $stmt->execute([$_SESSION['country_id'], $_SESSION['order_contact_email'], $msg]);
                        $order_id = $pdo->lastInsertId();
                
                        // 2. Rename the Physical Folder
                        $home_dir = dirname($_SERVER['DOCUMENT_ROOT']); 
                        $base_path = $home_dir . '/gov_id/' . date('Y/m/d') . '/';
                        
                        $old_folder_name = $_SESSION['order_folder_name'];
                        $new_folder_name = "order_" . $order_id;
                        
                        if (is_dir($base_path . $old_folder_name)) {
                            rename($base_path . $old_folder_name, $base_path . $new_folder_name);
                        }
                
                        // 3. Update File Paths in Database
                        for ($i = 1; $i <= $_SESSION['total_people']; $i++) {
                            $app_data = $_SESSION['collected_info']["applicant_$i"];
                            
                            // Insert applicant
                            $stmt = $pdo->prepare("INSERT INTO applicants (order_id, applicant_no, applicant_email, applicant_phone, visa_status) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$order_id, $i, $app_data['email'], $app_data['phone'], 'submitted']);
                            $app_id = $pdo->lastInsertId();
                
                            foreach ($app_data['answers'] as $q_id => $val) {
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
                        $_SESSION['current_order_id'] = $order_id;
                    } catch (Exception $e) { 
                        $pdo->rollBack(); 
                        $response = "Error: " . $e->getMessage(); 
                    }
                }
                break;
        }
        
        if ($response) {
            $_SESSION['messages'][] = ['role' => 'bot', 'text' => $response];
        }
    }

    $allow_upload = false;
    $step_label = "";
    $progress = 0;
    $show_date_calendar = false;
    $show_select_dropdown = false;
    $select_options = [];

    switch ($_SESSION['step']) {
        case 'country':
            $progress = 0;
            $step_label = "Country Selection";
            break;
            
        case 'how_many':
            $progress = 10;
            $step_label = "Applicant Count";
            break;
            
        case 'details':
            if (isset($_SESSION['db_questions'][$_SESSION['q_idx']])) {
                $current_q = $_SESSION['db_questions'][$_SESSION['q_idx']];
                $allow_upload = ($current_q['field_type'] === 'file');
                $total_questions = count($_SESSION['db_questions']);
                $progress = round((($_SESSION['q_idx']) / $total_questions) * 70 + 10);
                $step_label = "Document " . ($_SESSION['q_idx'] + 1) . " of " . $total_questions;
                
                // Check if current question is a date question
                $validation_rules = $current_q['validation_rules'] ?? '';
                if (isDateQuestion($validation_rules)) {
                    $show_date_calendar = true;
                }
                
                // Check if current question is a select field
                if ($current_q['field_type'] === 'select') {
                    $show_select_dropdown = true;
                    // Get options for select field
                    if (isset($_SESSION['question_data'][$current_q['id']]['options'])) {
                        $select_options = $_SESSION['question_data'][$current_q['id']]['options'];
                    }
                }
            }
            break;
            
        case 'applicant_email':
            $progress = 85;
            $step_label = "Applicant #" . ($_SESSION['current_person_num'] ?? 1) . " Details";
            break;
            
        case 'applicant_phone':
            $progress = 90;
            $step_label = "Applicant #" . ($_SESSION['current_person_num'] ?? 1) . " Details";
            break;
            
        case 'order_email':
            $progress = 95;
            $step_label = "Order Contact";
            break;
            
        case 'order_phone':
            $progress = 99;
            $step_label = "Order Contact";
            break;
            
        case 'finish':
            $progress = 100;
            $step_label = "Complete";
            break;
    }

    $step_count = "Step ";
    switch ($_SESSION['step']) {
        case 'country': $step_count .= "1/8"; break;
        case 'how_many': $step_count .= "2/8"; break;
        case 'details': $step_count .= "3/8"; break;
        case 'applicant_email': $step_count .= "4/8"; break;
        case 'applicant_phone': $step_count .= "5/8"; break;
        case 'order_email': $step_count .= "6/8"; break;
        case 'order_phone': $step_count .= "7/8"; break;
        case 'finish': $step_count .= "8/8"; break;
        default: $step_count .= "1/8";
    }

    echo json_encode([
        'text' => formatBold($response), 
        'is_finished' => ($_SESSION['step'] === 'finish'), 
        'progress' => $progress, 
        'allow_upload' => $allow_upload, 
        'img_path' => $img_path,
        'step_label' => $step_label,
        'step_count' => $step_count,
        'current_person' => $_SESSION['current_person_num'] ?? 1,
        'total_people' => $_SESSION['total_people'] ?? 1,
        'show_date_calendar' => $show_date_calendar,
        'show_select_dropdown' => $show_select_dropdown,
        'select_options' => $select_options,
        'current_question_id' => isset($_SESSION['db_questions'][$_SESSION['q_idx']]['id']) ? $_SESSION['db_questions'][$_SESSION['q_idx']]['id'] : null,
        'order_id' => $_SESSION['current_order_id'] ?? null,
    ]);
    exit;
}

if (isset($_POST['reset'])) { 
    session_unset(); 
    session_destroy(); 
    header('Location: '.$_SERVER['PHP_SELF']); 
    exit; 
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
            padding: 10px 16px; 
            border-radius: var(--border-radius);
            position: relative;
            box-shadow: var(--box-shadow);
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

        .gender-dropdown {
            margin-top: 12px;
            max-width: 300px;
        }

        .gender-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .gender-option {
            padding: 12px 16px;
            background: var(--light);
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .gender-option:hover {
            background: var(--primary-light);
            color: white;
            border-color: var(--primary-light);
            transform: translateX(5px);
        }

        .gender-option.selected {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .gender-icon {
            font-size: 16px;
        }

        .date-picker-container {
            margin-top: 12px;
            max-width: 300px;
        }
        
        .date-input-wrapper {
            position: relative;
        }
        
        .date-input {
            width: 100%;
            padding: 12px 16px;
            padding-right: 40px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            background: var(--light);
            color: var(--dark);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: var(--transition);
        }
        
        .date-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .calendar-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            cursor: pointer;
        }
        
        .calendar-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
            backdrop-filter: blur(5px);
        }
        
        .calendar {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 20px;
            width: 90%;
            max-width: 320px;
            box-shadow: var(--box-shadow-lg);
            border: 1px solid var(--gray-light);
        }
        
        .calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .calendar-title {
            font-weight: 600;
            color: var(--dark);
            font-size: 16px;
        }
        
        .calendar-nav {
            display: flex;
            gap: 10px;
        }
        
        .nav-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            color: var(--dark);
        }
        
        .nav-btn:hover {
            background: var(--primary);
            color: white;
        }
        
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .weekday {
            text-align: center;
            font-weight: 600;
            font-size: 12px;
            color: var(--gray);
            padding: 5px;
        }
        
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        
        .day {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
            background: transparent;
            border: none;
            color: var(--dark);
        }
        
        .day:hover:not(.empty):not(.selected) {
            background: var(--gray-light);
        }
        
        .day.selected {
            background: var(--primary);
            color: white;
        }
        
        .day.today {
            border: 2px solid var(--success);
        }
        
        .day.empty {
            cursor: default;
        }
        
        .day.other-month {
            color: var(--gray);
            opacity: 0.5;
        }
        
        .calendar-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 10px;
        }
        
        .calendar-btn {
            flex: 1;
            padding: 10px;
            border-radius: var(--border-radius-sm);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            font-family: 'Inter', sans-serif;
        }
        
        .calendar-btn.today {
            background: var(--gray-light);
            color: var(--dark);
        }
        
        .calendar-btn.today:hover {
            background: var(--gray);
            color: white;
        }
        
        .calendar-btn.close {
            background: var(--light);
            border: 1px solid var(--gray-light);
            color: var(--dark);
        }
        
        .calendar-btn.close:hover {
            background: var(--gray-light);
        }
        
        .date-format-hint {
            font-size: 12px;
            color: var(--gray);
            margin-top: 8px;
            text-align: center;
        }

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

        .typing-indicator {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background: var(--light);
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            max-width: 200px;
            box-shadow: var(--box-shadow);
            border: 1px solid var(--gray-light);
            animation: fadeIn 0.3s ease-out;
        }

        .loading-dots {
            display: flex;
            gap: 4px;
        }

        .loading-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            animation: dotPulse 1.4s ease-in-out infinite;
        }

        .loading-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        .typing-indicator span {
            font-size: 14px;
            color: var(--gray);
            font-weight: 500;
        }

        /* Summary Popup Styles */
        .summary-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 4000;
            backdrop-filter: blur(10px);
        }

        .summary-container {
            background: var(--light);
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            box-shadow: var(--box-shadow-lg);
            border: 1px solid var(--gray-light);
            overflow: hidden;
            animation: modalSlide 0.3s ease-out;
        }

        .summary-header {
            padding: 24px 30px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .summary-header h3 {
            font-size: 24px;
            font-weight: 700;
        }

        .summary-close {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .summary-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .summary-content {
            padding: 30px;
            overflow-y: auto;
            max-height: 60vh;
        }

        .summary-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-light);
        }

        .summary-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .summary-section h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-section h4 i {
            font-size: 16px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .summary-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .summary-label {
            font-size: 13px;
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 16px;
            color: var(--dark);
            font-weight: 500;
            word-break: break-word;
            padding: 8px 12px;
            background: var(--gray-light);
            border-radius: var(--border-radius-sm);
            border-left: 3px solid var(--primary);
        }

        .summary-file {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--gray-light);
            border-radius: var(--border-radius-sm);
            margin-bottom: 10px;
            border-left: 3px solid var(--success);
        }

        .summary-file i {
            font-size: 20px;
            color: var(--danger);
        }

        .file-info {
            flex: 1;
        }

        .file-info h5 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--dark);
        }

        .file-info p {
            font-size: 12px;
            color: var(--gray);
        }

        .file-view {
            color: var(--primary);
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-view:hover {
            text-decoration: underline;
        }

        .summary-footer {
            padding: 20px 30px;
            background: var(--gray-light);
            border-top: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-actions {
            display: flex;
            gap: 12px;
        }

        .summary-btn {
            padding: 12px 24px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-btn.close {
            background: var(--light);
            border: 1px solid var(--gray-light);
            color: var(--dark);
        }

        .summary-btn.close:hover {
            background: var(--gray-light);
        }

        .summary-btn.download {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .summary-btn.download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: var(--gray-light);
        }

        .select-dropdown {
            margin-top: 12px;
            max-width: 300px;
        }

        .select-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .select-option {
            padding: 12px 16px;
            background: var(--light);
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .select-option:hover {
            background: var(--primary-light);
            color: white;
            border-color: var(--primary-light);
            transform: translateX(5px);
        }

        .select-option.selected {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .select-icon {
            font-size: 16px;
        }

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

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(2deg); }
        }

        @keyframes progressShimmer {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes headerShimmer {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes avatarSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes buttonSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes countPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes progressPulse {
            0% { box-shadow: 0 0 0 0 rgba(76, 201, 240, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(76, 201, 240, 0); }
            100% { box-shadow: 0 0 0 0 rgba(76, 201, 240, 0); }
        }

        @keyframes pdfPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes previewPulse {
            0%, 100% { border-color: var(--primary-light); }
            50% { border-color: var(--success); }
        }

        @keyframes placeholderPulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        @keyframes dangerPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(247, 37, 133, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(247, 37, 133, 0); }
            100% { box-shadow: 0 0 0 0 rgba(247, 37, 133, 0); }
        }

        @keyframes successShimmer {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes iconGlow {
            0%, 100% { filter: drop-shadow(0 0 5px var(--success)); }
            50% { filter: drop-shadow(0 0 15px var(--success)); }
        }

        @keyframes orderIdGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(67, 97, 238, 0.3); }
            50% { box-shadow: 0 0 30px rgba(67, 97, 238, 0.6); }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px) scale(0.95); }
            to { opacity: 1; transform: translateX(0) scale(1); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px) scale(0.95); }
            to { opacity: 1; transform: translateX(0) scale(1); }
        }

        @keyframes shimmerBorder {
            0% { transform: rotate(45deg) translateX(-100%); }
            100% { transform: rotate(45deg) translateX(100%); }
        }

        @keyframes dotPulse {
            0%, 60%, 100% { transform: scale(1); opacity: 1; }
            30% { transform: scale(1.2); opacity: 0.7; }
        }

        @media (max-width: 1024px) {
            .sidebar { width: 280px; }
            .message-content { max-width: 85%; }
        }

        @media (max-width: 768px) {
            .app-container { flex-direction: column; }
            .sidebar { width: 100%; height: auto; padding: 20px; }
            .logo { margin-bottom: 20px; }
            .progress-section { margin-bottom: 20px; }
            .chat-header { padding: 15px 20px; }
            .chat-container { padding: 20px; }
            .input-section { padding: 15px 20px; }
            #previewTray { left: 20px; right: 20px; bottom: 90px; }
        }
    </style>
</head>
<body id="body">
<div class="app-container">

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
            <a href="edit.php" class="action-btn link-btn" style="text-decoration: none">
                <i class="fas fa-edit"></i>
                Edit Existing Order
            </a>
            <button class="action-btn danger" onclick="showSummary()">
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
            <?php foreach ($_SESSION['messages'] as $m): ?>
                <div class="message-row <?php echo $m['role']; ?>">
                    <?php if ($m['role'] === 'bot'): ?>
                        <div class="message-avatar">AI</div>
                    <?php endif; ?>
                    <div class="message-content">
                        <div class="message-text"><?php echo formatBold($m['text']); ?></div>
                        
                        <?php if (isset($m['img']) && $m['img']): ?>
                            <div class="message-attachment">
                                <?php if (isset($m['is_pdf']) && $m['is_pdf']): ?>
                                    <div class="pdf-card" onclick="openLightbox('<?php echo $m['img']; ?>', true)">
                                        <i class="fas fa-file-pdf pdf-icon"></i>
                                        <div class="pdf-info">
                                            <h4>Document.pdf</h4>
                                            <p>Click to view</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo $m['img']; ?>" class="msg-img" onclick="openLightbox(this.src)">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-time"><?php echo date('H:i'); ?></div>
                    </div>
                    <?php if ($m['role'] === 'user'): ?>
                        <div class="message-avatar">U</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
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

<div class="typing-indicator" id="typingIndicator" style="display: none;">
    <div class="loading-dots">
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
    </div>
    <span>Assistant is typing...</span>
</div>

<div id="lightbox" onclick="closeLightbox()">
    <div class="lightbox-close" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
    </div>
    <div id="lbContainer" onclick="event.stopPropagation()">
        <img id="lbImg">
        <iframe id="lbPdf"></iframe>
    </div>
</div>

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

<div id="summaryPopup" class="summary-popup">
    <div class="summary-container">
        <div class="summary-header">
            <h3>Application Summary</h3>
            <div class="summary-close" onclick="closeSummaryPopup()">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="summary-content" id="summaryContent">
            <!-- Dynamic content will be inserted here -->
        </div>
        <div class="summary-footer">
            <div class="summary-actions">
                <button class="summary-btn close" onclick="closeSummaryPopup()">
                    <i class="fas fa-times"></i> Close
                </button>
                <button class="summary-btn download" onclick="downloadSummaryAsPDF()">
                    <i class="fas fa-download"></i> Download as PDF
                </button>
            </div>
            <div class="summary-info">
                <small>Generated on <span id="summaryDate">...</span></small>
            </div>
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
    const typingIndicator = document.getElementById('typingIndicator');
    
    let isProcessing = false;
    let currentOrderId = null;
    let currentSelectSelection = null;
    let currentQuestionId = null;

    // Lightbox Logic
    function openLightbox(src, isPdf = false) { 
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
                    `${(file.size / 1024).toFixed(2)} KB • ${isPdf ? 'PDF Document' : 'Image'}`;
                
                document.getElementById('previewTray').style.display = 'flex'; 
            };
            reader.readAsDataURL(file);
        }
    }
    
    function clearPreview() { 
        fileInput.value = ""; 
        document.getElementById('previewTray').style.display = 'none'; 
    }

    // Select field selection function
    function selectOption(questionId, value, label) {
        currentSelectSelection = value;
        currentQuestionId = questionId;
        
        // Highlight selected option
        document.querySelectorAll('.select-option').forEach(option => {
            option.classList.remove('selected');
        });
        event.target.closest('.select-option').classList.add('selected');
        
        // Enable send button and auto-submit after a brief delay
        sendBtn.disabled = false;
        
        // Update input field
        msgInput.value = label || value;
        
        // Auto-submit after 500ms
        setTimeout(() => {
            if (!isProcessing) {
                sendMessage();
            }
        }, 500);
    }

    // Create select dropdown
    function createSelectDropdown(questionId, options) {
        const selectDropdown = document.createElement('div');
        selectDropdown.className = 'select-dropdown';
        
        let optionsHtml = '<div class="select-options">';
        options.forEach(option => {
            const value = option.option_value;
            const label = option.option_label || option.option_value;
            optionsHtml += `
                <div class="select-option" onclick="selectOption('${questionId}', '${value}', '${label}')">
                    <span>${label}</span>
                    <i class="fas fa-check select-icon"></i>
                </div>
            `;
        });
        optionsHtml += '</div>';
        
        selectDropdown.innerHTML = optionsHtml;
        
        return selectDropdown;
    }

    // Calendar functionality
    let calendarPopup = null;
    let currentDateInput = null;
    let currentCalendar = null;

    function createCalendarPopup() {
        const popup = document.createElement('div');
        popup.className = 'calendar-popup';
        popup.id = 'calendarPopup';
        
        popup.innerHTML = `
            <div class="calendar" onclick="event.stopPropagation()">
                <div class="calendar-header">
                    <div class="calendar-title" id="calendarTitle">Select Date</div>
                    <div class="calendar-nav">
                        <button class="nav-btn" onclick="changeCalendarMonth(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="nav-btn" onclick="changeCalendarMonth(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="calendar-weekdays">
                    <div class="weekday">Sun</div>
                    <div class="weekday">Mon</div>
                    <div class="weekday">Tue</div>
                    <div class="weekday">Wed</div>
                    <div class="weekday">Thu</div>
                    <div class="weekday">Fri</div>
                    <div class="weekday">Sat</div>
                </div>
                <div class="calendar-days" id="calendarDays"></div>
                <div class="date-format-hint">Format: DD-MM-YYYY</div>
                <div class="calendar-actions">
                    <button class="calendar-btn today" onclick="selectToday()">Today</button>
                    <button class="calendar-btn close" onclick="closeCalendar()">Close</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(popup);
        return popup;
    }

    function showCalendar(inputElement) {
        if (!calendarPopup) {
            calendarPopup = createCalendarPopup();
        }
        
        currentDateInput = inputElement;
        currentCalendar = {
            year: new Date().getFullYear(),
            month: new Date().getMonth()
        };
        
        renderCalendar();
        calendarPopup.style.display = 'flex';
    }

    function closeCalendar() {
        if (calendarPopup) {
            calendarPopup.style.display = 'none';
        }
    }

    function renderCalendar() {
        if (!calendarPopup || !currentCalendar) return;
        
        const { year, month } = currentCalendar;
        const calendarDays = document.getElementById('calendarDays');
        const calendarTitle = document.getElementById('calendarTitle');
        
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        calendarTitle.textContent = `${monthNames[month]} ${year}`;
        
        const firstDay = new Date(year, month, 1);
        const startingDay = firstDay.getDay();
        
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const today = new Date();
        const todayStr = `${today.getDate().toString().padStart(2, '0')}-${(today.getMonth() + 1).toString().padStart(2, '0')}-${today.getFullYear()}`;
        
        calendarDays.innerHTML = '';
        
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('button');
            emptyDay.className = 'day empty other-month';
            emptyDay.disabled = true;
            calendarDays.appendChild(emptyDay);
        }
        
        for (let day = 1; day <= daysInMonth; day++) {
            const dayButton = document.createElement('button');
            dayButton.className = 'day';
            dayButton.textContent = day;
            
            const dateStr = `${day.toString().padStart(2, '0')}-${(month + 1).toString().padStart(2, '0')}-${year}`;
            
            if (dateStr === todayStr) {
                dayButton.classList.add('today');
            }
            
            if (currentDateInput && currentDateInput.value === dateStr) {
                dayButton.classList.add('selected');
            }
            
            dayButton.onclick = () => selectDate(day, month + 1, year);
            calendarDays.appendChild(dayButton);
        }
    }

    function changeCalendarMonth(delta) {
        currentCalendar.month += delta;
        
        if (currentCalendar.month < 0) {
            currentCalendar.month = 11;
            currentCalendar.year--;
        } else if (currentCalendar.month > 11) {
            currentCalendar.month = 0;
            currentCalendar.year++;
        }
        
        renderCalendar();
    }

    function selectDate(day, month, year) {
        const dateStr = `${day.toString().padStart(2, '0')}-${month.toString().padStart(2, '0')}-${year}`;
        
        if (currentDateInput) {
            currentDateInput.value = dateStr;
            
            const event = new Event('input', { bubbles: true });
            currentDateInput.dispatchEvent(event);
            
            currentDateInput.focus();
        }
        
        closeCalendar();
    }

    function selectToday() {
        const today = new Date();
        selectDate(today.getDate(), today.getMonth() + 1, today.getFullYear());
    }

    document.addEventListener('click', (e) => {
        if (calendarPopup && !calendarPopup.contains(e.target) && e.target !== currentDateInput) {
            closeCalendar();
        }
    });

    // Typing indicator
    function showTypingIndicator() {
        typingIndicator.style.display = 'flex';
        chat.appendChild(typingIndicator);
        chat.scrollTop = chat.scrollHeight;
    }

    function hideTypingIndicator() {
        typingIndicator.style.display = 'none';
    }

    // Update progress display
    function updateProgressDisplay(data) {
        if (pBar && data.progress !== undefined) {
            pBar.style.width = data.progress + '%';
        }
        
        if (progressPercent && data.progress !== undefined) {
            progressPercent.textContent = data.progress + '%';
        }
        
        if (stepLabel && data.step_label) {
            stepLabel.textContent = data.step_label;
        }
        
        if (stepCount && data.step_count) {
            stepCount.textContent = data.step_count;
        }
        
        if (applicantCount && data.current_person && data.total_people) {
            applicantCount.textContent = `Applicant ${data.current_person}/${data.total_people}`;
        }
    }

    // Message Sending function
    async function sendMessage() {
        const file = fileInput.files[0];
        let text = msgInput.value.trim();
        
        // Check if we have a select selection
        if (currentSelectSelection && text === '') {
            text = currentSelectSelection;
        }
        
        // Check if we're on a date input
        const dateInput = document.getElementById('dateInput');
        if (dateInput && dateInput.value.trim() !== '') {
            text = dateInput.value.trim();
        }
        
        if (isProcessing || (!text && !file)) return;
        
        // Show typing indicator
        showTypingIndicator();
        
        isProcessing = true;
        msgInput.disabled = true;
        sendBtn.disabled = true;

        const formData = new FormData();
        formData.append('message', text);
        if (file) formData.append('image', file);
        
        // Add select selection if available
        if (currentSelectSelection && currentQuestionId) {
            formData.append('select_value', currentSelectSelection);
        }

        // Add user message to UI immediately with file preview
        if (text || file) {
            const userRow = document.createElement('div');
            userRow.className = 'message-row user';
            
            let attachmentHtml = '';
            let messageText = text || '';
            
            // Format select message for display
            if (currentSelectSelection && text === currentSelectSelection) {
                messageText = `Selected: ${text}`;
            }
            
            if (file) {
                const isPdf = file.type === "application/pdf";
                const fileName = file.name;
                const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                
                // Create object URL for preview
                const objectUrl = URL.createObjectURL(file);
                
                if (isPdf) {
                    attachmentHtml = `
                        <div class="message-attachment">
                            <div class="pdf-card" onclick="openLightbox('${objectUrl}', true)">
                                <i class="fas fa-file-pdf pdf-icon"></i>
                                <div class="pdf-info">
                                    <h4>${fileName}</h4>
                                    <p>${fileSize} • PDF Document</p>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    attachmentHtml = `
                        <div class="message-attachment">
                            <img src="${objectUrl}" class="msg-img" onclick="openLightbox(this.src)">
                        </div>
                    `;
                }
                
                // If no text was entered, show "Uploaded file" as message
                if (!text) {
                    messageText = isPdf ? "Uploaded PDF document" : "Uploaded image";
                }
            }
            
            userRow.innerHTML = `
                <div class="message-content">
                    <div class="message-text">${messageText}</div>
                    ${attachmentHtml}
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
        currentSelectSelection = null;
        currentQuestionId = null;
        
        // Remove any existing select dropdown highlights
        document.querySelectorAll('.select-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Remove any date input
        const datePicker = document.querySelector('.date-picker-container');
        if (datePicker) {
            datePicker.remove();
        }
        
        chat.scrollTop = chat.scrollHeight;

        try {
            const response = await fetch('?ajax=1', { method: 'POST', body: formData });
            const data = await response.json();

            // Hide typing indicator
            hideTypingIndicator();

            // Update progress display
            updateProgressDisplay(data);

            // Store order ID if available
            if (data.order_id) {
                currentOrderId = data.order_id;
            }

            // Check if we need to show select dropdown
            if (data.text && data.text.startsWith('json_select:') && data.show_select_dropdown) {
                // Extract question ID and message
                const parts = data.text.split(':');
                const questionId = parts[1];
                const actualMessage = parts.slice(2).join(':');
                
                // Add bot response to UI
                const botRow = document.createElement('div');
                botRow.className = 'message-row bot';
                
                const formattedText = formatBold(actualMessage);
                
                botRow.innerHTML = `
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-text">${formattedText}</div>
                        <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                    </div>
                `;
                
                chat.appendChild(botRow);
                
                // Add select dropdown with options
                if (data.select_options && data.select_options.length > 0) {
                    const selectDropdown = createSelectDropdown(questionId, data.select_options);
                    botRow.querySelector('.message-content').appendChild(selectDropdown);
                    
                    // Update input placeholder
                    msgInput.placeholder = "Select an option above";
                    msgInput.disabled = true;
                    sendBtn.disabled = true;
                }
            } else {
                // Regular bot response
                const botRow = document.createElement('div');
                botRow.className = 'message-row bot';
                
                const formattedText = data.text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
                
                botRow.innerHTML = `
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-text">${formattedText}</div>
                        <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                    </div>
                `;

                // Add calendar for date questions
                if (data.show_date_calendar) {
                    const datePickerContainer = document.createElement('div');
                    datePickerContainer.className = 'date-picker-container';
                    
                    datePickerContainer.innerHTML = `
                        <div class="date-input-wrapper">
                            <input type="text" 
                                   class="date-input" 
                                   placeholder="DD-MM-YYYY"
                                   id="dateInput"
                                   onfocus="showCalendar(this)">
                            <i class="fas fa-calendar-alt calendar-icon" onclick="showCalendar(document.getElementById('dateInput'))"></i>
                        </div>
                        <div class="date-format-hint">Click the calendar icon or enter date as DD-MM-YYYY</div>
                    `;
                    
                    botRow.querySelector('.message-content').appendChild(datePickerContainer);
                    
                    // Focus on date input
                    setTimeout(() => {
                        const dateInputEl = document.getElementById('dateInput');
                        if (dateInputEl) {
                            dateInputEl.focus();
                        }
                    }, 100);
                }
                
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
                    fileInput.value = "";
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
            }

        } catch (error) {
            console.error("Error sending message:", error);
            
            // Hide typing indicator on error
            hideTypingIndicator();
            
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
            msgInput.placeholder = "Type your response here...";
            msgInput.focus();
            chat.scrollTop = chat.scrollHeight;
        }
    }

    // Format bold text function for JS
    function formatBold(text) {
        return text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
    }

    // Summary functions
    async function showSummary() {
        try {
            const response = await fetch('?get_summary=1');
            const data = await response.json();
            
            if (data.success) {
                const content = document.getElementById('summaryContent');
                let html = '';
                
                // Order Information
                if (data.order_info) {
                    html += `
                        <div class="summary-section">
                            <h4><i class="fas fa-info-circle"></i> Order Information</h4>
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <span class="summary-label">Order ID</span>
                                    <span class="summary-value">${data.order_info.id || 'N/A'}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Country</span>
                                    <span class="summary-value">${data.country_name || 'N/A'}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Primary Contact Email</span>
                                    <span class="summary-value">${data.order_info.email || 'N/A'}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Primary Contact Phone</span>
                                    <span class="summary-value">${data.order_info.phone || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Applicants Information
                Object.keys(data.data).forEach((applicantKey, index) => {
                    const applicant = data.data[applicantKey];
                    const applicantNum = index + 1;
                    
                    html += `
                        <div class="summary-section">
                            <h4><i class="fas fa-user"></i> Applicant #${applicantNum}</h4>
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <span class="summary-label">Email</span>
                                    <span class="summary-value">${applicant.email || 'N/A'}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Phone</span>
                                    <span class="summary-value">${applicant.phone || 'N/A'}</span>
                                </div>
                            </div>
                    `;
                    
                    // Answers and Files
                    if (applicant.answers) {
                        Object.keys(applicant.answers).forEach(qId => {
                            const answer = applicant.answers[qId];
                            const label = data.question_labels ? (data.question_labels[qId] || `Question ${qId}`) : `Question ${qId}`;
                            
                            // Check if it's a file (contains fetch_file.php)
                            if (answer.includes('fetch_file.php')) {
                                const fileName = getFileNameFromPath(answer);
                                html += `
                                    <div class="summary-file">
                                        <i class="fas fa-file-pdf"></i>
                                        <div class="file-info">
                                            <h5>${label}</h5>
                                            <p>Uploaded Document</p>
                                        </div>
                                        <a class="file-view" onclick="openLightbox('${answer}', ${answer.includes('.pdf')})">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                `;
                            } else {
                                html += `
                                    <div class="summary-item">
                                        <span class="summary-label">${label}</span>
                                        <span class="summary-value">${answer}</span>
                                    </div>
                                `;
                            }
                        });
                    }
                    
                    html += `</div>`;
                });
                
                content.innerHTML = html || '<div class="empty-state"><i class="fas fa-file-alt"></i><h3>No Application Data</h3><p>Please complete a visa application first to generate a summary.</p></div>';
                document.getElementById('summaryDate').textContent = new Date().toLocaleString();
                document.getElementById('summaryPopup').style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                // Store data for PDF download
                window.summaryData = data;
            } else {
                alert('No application data found. Please complete an application first.');
            }
        } catch (error) {
            console.error('Error fetching summary:', error);
            alert('Unable to fetch summary data. Please try again.');
        }
    }

    // Helper function to extract filename from path
    function getFileNameFromPath(path) {
        const parts = path.split('/');
        const filename = parts[parts.length - 1];
        const decodedFilename = decodeURIComponent(filename);
        return decodedFilename.split('?')[0];
    }

    // Function to close summary popup
    function closeSummaryPopup() {
        document.getElementById('summaryPopup').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Function to download summary as PDF
    function downloadSummaryAsPDF() {
        const summaryContent = document.getElementById('summaryContent');
        const data = window.summaryData || {};
        const country = data.country_name || 'Visa Application';
        const orderId = data.order_info ? data.order_info.id : '';
        
        // Create a temporary div for PDF generation
        const pdfContent = document.createElement('div');
        pdfContent.style.padding = '40px';
        pdfContent.style.background = 'white';
        pdfContent.style.color = '#333';
        
        // Add header
        pdfContent.innerHTML = `
            <h1 style="color: #4361ee; border-bottom: 2px solid #4361ee; padding-bottom: 10px; margin-bottom: 30px;">
                <i class="fas fa-passport" style="margin-right: 10px;"></i>
                ${country} Visa Application Summary
            </h1>
            ${orderId ? `<p><strong>Order ID:</strong> ${orderId}</p>` : ''}
            <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
            <hr style="margin: 30px 0;">
        `;
        
        // Clone and style the summary content
        const contentClone = summaryContent.cloneNode(true);
        
        // Apply PDF-specific styles
        contentClone.querySelectorAll('.summary-section').forEach(section => {
            section.style.marginBottom = '30px';
            section.style.paddingBottom = '20px';
            section.style.borderBottom = '1px solid #ddd';
        });
        
        contentClone.querySelectorAll('h4').forEach(h4 => {
            h4.style.color = '#4361ee';
            h4.style.marginBottom = '15px';
        });
        
        contentClone.querySelectorAll('.summary-item').forEach(item => {
            item.style.marginBottom = '15px';
        });
        
        contentClone.querySelectorAll('.summary-label').forEach(label => {
            label.style.color = '#666';
            label.style.fontSize = '12px';
            label.style.marginBottom = '5px';
        });
        
        contentClone.querySelectorAll('.summary-value').forEach(value => {
            value.style.background = '#f5f5f5';
            value.style.padding = '8px';
            value.style.borderRadius = '4px';
            value.style.borderLeft = '3px solid #4361ee';
        });
        
        contentClone.querySelectorAll('.summary-file').forEach(file => {
            file.style.background = '#f5f5f5';
            file.style.padding = '10px';
            file.style.borderRadius = '4px';
            file.style.marginBottom = '10px';
            file.style.borderLeft = '3px solid #4cc9f0';
        });
        
        // Remove action buttons and links from PDF
        contentClone.querySelectorAll('.file-view, .summary-actions').forEach(el => {
            el.remove();
        });
        
        pdfContent.appendChild(contentClone);
        
        // Generate PDF using html2pdf
        const options = {
            margin: 10,
            filename: `Visa_Application_Summary_${orderId || new Date().getTime()}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        // Use html2pdf library
        if (typeof html2pdf !== 'undefined') {
            html2pdf().from(pdfContent).set(options).save();
        } else {
            alert('PDF generation requires html2pdf library. Please include it in your project.');
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

    // Also handle enter key for date input
    document.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            const dateInput = document.getElementById('dateInput');
            if (dateInput && dateInput === document.activeElement) {
                e.preventDefault();
                sendMessage();
            }
        }
    });

    // Initialize
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
                
                // Set current order ID if available
                if (data.order_id) {
                    currentOrderId = data.order_id;
                }
            });
            
        msgInput.focus();
    });
</script>
</body>
</html>