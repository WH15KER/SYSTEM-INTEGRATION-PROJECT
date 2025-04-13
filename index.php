<?php
session_start();
include("connection.php");
include("function.php");

$user_data = check_login($con);
header("Location: Home-Page.php");
die;
?>