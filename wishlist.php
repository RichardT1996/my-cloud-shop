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
    <title>My Wishlist - Luxury Watch Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; min-height: 100vh; }
        .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; }
        .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
        .header p { font-size: 0.9em; color: #999; letter-spacing: 2px; text-transform: uppercase; font-weight: 300; }
        .user-bar { background: #111; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #222; }
        .user-bar .user-info { font-size: 13px; color: #999; font-weight: 300; }
        .user-bar .user-info strong { color: #fff; font-weight: 400; }
        .user-bar a { color: #fff; text-decoration: none; padding: 8px 20px; background: transparent; border: 1px solid #444; border-radius: 0; font-size: 12px; font-weight: 400; transition: all 0.3s ease; margin-left: 10px; letter-spacing: 1px; text-transform: uppercase; }
        .user-bar a:hover { background: #fff; color: #000; border-color: #fff; }
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
        .card-actions { display: flex; gap: 10px; }
        .btn { display: inline-block; padding: 12px 30px; background: transparent; border: 1px solid #444; color: #fff; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; cursor: pointer; text-align: center; }
        .btn:hover { background: #fff; color: #000; border-color: #fff; }
        .btn-danger { border-color: #c0392b; color: #c0392b; }
        .btn-danger:hover { background: #c0392b; color: #fff; border-color: #c0392b; }
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
    
    <div class="user-bar">
        <div class="user-info">
            Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>
        </div>
        <div>
            <a href="index.php">Home</a>
            <a href="catalog.php">Browse Watches</a>
            <a href="logout.php">Log Out</a>
        </div>
    </div>
    
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
                const response = await fetch(`https://wishlists-bvgrckbzfmf2gzd9.norwayeast-01.azurewebsites.net/api/get_wishlist?user_id=${userId}`);
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
                        <a href="catalog.php" class="btn">Browse Watches</a>
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
                    ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}" class="watch-image">` : '<div style="color:#666;">No Image</div>'}
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
                const response = await fetch('https://wishlists-bvgrckbzfmf2gzd9.norwayeast-01.azurewebsites.net/api/remove_from_wishlist', {
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
