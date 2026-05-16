<?php
session_start();
require 'db.php';

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

// Function to detect if question is asking for gender
function isGenderQuestion($question_label) {
    $label_lower = strtolower($question_label);
    $gender_keywords = ['gender', 'sex'];
    
    foreach ($gender_keywords as $keyword) {
        if (strpos($label_lower, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Function to validate gender selection
function validateGender($gender) {
    if (empty($gender)) {
        return "Please select a gender.";
    }
    
    $valid_genders = ['male', 'female', 'other'];
    if (!in_array(strtolower($gender), $valid_genders)) {
        return "Please select a valid gender (Male, Female, or Other).";
    }
    
    return null; // No error
}

// Improved date validation functions
function isValidDate($date, $format = 'd-m-Y') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Function to detect if question is asking for a date
function isDateQuestion($question_label) {
    $label_lower = strtolower($question_label);
    $date_keywords = ['date', 'dob', 'birth date', 'issue date', 'expiry date', 'expiration date', 
                     'valid until', 'valid till', 'arrival date', 'departure date', 'travel date',
                     'journey date', 'trip date', 'flight date'];
    
    // Check if it's NOT a flight number question (flight number has "flight" but not "date")
    if (strpos($label_lower, 'flight') !== false && strpos($label_lower, 'number') !== false) {
        return false;
    }
    
    // Check if it's NOT a place of birth question
    if (strpos($label_lower, 'place') !== false && strpos($label_lower, 'birth') !== false) {
        return false;
    }
    
    // Check if it's NOT a gender question
    if (isGenderQuestion($question_label)) {
        return false;
    }
    
    foreach ($date_keywords as $keyword) {
        if (strpos($label_lower, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Function to detect if question is asking for flight number (NOT date)
function isFlightNumberQuestion($question_label) {
    $label_lower = strtolower($question_label);
    $flight_number_keywords = ['flight number', 'flight no', 'flight #', 'airline flight', 'flight code'];
    
    foreach ($flight_number_keywords as $keyword) {
        if (strpos($label_lower, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Function to detect if question is asking for place of birth (NOT date)
function isPlaceOfBirthQuestion($question_label) {
    $label_lower = strtolower($question_label);
    $place_keywords = ['place of birth', 'birth place', 'born in', 'birth city', 'birth location',
                      'city of birth', 'town of birth', 'birth town', 'birth village'];
    
    foreach ($place_keywords as $keyword) {
        if (strpos($label_lower, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Function to detect if question is asking for phone number
function isPhoneQuestion($question_label) {
    $label_lower = strtolower($question_label);
    $phone_keywords = ['phone', 'mobile', 'contact number', 'telephone', 'cell', 'phone number'];
    
    foreach ($phone_keywords as $keyword) {
        if (strpos($label_lower, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Specific validation functions
function validatePassportDates($issue_date, $expiry_date) {
    if (empty($issue_date) || empty($expiry_date)) {
        return "Both issue date and expiry date are required.";
    }
    
    if (!isValidDate($issue_date) || !isValidDate($expiry_date)) {
        return "Invalid date format. Please use DD-MM-YYYY format (e.g., 31-12-2024).";
    }
    
    $issue = DateTime::createFromFormat('d-m-Y', $issue_date);
    $expiry = DateTime::createFromFormat('d-m-Y', $expiry_date);
    
    if ($expiry <= $issue) {
        return "Passport expiry date must be after the issue date.";
    }
    
    return null; // No error
}

function validateFlightDate($flight_date) {
    if (empty($flight_date)) {
        return "Flight date is required.";
    }
    
    if (!isValidDate($flight_date)) {
        return "Invalid date format. Please use DD-MM-YYYY format (e.g., 31-12-2024).";
    }
    
    $flight = DateTime::createFromFormat('d-m-Y', $flight_date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    $min_arrival_date = clone $today;
    $min_arrival_date->modify('+1 day');
    
    if ($flight <= $today) {
        return "Flight arrival date must be after today.";
    }
    
    if ($flight < $min_arrival_date) {
        return "Flight arrival date must be at least tomorrow (today + 1 day).";
    }
    
    return null; // No error
}

function validateDOB($dob) {
    if (empty($dob)) {
        return "Date of birth is required.";
    }
    
    if (!isValidDate($dob)) {
        return "Invalid date format. Please use DD-MM-YYYY format (e.g., 15-05-1990).";
    }
    
    $birth_date = DateTime::createFromFormat('d-m-Y', $dob);
    $today = new DateTime();
    
    // Check if date is in the future
    if ($birth_date > $today) {
        return "Date of birth cannot be in the future.";
    }
    
    // Check if person is at least 1 year old (for passport applications)
    $age = $birth_date->diff($today)->y;
    if ($age < 1) {
        return "Applicant must be at least 1 year old for passport applications.";
    }
    
    return null; // No error
}

function validateDOBWithPassport($dob, $issue_date) {
    if (empty($dob) || empty($issue_date)) {
        return "Both date of birth and passport issue date are required.";
    }
    
    if (!isValidDate($dob) || !isValidDate($issue_date)) {
        return "Invalid date format. Please use DD-MM-YYYY format.";
    }
    
    $birth_date = DateTime::createFromFormat('d-m-Y', $dob);
    $passport_issue = DateTime::createFromFormat('d-m-Y', $issue_date);
    $today = new DateTime();
    
    // Validate DOB first
    $dob_error = validateDOB($dob);
    if ($dob_error) {
        return $dob_error;
    }
    
    // Calculate age at passport issue
    $age_at_issue = $birth_date->diff($passport_issue)->y;
    
    if ($age_at_issue < 1) {
        return "Passport must be issued at least 1 year after your birth date.";
    }
    
    if ($passport_issue <= $birth_date) {
        return "Passport issue date must be after your date of birth.";
    }
    
    return null; // No error
}

// Phone number validation by country
function validatePhoneNumber($phone, $country_name) {
    if (empty($phone)) {
        return "Phone number is required.";
    }
    
    // Clean the phone number (remove spaces, dashes, parentheses)
    $cleaned_phone = preg_replace('/[^0-9\+]/', '', $phone);
    
    // Country-specific validations
    $country_lower = strtolower($country_name);
    
    if (strpos($country_lower, 'india') !== false) {
        // India: 10 digits, starts with 6-9
        if (!preg_match('/^[6-9][0-9]{9}$/', $cleaned_phone)) {
            return "Indian phone number must be 10 digits starting with 6, 7, 8, or 9.";
        }
    } elseif (strpos($country_lower, 'usa') !== false || strpos($country_lower, 'united states') !== false) {
        // USA: 10 digits
        if (!preg_match('/^[0-9]{10}$/', $cleaned_phone)) {
            return "US phone number must be 10 digits.";
        }
    } elseif (strpos($country_lower, 'uk') !== false || strpos($country_lower, 'united kingdom') !== false) {
        // UK: 10-11 digits, starts with 07 or +447
        if (!preg_match('/^(07[0-9]{9}|447[0-9]{9})$/', $cleaned_phone)) {
            return "UK phone number must start with 07 or +447 and be 10-11 digits.";
        }
    } elseif (strpos($country_lower, 'china') !== false) {
        // China: 11 digits, starts with 1
        if (!preg_match('/^1[0-9]{10}$/', $cleaned_phone)) {
            return "Chinese phone number must be 11 digits starting with 1.";
        }
    } elseif (strpos($country_lower, 'germany') !== false) {
        // Germany: 10-13 digits
        if (!preg_match('/^[0-9]{10,13}$/', $cleaned_phone)) {
            return "German phone number must be 10-13 digits.";
        }
    } elseif (strpos($country_lower, 'france') !== false) {
        // France: 9 digits
        if (!preg_match('/^[0-9]{9}$/', $cleaned_phone)) {
            return "French phone number must be 9 digits.";
        }
    } else {
        // Generic validation for other countries
        if (!preg_match('/^\+?[0-9]{8,15}$/', $cleaned_phone)) {
            return "Phone number must be 8-15 digits, may start with +.";
        }
    }
    
    return null; // No error
}

// Flight number validation
function validateFlightNumber($flight_number) {
    if (empty($flight_number)) {
        return "Flight number is required.";
    }
    
    // Flight number format: Typically 1-4 letters followed by 1-4 numbers
    // Examples: AA123, DL4567, BA789, AI202, EK202
    if (!preg_match('/^[A-Z]{1,4}[0-9]{1,4}$/i', $flight_number)) {
        return "Flight number should be 1-4 letters followed by 1-4 numbers (e.g., AA123, DL4567).";
    }
    
    return null; // No error
}

// Place of birth validation
function validatePlaceOfBirth($place) {
    if (empty($place)) {
        return "Place of birth is required.";
    }
    
    // Basic validation for place names
    if (strlen($place) < 2) {
        return "Place of birth should be at least 2 characters.";
    }
    
    if (strlen($place) > 100) {
        return "Place of birth should not exceed 100 characters.";
    }
    
    // Check if it looks like a date (common mistake)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $place) || 
        preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $place) ||
        preg_match('/^\d{2}-\d{2}-\d{4}$/', $place)) {
        return "Please enter a place (city/town), not a date. Example: Mumbai, New York, London";
    }
    
    // Check if it contains only numbers
    if (preg_match('/^\d+$/', $place)) {
        return "Place of birth should contain letters, not just numbers.";
    }
    
    return null; // No error
}

if (isset($_GET['ajax'])) {
    $msg = htmlspecialchars(trim($_POST['message'] ?? ''));
    $response = "";
    $img_path = "";
    $progress = 0;

    // Check if this is a gender selection from dropdown
    $is_gender_selection = isset($_POST['gender']) && $_POST['gender'] !== '';
    if ($is_gender_selection) {
        $msg = $_POST['gender'];
    }

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
            $img_path = '/fetch_file.php?path=' . urlencode($sub_path . '/' . $filename);
        }
    }

    // 2. Main Logic
    if ($msg !== '' || $img_path !== '' || $is_gender_selection) {
        $p_num = $_SESSION['current_person_num'] ?? 1;
        
        if ($img_path !== '') {
            $is_pdf = (strpos(strtolower($img_path), 'pdf') !== false);
            // Store with file preview for user message
            $_SESSION['messages'][] = [
                'role' => 'user', 
                'text' => $is_pdf ? "Uploaded PDF" : "Uploaded Image", 
                'img' => $img_path, 
                'is_pdf' => $is_pdf
            ];
        } else if ($msg !== '') {
            // For gender selections, show the selected option
            if ($is_gender_selection) {
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
                    $_SESSION['country_name'] = $country['country_name']; // Store for phone validation
                    $q_stmt = $pdo->prepare("SELECT id, label, field_type FROM country_questions WHERE country_id = ? ORDER BY sort_order ASC");
                    $q_stmt->execute([$country['id']]);
                    $_SESSION['db_questions'] = $q_stmt->fetchAll();
                    
                    // Store question labels for validation lookups
                    $_SESSION['question_labels'] = [];
                    foreach ($_SESSION['db_questions'] as $q) {
                        $_SESSION['question_labels'][$q['id']] = $q['label'];
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
                    $response = "Applicant #1. **" . trim($_SESSION['db_questions'][0]['label']) . "**?";
                } else { 
                    $response = "Please enter a valid number between 1 and 20."; 
                }
                break;

            case 'details':
                $questions = $_SESSION['db_questions'];
                $current_q = $questions[$_SESSION['q_idx']];
                $current_q_id = $current_q['id'];
                $current_q_label = $current_q['label'];

                if ($current_q['field_type'] === 'file' && !$img_path) {
                    $response = "I need a file for: **" . trim($current_q_label) . "**. Please use the 📎 icon.";
                } else {
                    // Check for specific validations based on question type
                    $validation_error = null;
                    
                    if ($img_path === '') {
                        // First check for gender (dropdown selection)
                        if (isGenderQuestion($current_q_label)) {
                            $validation_error = validateGender($msg);
                        }
                        // Then check for place of birth (not a date)
                        elseif (isPlaceOfBirthQuestion($current_q_label)) {
                            $validation_error = validatePlaceOfBirth($msg);
                        }
                        // Then check for flight number (not a date)
                        elseif (isFlightNumberQuestion($current_q_label)) {
                            $validation_error = validateFlightNumber($msg);
                        }
                        // Then check for phone number
                        elseif (isPhoneQuestion($current_q_label)) {
                            if (isset($_SESSION['country_name'])) {
                                $validation_error = validatePhoneNumber($msg, $_SESSION['country_name']);
                            }
                        }
                        // Then check for date questions
                        elseif (isDateQuestion($current_q_label)) {
                            // This is a date question
                            if (strpos(strtolower($current_q_label), 'birth') !== false && 
                                !strpos(strtolower($current_q_label), 'place')) {
                                // Date of birth
                                $validation_error = validateDOB($msg);
                                
                                if (!$validation_error) {
                                    // Also check if we have passport issue date for validation
                                    $issue_key = null;
                                    foreach ($_SESSION['question_labels'] as $qid => $label) {
                                        if ((strpos(strtolower($label), 'issue') !== false || 
                                             strpos(strtolower($label), 'issued') !== false) && 
                                            (strpos(strtolower($label), 'passport') !== false ||
                                             strpos(strtolower($label), 'document') !== false) &&
                                            isset($_SESSION['collected_info']["applicant_$p_num"]['answers'][$qid])) {
                                            $issue_key = $qid;
                                            break;
                                        }
                                    }
                                    
                                    if ($issue_key) {
                                        $issue_date = $_SESSION['collected_info']["applicant_$p_num"]['answers'][$issue_key];
                                        $validation_error = validateDOBWithPassport($msg, $issue_date);
                                    }
                                }
                            } elseif (strpos(strtolower($current_q_label), 'flight') !== false &&
                                     strpos(strtolower($current_q_label), 'date') !== false) {
                                // Flight date
                                $validation_error = validateFlightDate($msg);
                            } elseif ((strpos(strtolower($current_q_label), 'expiry') !== false || 
                                      strpos(strtolower($current_q_label), 'expiration') !== false) &&
                                     (strpos(strtolower($current_q_label), 'passport') !== false ||
                                      strpos(strtolower($current_q_label), 'document') !== false)) {
                                // Passport expiry date - validate against issue date if available
                                $issue_date_key = null;
                                foreach ($_SESSION['question_labels'] as $qid => $label) {
                                    if ((strpos(strtolower($label), 'issue') !== false || 
                                         strpos(strtolower($label), 'issued') !== false) && 
                                        (strpos(strtolower($label), 'passport') !== false ||
                                         strpos(strtolower($label), 'document') !== false) &&
                                        isset($_SESSION['collected_info']["applicant_$p_num"]['answers'][$qid])) {
                                        $issue_date_key = $qid;
                                        break;
                                    }
                                }
                                
                                if ($issue_date_key) {
                                    $issue_date = $_SESSION['collected_info']["applicant_$p_num"]['answers'][$issue_date_key];
                                    $validation_error = validatePassportDates($issue_date, $msg);
                                } else {
                                    // Just validate date format for now
                                    if (!isValidDate($msg)) {
                                        $validation_error = "Invalid date format. Please use DD-MM-YYYY format (e.g., 31-12-2024).";
                                    }
                                }
                            } elseif ((strpos(strtolower($current_q_label), 'issue') !== false || 
                                      strpos(strtolower($current_q_label), 'issued') !== false) &&
                                     (strpos(strtolower($current_q_label), 'passport') !== false ||
                                      strpos(strtolower($current_q_label), 'document') !== false)) {
                                // Passport issue date - validate against DOB if available
                                $dob_key = null;
                                foreach ($_SESSION['question_labels'] as $qid => $label) {
                                    if (strpos(strtolower($label), 'birth') !== false && 
                                        !strpos(strtolower($label), 'place') &&
                                        isset($_SESSION['collected_info']["applicant_$p_num"]['answers'][$qid])) {
                                        $dob_key = $qid;
                                        break;
                                    }
                                }
                                
                                if ($dob_key) {
                                    $dob = $_SESSION['collected_info']["applicant_$p_num"]['answers'][$dob_key];
                                    $validation_error = validateDOBWithPassport($dob, $msg);
                                }
                                
                                // Also validate against expiry date if available
                                if (!$validation_error) {
                                    $expiry_key = null;
                                    foreach ($_SESSION['question_labels'] as $qid => $label) {
                                        if ((strpos(strtolower($label), 'expiry') !== false || 
                                             strpos(strtolower($label), 'expiration') !== false) &&
                                            (strpos(strtolower($label), 'passport') !== false ||
                                             strpos(strtolower($label), 'document') !== false) &&
                                            isset($_SESSION['collected_info']["applicant_$p_num"]['answers'][$qid])) {
                                            $expiry_key = $qid;
                                            break;
                                        }
                                    }
                                    
                                    if ($expiry_key) {
                                        $expiry_date = $_SESSION['collected_info']["applicant_$p_num"]['answers'][$expiry_key];
                                        $validation_error = validatePassportDates($msg, $expiry_date);
                                    }
                                }
                                
                                // If no related dates, just validate format
                                if (!$validation_error && !isValidDate($msg)) {
                                    $validation_error = "Invalid date format. Please use DD-MM-YYYY format (e.g., 31-12-2024).";
                                }
                            } else {
                                // Generic date validation
                                if (!isValidDate($msg)) {
                                    $validation_error = "Invalid date format. Please use DD-MM-YYYY format (e.g., 31-12-2024).";
                                }
                            }
                        }
                    }
                    
                    if ($validation_error) {
                        $response = $validation_error;
                        // Don't increment the question index, ask again
                    } else {
                        // Store the answer
                        $_SESSION['collected_info']["applicant_$p_num"]['answers'][$current_q_id] = $img_path ?: $msg;
                        $_SESSION['q_idx']++;

                        if ($_SESSION['q_idx'] < count($questions)) {
                            $next_q = $questions[$_SESSION['q_idx']];
                            $next_q_label = $next_q['label'];
                            
                            // Check if next question is gender to show dropdown
                            if (isGenderQuestion($next_q_label)) {
                                $response = "json_select:gender:Applicant #$p_num: **" . trim($next_q_label) . "**?";
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
                // Simple email validation
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
                        $first_q_label = $_SESSION['db_questions'][0]['label'];
                        
                        // Check if first question is gender to show dropdown
                        if (isGenderQuestion($first_q_label)) {
                            $response = "json_select:gender:Next: Applicant #$p. **" . trim($first_q_label) . "**?";
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
                        $_SESSION['current_order_id'] = $order_id; // Store order ID for download
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

    // Calculate progress based on current step
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
                $allow_upload = ($_SESSION['db_questions'][$_SESSION['q_idx']]['field_type'] === 'file');
                $total_questions = count($_SESSION['db_questions']);
                $progress = round((($_SESSION['q_idx']) / $total_questions) * 70 + 10);
                $step_label = "Document " . ($_SESSION['q_idx'] + 1) . " of " . $total_questions;
                
                // Check if current question is a date question
                $current_q_label = $_SESSION['db_questions'][$_SESSION['q_idx']]['label'];
                if (isDateQuestion($current_q_label)) {
                    $show_date_calendar = true;
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

    // Calculate step count
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

    // Check if we should show gender dropdown
    $show_gender_dropdown = false;
    if ($_SESSION['step'] === 'details' && isset($_SESSION['db_questions'][$_SESSION['q_idx']])) {
        $current_q_label = $_SESSION['db_questions'][$_SESSION['q_idx']]['label'];
        if (isGenderQuestion($current_q_label)) {
            $show_gender_dropdown = true;
        }
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
        'show_gender_dropdown' => $show_gender_dropdown,
        'show_date_calendar' => $show_date_calendar,
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
    <style>
        /* ALL YOUR ORIGINAL CSS HERE - NOT CHANGED */
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

        /* Gender Dropdown Styles */
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

        /* Calendar Date Picker Styles */
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

        /* Typing Indicator */
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

        /* Responsive */
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

<!-- Typing Indicator -->
<div class="typing-indicator" id="typingIndicator" style="display: none;">
    <div class="loading-dots">
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
    </div>
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
    const typingIndicator = document.getElementById('typingIndicator');
    
    let isProcessing = false;
    let currentOrderId = null;
    let currentGenderSelection = null;

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

    // Gender selection function
    function selectGender(gender) {
        currentGenderSelection = gender;
        
        // Highlight selected option
        document.querySelectorAll('.gender-option').forEach(option => {
            option.classList.remove('selected');
        });
        event.target.closest('.gender-option').classList.add('selected');
        
        // Enable send button and auto-submit after a brief delay
        sendBtn.disabled = false;
        
        // Update input field
        msgInput.value = gender;
        
        // Auto-submit after 500ms
        setTimeout(() => {
            if (!isProcessing) {
                sendMessage();
            }
        }, 500);
    }

    // Create gender dropdown
    function createGenderDropdown() {
        const genderDropdown = document.createElement('div');
        genderDropdown.className = 'gender-dropdown';
        
        genderDropdown.innerHTML = `
            <div class="gender-options">
                <div class="gender-option" onclick="selectGender('male')">
                    <span>Male</span>
                    <i class="fas fa-mars gender-icon"></i>
                </div>
                <div class="gender-option" onclick="selectGender('female')">
                    <span>Female</span>
                    <i class="fas fa-venus gender-icon"></i>
                </div>
                <div class="gender-option" onclick="selectGender('other')">
                    <span>Other</span>
                    <i class="fas fa-transgender-alt gender-icon"></i>
                </div>
            </div>
        `;
        
        return genderDropdown;
    }

    // Calendar functionality
    let calendarPopup = null;
    let currentDateInput = null;
    let currentCalendar = null;

    // Create calendar popup
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

    // Show calendar for date input
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

    // Close calendar
    function closeCalendar() {
        if (calendarPopup) {
            calendarPopup.style.display = 'none';
        }
    }

    // Render calendar
    function renderCalendar() {
        if (!calendarPopup || !currentCalendar) return;
        
        const { year, month } = currentCalendar;
        const calendarDays = document.getElementById('calendarDays');
        const calendarTitle = document.getElementById('calendarTitle');
        
        // Set title
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        calendarTitle.textContent = `${monthNames[month]} ${year}`;
        
        // Get first day of month
        const firstDay = new Date(year, month, 1);
        const startingDay = firstDay.getDay();
        
        // Get days in month
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Get today's date
        const today = new Date();
        const todayStr = `${today.getDate().toString().padStart(2, '0')}-${(today.getMonth() + 1).toString().padStart(2, '0')}-${today.getFullYear()}`;
        
        // Clear previous days
        calendarDays.innerHTML = '';
        
        // Add empty days for previous month
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('button');
            emptyDay.className = 'day empty other-month';
            emptyDay.disabled = true;
            calendarDays.appendChild(emptyDay);
        }
        
        // Add days of current month
        for (let day = 1; day <= daysInMonth; day++) {
            const dayButton = document.createElement('button');
            dayButton.className = 'day';
            dayButton.textContent = day;
            
            const dateStr = `${day.toString().padStart(2, '0')}-${(month + 1).toString().padStart(2, '0')}-${year}`;
            
            // Check if this is today
            if (dateStr === todayStr) {
                dayButton.classList.add('today');
            }
            
            // Check if this is the selected date
            if (currentDateInput && currentDateInput.value === dateStr) {
                dayButton.classList.add('selected');
            }
            
            dayButton.onclick = () => selectDate(day, month + 1, year);
            calendarDays.appendChild(dayButton);
        }
    }

    // Change calendar month
    function changeCalendarMonth(delta) {
        currentCalendar.month += delta;
        
        // Adjust year if month goes out of bounds
        if (currentCalendar.month < 0) {
            currentCalendar.month = 11;
            currentCalendar.year--;
        } else if (currentCalendar.month > 11) {
            currentCalendar.month = 0;
            currentCalendar.year++;
        }
        
        renderCalendar();
    }

    // Select date
    function selectDate(day, month, year) {
        const dateStr = `${day.toString().padStart(2, '0')}-${month.toString().padStart(2, '0')}-${year}`;
        
        if (currentDateInput) {
            currentDateInput.value = dateStr;
            
            // Trigger input event for validation
            const event = new Event('input', { bubbles: true });
            currentDateInput.dispatchEvent(event);
            
            // Focus back on the input
            currentDateInput.focus();
        }
        
        closeCalendar();
    }

    // Select today
    function selectToday() {
        const today = new Date();
        selectDate(today.getDate(), today.getMonth() + 1, today.getFullYear());
    }

    // Close calendar when clicking outside
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
        // Update progress bar
        if (pBar && data.progress !== undefined) {
            pBar.style.width = data.progress + '%';
        }
        
        // Update progress percentage
        if (progressPercent && data.progress !== undefined) {
            progressPercent.textContent = data.progress + '%';
        }
        
        // Update step label
        if (stepLabel && data.step_label) {
            stepLabel.textContent = data.step_label;
        }
        
        // Update step count
        if (stepCount && data.step_count) {
            stepCount.textContent = data.step_count;
        }
        
        // Update applicant count
        if (applicantCount && data.current_person && data.total_people) {
            applicantCount.textContent = `Applicant ${data.current_person}/${data.total_people}`;
        }
    }

    // Message Sending function
    async function sendMessage() {
        const file = fileInput.files[0];
        let text = msgInput.value.trim();
        
        // Check if we have a gender selection
        if (currentGenderSelection && text === '') {
            text = currentGenderSelection;
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
        
        // Add gender selection if available
        if (currentGenderSelection) {
            formData.append('gender', currentGenderSelection);
        }

        // Add user message to UI immediately with file preview
        if (text || file) {
            const userRow = document.createElement('div');
            userRow.className = 'message-row user';
            
            let attachmentHtml = '';
            let messageText = text || '';
            
            // Format gender message for display
            if (currentGenderSelection && text === currentGenderSelection) {
                messageText = `Selected: ${currentGenderSelection.charAt(0).toUpperCase() + currentGenderSelection.slice(1)}`;
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
        currentGenderSelection = null; // Reset gender selection
        
        // Remove any existing gender dropdown highlights
        document.querySelectorAll('.gender-option').forEach(option => {
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

            // Check if we need to show gender dropdown
            if (data.text && data.text.startsWith('json_select:gender:')) {
                // Extract the actual message
                const parts = data.text.split(':');
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
                
                // Add gender dropdown
                const genderDropdown = createGenderDropdown();
                botRow.querySelector('.message-content').appendChild(genderDropdown);
                
                // Update input placeholder
                msgInput.placeholder = "Select a gender option above";
                msgInput.disabled = true;
                sendBtn.disabled = true;
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
                        document.getElementById('dateInput').focus();
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

    // Download Summary Function - FIXED VERSION
    function downloadSummary(event) {
        // Get order ID from multiple sources
        let orderId = null;
        
        // First try to get from currentOrderId variable
        if (currentOrderId) {
            orderId = currentOrderId;
        }
        // Try to extract from completion state
        else if (finalOrderId && finalOrderId.textContent !== '#0000') {
            const match = finalOrderId.textContent.match(/#(\d+)/);
            if (match) {
                orderId = match[1];
            }
        }
        // Try to extract from recent messages
        else {
            // Look for order ID in chat messages
            const chatMessages = document.querySelectorAll('.message-row.bot .message-text');
            for (let i = chatMessages.length - 1; i >= 0; i--) {
                const text = chatMessages[i].textContent || chatMessages[i].innerText;
                const match = text.match(/Order ID:\s*\*\*(\d+)\*\*/);
                if (match) {
                    orderId = match[1];
                    break;
                }
            }
        }
        
        if (!orderId) {
            alert('No application data available to download. Please complete an application first.');
            return;
        }
        
        // Get current date and time
        const now = new Date();
        const dateStr = now.toLocaleDateString();
        const timeStr = now.toLocaleTimeString();
        
        // Collect all application data from chat
        let summary = `=======================================================\n`;
        summary += `               VISA APPLICATION SUMMARY\n`;
        summary += `=======================================================\n\n`;
        
        summary += `ORDER INFORMATION\n`;
        summary += `-------------------------------------------------------\n`;
        summary += `Order ID:           ${orderId}\n`;
        summary += `Generated On:       ${dateStr} at ${timeStr}\n`;
        summary += `Application Status: Submitted\n`;
        summary += `Summary Date:       ${now.toISOString().split('T')[0]}\n\n`;
        
        summary += `APPLICATION DETAILS\n`;
        summary += `-------------------------------------------------------\n`;
        
        // Extract application conversation
        const messages = [];
        document.querySelectorAll('.message-row').forEach(row => {
            const role = row.classList.contains('user') ? 'User' : 'Assistant';
            const textElem = row.querySelector('.message-text');
            const timeElem = row.querySelector('.message-time');
            
            if (textElem && timeElem) {
                const text = textElem.textContent || textElem.innerText;
                const time = timeElem.textContent || timeElem.innerText;
                
                // Skip empty or system messages
                if (text.trim() && !text.includes('Type your response')) {
                    messages.push({
                        role: role,
                        text: text.trim(),
                        time: time.trim()
                    });
                }
            }
        });
        
        // Add conversation to summary
        messages.forEach((msg, index) => {
            const prefix = msg.role === 'User' ? '[You]' : '[Assistant]';
            summary += `${msg.time} ${prefix}: ${msg.text}\n`;
            
            // Add separator between major sections
            if (msg.text.includes('How many applicants?') || 
                msg.text.includes('Applicant #') ||
                msg.text.includes('All applicant details captured')) {
                summary += `-------------------------------------------------------\n`;
            }
        });
        
        summary += `\n\nAPPLICATION CHECKLIST\n`;
        summary += `-------------------------------------------------------\n`;
        
        // Extract checklist items
        const completedItems = messages.filter(msg => 
            msg.role === 'User' && 
            !msg.text.includes('Uploaded') &&
            !msg.text.includes('Selected:')
        ).map(msg => `✓ ${msg.text}`);
        
        completedItems.forEach(item => {
            summary += `${item}\n`;
        });
        
        summary += `\n\n=======================================================\n`;
        summary += `IMPORTANT INFORMATION\n`;
        summary += `=======================================================\n`;
        summary += `1. This document serves as your application receipt\n`;
        summary += `2. Order ID ${orderId} is your reference for all inquiries\n`;
        summary += `3. Application submitted to: Ask Visa Portal\n`;
        summary += `4. Submission date: ${dateStr}\n`;
        summary += `5. For support: contact@askvisa.in\n`;
        summary += `6. Website: https://askvisa.in\n`;
        summary += `7. Keep this document safe for future reference\n`;
        summary += `=======================================================\n`;
        summary += `\nEND OF DOCUMENT\n`;
        
        // Create and download the file
        const blob = new Blob([summary], { 
            type: 'text/plain;charset=utf-8',
            endings: 'native'
        });
        
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `visa_application_${orderId}_summary.txt`;
        document.body.appendChild(a);
        a.click();
        
        // Cleanup
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 100);
        
        // Show confirmation on button
        if (event && event.target) {
            const originalHTML = event.target.innerHTML;
            const originalBg = event.target.style.background;
            
            event.target.innerHTML = '<i class="fas fa-check"></i> Downloaded';
            event.target.style.background = 'rgba(76, 201, 240, 0.3)';
            
            setTimeout(() => {
                event.target.innerHTML = originalHTML;
                event.target.style.background = originalBg;
            }, 2000);
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