<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$order_number = $_POST['order_number'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($order_number)) {
    echo json_encode(['success' => false, 'message' => 'Order number required']);
    exit;
}

// Database connection
require_once '../db_config.php';

try {
    $conn = getDbConnection();
    
    // Check if order exists and belongs to user
    $sql = "SELECT id, status FROM orders WHERE order_number = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$order_number, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Check if order can be cancelled
    if ($order['status'] !== 'pending' && $order['status'] !== 'processing') {
        echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
        exit;
    }
    
    // Update order status to cancelled
    $sql = "UPDATE orders SET status = 'cancelled' WHERE order_number = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$order_number, $user_id]);
    
} catch (Exception $e) {
    error_log("Cancel order error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
?>
