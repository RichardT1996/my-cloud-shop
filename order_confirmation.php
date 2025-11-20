<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_number = $_GET['order_number'] ?? '';

if (empty($order_number)) {
    header("Location: index.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_id = $_SESSION['user_id'];

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
    die("Database connection failed");
}

// Fetch order details
$sql = "SELECT * FROM orders WHERE order_number = ? AND user_id = ?";
$stmt = sqlsrv_query($conn, $sql, array($order_number, $user_id));

if ($stmt === false || !($order = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
    header("Location: index.php");
    exit;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - ShopSphere</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; min-height: 100vh; }
        .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; position: relative; }
        .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
        .header .tagline { font-size: 12px; color: #888; letter-spacing: 2px; text-transform: uppercase; }
        .welcome { position: absolute; top: 30px; right: 40px; color: #888; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
        .welcome span { color: #fff; margin-left: 5px; }
        .welcome a { color: #fff; text-decoration: none; margin-left: 15px; padding: 8px 16px; border: 1px solid #333; transition: all 0.3s ease; }
        .welcome a:hover { background: #fff; color: #000; border-color: #fff; }
        .nav { background: #111; border-bottom: 1px solid #222; padding: 0; }
        .nav ul { list-style: none; display: flex; justify-content: center; max-width: 1200px; margin: 0 auto; }
        .nav li { margin: 0; }
        .nav a { display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent; }
        .nav a:hover, .nav a.active { color: #fff; background: rgba(255,255,255,0.05); border-bottom-color: #fff; }
        .container { max-width: 800px; margin: 80px auto; padding: 0 40px; text-align: center; }
        .success-icon { font-size: 80px; color: #27ae60; margin-bottom: 30px; }
        .confirmation-box { background: #111; border: 1px solid #222; padding: 50px; margin-bottom: 30px; }
        .confirmation-box h2 { color: #fff; font-size: 2em; font-weight: 300; letter-spacing: 2px; margin-bottom: 15px; }
        .confirmation-box p { color: #888; font-size: 1.1em; margin-bottom: 30px; }
        .order-number { background: #1a1a1a; border: 1px solid #333; padding: 20px; margin: 30px 0; }
        .order-number strong { color: #27ae60; font-size: 1.5em; letter-spacing: 2px; }
        .order-details { text-align: left; margin: 30px 0; padding: 20px; background: #1a1a1a; border: 1px solid #333; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #222; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; font-size: 13px; letter-spacing: 1px; text-transform: uppercase; }
        .detail-value { color: #fff; font-size: 14px; }
        .actions { display: flex; gap: 15px; justify-content: center; margin-top: 40px; }
        .btn { display: inline-block; padding: 14px 32px; background: transparent; border: 1px solid #444; color: #fff; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; }
        .btn:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,255,255,0.15); }
        .btn-primary { border-color: #27ae60; color: #27ae60; }
        .btn-primary:hover { background: #27ae60; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ShopSphere</h1>
        <div class="tagline">Luxury Timepieces</div>
        <div class="welcome">
            Welcome, <span><?php echo htmlspecialchars($user_name); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <nav class="nav">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="catalog.php">Catalog</a></li>
            <li><a href="wishlist.php">Wishlist</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <div class="success-icon">✓</div>
        
        <div class="confirmation-box">
            <h2>Order Confirmed!</h2>
            <p>Thank you for your purchase. Your order has been successfully placed.</p>
            
            <div class="order-number">
                Order Number: <strong><?php echo htmlspecialchars($order_number); ?></strong>
            </div>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value">£<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method']))); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Status</span>
                    <span class="detail-value"><?php echo htmlspecialchars(ucfirst($order['payment_status'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Status</span>
                    <span class="detail-value"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Shipping Address</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['shipping_city']); ?></span>
                </div>
            </div>
            
            <p style="color: #666; font-size: 13px; margin-top: 30px;">
                An order confirmation email has been sent to your registered email address.
                You can track your order status from your orders page.
            </p>
        </div>
        
        <div class="actions">
            <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
            <a href="catalog.php" class="btn">Continue Shopping</a>
        </div>
    </div>
</body>
</html>
