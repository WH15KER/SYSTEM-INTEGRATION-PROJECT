<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "medical_check_db";

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    
    if (!$con) {
        throw new Exception("Failed to connect: " . mysqli_connect_error());
    }
    
    // Set charset to utf8mb4 for full Unicode support
    mysqli_set_charset($con, "utf8mb4");
    
} catch (Exception $e) {
    // Log the error and display a user-friendly message
    error_log("Database connection error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}
?>