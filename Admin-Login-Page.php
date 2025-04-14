<?php

	session_start();
	include("connection.php");
	include("admin-function.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = sanitize_input($con, $_POST['email']);
    $password = sanitize_input($con, $_POST['password']);

    if (!empty($email) && !empty($password) && is_valid_email($email)) {
        // Check user credentials
        $query = "SELECT * FROM users WHERE user_name = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user_data['password'])) {
                $_SESSION['user_id'] = $user_data['user_id'];
                header("Location: index.php");
                die;
            }
        }
        
        $error = "Invalid email or password!";
    } else {
        $error = "Please enter valid email and password!";
    }
}

?>

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
        
            <div class="login-container animate__animated animate__fadeIn">
                <div class="login-header">
                    <div class="logo">
                        <i class="fas fa-user-shield admin-icon"></i>
                    </div>
                    <h1>Admin Portal</h1>
                    <p class="welcome-text">Sign in to access the dashboard</p>
                </div>

                <form id="adminLoginForm">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" id="email" name="username" placeholder="Enter admin username" required autocomplete="username">
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
                        <a href="Forgot-Password-Page.html" class="forgot-password">Forgot password?</a>
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