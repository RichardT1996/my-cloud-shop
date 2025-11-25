<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /php/login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_id = $_SESSION['user_id'];

// Database connection
require_once '../db_config.php';

// Fetch cart items
try {
    $conn = getDbConnection();
    $cart_items = array();
    $cart_total = 0;
    
    $sql = "SELECT c.watch_id, c.quantity, w.name, w.brand, w.price, w.image_url
            FROM cart c
            INNER JOIN watches w ON c.watch_id = w.id
            WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    
    while ($row = $stmt->fetch()) {
        $quantity = (int)$row['quantity'];
        $price = (float)$row['price'];
        $cart_total += $price * $quantity;
        $cart_items[] = $row;
    }
} catch (Exception $e) {
    error_log("Checkout error: " . $e->getMessage());
    die("Database connection failed");
}

// If cart is empty, redirect to cart page
if (empty($cart_items)) {
    header("Location: /php/cart.php");
    exit;
}

$message = '';
$error = '';

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $shipping_name = trim($_POST['shipping_name'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $shipping_city = trim($_POST['shipping_city'] ?? '');
    $shipping_postcode = trim($_POST['shipping_postcode'] ?? '');
    $shipping_country = trim($_POST['shipping_country'] ?? 'United Kingdom');
    $payment_method = $_POST['payment_method'] ?? '';
    
    // Validate inputs
    if (empty($shipping_name) || empty($shipping_address) || empty($shipping_city) || 
        empty($shipping_postcode) || empty($payment_method)) {
        $error = 'Please fill in all required fields';
    } else {
        // Generate unique order number
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        // Process payment via Azure Function
        $payment_data = array(
            'amount' => $cart_total,
            'currency' => 'GBP',
            'payment_method' => $payment_method,
            'order_number' => $order_number,
            'user_id' => $user_id
        );
        
        $payment_url = 'http://localhost:7073/api/process_payment';
        $payment_options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($payment_data),
                'timeout' => 10
            )
        );
        
        $payment_response = @file_get_contents($payment_url, false, stream_context_create($payment_options));
        $payment_result = $payment_response ? json_decode($payment_response, true) : null;
        
        $payment_status = ($payment_result && isset($payment_result['success']) && $payment_result['success']) ? 'completed' : 'pending';
        
        // Create order in database
        try {
            $insertOrderSql = "INSERT INTO orders (user_id, order_number, status, total_amount, 
                              shipping_name, shipping_address, shipping_city, shipping_postcode, shipping_country,
                              payment_method, payment_status)
                              VALUES (?, ?, 'processing', ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $orderStmt = $conn->prepare($insertOrderSql);
            $orderStmt->execute([$user_id, $order_number, $cart_total, $shipping_name, $shipping_address,
                                $shipping_city, $shipping_postcode, $shipping_country, $payment_method, $payment_status]);
            
            // Get the order ID
            $order_id = $conn->lastInsertId();
            
            // Insert order items
            foreach ($cart_items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $insertItemSql = "INSERT INTO order_items (order_id, watch_id, watch_name, watch_brand, 
                                 watch_price, watch_image_url, quantity, subtotal)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $itemStmt = $conn->prepare($insertItemSql);
                $itemStmt->execute([$order_id, $item['watch_id'], $item['name'], $item['brand'],
                                   $item['price'], $item['image_url'], $item['quantity'], $subtotal]);
            }
            
            // Clear the cart
            $clearCartSql = "DELETE FROM cart WHERE user_id = ?";
            $clearStmt = $conn->prepare($clearCartSql);
            $clearStmt->execute([$user_id]);
            
            // Redirect to order confirmation
            header("Location: /php/order_confirmation.php?order_number=" . urlencode($order_number));
            exit;
        } catch (Exception $e) {
            error_log("Order creation error: " . $e->getMessage());
            $error = 'Failed to create order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ShopSphere</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; min-height: 100vh; }
        .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; position: relative; }
        .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
        .welcome a:hover { background: #fff; color: #000; border-color: #fff; }
        .nav a:hover, .nav a.active { color: #fff; background: rgba(255,255,255,0.05); border-bottom-color: #fff; }
        .container { max-width: 1400px; margin: 60px auto; padding: 0 40px; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 400px; gap: 40px; }
        .checkout-form { background: #111; border: 1px solid #222; padding: 40px; }
        .form-section { margin-bottom: 40px; }
        .form-section h3 { color: #fff; margin-bottom: 20px; font-size: 1.3em; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid #222; padding-bottom: 15px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #999; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; background: #1a1a1a; border: 1px solid #333; color: #fff; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #27ae60; }
        .payment-methods { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .payment-option { position: relative; }
        .payment-option input[type="radio"] { position: absolute; opacity: 0; }
        .payment-option label { display: block; padding: 15px; background: #1a1a1a; border: 2px solid #333; text-align: center; cursor: pointer; transition: all 0.3s ease; font-size: 13px; letter-spacing: 1px; }
        .payment-option input[type="radio"]:checked + label { border-color: #27ae60; background: #1a2a1a; }
        .payment-option label:hover { border-color: #555; }
        .order-summary { background: #111; border: 1px solid #222; padding: 30px; height: fit-content; position: sticky; top: 20px; }
        .order-summary h3 { color: #fff; margin-bottom: 25px; font-size: 1.5em; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid #222; padding-bottom: 15px; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #222; }
        .item-details { flex: 1; }
        .item-name { font-size: 14px; color: #fff; margin-bottom: 5px; }
        .item-meta { font-size: 11px; color: #666; }
        .item-price { font-size: 14px; color: #fff; white-space: nowrap; margin-left: 15px; }
        .summary-total { font-size: 22px; color: #fff; margin-top: 20px; padding-top: 20px; border-top: 1px solid #222; display: flex; justify-content: space-between; font-weight: 400; }
        .submit-btn { width: 100%; padding: 16px; background: #27ae60; border: none; color: #fff; text-align: center; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; cursor: pointer; margin-top: 20px; }
        .submit-btn:hover { background: #229954; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(39,174,96,0.3); }
        .error-message { background: #c0392b; color: white; padding: 15px 30px; margin-bottom: 20px; font-size: 13px; letter-spacing: 1px; border: 1px solid #a93226; }
        .success-message { background: #27ae60; color: white; padding: 15px 30px; margin-bottom: 20px; font-size: 13px; letter-spacing: 1px; border: 1px solid #229954; }
        @media (max-width: 1024px) {
            .checkout-grid { grid-template-columns: 1fr; }
            .order-summary { position: static; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ShopSphere</h1>
        <div class="tagline" style="font-size: 12px; color: #888; letter-spacing: 2px; text-transform: uppercase;">Secure Checkout</div>
        <div class="welcome" style="position: absolute; top: 30px; right: 40px; color: #888; font-size: 12px; letter-spacing: 1px; text-transform: uppercase;">
            Welcome, <span style="color: #fff; margin-left: 5px;"><?php echo htmlspecialchars($user_name); ?></span>
            <a href="/php/logout.php" style="color: #fff; text-decoration: none; margin-left: 15px; padding: 8px 16px; border: 1px solid #333; transition: all 0.3s ease;">Logout</a>
        </div>
    </div>
    
    <nav class="nav" style="background: #111; border-bottom: 1px solid #222; padding: 0;">
        <ul style="list-style: none; display: flex; justify-content: center; max-width: 1200px; margin: 0 auto;">
            <li style="margin: 0;"><a href="/php/index.php" style="display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent;">Home</a></li>
            <li style="margin: 0;"><a href="/php/catalog.php" style="display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent;">Catalog</a></li>
            <li style="margin: 0;"><a href="/php/wishlist.php" style="display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent;">Wishlist</a></li>
            <li style="margin: 0;"><a href="/php/cart.php" style="display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent;">Cart</a></li>
            <li style="margin: 0;"><a href="/php/my_orders.php" style="display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent;">My Orders</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="error-message">✗ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="success-message">✓ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <form method="POST" class="checkout-form">
                <div class="form-section">
                    <h3>Shipping Address</h3>
                    <div class="form-group">
                        <label for="shipping_name">Full Name *</label>
                        <input type="text" id="shipping_name" name="shipping_name" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_address">Street Address *</label>
                        <input type="text" id="shipping_address" name="shipping_address" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_city">City *</label>
                        <input type="text" id="shipping_city" name="shipping_city" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_postcode">Postcode *</label>
                        <input type="text" id="shipping_postcode" name="shipping_postcode" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_country">Country *</label>
                        <input type="text" id="shipping_country" name="shipping_country" value="United Kingdom" required>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Payment Method</h3>
                    <div class="payment-methods">
                        <div class="payment-option">
                            <input type="radio" id="card" name="payment_method" value="credit_card" required onchange="showCardFields('credit')">
                            <label for="card">💳 Credit Card</label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" id="debit" name="payment_method" value="debit_card" onchange="showCardFields('debit')">
                            <label for="debit">💳 Debit Card</label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" id="paypal" name="payment_method" value="paypal" onchange="hideCardFields()">
                            <label for="paypal">🅿️ PayPal</label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" id="klarna" name="payment_method" value="klarna" onchange="hideCardFields()">
                            <label for="klarna">🔷 Klarna</label>
                        </div>
                    </div>
                    
                    <div id="cardFields" style="display: none; margin-top: 25px; padding-top: 25px; border-top: 1px solid #222;">
                        <div class="form-group">
                            <label for="card_number">Card Number *</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="form-group">
                            <label for="card_name">Cardholder Name *</label>
                            <input type="text" id="card_name" name="card_name" placeholder="John Smith">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="card_expiry">Expiry Date *</label>
                                <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label for="card_cvv">CVV *</label>
                                <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4">
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="place_order" class="submit-btn">Place Order - £<?php echo number_format($cart_total, 2); ?></button>
            </form>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-meta">
                                <?php echo htmlspecialchars($item['brand']); ?> × <?php echo $item['quantity']; ?>
                            </div>
                        </div>
                        <div class="item-price">
                            £<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="summary-total">
                    <span>Total</span>
                    <span>£<?php echo number_format($cart_total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showCardFields(type) {
            const cardFields = document.getElementById('cardFields');
            cardFields.style.display = 'block';
            
            // Make card fields required
            document.getElementById('card_number').required = true;
            document.getElementById('card_name').required = true;
            document.getElementById('card_expiry').required = true;
            document.getElementById('card_cvv').required = true;
        }
        
        function hideCardFields() {
            const cardFields = document.getElementById('cardFields');
            cardFields.style.display = 'none';
            
            // Make card fields optional
            document.getElementById('card_number').required = false;
            document.getElementById('card_name').required = false;
            document.getElementById('card_expiry').required = false;
            document.getElementById('card_cvv').required = false;
            
            // Clear values
            document.getElementById('card_number').value = '';
            document.getElementById('card_name').value = '';
            document.getElementById('card_expiry').value = '';
            document.getElementById('card_cvv').value = '';
        }
        
        // Auto-format card number (add spaces every 4 digits)
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
        
        // Auto-format expiry date (MM/YY)
        document.getElementById('card_expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
        
        // Only allow numbers in CVV
        document.getElementById('card_cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>
