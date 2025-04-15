<?php
function check_login($con) {
    if (isset($_SESSION['user_id'])) {
        $id = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    
    header("Location: Login-Page.php");
    die;
}

function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sanitize_input($con, $data) {
    return mysqli_real_escape_string($con, htmlspecialchars(trim($data)));
}

function generate_uuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant RFC 4122
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function random_num($length) {
    $text = "";
    if ($length < 5) {
        $length = 5;
    }

    $len = rand(4, $length);
    for ($i = 0; $i < $len; $i++) {
        $text .= rand(0, 9);
    }
    
    return $text;
}

function email_exists($con, $email) {
    $query = "SELECT * FROM users WHERE email = ? OR user_name = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ss", $email, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

function generate_password_reset_token($con, $email) {
    // Generate a unique token
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
    
    // Store token in database
    $query = "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?) 
              ON DUPLICATE KEY UPDATE token = ?, expires_at = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $email, $token, $expires, $token, $expires);
    mysqli_stmt_execute($stmt);
    
    return $token;
}

function send_password_reset_email($email, $token) {
    $subject = "Password Reset Request";
    $reset_link = "http://".$_SERVER['HTTP_HOST']."/Change-Password-Page.php?token=$token";
    
    $message = "
    <html>
    <head>
        <title>Password Reset</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { 
                display: inline-block; 
                padding: 10px 20px; 
                background-color: #38a3a5; 
                color: white; 
                text-decoration: none; 
                border-radius: 5px; 
                margin: 15px 0;
            }
            .footer { margin-top: 20px; font-size: 0.8em; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Password Reset Request</h2>
            <p>You requested to reset your password for your account. Click the button below to proceed:</p>
            <p><a href='$reset_link' class='button'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email or contact support if you have concerns.</p>
            <div class='footer'>
                <p>Best regards,<br>Medical Check Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // To send HTML mail, the Content-type header must be set
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@medicalcheck.com\r\n";
    $headers .= "Reply-To: support@medicalcheck.com\r\n";
    
    // Send email
    return mail($email, $subject, $message, $headers);
}

function get_service_icon($service_name) {
    $icons = [
        'checkup' => 'user-md',
        'blood' => 'tint',
        'vision' => 'eye',
        'cardiac' => 'heart',
        'x-ray' => 'x-ray',
        'dental' => 'tooth',
        'vaccine' => 'syringe'
    ];
    
    $service_lower = strtolower($service_name);
    
    foreach ($icons as $keyword => $icon) {
        if (strpos($service_lower, $keyword) !== false) {
            return $icon;
        }
    }
    
    return 'stethoscope'; // default icon
}

/**
 * Check if a time slot is available
 */
function is_slot_available($date, $time, $duration, $booked_slots) {
    $proposed_start = strtotime("$date $time");
    $proposed_end = $proposed_start + ($duration * 60);
    
    foreach ($booked_slots as $slot) {
        $slot_start = strtotime("{$slot['appointment_date']} {$slot['start_time']}");
        $slot_end = strtotime("{$slot['appointment_date']} {$slot['end_time']}");
        
        // Check for overlap
        if ($proposed_start < $slot_end && $proposed_end > $slot_start) {
            return false;
        }
    }
    
    return true;
}

function get_inventory_item_details($con, $item_id) {
    $item_id = sanitize_input($con, $item_id);
    $query = "SELECT * FROM inventory_items WHERE item_id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $item = mysqli_fetch_assoc($result);
        
        // Format the expiry date if it exists
        if ($item['expiry_date']) {
            $item['expiry_date'] = date('M. d, Y', strtotime($item['expiry_date']));
        }
        
        return ['success' => true, 'data' => $item];
    } else {
        return ['success' => false, 'message' => 'Item not found'];
    }
}

function add_medical_record($con, $user_id, $data, $record_type) {
    $response = ['success' => false, 'message' => ''];
    
    switch ($record_type) {
        case 'visit':
            $required = ['visit_date', 'visit_type', 'physician_name'];
            $fields = array_intersect_key($data, array_flip($required));
            
            if (count($fields) != count($required)) {
                $response['message'] = 'Missing required fields for visit';
                return $response;
            }
            
            $query = "INSERT INTO medical_visits (visit_id, user_id, visit_date, visit_type, physician_name, diagnosis, notes) 
                      VALUES (UUID(), ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "ssssss", 
                $user_id,
                $data['visit_date'],
                $data['visit_type'],
                $data['physician_name'],
                $data['diagnosis'] ?? '',
                $data['notes'] ?? ''
            );
            break;
            
        case 'medication':
            $required = ['name', 'dosage', 'frequency', 'prescribed_by', 'start_date'];
            $fields = array_intersect_key($data, array_flip($required));
            
            if (count($fields) != count($required)) {
                $response['message'] = 'Missing required fields for medication';
                return $response;
            }
            
            $query = "INSERT INTO medications (medication_id, user_id, name, dosage, frequency, prescribed_by, start_date, end_date, is_current, reason, notes) 
                      VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "ssssssssss", 
                $user_id,
                $data['name'],
                $data['dosage'],
                $data['frequency'],
                $data['prescribed_by'],
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['is_current'] ?? true,
                $data['reason'] ?? '',
                $data['notes'] ?? ''
            );
            break;
            
        case 'allergy':
            $required = ['allergen', 'reaction_severity', 'reaction_description'];
            $fields = array_intersect_key($data, array_flip($required));
            
            if (count($fields) != count($required)) {
                $response['message'] = 'Missing required fields for allergy';
                return $response;
            }
            
            $query = "INSERT INTO allergies (allergy_id, user_id, allergen, reaction_severity, reaction_description, first_observed, notes) 
                      VALUES (UUID(), ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "ssssss", 
                $user_id,
                $data['allergen'],
                $data['reaction_severity'],
                $data['reaction_description'],
                $data['first_observed'] ?? null,
                $data['notes'] ?? ''
            );
            break;
            
        case 'immunization':
            $required = ['vaccine_name', 'administration_date', 'administered_by'];
            $fields = array_intersect_key($data, array_flip($required));
            
            if (count($fields) != count($required)) {
                $response['message'] = 'Missing required fields for immunization';
                return $response;
            }
            
            $query = "INSERT INTO immunizations (immunization_id, user_id, vaccine_name, administration_date, administered_by, location, next_due_date, lot_number, notes) 
                      VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "ssssssss", 
                $user_id,
                $data['vaccine_name'],
                $data['administration_date'],
                $data['administered_by'],
                $data['location'] ?? '',
                $data['next_due_date'] ?? null,
                $data['lot_number'] ?? '',
                $data['notes'] ?? ''
            );
            break;
            
        default:
            $response['message'] = 'Invalid record type';
            return $response;
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = ucfirst($record_type) . ' record added successfully';
    } else {
        $response['message'] = 'Failed to add record: ' . mysqli_error($con);
    }
    
    return $response;
}

