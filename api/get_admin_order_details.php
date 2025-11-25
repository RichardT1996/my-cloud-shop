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

$order_number = $_GET['order_number'] ?? '';

if (empty($order_number)) {
    echo json_encode(['success' => false, 'message' => 'Order number required']);
    exit;
}

// Database connection
require_once '../db_config.php';

try {
    $conn = getDbConnection();
    
    // Fetch order
    $sql = "SELECT * FROM orders WHERE order_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$order_number]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Fetch order items
    $sql = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$order['id']]);
    $items = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Get admin order details error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);
?>
