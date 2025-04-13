<?php
session_start();
include("connection.php");
include("function.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = sanitize_input($con, $_POST['email']);
    $password = sanitize_input($con, $_POST['password']);
    $confirm_password = sanitize_input($con, $_POST['confirm_password']);

    if (!empty($email) && !empty($password) && is_valid_email($email)) {
        if ($password === $confirm_password) {
            // Check if email already exists
            $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $error = "Email already registered!";
            } else {
                // Save to database
                $user_id = random_num(20);
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (user_id, user_name, email, password) VALUES (?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($con, $query);
                mysqli_stmt_bind_param($stmt, "ssss", $user_id, $email, $email, $hashed_password);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['user_id'] = $user_id;
                    header("Location: Login-Page.php");
                    die;
                } else {
                    $error = "Error creating account!";
                }
            }
        } else {
            $error = "Passwords don't match!";
        }
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
    <title>Create Your Account</title>
    <link rel="stylesheet" href="Style/Sign-Up-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
    <body>
        <div class="signup-container">
            <div class="signup-header">
                <h1>Create Your Account</h1>
                <p>Join our community today</p>
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
            </div>

            <form method="post" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                        <div class="password-strength">
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                        </div>
                    </div>
                    <div class="password-hint">
                        <i class="fas fa-info-circle"></i> Use 8+ characters with a mix of letters, numbers & symbols
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Re-enter your password" required>
                    </div>
                </div>

                <div class="terms-agreement">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>

                <button type="submit" class="btn-signup">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>

                <div class="divider">
                    <span>or</span>
                </div>

                <button type="button" class="btn-social">
                    <i class="fab fa-google"></i> Sign up with Google
                </button>

                <p class="login-link">Already have an account? <a href="Login-Page.php">Sign in</a></p>
            </form>
        </div>
        <script src="Scripts/auth/Sign-Up.js"></script>
    </body>
</html>