<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /php/login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Luxury Watch Shop</title>
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
        .container { max-width: 1400px; margin: 60px auto; padding: 0 40px; }
        .catalog-header { text-align: center; margin-bottom: 60px; padding-bottom: 30px; border-bottom: 1px solid #222; }
        .catalog-header h2 { color: #fff; margin-bottom: 15px; font-size: 2.5em; font-weight: 300; letter-spacing: 3px; text-transform: uppercase; }
        .catalog-header p { color: #888; font-size: 1em; letter-spacing: 1px; font-weight: 300; }
        .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 40px; }
        .watch-card { background: #111; border: 1px solid #222; overflow: hidden; transition: all 0.4s ease; }
        .watch-card:hover { border-color: #444; transform: translateY(-5px); box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
        .watch-image-container { width: 100%; height: 350px; background: #1a1a1a; display: flex; align-items: center; justify-content: center; padding: 40px; border-bottom: 1px solid #222; }
        .watch-image { max-width: 90%; max-height: 90%; object-fit: contain; filter: brightness(1.05) contrast(1.1); }
        .watch-content { padding: 30px; background: #111; }
        .watch-brand { color: #888; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; font-weight: 400; }
        .watch-name { font-size: 18px; color: #fff; margin-bottom: 15px; font-weight: 400; line-height: 1.4; letter-spacing: 0.5px; }
        .watch-description { color: #666; font-size: 13px; margin-bottom: 20px; line-height: 1.6; font-weight: 300; }
        .watch-price { font-size: 20px; color: #fff; font-weight: 300; letter-spacing: 1px; margin-bottom: 15px; }
        .watch-meta { font-size: 11px; color: #555; margin-bottom: 15px; letter-spacing: 1px; }
        .card-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn { display: inline-block; padding: 14px 32px; background: transparent; border: 1px solid #444; color: #fff; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; cursor: pointer; text-align: center; }
        .btn:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,255,255,0.15); }
        .btn-danger { border-color: #e74c3c; color: #e74c3c; background: #1a1a1a; }
        .btn-danger:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(231,76,60,0.3); }
        .empty-state { text-align: center; padding: 100px 20px; color: #666; background: #111; border: 1px solid #222; margin-top: 40px; }
        .empty-state h2 { color: #fff; margin-bottom: 15px; font-weight: 300; letter-spacing: 2px; }
        .empty-state p { font-size: 14px; margin-bottom: 30px; font-weight: 300; letter-spacing: 1px; color: #888; }
        .loading { text-align: center; padding: 40px; color: #888; font-size: 14px; letter-spacing: 1px; }
        .error-message { background: #c0392b; color: white; padding: 15px 30px; margin-bottom: 20px; font-size: 13px; letter-spacing: 1px; border: 1px solid #a93226; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ShopSphere</h1>
        <p>My Wishlist</p>
    </div>
    
    <div class="welcome">
        Welcome, <span><?php echo htmlspecialchars($user_name); ?></span>
        <a href="/php/logout.php">Logout</a>
    </div>
    
    <nav class="nav">
        <ul>
            <li><a href="/php/index.php">Home</a></li>
            <li><a href="/php/catalog.php">Catalog</a></li>
            <li><a href="/php/wishlist.php" class="active">Wishlist</a></li>
            <li><a href="/php/cart.php">Cart</a></li>
            <li><a href="/php/my_orders.php">My Orders</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <div class="catalog-header">
            <h2>My Wishlist</h2>
            <p>Your curated collection of luxury timepieces</p>
        </div>
        
        <div id="error-container"></div>
        <div id="loading" class="loading">Loading your wishlist...</div>
        <div id="wishlist-container"></div>
    </div>
    
    <script>
        // Load wishlist on page load
        document.addEventListener('DOMContentLoaded', loadWishlist);
        
        async function loadWishlist() {
            try {
                const userId = <?php echo $_SESSION['user_id']; ?>;
                const response = await fetch(`http://localhost:7072/api/get_wishlist?user_id=${userId}`);
                const data = await response.json();
                
                document.getElementById('loading').style.display = 'none';
                
                if (!data.success) {
                    showError(data.error || 'Failed to load wishlist');
                    return;
                }
                
                displayWishlist(data.items, data.items.length);
                
            } catch (error) {
                document.getElementById('loading').style.display = 'none';
                showError('Error loading wishlist');
                console.error(error);
            }
        }
        
        function displayWishlist(items, count) {
            const container = document.getElementById('wishlist-container');
            
            if (items.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <h2>Your wishlist is empty</h2>
                        <p>Browse our collection and add watches you love</p>
                        <a href="/php/catalog.php" class="btn">Browse Watches</a>
                    </div>
                `;
                return;
            }
            
            const grid = document.createElement('div');
            grid.className = 'wishlist-grid';
            
            items.forEach(item => {
                const card = createWatchCard(item);
                grid.appendChild(card);
            });
            
            container.appendChild(grid);
        }
        
        function createWatchCard(item) {
            const card = document.createElement('div');
            card.className = 'watch-card';
            
            const addedDate = new Date(item.added_at).toLocaleDateString();
            const price = parseFloat(item.price).toLocaleString('en-GB', { 
                style: 'currency', 
                currency: 'GBP' 
            });
            
            card.innerHTML = `
                <div class="watch-image-container">
                    ${item.image_url ? `<img src="${item.image_url.startsWith('/') ? item.image_url : '/' + item.image_url}" alt="${item.name}" class="watch-image">` : '<div style="color:#666;">No Image</div>'}
                </div>
                <div class="watch-content">
                    <div class="watch-brand">${item.brand}</div>
                    <div class="watch-name">${item.name}</div>
                    <div class="watch-price">£${parseFloat(item.price).toLocaleString('en-GB', {minimumFractionDigits: 0})}</div>
                    <div class="watch-description">${item.description || ''}</div>
                    <div class="watch-meta">Added ${addedDate}</div>
                    <div class="card-actions">
                        <button class="btn btn-danger" onclick="removeFromWishlist(${item.watch_id})">
                            Remove from Wishlist
                        </button>
                    </div>
                </div>
            `;
            
            return card;
        }
        
        async function removeFromWishlist(watchId) {
            if (!confirm('Remove this item from your wishlist?')) {
                return;
            }
            
            try {
                const userId = <?php echo $_SESSION['user_id']; ?>;
                const response = await fetch('http://localhost:7072/api/remove_from_wishlist', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        user_id: userId,
                        watch_id: watchId 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reload the wishlist
                    document.getElementById('wishlist-container').innerHTML = '';
                    document.getElementById('loading').style.display = 'block';
                    loadWishlist();
                } else {
                    showError(data.error || 'Failed to remove item');
                }
                
            } catch (error) {
                showError('Error removing item');
                console.error(error);
            }
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
