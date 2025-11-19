<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized - Please login first'
    ]);
    exit;
}

header('Content-Type: application/json');

// Database connection
$server = "tcp:mycardiffmet1.database.windows.net,1433";
$database = "myDatabase";
$username = "myadmin";
$password = "password123!";

try {
    $conn = new PDO("sqlsrv:server=$server;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// GET - Retrieve user's wishlist
if ($method === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                w.id as wishlist_id,
                w.added_at,
                wt.id as watch_id,
                wt.name,
                wt.brand,
                wt.price,
                wt.description,
                wt.image_url
            FROM wishlist w
            INNER JOIN watches wt ON w.watch_id = wt.id
            WHERE w.user_id = :user_id
            ORDER BY w.added_at DESC
        ");
        
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => count($wishlist_items),
            'items' => $wishlist_items
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to retrieve wishlist'
        ]);
    }
}

// POST - Add item to wishlist
else if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['watch_id']) || !is_numeric($data['watch_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid watch_id'
        ]);
        exit;
    }
    
    $watch_id = intval($data['watch_id']);
    
    try {
        // Check if watch exists
        $stmt = $conn->prepare("SELECT id FROM watches WHERE id = :watch_id");
        $stmt->bindParam(':watch_id', $watch_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Watch not found'
            ]);
            exit;
        }
        
        // Add to wishlist (or ignore if already exists due to UNIQUE constraint)
        $stmt = $conn->prepare("
            INSERT INTO wishlist (user_id, watch_id) 
            VALUES (:user_id, :watch_id)
        ");
        
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':watch_id', $watch_id, PDO::PARAM_INT);
        $stmt->execute();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Added to wishlist'
        ]);
        
    } catch(PDOException $e) {
        // Check if it's a duplicate entry error
        if (strpos($e->getMessage(), 'UNIQUE') !== false) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Item already in wishlist'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to add to wishlist'
            ]);
        }
    }
}

// DELETE - Remove item from wishlist
else if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['watch_id']) || !is_numeric($data['watch_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid watch_id'
        ]);
        exit;
    }
    
    $watch_id = intval($data['watch_id']);
    
    try {
        $stmt = $conn->prepare("
            DELETE FROM wishlist 
            WHERE user_id = :user_id AND watch_id = :watch_id
        ");
        
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':watch_id', $watch_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Removed from wishlist'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Item not found in wishlist'
            ]);
        }
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to remove from wishlist'
        ]);
    }
}

else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}

$conn = null;
?>
