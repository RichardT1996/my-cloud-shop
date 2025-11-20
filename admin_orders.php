<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// Check if user is admin
if ($user_email !== 'admin@gmail.com') {
    header("Location: index.php");
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
    die("Database connection failed");
}

// Fetch all orders with user information
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o 
        INNER JOIN shopusers u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$stmt = sqlsrv_query($conn, $sql);

$orders = array();
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $orders[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}

// Get order statistics
$stats_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(total_amount) as total_revenue
    FROM orders";
$stats_stmt = sqlsrv_query($conn, $stats_sql);
$stats = sqlsrv_fetch_array($stats_stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stats_stmt);

sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; min-height: 100vh; }
        .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; position: relative; }
        .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
        .header .tagline { font-size: 12px; color: #e74c3c; letter-spacing: 2px; text-transform: uppercase; }
        .welcome { position: absolute; top: 30px; right: 40px; color: #888; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
        .welcome span { color: #fff; margin-left: 5px; }
        .welcome a { color: #fff; text-decoration: none; margin-left: 15px; padding: 8px 16px; border: 1px solid #333; transition: all 0.3s ease; }
        .welcome a:hover { background: #fff; color: #000; border-color: #fff; }
        .nav { background: #111; border-bottom: 1px solid #222; padding: 0; }
        .nav ul { list-style: none; display: flex; justify-content: center; max-width: 1200px; margin: 0 auto; }
        .nav li { margin: 0; }
        .nav a { display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent; }
        .nav a:hover, .nav a.active { color: #fff; background: rgba(255,255,255,0.05); border-bottom-color: #fff; }
        .container { max-width: 1400px; margin: 60px auto; padding: 0 40px; }
        .page-title { font-size: 2.5em; font-weight: 300; letter-spacing: 3px; margin-bottom: 15px; }
        .page-subtitle { color: #888; margin-bottom: 40px; font-size: 13px; letter-spacing: 1px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 50px; }
        .stat-card { background: #111; border: 1px solid #222; padding: 25px; text-align: center; }
        .stat-value { font-size: 2.5em; font-weight: 300; color: #27ae60; margin-bottom: 10px; word-break: break-word; }
        .stat-label { color: #666; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
        .filters { display: flex; gap: 15px; margin-bottom: 30px; align-items: center; }
        .filter-label { color: #888; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
        .filter-select { background: #111; border: 1px solid #333; color: #fff; padding: 12px 20px; font-size: 12px; cursor: pointer; transition: all 0.3s ease; }
        .filter-select:hover { border-color: #555; }
        .orders-table { width: 100%; border-collapse: collapse; background: #111; border: 1px solid #222; }
        .orders-table th { background: #1a1a1a; color: #888; padding: 20px; text-align: left; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; font-weight: 400; border-bottom: 1px solid #222; }
        .orders-table td { padding: 20px; border-bottom: 1px solid #222; color: #ccc; font-size: 13px; }
        .orders-table tr:hover { background: rgba(255,255,255,0.02); }
        .order-number { color: #fff; font-weight: 400; letter-spacing: 1px; }
        .customer-info { }
        .customer-name { color: #fff; margin-bottom: 3px; }
        .customer-email { color: #666; font-size: 11px; }
        .order-status { display: inline-block; padding: 6px 14px; border-radius: 3px; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; font-weight: 600; }
        .status-pending { background: rgba(241, 196, 15, 0.2); color: #f1c40f; border: 1px solid #f1c40f; }
        .status-processing { background: rgba(52, 152, 219, 0.2); color: #3498db; border: 1px solid #3498db; }
        .status-shipped { background: rgba(155, 89, 182, 0.2); color: #9b59b6; border: 1px solid #9b59b6; }
        .status-delivered { background: rgba(39, 174, 96, 0.2); color: #27ae60; border: 1px solid #27ae60; }
        .status-cancelled { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
        .btn { display: inline-block; padding: 8px 16px; background: transparent; border: 1px solid #444; color: #fff; text-decoration: none; font-size: 9px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; cursor: pointer; margin-right: 5px; }
        .btn:hover { background: #fff; color: #000; border-color: #fff; }
        .btn-sm { padding: 6px 12px; font-size: 8px; }
        .status-select { background: #1a1a1a; border: 1px solid #333; color: #fff; padding: 8px 12px; font-size: 11px; cursor: pointer; border-radius: 3px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); overflow: auto; }
        .modal-content { background: #111; margin: 3% auto; padding: 40px; max-width: 900px; border: 1px solid #333; position: relative; }
        .close { position: absolute; right: 25px; top: 25px; color: #888; font-size: 30px; font-weight: 300; cursor: pointer; transition: color 0.3s; }
        .close:hover { color: #fff; }
        .modal-title { font-size: 1.8em; font-weight: 300; letter-spacing: 2px; margin-bottom: 30px; }
        .detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; margin: 30px 0; }
        .detail-section { }
        .detail-section h3 { color: #888; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; font-weight: 400; margin-bottom: 15px; border-bottom: 1px solid #222; padding-bottom: 10px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .detail-label { color: #666; font-size: 11px; }
        .detail-value { color: #fff; font-size: 13px; }
        .order-items { margin-top: 30px; }
        .item-row { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #1a1a1a; border: 1px solid #222; margin-bottom: 10px; }
        .item-info { flex: 1; }
        .item-brand { color: #888; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 5px; }
        .item-model { color: #fff; font-size: 13px; margin-bottom: 3px; }
        .item-qty { color: #666; font-size: 11px; }
        .item-price { color: #fff; font-size: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ShopSphere</h1>
        <div class="tagline">Admin Dashboard</div>
        <div class="welcome">
            Admin, <span><?php echo htmlspecialchars($user_name); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <nav class="nav">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="catalog.php">Catalog</a></li>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="view_users.php">Users</a></li>
            <li><a href="admin_orders.php" class="active">Orders</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h1 class="page-title">Order Management</h1>
        <p class="page-subtitle">View and manage all customer orders</p>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #f1c40f;"><?php echo $stats['pending_orders'] ?? 0; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #3498db;"><?php echo $stats['processing_orders'] ?? 0; ?></div>
                <div class="stat-label">Processing</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #9b59b6;"><?php echo $stats['shipped_orders'] ?? 0; ?></div>
                <div class="stat-label">Shipped</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #27ae60;"><?php echo $stats['delivered_orders'] ?? 0; ?></div>
                <div class="stat-label">Delivered</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">£<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        
        <div class="filters">
            <span class="filter-label">Filter by Status:</span>
            <select class="filter-select" id="statusFilter" onchange="filterOrders()">
                <option value="all">All Orders</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <?php foreach ($orders as $order): 
                    $order_date = $order['created_at'];
                    $formatted_date = $order_date->format('d M Y');
                    $status_class = 'status-' . strtolower($order['status']);
                ?>
                    <tr class="order-row" data-status="<?php echo htmlspecialchars($order['status']); ?>">
                        <td class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td class="customer-info">
                            <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            <div class="customer-email"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                        </td>
                        <td><?php echo $formatted_date; ?></td>
                        <td><?php echo $order['item_count']; ?></td>
                        <td>£<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <select class="status-select <?php echo $status_class; ?>" onchange="updateOrderStatus('<?php echo htmlspecialchars($order['order_number']); ?>', this.value)">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-sm" onclick="viewOrderDetails('<?php echo htmlspecialchars($order['order_number']); ?>')">View Details</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
        function filterOrders() {
            const filter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('.order-row');
            
            rows.forEach(row => {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        function updateOrderStatus(orderNumber, newStatus) {
            if (!confirm(`Update order ${orderNumber} to ${newStatus}?`)) {
                location.reload(); // Reset the select if cancelled
                return;
            }
            
            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_number=${orderNumber}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order status updated successfully');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update order status');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating order status');
                location.reload();
            });
        }
        
        function viewOrderDetails(orderNumber) {
            fetch(`get_admin_order_details.php?order_number=${orderNumber}`)
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
            let itemsHtml = '<h3 style="color: #888; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; margin: 30px 0 15px;">Order Items</h3><div class="order-items">';
            items.forEach(item => {
                itemsHtml += `
                    <div class="item-row">
                        <div class="item-info">
                            <div class="item-brand">${item.watch_brand}</div>
                            <div class="item-model">${item.watch_name}</div>
                            <div class="item-qty">Quantity: ${item.quantity} × £${parseFloat(item.watch_price).toFixed(2)}</div>
                        </div>
                        <div class="item-price">£${parseFloat(item.subtotal).toFixed(2)}</div>
                    </div>
                `;
            });
            itemsHtml += '</div>';
            
            const detailsHtml = `
                <div class="detail-grid">
                    <div class="detail-section">
                        <h3>Order Information</h3>
                        <div class="detail-row">
                            <span class="detail-label">Order Number</span>
                            <span class="detail-value">${order.order_number}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">${order.status.toUpperCase()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Status</span>
                            <span class="detail-value">${order.payment_status.toUpperCase()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Method</span>
                            <span class="detail-value">${order.payment_method.replace('_', ' ').toUpperCase()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Amount</span>
                            <span class="detail-value">£${parseFloat(order.total_amount).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="detail-section">
                        <h3>Shipping Information</h3>
                        <div class="detail-row">
                            <span class="detail-label">Name</span>
                            <span class="detail-value">${order.shipping_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Address</span>
                            <span class="detail-value">${order.shipping_address}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">City</span>
                            <span class="detail-value">${order.shipping_city}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Postal Code</span>
                            <span class="detail-value">${order.shipping_postal_code}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone</span>
                            <span class="detail-value">${order.shipping_phone}</span>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = detailsHtml + itemsHtml;
            document.getElementById('orderModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
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
