<?php
require_once('connection.php');
require_once('function.php');

// Check if user is logged in
$user_data = check_login($con);
$user_id = $user_data['user_id'];

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $data = [];
    foreach ($_POST as $key => $value) {
        $data[$key] = sanitize_input($con, $value);
    }
    
    // Get record type from URL parameter
    $record_type = isset($_GET['type']) ? sanitize_input($con, $_GET['type']) : '';
    
    // Add record to database
    $response = add_medical_record($con, $user_id, $data, $record_type);
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>