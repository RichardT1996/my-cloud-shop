<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_id = $_SESSION['user_id'];

// Database connection
$serverName = "tcp:mydatabase-replica.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin",
    "PWD" => "password123!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

$message = '';
$error = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    
    if ($conn) {
        $action = $_POST['action'] ?? '';
        $watch_id = $_POST['watch_id'] ?? 0;
        
        if ($action === 'remove' && $watch_id) {
            $sql = "DELETE FROM cart WHERE user_id = ? AND watch_id = ?";
            $stmt = sqlsrv_query($conn, $sql, array($user_id, $watch_id));
            if ($stmt) {
                $message = 'Item removed from cart';
                sqlsrv_free_stmt($stmt);
            } else {
                $error = 'Failed to remove item';
            }
        } elseif ($action === 'update_quantity' && $watch_id) {
            $quantity = (int)($_POST['quantity'] ?? 1);
            if ($quantity < 1) {
                // Remove if quantity is 0 or less
                $sql = "DELETE FROM cart WHERE user_id = ? AND watch_id = ?";
                $stmt = sqlsrv_query($conn, $sql, array($user_id, $watch_id));
                if ($stmt) {
                    $message = 'Item removed from cart';
                    sqlsrv_free_stmt($stmt);
                }
            } else {
                $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND watch_id = ?";
                $stmt = sqlsrv_query($conn, $sql, array($quantity, $user_id, $watch_id));
                if ($stmt) {
                    $message = 'Quantity updated';
                    sqlsrv_free_stmt($stmt);
                } else {
                    $error = 'Failed to update quantity';
                }
            }
        }
        
        sqlsrv_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ShopSphere</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; min-height: 100vh; }
        .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; }
        .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
        .header p { font-size: 0.9em; color: #999; letter-spacing: 2px; text-transform: uppercase; font-weight: 300; }
        .user-bar { background: #111; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #222; }
        .user-bar .user-info { font-size: 13px; color: #999; font-weight: 300; }
        .user-bar .user-info strong { color: #fff; font-weight: 400; }
        .user-bar a { color: #fff; text-decoration: none; padding: 10px 24px; background: transparent; border: 1px solid #444; border-radius: 0; font-size: 11px; font-weight: 400; transition: all 0.3s ease; margin-left: 10px; letter-spacing: 1.5px; text-transform: uppercase; }
        .user-bar a:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(255,255,255,0.1); }
        .container { max-width: 1400px; margin: 60px auto; padding: 0 40px; }
        .cart-header { text-align: center; margin-bottom: 60px; padding-bottom: 30px; border-bottom: 1px solid #222; }
        .cart-header h2 { color: #fff; margin-bottom: 15px; font-size: 2.5em; font-weight: 300; letter-spacing: 3px; text-transform: uppercase; }
        .cart-header p { color: #888; font-size: 1em; letter-spacing: 1px; font-weight: 300; }
        .cart-content { display: grid; grid-template-columns: 1fr 400px; gap: 40px; }
        .cart-items { background: #111; border: 1px solid #222; }
        .cart-item { display: grid; grid-template-columns: 150px 1fr 120px 100px 50px; gap: 20px; padding: 30px; border-bottom: 1px solid #222; align-items: center; }
        .cart-item:last-child { border-bottom: none; }
        .item-image { width: 150px; height: 150px; background: #1a1a1a; display: flex; align-items: center; justify-content: center; padding: 15px; }
        .item-image img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .item-details { flex: 1; }
        .item-brand { color: #888; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; font-weight: 400; }
        .item-name { font-size: 18px; color: #fff; margin-bottom: 10px; font-weight: 400; letter-spacing: 0.5px; }
        .item-description { color: #666; font-size: 13px; line-height: 1.6; font-weight: 300; }
        .item-price { font-size: 20px; color: #fff; font-weight: 300; letter-spacing: 1px; text-align: center; }
        .item-quantity { display: flex; align-items: center; justify-content: center; gap: 10px; }
        .qty-btn { background: transparent; border: 1px solid #444; color: #fff; width: 30px; height: 30px; font-size: 16px; cursor: pointer; transition: all 0.3s ease; }
        .qty-btn:hover { background: #fff; color: #000; border-color: #fff; }
        .qty-display { color: #fff; font-size: 16px; min-width: 30px; text-align: center; }
        .item-remove { text-align: center; }
        .remove-btn { background: transparent; border: 1px solid #e74c3c; color: #e74c3c; width: 40px; height: 40px; font-size: 18px; cursor: pointer; transition: all 0.3s ease; }
        .remove-btn:hover { background: #e74c3c; color: #fff; transform: scale(1.1); }
        .cart-summary { background: #111; border: 1px solid #222; padding: 30px; height: fit-content; position: sticky; top: 20px; }
        .cart-summary h3 { color: #fff; margin-bottom: 25px; font-size: 1.5em; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid #222; padding-bottom: 15px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; color: #888; letter-spacing: 1px; }
        .summary-row.total { font-size: 22px; color: #fff; margin-top: 20px; padding-top: 20px; border-top: 1px solid #222; font-weight: 400; }
        .checkout-btn { display: block; width: 100%; padding: 16px; background: transparent; border: 1px solid #27ae60; color: #27ae60; text-align: center; text-decoration: none; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; margin-top: 25px; cursor: pointer; }
        .checkout-btn:hover { background: #27ae60; color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(39,174,96,0.3); }
        .continue-shopping { display: block; width: 100%; padding: 14px; background: transparent; border: 1px solid #444; color: #fff; text-align: center; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; margin-top: 15px; }
        .continue-shopping:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-2px); }
        .empty-cart { text-align: center; padding: 100px 20px; color: #666; background: #111; border: 1px solid #222; }
        .empty-cart h2 { color: #fff; margin-bottom: 15px; font-weight: 300; letter-spacing: 2px; }
        .empty-cart p { font-size: 14px; margin-bottom: 30px; font-weight: 300; letter-spacing: 1px; color: #888; }
        .loading { text-align: center; padding: 40px; color: #888; font-size: 14px; letter-spacing: 1px; }
        .error-message { background: #c0392b; color: white; padding: 15px 30px; margin-bottom: 20px; font-size: 13px; letter-spacing: 1px; border: 1px solid #a93226; }
        @media (max-width: 1024px) {
            .cart-content { grid-template-columns: 1fr; }
            .cart-summary { position: static; }
            .cart-item { grid-template-columns: 100px 1fr; gap: 15px; }
            .item-quantity, .item-price, .item-remove { grid-column: 2; justify-self: start; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ShopSphere</h1>
        <p>Shopping Cart</p>
    </div>
    
    <div class="user-bar">
        <div class="user-info">
            Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>
        </div>
        <div>
            <a href="index.php">Home</a>
            <a href="catalog.php">Browse Watches</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="logout.php">Log Out</a>
        </div>
    </div>
    
    <div class="container">
        <div class="cart-header">
            <h2>Your Shopping Cart</h2>
            <p id="cart-count">Loading items...</p>
        </div>
        
        <?php if ($message): ?>
            <div style="background: #27ae60; color: white; padding: 15px 30px; margin-bottom: 20px; font-size: 13px; letter-spacing: 1px; border: 1px solid #229954;">
                ✓ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message">
                ✗ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div id="error-container"></div>
        <div id="loading" class="loading">Loading your cart...</div>
        <div id="cart-container"></div>
    </div>
    
    <?php
    // Fetch cart items from database
    $user_id = $_SESSION['user_id'];
    $serverName = "tcp:mydatabase-replica.database.windows.net,1433";
    $connectionOptions = array(
        "Database" => "myDatabase",
        "Uid" => "myadmin",
        "PWD" => "password123!",
        "Encrypt" => 1,
        "TrustServerCertificate" => 0
    );
    
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    $cart_items = array();
    $cart_total = 0;
    $cart_count = 0;
    
    if ($conn) {
        $sql = "SELECT c.id, c.watch_id, c.quantity, c.added_at,
                       w.name, w.brand, w.price, w.description, w.image_url
                FROM cart c
                INNER JOIN watches w ON c.watch_id = w.id
                WHERE c.user_id = ?
                ORDER BY c.added_at DESC";
        
        $params = array($user_id);
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt !== false) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $quantity = (int)$row['quantity'];
                $price = (float)$row['price'];
                $cart_total += $price * $quantity;
                $cart_count += $quantity;
                $cart_items[] = $row;
            }
            sqlsrv_free_stmt($stmt);
        }
        sqlsrv_close($conn);
    }
    ?>
    
    <script>
        const cartData = <?php echo json_encode([
            'items' => $cart_items,
            'count' => $cart_count,
            'subtotal' => $cart_total,
            'total' => $cart_total
        ]); ?>;
        
        // Display cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loading').style.display = 'none';
            displayCart(cartData);
        });
        
        function displayCart(data) {
            const container = document.getElementById('cart-container');
            const countElement = document.getElementById('cart-count');
            
            countElement.textContent = `${data.count} item${data.count !== 1 ? 's' : ''} in your cart`;
            
            if (data.items.length === 0) {
                container.innerHTML = `
                    <div class="empty-cart">
                        <h2>Your cart is empty</h2>
                        <p>Browse our collection and add watches to your cart</p>
                        <a href="catalog.php" class="checkout-btn" style="max-width: 300px; margin: 0 auto;">Browse Watches</a>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <div class="cart-content">
                    <div class="cart-items" id="items-list"></div>
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Items (${data.count})</span>
                            <span>£${formatPrice(data.subtotal)}</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>FREE</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>£${formatPrice(data.total)}</span>
                        </div>
                        <button class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
                        <a href="catalog.php" class="continue-shopping">Continue Shopping</a>
                    </div>
                </div>
            `;
            
            const itemsList = document.getElementById('items-list');
            data.items.forEach(item => {
                const itemElement = createCartItem(item);
                itemsList.appendChild(itemElement);
            });
        }
        
        function createCartItem(item) {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'cart-item';
            itemDiv.setAttribute('data-watch-id', item.watch_id);
            
            const addedDate = item.added_at ? new Date(item.added_at.date || item.added_at).toLocaleDateString() : 'Recently';
            
            itemDiv.innerHTML = `
                <div class="item-image">
                    ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}">` : '<div style="color:#666;">No Image</div>'}
                </div>
                <div class="item-details">
                    <div class="item-brand">${item.brand}</div>
                    <div class="item-name">${item.name}</div>
                    <div class="item-description">${item.description || ''}</div>
                    <div style="color: #555; font-size: 11px; margin-top: 10px;">Added ${addedDate}</div>
                </div>
                <div class="item-price">£${formatPrice(item.price)}</div>
                <div class="item-quantity">
                    <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                        <input type="hidden" name="action" value="update_quantity">
                        <input type="hidden" name="watch_id" value="${item.watch_id}">
                        <button type="submit" name="quantity" value="${item.quantity - 1}" class="qty-btn">−</button>
                        <span class="qty-display">${item.quantity}</span>
                        <button type="submit" name="quantity" value="${item.quantity + 1}" class="qty-btn">+</button>
                    </form>
                </div>
                <div class="item-remove">
                    <form method="POST">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="watch_id" value="${item.watch_id}">
                        <button type="submit" class="remove-btn" title="Remove from cart" onclick="return confirm('Remove this item from your cart?')">×</button>
                    </form>
                </div>
            `;
            
            return itemDiv;
        }
        

        
        function proceedToCheckout() {
            alert('Checkout functionality will be implemented soon!\n\nTotal: £' + formatPrice(cartData.total));
            // TODO: Redirect to checkout page
            // window.location.href = 'checkout.php';
        }
        
        function formatPrice(price) {
            return parseFloat(price).toLocaleString('en-GB', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }
        
        function showError(message) {
            const errorContainer = document.getElementById('error-container');
            errorContainer.innerHTML = `<div class="error-message">${message}</div>`;
            setTimeout(() => {
                errorContainer.innerHTML = '';
            }, 5000);
        }
    </script>
</body>
</html>
