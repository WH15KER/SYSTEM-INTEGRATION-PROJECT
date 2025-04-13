<?php
require_once('connection.php');
require_once('function.php');

header('Content-Type: application/json');

if (!isset($_GET['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID not provided']);
    exit;
}

$item_id = $_GET['item_id'];
$result = get_inventory_item_details($con, $item_id);
echo json_encode($result);
?>