function execute_query($con, $query, $params = [], $types = "") {
    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($con));
    }
    
    if (!empty($params)) {
        if (empty($types)) {
            $types = str_repeat("s", count($params));
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    return $stmt;
}

function log_error($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, 'error_log.txt');
}

function generate_secure_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';
    $password = '';
    $chars_length = strlen($chars) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, $chars_length)];
    }
    
    return $password;
}

function setup_super_admin($con, $user_id) {
    // Create system role if not exists
    $role_id = generate_uuid();
    $query = "INSERT INTO admin_roles (role_id, name, description, is_system_role) 
              VALUES (?, 'Super Admin', 'Has all permissions in the system', TRUE)
              ON DUPLICATE KEY UPDATE role_id=role_id";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $role_id);
    mysqli_stmt_execute($stmt);

    // Get all available permissions and assign to this role
    $query = "INSERT IGNORE INTO role_permissions (role_id, permission_id)
              SELECT ?, permission_id FROM admin_permissions";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $role_id);
    mysqli_stmt_execute($stmt);

    // Create admin user record
    $admin_id = generate_uuid();
    $query = "INSERT INTO admin_users (admin_id, user_id, role_id, is_active) 
              VALUES (?, ?, ?, TRUE)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sss", $admin_id, $user_id, $role_id);
    mysqli_stmt_execute($stmt);

    return true;
}

function create_default_permissions($con) {
    $permissions = [
        ['Manage Users', 'manage_users', 'Can create, edit, and delete users'],
        ['Manage Roles', 'manage_roles', 'Can create, edit, and delete roles and permissions'],
        ['Manage Appointments', 'manage_appointments', 'Can view and manage all appointments'],
        ['Manage Medical Records', 'manage_records', 'Can view and manage medical records'],
        ['Manage Inventory', 'manage_inventory', 'Can manage inventory items'],
        ['System Configuration', 'system_config', 'Can change system settings'],
        ['View Reports', 'view_reports', 'Can access all system reports'],
        ['Full Access', 'full_access', 'Has unrestricted access to all features']
    ];

    foreach ($permissions as $perm) {
        $perm_id = generate_uuid();
        $query = "INSERT IGNORE INTO admin_permissions (permission_id, name, code, description) 
                  VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $perm_id, $perm[0], $perm[1], $perm[2]);
        mysqli_stmt_execute($stmt);
    }
}

















?>
