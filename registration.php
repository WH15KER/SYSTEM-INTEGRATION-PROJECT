<?php
// registration.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    // Validate input fields.
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } else {
        // Check if the username already exists.
        $stmt = $conn->prepare("SELECT user_id FROM `user` WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Username already taken.";
        } else {
            $stmt->close();
            // Insert new user (in production, use password_hash())
            $default_role = 2; // Adjust default role_id as needed.
            $stmt = $conn->prepare("INSERT INTO `user` (username, password, role_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $username, $password, $default_role);
            if ($stmt->execute()) {
                $message = "Registration successful! You can now log in.";
            } else {
                $message = "Registration error: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Pharmacy Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Global header -->
  <div class="header">
    <div class="logo">Pharmacy</div>
    <div class="header-title">User Registration</div>
  </div>

  <div class="login-container">
    <div class="login-box">
      <h1>Register</h1>
      <?php if (!empty($message)): ?>
        <p class="login-message"><?php echo $message; ?></p>
      <?php endif; ?>
      <form method="post" action="registration.php">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" name="username" id="username" required autofocus>
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
          <label for="password">Password (min. 8 characters):</label>
          <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password:</label>
          <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        <button type="submit" class="login-button">Register</button>
      </form>
      <p><a href="login.php">Already have an account? Login here.</a></p>
    </div>
  </div>
</body>
</html>
