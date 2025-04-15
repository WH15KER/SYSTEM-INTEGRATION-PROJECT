<?php
// Admin Check login
function check_admin_login($con) {
    if (isset($_SESSION['admin_id'])) {
        $id = $_SESSION['admin_id'];
        $query = "SELECT * FROM admin_users WHERE admin_id = ? LIMIT 1";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    
    header("Location: Admin-Login-Page.php");
    exit;
}

function verify_admin_credentials($con, $username, $password) {
    $query = "SELECT * FROM admin_users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin_data = mysqli_fetch_assoc($result);
        
        // Verify the password (plain text comparison as shown in your DB)
        if ($password === $admin_data['Password']) {
            return $admin_data;
        }
    }
    
    return false;
}
?>