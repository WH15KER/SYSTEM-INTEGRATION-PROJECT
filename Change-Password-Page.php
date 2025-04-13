<?php
session_start();
include("connection.php");
include("function.php");

$error = "";
$token = isset($_GET['token']) ? sanitize_input($con, $_GET['token']) : '';
$show_form = false;

// Verify token if it exists
if (!empty($token)) {
    $query = "SELECT email FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $email = $row['email'];
        $show_form = true;
    } else {
        $error = "Invalid or expired token. Please request a new password reset link.";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == "POST" && $show_form) {
    $password = sanitize_input($con, $_POST['password']);
    $confirm_password = sanitize_input($con, $_POST['confirm_password']);
    
    if (!empty($password) && $password === $confirm_password) {
        // Update password in database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            // Delete the used token
            $query = "DELETE FROM password_reset_tokens WHERE token = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);
            
            $success = true;
        } else {
            $error = "Error updating password. Please try again.";
        }
    } else {
        $error = "Passwords do not match or are empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="Style/Change-Password-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
    <body>
        <div class="change-password-container animate__animated animate__fadeIn">
            <div class="password-icon">
                <svg viewBox="0 0 24 24" width="64" height="64">
                    <path fill="#38a3a5" d="M12,3A4,4 0 0,0 8,7V8H7A2,2 0 0,0 5,10V20A2,2 0 0,0 7,22H17A2,2 0 0,0 19,20V10A2,2 0 0,0 17,8H16V7A4,4 0 0,0 12,3M12,5A2,2 0 0,1 14,7V8H10V7A2,2 0 0,1 12,5M12,12A1,1 0 0,1 13,13A1,1 0 0,1 12,14A1,1 0 0,1 11,13A1,1 0 0,1 12,12Z" />
                </svg>
            </div>
            <h1>Change Password</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-message">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="#38a3a5" d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                    </svg>
                    <h3>Password Updated!</h3>
                    <p>Your password has been successfully changed.</p>
                    <a href="Login-Page.php" class="success-btn">Continue to Login</a>
                </div>
            <?php elseif ($show_form): ?>
                <p class="instructions">Create a new secure password</p>
                <form method="post" id="passwordForm">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Enter new password" required>
                            <span class="toggle-password" onclick="togglePassword('password')">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <span class="strength-segment"></span>
                                <span class="strength-segment"></span>
                                <span class="strength-segment"></span>
                                <span class="strength-segment"></span>
                            </div>
                            <span class="strength-text">Password Strength: <span id="strengthValue">Weak</span></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                            <div class="password-wrapper">
                            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm new password" required>
                            <span class="toggle-password" onclick="togglePassword('confirm-password')">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="password-match" id="passwordMatch"></div>
                    </div>

                    <div class="password-requirements">
                        <p>Your password should include:</p>
                        <ul>
                            <li id="req-length"><i class="fas fa-check"></i> At least 8 characters</li>
                            <li id="req-uppercase"><i class="fas fa-check"></i> One uppercase letter</li>
                            <li id="req-number"><i class="fas fa-check"></i> One number</li>
                            <li id="req-special"><i class="fas fa-check"></i> One special character</li>
                        </ul>
                    </div>

                    <button type="submit" id="submitBtn">
                        <span class="btn-text">Update Password</span>
                        <span class="btn-loader">
                            <span class="loader-dot"></span>
                            <span class="loader-dot"></span>
                            <span class="loader-dot"></span>
                        </span>
                    </button>
                </form>
            <?php else: ?>
                <p class="instructions">Please request a password reset link from the login page.</p>
                <a href="Login-Page.php" class="success-btn">Back to Login</a>
            <?php endif; ?>
        </div>
        <script src="Scripts/auth/Change-Password.js"></script>
    </body>
</html>