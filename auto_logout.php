<?php
// auto_logout.php
session_start();
$inactivityLimit = 600; // 10 minutes

if (isset($_SESSION['LAST_ACTIVITY'])) {
    if (time() - $_SESSION['LAST_ACTIVITY'] > $inactivityLimit) {
        session_unset();
        session_destroy();
        header("Location: login.php?message=Session expired due to inactivity.");
        exit;
    }
}
$_SESSION['LAST_ACTIVITY'] = time();
?>
