<?php
// connection.php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'medical_check_db'; // Change to your database name

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection with error handling
$con = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$con) {
    error_log("Connection failed: " . mysqli_connect_error());
    die("Connection failed. Please try again later. Error: " . mysqli_connect_error());
}

// Set charset to utf8mb4 for full Unicode support
mysqli_set_charset($con, 'utf8mb4');
?>