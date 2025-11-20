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

// Update order status
$sql = "UPDATE orders SET status = ? WHERE order_number = ?";
$stmt = sqlsrv_query($conn, $sql, array($new_status, $order_number));

if ($stmt === false) {
    sqlsrv_close($conn);
    echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    exit;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
?>
