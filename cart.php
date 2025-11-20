<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'User';
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
        
        <div id="error-container"></div>
        <div id="loading" class="loading">Loading your cart...</div>
        <div id="cart-container"></div>
    </div>
    
    <script>
        let cartData = { items: [], total: 0, count: 0 };
        
        // Load cart on page load
        document.addEventListener('DOMContentLoaded', loadCart);
        
        async function loadCart() {
            try {
                const userId = <?php echo $_SESSION['user_id']; ?>;
                const response = await fetch(`cart_api.php?action=get_cart&user_id=${userId}`);
                const data = await response.json();
                
                document.getElementById('loading').style.display = 'none';
                
                if (!data.success) {
                    showError(data.error || 'Failed to load cart');
                    return;
                }
                
                cartData = data;
                displayCart(data);
                
            } catch (error) {
                document.getElementById('loading').style.display = 'none';
                showError('Error loading cart');
                console.error(error);
            }
        }
        
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
            
            itemDiv.innerHTML = `
                <div class="item-image">
                    ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}">` : '<div style="color:#666;">No Image</div>'}
                </div>
                <div class="item-details">
                    <div class="item-brand">${item.brand}</div>
                    <div class="item-name">${item.name}</div>
                    <div class="item-description">${item.description || ''}</div>
                </div>
                <div class="item-price">£${formatPrice(item.price)}</div>
                <div class="item-quantity">
                    <button class="qty-btn" onclick="updateQuantity(${item.watch_id}, ${item.quantity - 1})">−</button>
                    <span class="qty-display">${item.quantity}</span>
                    <button class="qty-btn" onclick="updateQuantity(${item.watch_id}, ${item.quantity + 1})">+</button>
                </div>
                <div class="item-remove">
                    <button class="remove-btn" onclick="removeFromCart(${item.watch_id})" title="Remove from cart">×</button>
                </div>
            `;
            
            return itemDiv;
        }
        
        async function updateQuantity(watchId, newQuantity) {
            if (newQuantity < 1) {
                removeFromCart(watchId);
                return;
            }
            
            try {
                const userId = <?php echo $_SESSION['user_id']; ?>;
                const response = await fetch('cart_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        action: 'update_quantity',
                        user_id: userId,
                        watch_id: watchId,
                        quantity: newQuantity
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadCart(); // Reload cart to update totals
                } else {
                    showError(data.error || 'Failed to update quantity');
                }
                
            } catch (error) {
                showError('Error updating quantity');
                console.error(error);
            }
        }
        
        async function removeFromCart(watchId) {
            if (!confirm('Remove this item from your cart?')) {
                return;
            }
            
            try {
                const userId = <?php echo $_SESSION['user_id']; ?>;
                const response = await fetch('cart_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        action: 'remove_from_cart',
                        user_id: userId,
                        watch_id: watchId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadCart(); // Reload cart
                } else {
                    showError(data.error || 'Failed to remove item');
                }
                
            } catch (error) {
                showError('Error removing item');
                console.error(error);
            }
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
