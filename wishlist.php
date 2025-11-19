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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
        }
        
        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .user-info {
            color: #666;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .watch-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .watch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .watch-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        
        .watch-content {
            padding: 20px;
        }
        
        .watch-brand {
            color: #667eea;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .watch-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin: 10px 0;
        }
        
        .watch-price {
            font-size: 24px;
            color: #27ae60;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .watch-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .watch-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
        }
        
        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: white;
            font-size: 18px;
        }
        
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💝 My Wishlist</h1>
            <div class="header-right">
                <span class="user-info">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                <a href="catalog.php" class="btn btn-primary">Browse Watches</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
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
                const response = await fetch('api_wishlist.php');
                const data = await response.json();
                
                document.getElementById('loading').style.display = 'none';
                
                if (!data.success) {
                    showError(data.error || 'Failed to load wishlist');
                    return;
                }
                
                displayWishlist(data.items, data.count);
                
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
                        <p>Browse our collection and add watches you love!</p>
                        <a href="catalog.php" class="btn btn-primary">Browse Watches</a>
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
            const price = parseFloat(item.price).toLocaleString('en-US', { 
                style: 'currency', 
                currency: 'USD' 
            });
            
            card.innerHTML = `
                <div class="watch-image">
                    ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}" style="width:100%;height:100%;object-fit:cover;">` : '📷 No Image'}
                </div>
                <div class="watch-content">
                    <div class="watch-brand">${item.brand}</div>
                    <div class="watch-name">${item.name}</div>
                    <div class="watch-price">${price}</div>
                    <div class="watch-description">${item.description || ''}</div>
                    <div class="watch-meta">Added on ${addedDate}</div>
                    <div class="card-actions">
                        <button class="btn btn-danger" onclick="removeFromWishlist(${item.watch_id})">
                            Remove
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
                const response = await fetch('api_wishlist.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ watch_id: watchId })
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
