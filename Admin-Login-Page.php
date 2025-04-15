<?php
session_start();
include("connection.php");
include("admin-function.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $_SESSION['username'] = $username; 
    $password = $_POST['password'];
    
    if (authenticateAdmin($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $username;
        
        // Handle "Remember me" functionality if needed
        if (isset($_POST['remember'])) {
            // Set a long-term cookie (30 days)
            setcookie('remember_token', 'admin_remember', time() + (30 * 24 * 60 * 60), "/");
        }
        
        header("Location: Admin-Dashboard-Page.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<!-- Rest of your HTML remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Secure Sign In</title>
    <link rel="stylesheet" href="Style/Admin-Login-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
    <body>
        <div class="login-wrapper">
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Secure Admin Portal</span>
            </div>
        
            <div class="login-container">
                <div class="login-header">
                    <div class="logo">
                        <i class="fas fa-user-shield admin-icon"></i>
                    </div>
                    <h1>Admin Portal</h1>
                    <p class="welcome-text">Sign in to access the dashboard</p>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" id="adminLoginForm">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Email
                        </label>
                        <input type="text" id="username" name="username" placeholder="Enter admin email" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                            <span class="toggle-password" onclick="togglePassword()">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember"> Remember this device
                        </label>
                        <a href="Forgot-Password-Page.php" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" id="loginButton">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loader">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </span>
                    </button>

                    <div class="security-notice">
                        <i class="fas fa-info-circle"></i>
                        <span>Ensure you're on a secure network before logging in</span>
                    </div>
                </form>
            </div>

            <div class="login-footer">
                <p>&copy; 2025 Admin Portal. All rights reserved.</p>
                <p class="version">v2.5.1</p>
            </div>
        </div>
        <script src="Scripts/auth/Admin-Login.js"></script>
    </body>
</html>