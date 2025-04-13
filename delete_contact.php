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

$contactId = $_GET['id'] ?? null;
if (!$contactId) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Contact ID is required']);
    exit;
}

// Verify that the contact belongs to the current user
$verifyQuery = "SELECT user_id FROM emergency_contacts WHERE contact_id = ?";
$stmt = mysqli_prepare($con, $verifyQuery);
mysqli_stmt_bind_param($stmt, "s", $contactId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$contact = mysqli_fetch_assoc($result);

if (!$contact || $contact['user_id'] !== $_SESSION['user_id']) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this contact']);
    exit;
}

// Delete the contact
$deleteQuery = "DELETE FROM emergency_contacts WHERE contact_id = ?";
$stmt = mysqli_prepare($con, $deleteQuery);
mysqli_stmt_bind_param($stmt, "s", $contactId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete contact']);
}
?>