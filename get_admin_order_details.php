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
$serverName = "tcp:shopspshere-dbserver.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "shopspheredb",
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

// Fetch order
$sql = "SELECT * FROM orders WHERE order_number = ?";
$stmt = sqlsrv_query($conn, $sql, array($order_number));

if ($stmt === false || !($order = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
    sqlsrv_close($conn);
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

sqlsrv_free_stmt($stmt);

// Fetch order items
$sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = sqlsrv_query($conn, $sql, array($order['id']));

$items = array();
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}

sqlsrv_close($conn);

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);
?>
