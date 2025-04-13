<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "medical_check_db";

$con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$con) {
    die("Failed to connect: " . mysqli_connect_error());
}
?>