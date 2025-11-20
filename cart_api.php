<?php
// cart_api.php - Handle cart operations
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Database configuration
$serverName = "tcp:mydatabase-replica.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin",
    "PWD" => "password123!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

// Connect to database
$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Handle different actions
$action = $_GET['action'] ?? ($_POST['action'] ?? null);

if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;
}

switch ($action) {
    case 'get_cart':
        getCart($conn);
        break;
    case 'add_to_cart':
        addToCart($conn);
        break;
    case 'remove_from_cart':
        removeFromCart($conn);
        break;
    case 'update_quantity':
        updateQuantity($conn);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

sqlsrv_close($conn);

// Get user's cart
function getCart($conn) {
    $user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
    
    $sql = "SELECT c.id, c.watch_id, c.quantity, c.added_at,
                   w.name, w.brand, w.price, w.description, w.image_url
            FROM cart c
            INNER JOIN watches w ON c.watch_id = w.id
            WHERE c.user_id = ?
            ORDER BY c.added_at DESC";
    
    $params = array($user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to fetch cart']);
        return;
    }
    
    $items = array();
    $subtotal = 0;
    $count = 0;
    
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $quantity = (int)$row['quantity'];
        $price = (float)$row['price'];
        $itemTotal = $price * $quantity;
        $subtotal += $itemTotal;
        $count += $quantity;
        
        $items[] = array(
            'id' => $row['id'],
            'watch_id' => $row['watch_id'],
            'quantity' => $quantity,
            'name' => $row['name'],
            'brand' => $row['brand'],
            'price' => $price,
            'description' => $row['description'],
            'image_url' => $row['image_url'],
            'added_at' => $row['added_at'] ? $row['added_at']->format('Y-m-d H:i:s') : null
        );
    }
    
    sqlsrv_free_stmt($stmt);
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => $count,
        'subtotal' => $subtotal,
        'total' => $subtotal // Can add shipping/tax logic here
    ]);
}

// Add item to cart
function addToCart($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? $_SESSION['user_id'];
    $watch_id = $input['watch_id'] ?? null;
    $quantity = $input['quantity'] ?? 1;
    
    if (!$watch_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Watch ID is required']);
        return;
    }
    
    // Check if item already exists in cart
    $checkSql = "SELECT id, quantity FROM cart WHERE user_id = ? AND watch_id = ?";
    $checkParams = array($user_id, $watch_id);
    $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);
    
    if ($checkStmt === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        return;
    }
    
    $existing = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($checkStmt);
    
    if ($existing) {
        // Update quantity
        $newQuantity = $existing['quantity'] + $quantity;
        $updateSql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $updateParams = array($newQuantity, $existing['id']);
        $updateStmt = sqlsrv_query($conn, $updateSql, $updateParams);
        
        if ($updateStmt === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update cart']);
            return;
        }
        
        sqlsrv_free_stmt($updateStmt);
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
    } else {
        // Insert new item
        $insertSql = "INSERT INTO cart (user_id, watch_id, quantity, added_at) VALUES (?, ?, ?, GETDATE())";
        $insertParams = array($user_id, $watch_id, $quantity);
        $insertStmt = sqlsrv_query($conn, $insertSql, $insertParams);
        
        if ($insertStmt === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to add to cart']);
            return;
        }
        
        sqlsrv_free_stmt($insertStmt);
        echo json_encode(['success' => true, 'message' => 'Added to cart successfully']);
    }
}

// Remove item from cart
function removeFromCart($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? $_SESSION['user_id'];
    $watch_id = $input['watch_id'] ?? null;
    
    if (!$watch_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Watch ID is required']);
        return;
    }
    
    $sql = "DELETE FROM cart WHERE user_id = ? AND watch_id = ?";
    $params = array($user_id, $watch_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to remove from cart']);
        return;
    }
    
    sqlsrv_free_stmt($stmt);
    echo json_encode(['success' => true, 'message' => 'Removed from cart']);
}

// Update item quantity
function updateQuantity($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? $_SESSION['user_id'];
    $watch_id = $input['watch_id'] ?? null;
    $quantity = $input['quantity'] ?? null;
    
    if (!$watch_id || !$quantity || $quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        return;
    }
    
    $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND watch_id = ?";
    $params = array($quantity, $user_id, $watch_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update quantity']);
        return;
    }
    
    sqlsrv_free_stmt($stmt);
    echo json_encode(['success' => true, 'message' => 'Quantity updated']);
}
?>
