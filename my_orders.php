<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

// Fetch user's orders with order items
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
$stmt = sqlsrv_query($conn, $sql, array($user_id));

$orders = array();
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $orders[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}

sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - ShopSphere</title>
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
        .container { max-width: 1200px; margin: 60px auto; padding: 0 40px; }
        .page-title { font-size: 2.5em; font-weight: 300; letter-spacing: 3px; margin-bottom: 15px; text-align: center; }
        .page-subtitle { color: #888; text-align: center; margin-bottom: 50px; font-size: 13px; letter-spacing: 1px; }
        .orders-grid { display: grid; gap: 25px; }
        .order-card { background: #111; border: 1px solid #222; padding: 30px; transition: all 0.3s ease; }
        .order-card:hover { border-color: #333; box-shadow: 0 8px 30px rgba(0,0,0,0.5); }
        .order-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #222; }
        .order-number { font-size: 16px; color: #fff; letter-spacing: 2px; font-weight: 400; }
        .order-date { color: #666; font-size: 12px; letter-spacing: 1px; }
        .order-status { display: inline-block; padding: 6px 14px; border-radius: 3px; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; font-weight: 600; }
        .status-pending { background: rgba(241, 196, 15, 0.2); color: #f1c40f; border: 1px solid #f1c40f; }
        .status-processing { background: rgba(52, 152, 219, 0.2); color: #3498db; border: 1px solid #3498db; }
        .status-shipped { background: rgba(155, 89, 182, 0.2); color: #9b59b6; border: 1px solid #9b59b6; }
        .status-delivered { background: rgba(39, 174, 96, 0.2); color: #27ae60; border: 1px solid #27ae60; }
        .status-cancelled { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
        .order-details { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0; }
        .detail-item { }
        .detail-label { color: #666; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 5px; }
        .detail-value { color: #fff; font-size: 14px; }
        .order-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn { display: inline-block; padding: 12px 24px; background: transparent; border: 1px solid #444; color: #fff; text-decoration: none; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; cursor: pointer; }
        .btn:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-2px); }
        .btn-sm { padding: 8px 16px; font-size: 9px; }
        .empty-state { text-align: center; padding: 100px 20px; }
        .empty-state-icon { font-size: 80px; margin-bottom: 30px; opacity: 0.3; }
        .empty-state h2 { font-size: 2em; font-weight: 300; letter-spacing: 2px; margin-bottom: 15px; color: #666; }
        .empty-state p { color: #666; margin-bottom: 30px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); overflow: auto; }
        .modal-content { background: #111; margin: 5% auto; padding: 40px; max-width: 800px; border: 1px solid #333; position: relative; }
        .close { position: absolute; right: 25px; top: 25px; color: #888; font-size: 30px; font-weight: 300; cursor: pointer; transition: color 0.3s; }
        .close:hover { color: #fff; }
        .modal-title { font-size: 1.8em; font-weight: 300; letter-spacing: 2px; margin-bottom: 30px; }
        .order-items { margin-top: 30px; }
        .item-row { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid #222; }
        .item-row:last-child { border-bottom: none; }
        .item-info { flex: 1; }
        .item-brand { color: #888; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 5px; }
        .item-model { color: #fff; font-size: 14px; margin-bottom: 5px; }
        .item-qty { color: #666; font-size: 12px; }
        .item-price { color: #fff; font-size: 16px; font-weight: 400; }
        .order-summary { margin-top: 30px; padding-top: 20px; border-top: 2px solid #333; }
        .summary-row { display: flex; justify-content: space-between; padding: 10px 0; }
        .summary-total { font-size: 18px; font-weight: 400; color: #fff; padding-top: 15px; border-top: 1px solid #333; }
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
            <li><a href="my_orders.php" class="active">My Orders</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h1 class="page-title">My Orders</h1>
        <p class="page-subtitle">Track and manage your orders</p>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders. Start shopping to see your orders here.</p>
                <a href="catalog.php" class="btn">Browse Catalog</a>
            </div>
        <?php else: ?>
            <div class="orders-grid">
                <?php foreach ($orders as $order): 
                    $order_date = $order['created_at'];
                    $formatted_date = $order_date->format('d M Y, H:i');
                    $status_class = 'status-' . strtolower($order['status']);
                ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                                <div class="order-date"><?php echo $formatted_date; ?></div>
                            </div>
                            <span class="order-status <?php echo $status_class; ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-item">
                                <div class="detail-label">Total Amount</div>
                                <div class="detail-value">£<?php echo number_format($order['total_amount'], 2); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Items</div>
                                <div class="detail-value"><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] > 1 ? 's' : ''; ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Payment Status</div>
                                <div class="detail-value"><?php echo htmlspecialchars(ucfirst($order['payment_status'])); ?></div>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <button class="btn btn-sm" onclick="viewOrderDetails('<?php echo htmlspecialchars($order['order_number']); ?>')">View Details</button>
                            <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
                                <button class="btn btn-sm" style="border-color: #e74c3c; color: #e74c3c;" onclick="cancelOrder('<?php echo htmlspecialchars($order['order_number']); ?>')">Cancel Order</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="modal-title">Order Details</h2>
            <div id="modalContent"></div>
        </div>
    </div>
    
    <script>
        function viewOrderDetails(orderNumber) {
            fetch(`get_order_details.php?order_number=${orderNumber}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.order, data.items);
                    } else {
                        alert('Failed to load order details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading order details');
                });
        }
        
        function displayOrderDetails(order, items) {
            let itemsHtml = '<div class="order-items">';
            items.forEach(item => {
                itemsHtml += `
                    <div class="item-row">
                        <div class="item-info">
                            <div class="item-brand">${item.brand}</div>
                            <div class="item-model">${item.model}</div>
                            <div class="item-qty">Quantity: ${item.quantity}</div>
                        </div>
                        <div class="item-price">£${parseFloat(item.subtotal).toFixed(2)}</div>
                    </div>
                `;
            });
            itemsHtml += '</div>';
            
            const shippingInfo = `
                <div class="order-details" style="margin-top: 30px;">
                    <div class="detail-item">
                        <div class="detail-label">Shipping Address</div>
                        <div class="detail-value">
                            ${order.shipping_name}<br>
                            ${order.shipping_address}<br>
                            ${order.shipping_city}, ${order.shipping_postal_code}<br>
                            ${order.shipping_phone}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value">${order.payment_method.replace('_', ' ').toUpperCase()}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Order Status</div>
                        <div class="detail-value">${order.status.toUpperCase()}</div>
                    </div>
                </div>
            `;
            
            const summary = `
                <div class="order-summary">
                    <div class="summary-row summary-total">
                        <span>Total Amount</span>
                        <span>£${parseFloat(order.total_amount).toFixed(2)}</span>
                    </div>
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = itemsHtml + shippingInfo + summary;
            document.getElementById('orderModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        
        function cancelOrder(orderNumber) {
            if (!confirm('Are you sure you want to cancel this order?')) {
                return;
            }
            
            fetch('cancel_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_number=${orderNumber}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order cancelled successfully');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to cancel order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error cancelling order');
            });
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
