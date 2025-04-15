<?php
// admin-function.php
function authenticateAdmin($username, $password) {
    // Hardcoded admin credentials
    $admin_email = 'admin123@gmail.com';
    $admin_password = 'admin123';
    
    return ($username === $admin_email && $password === $admin_password);
}
?>