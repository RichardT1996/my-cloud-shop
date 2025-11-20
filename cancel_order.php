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
$serverName = "tcp:mycardiffmet1.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin",
    "PWD" => "password123!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if order exists and belongs to user
$sql = "SELECT order_id, status FROM orders WHERE order_number = ? AND user_id = ?";
$stmt = sqlsrv_query($conn, $sql, array($order_number, $user_id));

if ($stmt === false || !($order = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

sqlsrv_free_stmt($stmt);

// Check if order can be cancelled
if ($order['status'] !== 'pending' && $order['status'] !== 'processing') {
    sqlsrv_close($conn);
    echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
    exit;
}

// Update order status to cancelled
$sql = "UPDATE orders SET status = 'cancelled' WHERE order_number = ? AND user_id = ?";
$stmt = sqlsrv_query($conn, $sql, array($order_number, $user_id));

if ($stmt === false) {
    sqlsrv_close($conn);
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
    exit;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
?>
