<?php
// reset_password.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';

if (!isset($_SESSION['reset_user'])) {
    header("Location: forgot_password.php");
    exit;
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $message = "Both fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
    } elseif (strlen($newPassword) < 8) {
        $message = "Password must be at least 8 characters long.";
    } else {
        $user_id = $_SESSION['reset_user'];
        $stmt = $conn->prepare("UPDATE `user` SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newPassword, $user_id);
        if ($stmt->execute()) {
            $message = "Password updated successfully. Please <a href='login.php'>login</a>.";
            unset($_SESSION['reset_user']);
        } else {
            $message = "Error updating password: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - Pharmacy Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="header">
    <div class="logo">Pharmacy</div>
    <div class="header-title">Reset Password</div>
  </div>
  <div class="login-container">
    <div class="login-box">
      <h1>Reset Password</h1>
      <?php if (!empty($message)): ?>
        <p class="login-message"><?php echo $message; ?></p>
      <?php endif; ?>
      <form method="post" action="reset_password.php">
        <div class="form-group">
          <label for="new_password">New Password:</label>
          <input type="password" name="new_password" id="new_password" required>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm New Password:</label>
          <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        <button type="submit" class="login-button">Update Password</button>
      </form>
      <p><a href="login.php">Back to Login</a></p>
    </div>
  </div>
</body>
</html>
