<?php
session_start();
require_once 'connection.php';
require_once 'function.php';
require_once 'vendor/autoload.php';
use OTPHP\TOTP;

$user_data = check_login($con);

// Generate a new secret if one doesn't exist
$query = "SELECT google_auth_secret FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$secret = $row['google_auth_secret'];

if (empty($secret)) {
    $secret = TOTP::create()->getSecret();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_2fa'])) {
        $totp = TOTP::create($secret);
        if ($totp->verify($_POST['auth_code'])) {
            // Save the secret to the database
            $update_query = "UPDATE users SET google_auth_secret = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($stmt, "ss", $secret, $user_data['user_id']);
            mysqli_stmt_execute($stmt);
            
            $_SESSION['success'] = "Two-factor authentication has been enabled successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $error = "Invalid verification code. Please try again.";
        }
    } elseif (isset($_POST['disable_2fa'])) {
        $update_query = "UPDATE users SET google_auth_secret = NULL WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
        mysqli_stmt_execute($stmt);
        
        $_SESSION['success'] = "Two-factor authentication has been disabled.";
        header("Location: profile.php");
        exit();
    }
}

// Generate QR code URL
$totp = TOTP::create($secret);
$totp->setLabel('MedicalChecks (' . $user_data['email'] . ')');
$qrCodeUrl = $totp->getQrCodeUri(
    'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=200x200&ecc=M',
    '[DATA]'
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup Two-Factor Authentication</title>
    <link rel="stylesheet" href="Style/Payment-Method-Page.css">
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <h2>Two-Factor Authentication Setup</h2>
        
        <?php if (empty($row['google_auth_secret'])): ?>
            <p>Scan this QR code with Google Authenticator:</p>
            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
            
            <p>Or enter this secret key manually:</p>
            <div style="background: #f5f5f5; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <code><?php echo chunk_split($secret, 4, ' '); ?></code>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="auth_code">Enter 6-digit code from app:</label>
                    <input type="text" id="auth_code" name="auth_code" required>
                </div>
                <button type="submit" name="enable_2fa" class="pay-button">Enable 2FA</button>
            </form>
        <?php else: ?>
            <p>Two-factor authentication is currently enabled for your account.</p>
            <form method="POST">
                <button type="submit" name="disable_2fa" class="pay-button" style="background-color: #e74c3c;">Disable 2FA</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>