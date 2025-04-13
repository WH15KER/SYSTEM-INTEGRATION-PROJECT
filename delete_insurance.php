<?php
session_start();
require_once('connection.php');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header("HTTP/1.1 405 Method Not Allowed");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

$insuranceId = $_GET['id'] ?? null;
if (!$insuranceId) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Insurance ID is required']);
    exit;
}

// Verify that the insurance belongs to the current user
$verifyQuery = "SELECT user_id FROM insurance WHERE insurance_id = ?";
$stmt = mysqli_prepare($con, $verifyQuery);
mysqli_stmt_bind_param($stmt, "s", $insuranceId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$insurance = mysqli_fetch_assoc($result);

if (!$insurance || $insurance['user_id'] !== $_SESSION['user_id']) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this insurance']);
    exit;
}

// Delete the insurance
$deleteQuery = "DELETE FROM insurance WHERE insurance_id = ?";
$stmt = mysqli_prepare($con, $deleteQuery);
mysqli_stmt_bind_param($stmt, "s", $insuranceId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete insurance']);
}
?>