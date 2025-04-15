<?php
session_start();
include("connection.php");
include("function.php");

$errors = [];
$success = "";

// Form data
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'role' => 'patient'
];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Sanitize and validate inputs
    $formData['first_name'] = sanitize_input($con, $_POST['first_name'] ?? '');
    $formData['last_name'] = sanitize_input($con, $_POST['last_name'] ?? '');
    $formData['email'] = sanitize_input($con, $_POST['email'] ?? '');
    $formData['phone'] = sanitize_input($con, $_POST['phone'] ?? '');
    $formData['role'] = sanitize_input($con, $_POST['role'] ?? 'patient');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($formData['first_name'])) {
        $errors['first_name'] = "First name is required";
    }

    if (empty($formData['last_name'])) {
        $errors['last_name'] = "Last name is required";
    }

    if (empty($formData['email']) || !is_valid_email($formData['email'])) {
        $errors['email'] = "Valid email is required";
    } else {
        // Check if email exists
        $query = "SELECT email FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $formData['email']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $errors['email'] = "Email already exists";
        }
    }

    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    if (!in_array($formData['role'], ['patient', 'provider', 'admin'])) {
        $errors['role'] = "Invalid role selected";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Generate user ID
        $user_id = generate_uuid();
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        mysqli_begin_transaction($con);
        
        try {
            // Insert into users table
            $query = "INSERT INTO users (user_id, first_name, last_name, email, password, phone, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "ssssss", $user_id, $formData['first_name'], $formData['last_name'], 
                                  $formData['email'], $password_hash, $formData['phone']);
            mysqli_stmt_execute($stmt);
            
            // Handle role-specific tables
            if ($formData['role'] === 'admin') {
                // Create default permissions if they don't exist
                create_default_permissions($con);
                
                // Set up super admin
                setup_super_admin($con, $user_id);
                
                $success = "Admin account created successfully! You can now login with super admin privileges.";
            } elseif ($formData['role'] === 'provider') {
                $provider_id = generate_uuid();
                $query = "INSERT INTO healthcare_providers (provider_id, user_id, status, created_at) 
                          VALUES (?, ?, 'pending', NOW())";
                $stmt = mysqli_prepare($con, $query);
                mysqli_stmt_bind_param($stmt, "ss", $provider_id, $user_id);
                mysqli_stmt_execute($stmt);
                $success = "Provider registration submitted! Your account will be activated after review.";
            } else {
                $success = "Registration successful! You can now login.";
            }
            
            // Commit transaction
            mysqli_commit($con);
            
            // Clear form data
            $formData = [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'role' => 'patient'
            ];
        } catch (Exception $e) {
            mysqli_rollback($con);
            $errors[] = "Registration failed. Please try again. Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Medical Check</title>
    <link rel="stylesheet" href="Style/Sign-Up-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
    <body>
        <div class="signup-container">
            <div class="signup-header">
                <h1>Create Account</h1>
                <p>Join our healthcare platform</p>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errors) && is_array($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <?php if (is_array($error)): ?>
                                    <?php foreach ($error as $err): ?>
                                        <li><?php echo $err; ?></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><?php echo $error; ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="post" action="" class="signup-form">
                <div class="form-row">
                    <div class="input-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                            value="<?php echo htmlspecialchars($formData['first_name']); ?>" 
                            required>
                        <?php if (isset($errors['first_name'])): ?>
                            <span class="input-error"><?php echo $errors['first_name']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="input-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                            value="<?php echo htmlspecialchars($formData['last_name']); ?>" 
                            required>
                        <?php if (isset($errors['last_name'])): ?>
                            <span class="input-error"><?php echo $errors['last_name']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" 
                            value="<?php echo htmlspecialchars($formData['email']); ?>" 
                            placeholder="example@domain.com" required>
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <span class="input-error"><?php echo $errors['email']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="input-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" 
                            value="<?php echo htmlspecialchars($formData['phone']); ?>" 
                            placeholder="+1 (123) 456-7890">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" 
                                placeholder="At least 8 characters" required>
                            <i class="fas fa-eye toggle-password" data-target="password"></i>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="input-error"><?php echo $errors['password']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                placeholder="Confirm your password" required>
                            <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <span class="input-error"><?php echo $errors['confirm_password']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="input-group role-selection">
                    <label>I am registering as:</label>
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="role" value="patient" 
                                <?php echo $formData['role'] === 'patient' ? 'checked' : ''; ?>>
                            <div class="role-card">
                                <i class="fas fa-user"></i>
                                <span>Patient</span>
                            </div>
                        </label>
                        
                        <label class="role-option">
                            <input type="radio" name="role" value="provider" 
                                <?php echo $formData['role'] === 'provider' ? 'checked' : ''; ?>>
                            <div class="role-card">
                                <i class="fas fa-user-md"></i>
                                <span>Healthcare Provider</span>
                            </div>
                        </label>
                        
                        <label class="role-option">
                            <input type="radio" name="role" value="admin" 
                                <?php echo $formData['role'] === 'admin' ? 'checked' : ''; ?>>
                            <div class="role-card">
                                <i class="fas fa-user-shield"></i>
                                <span>Administrator</span>
                            </div>
                        </label>
                    </div>
                    <?php if (isset($errors['role'])): ?>
                        <span class="input-error"><?php echo $errors['role']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="terms-agreement">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="btn-signup">Create Account</button>
                
                <p class="login-link">Already have an account? <a href="Login-Page.php">Sign in</a></p>
            </form>
        </div>
        
        <script src="Scripts/auth/Sign-Up.js"></script>
    </body>
</html>