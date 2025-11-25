<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_email = $_SESSION['user_email'] ?? '';

// Check if user is admin
if ($user_email !== 'admin@gmail.com') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$order_number = $_POST['order_number'] ?? '';
$new_status = $_POST['status'] ?? '';

if (empty($order_number) || empty($new_status)) {
    echo json_encode(['success' => false, 'message' => 'Order number and status required']);
    exit;
}

// Validate status
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Database connection
require_once '../db_config.php';

try {
    $conn = getDbConnection();
    
    // Update order status
    $sql = "UPDATE orders SET status = ? WHERE order_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$new_status, $order_number]);
    
} catch (Exception $e) {
    error_log("Update order status error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
?>
