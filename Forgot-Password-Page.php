<?php
session_start();
include("connection.php");
include("function.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = sanitize_input($con, $_POST['email']);
    
    if (!empty($email) && is_valid_email($email)) {
        // Directly check if email exists in users table
        $query = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            // Email exists - generate token
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // Store token in database
            $query = "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?) 
                     ON DUPLICATE KEY UPDATE token = ?, expires_at = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "sssss", $email, $token, $expires, $token, $expires);
            mysqli_stmt_execute($stmt);
            
            // Store in session for verification
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $token;
            
            $message = "success";
        } else {
            $message = "Email doesn't exist in our system.";
        }
    } else {
        $message = "Please enter a valid email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="Style/Forgot-Password-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <?php if ($message === "success"): ?>
    <meta http-equiv="refresh" content="5;url=Change-Password-Page.php">
    <?php endif; ?>
</head>
    <body>
        <div class="reset-container animate__animated animate__fadeIn">
            <div class="password-icon">
                <svg viewBox="0 0 24 24" width="64" height="64">
                    <path fill="#38a3a5" d="M12,3A4,4 0 0,0 8,7V8H7A2,2 0 0,0 5,10V20A2,2 0 0,0 7,22H17A2,2 0 0,0 19,20V10A2,2 0 0,0 17,8H16V7A4,4 0 0,0 12,3M12,5A2,2 0 0,1 14,7V8H10V7A2,2 0 0,1 12,5M12,12A1,1 0 0,1 13,13A1,1 0 0,1 12,14A1,1 0 0,1 11,13A1,1 0 0,1 12,12Z" />
                </svg>
            </div>
            
            <?php if ($message === "success"): ?>
                <div class="success-message">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="#38a3a5" d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                    </svg>
                    <h3>Password Reset Initiated!</h3>
                    <p>You'll be redirected to the password change page in 5 seconds...</p>
                    <p>If not, <a href="Change-Password-Page.php">click here</a>.</p>
                </div>
            <?php else: ?>
                <h1>Reset Password</h1>
                <p class="instructions">Enter your email to proceed to password reset</p>

                <?php if (!empty($message)): ?>
                    <div class="error-message"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="your@email.com" required>
                    </div>

                    <button type="submit" id="submitBtn" class="btn-pulse">
                        <span class="btn-text">Continue to Reset</span>
                    </button>
                </form>
            <?php endif; ?>

            <p class="login-link">Remember your password? <a href="Login-Page.php">Back to Login</a></p>
        </div>
    </body>
</html>