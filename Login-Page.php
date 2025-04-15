<?php
session_start();
include("connection.php");
include("function.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = sanitize_input($con, $_POST['email']);
    $password = sanitize_input($con, $_POST['password']);

    if (!empty($email) && !empty($password) && is_valid_email($email)) {
        // Check user credentials - changed to check email field instead of user_name
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
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
    <title>Welcome Back | Sign In</title>
    <link rel="stylesheet" href="Style/Login-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to access your account</p>
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
            </div>
            
            <form method="post" action="">
                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon password-container">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="Forgot-Password-Page.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-signin">Sign In</button>
                
                <div class="divider">
                    <span>or</span>
                </div>

                <a href="Admin-Login-Page.php" class="btn-admin-link">
                    <button type="button" class="btn-admin">
                        <i class="fas fa-user-shield"></i> Log in as Admin
                    </button>
                </a>

                <p class="signup-link">Don't have an account? <a href="Sign-Up-Page.php">Sign up now</a></p>
            </form>
        </div>
        <script src="Scripts/auth/Login.js"></script>
    </body>
</html>