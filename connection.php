<?php
// connection.php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'medical_check_db';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$con = mysqli_connect($host, $user, $password, $database);

if (!$con) {
    error_log("Connection failed: " . mysqli_connect_error());
    die("Connection failed. Please try again later. Error: " . mysqli_connect_error());
}

mysqli_set_charset($con, 'utf8mb4');
?>