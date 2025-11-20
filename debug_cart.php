<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$user_id = $_SESSION['user_id'];

echo "<h2>Cart Debug Info</h2>";
echo "<p>User ID: $user_id</p>";
echo "<p>User Name: " . ($_SESSION['user_name'] ?? 'N/A') . "</p>";

$serverName = "tcp:mydatabase-replica.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin",
    "PWD" => "password123!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    echo "<p style='color:red;'>❌ Database connection failed</p>";
    echo "<pre>" . print_r(sqlsrv_errors(), true) . "</pre>";
    exit;
}

echo "<p style='color:green;'>✅ Database connected</p>";

// Check if cart table exists
$checkTable = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'cart'";
$stmt = sqlsrv_query($conn, $checkTable);
if ($stmt) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row['count'] > 0) {
        echo "<p style='color:green;'>✅ Cart table exists</p>";
    } else {
        echo "<p style='color:red;'>❌ Cart table does NOT exist</p>";
    }
    sqlsrv_free_stmt($stmt);
}

// Check cart items for this user
$sql = "SELECT c.*, w.name, w.brand FROM cart c LEFT JOIN watches w ON c.watch_id = w.id WHERE c.user_id = ?";
$stmt = sqlsrv_query($conn, $sql, array($user_id));

if ($stmt === false) {
    echo "<p style='color:red;'>❌ Query failed</p>";
    echo "<pre>" . print_r(sqlsrv_errors(), true) . "</pre>";
} else {
    $count = 0;
    echo "<h3>Cart Items:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Watch ID</th><th>Watch Name</th><th>Brand</th><th>Quantity</th><th>Added At</th></tr>";
    
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $count++;
        $added = $row['added_at'] ? $row['added_at']->format('Y-m-d H:i:s') : 'N/A';
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['watch_id'] . "</td>";
        echo "<td>" . ($row['name'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['brand'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>" . $added . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($count === 0) {
        echo "<p>No items in cart</p>";
    } else {
        echo "<p><strong>Total items: $count</strong></p>";
    }
    
    sqlsrv_free_stmt($stmt);
}

// Check all cart items (regardless of user)
echo "<h3>All Cart Items (All Users):</h3>";
$allSql = "SELECT * FROM cart";
$allStmt = sqlsrv_query($conn, $allSql);
if ($allStmt) {
    $total = 0;
    while ($row = sqlsrv_fetch_array($allStmt, SQLSRV_FETCH_ASSOC)) {
        $total++;
    }
    echo "<p>Total cart items in database: $total</p>";
    sqlsrv_free_stmt($allStmt);
}

sqlsrv_close($conn);

echo "<hr>";
echo "<p><a href='catalog.php'>← Back to Catalog</a> | <a href='cart.php'>View Cart</a></p>";
?>
