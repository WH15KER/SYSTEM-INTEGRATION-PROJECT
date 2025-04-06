<?php
// db.php
$host = 'localhost';
$db   = 'macpharmacy_db';
$user = 'root';
$pass = '';

// Create connection as $conn instead of $mysqli
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}