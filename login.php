<?php
// login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';
$conn = $mysqli;

// Include auto-logout (see auto_logout.php below) on each protected page if needed
// include('auto_logout.php');

$message = "";

// Initialize session variables for login attempts.
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['total_login_attempts'])) {
    $_SESSION['total_login_attempts'] = 0;
}
if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = 0;
}

$max_warning_attempts = 3; // Show warning after 3 failed attempts
$max_total_attempts = 6;   // Lock account after 6 failed attempts
$lockout_duration = 60;    // 60 seconds cooldown after 3 failed attempts

// Check for lockout.
if ($_SESSION['total_login_attempts'] >= $max_warning_attempts) {
    if ($_SESSION['total_login_attempts'] < $max_total_attempts) {
        // Show warning and enforce cooldown
        $elapsed = time() - $_SESSION['lockout_time'];
        if ($elapsed < $lockout_duration) {
            $remaining = ceil($lockout_duration - $elapsed);
            $message = "You have exceeded 3 login attempts. Please try again in {$remaining} seconds.";
        } else {
            // Reset cooldown timer
            $_SESSION['lockout_time'] = 0;
        }
    } else {
        // Lock account after 6 failed attempts
        $message = "Your account has been locked. Please contact the administrator to reset it.";
        // Optionally, you can update the database to mark the account as locked.
    }
}

// Function to insert log entries.
function insertLog($conn, $user_id, $action, $description) {
    $stmt = $conn->prepare("INSERT INTO userlogs (user_id, action, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $description);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $message = "Both username and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password, role_id, is_locked FROM `user` WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Check if the account is locked
                if ($user['is_locked']) {
                    $message = "Your account is locked. Please contact the administrator to reset it.";
                } else {
                    // Verify password
                    if ($password === $user['password']) {
                        // Reset login attempts on successful login
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['total_login_attempts'] = 0;
                        $_SESSION['lockout_time'] = 0;

                        // Set session variables and log the user in
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role_id'] = $user['role_id'];
                        insertLog($conn, $user['user_id'], 'LOGIN_SUCCESS', 'User logged in successfully.');
                        header("Location: index.php");
                        exit;
                    } else {
                        // Increment login attempts
                        $_SESSION['login_attempts']++;
                        $_SESSION['total_login_attempts']++;
                        insertLog($conn, $user['user_id'], 'LOGIN_FAIL', 'Incorrect password entered.');

                        // Check if the account should be locked
                        if ($_SESSION['total_login_attempts'] >= $max_total_attempts) {
                            // Lock the account in the database
                            $lockStmt = $conn->prepare("UPDATE `user` SET is_locked = 1 WHERE user_id = ?");
                            $lockStmt->bind_param("i", $user['user_id']);
                            $lockStmt->execute();
                            $lockStmt->close();

                            $message = "Your account has been locked. Please contact the administrator to reset it.";
                        } elseif ($_SESSION['total_login_attempts'] >= $max_warning_attempts) {
                            // Enforce cooldown after 3 failed attempts
                            $_SESSION['lockout_time'] = time();
                            $message = "You have exceeded 3 login attempts. Please try again in 60 seconds.";
                        } else {
                            $message = "Invalid username or password.";
                        }
                    }
                }
            } else {
                // Handle invalid username
                $_SESSION['login_attempts']++;
                $_SESSION['total_login_attempts']++;
                insertLog($conn, 0, 'LOGIN_FAIL', 'Username not found: ' . $username);
                if ($_SESSION['total_login_attempts'] >= $max_total_attempts) {
                    $message = "Your account has been locked. Please contact the administrator to reset it.";
                } elseif ($_SESSION['total_login_attempts'] >= $max_warning_attempts) {
                    $_SESSION['lockout_time'] = time();
                    $message = "You have exceeded 3 login attempts. Please try again in 60 seconds.";
                } else {
                    $message = "Invalid username or password.";
                }
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Pharmacy Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <script>
    // Disable password field and login button on initial load.
    window.addEventListener("DOMContentLoaded", function() {
      const usernameInput = document.getElementById("username");
      const passwordInput = document.getElementById("password");
      const loginButton = document.querySelector(".login-button");

      passwordInput.disabled = true;
      loginButton.disabled = true;

      // Enable password field when username is filled out.
      usernameInput.addEventListener("input", function() {
        if (usernameInput.value.trim() !== "") {
          passwordInput.disabled = false;
        } else {
          passwordInput.disabled = true;
          loginButton.disabled = true;
        }
      });

      // Enable login button when both fields are filled out.
      passwordInput.addEventListener("input", function() {
        if (usernameInput.value.trim() !== "" && passwordInput.value.trim() !== "") {
          loginButton.disabled = false;
        } else {
          loginButton.disabled = true;
        }
      });
    });
  </script>
</head>
<body>
  <!-- Global header remains unchanged -->
  <div class="header">
    <div class="logo">Pharmacy</div>
    <div class="header-title">User Login</div>
  </div>
  
  <div class="login-container">
    <div class="login-box">
      <h1>Login</h1>
      <?php if (!empty($message)): ?>
        <p class="login-message"><?php echo $message; ?></p>
      <?php endif; ?>
      <form method="post" action="login.php">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" name="username" id="username" required autofocus>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="login-button">Login</button>
      </form>
      <p><a href="forgot_password.php">Forgot Password?</a></p>
      <p><a href="registration.php">Register</a></p>
    </div>
  </div>
</body>
</html>