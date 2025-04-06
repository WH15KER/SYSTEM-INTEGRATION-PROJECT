<?php
// forgot_password.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    if (empty($username)) {
        $message = "Please enter your username.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM `user` WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            $_SESSION['reset_user'] = $user_id;
            header("Location: reset_password.php");
            exit;
        } else {
            $message = "Username not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - Pharmacy Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="header">
    <div class="logo">Pharmacy</div>
    <div class="header-title">Forgot Password</div>
  </div>
  <div class="login-container">
    <div class="login-box">
      <h1>Forgot Password</h1>
      <?php if (!empty($message)): ?>
        <p class="login-message"><?php echo $message; ?></p>
      <?php endif; ?>
      <form method="post" action="forgot_password.php">
        <div class="form-group">
          <label for="username">Enter your username:</label>
          <input type="text" name="username" id="username" required>
        </div>
        <button type="submit" class="login-button">Submit</button>
      </form>
      <p><a href="login.php">Back to Login</a></p>
    </div>
  </div>
</body>
</html>
