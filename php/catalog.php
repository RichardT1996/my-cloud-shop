<?php
// catalog.php - display watches catalog for logged-in users
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /php/login.php');
    exit();
}

// Handle add to cart action
$cart_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $watch_id = (int)$_POST['watch_id'];
    $user_id = $_SESSION['user_id'];
    
    require_once '../db_config.php';
    
    try {
        $conn = getDbConnection();
        
        // Check if item already in cart
        $checkSql = "SELECT id, quantity FROM cart WHERE user_id = ? AND watch_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$user_id, $watch_id]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Update quantity
            $newQty = $existing['quantity'] + 1;
            $updateSql = "UPDATE cart SET quantity = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([$newQty, $existing['id']]);
            $cart_message = 'Cart updated! Quantity increased.';
        } else {
            // Insert new item
            $insertSql = "INSERT INTO cart (user_id, watch_id, quantity) VALUES (?, ?, 1)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->execute([$user_id, $watch_id]);
            $cart_message = 'Added to cart successfully!';
        }
    } catch (Exception $e) {
        error_log("Add to cart error: " . $e->getMessage());
        $cart_message = 'Error adding to cart. Please try again.';
    }
}

// DB connection
require_once '../db_config.php';

try {
    $conn = getDbConnection();
    
    // Fetch watches from database
    $sql = "SELECT id, name, brand, price, description, image_url FROM watches ORDER BY brand, name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $watches = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Catalog error: " . $e->getMessage());
    die("<p style='color:red;'>Database error occurred. Please try again later.</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Watch Catalog - ShopSphere</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; }
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
    .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 40px; }
    .watch-card { background: #111; border: 1px solid #222; overflow: hidden; transition: all 0.4s ease; }
    .watch-card:hover { border-color: #444; transform: translateY(-5px); box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
    .watch-image-container { width: 100%; height: 350px; background: #1a1a1a; display: flex; align-items: center; justify-content: center; padding: 40px; border-bottom: 1px solid #222; }
    .watch-image { max-width: 90%; max-height: 90%; object-fit: contain; filter: brightness(1.05) contrast(1.1); }
    .watch-info { padding: 30px; background: #111; }
    .watch-brand { color: #888; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; font-weight: 400; }
    .watch-name { font-size: 18px; color: #fff; margin-bottom: 15px; font-weight: 400; line-height: 1.4; letter-spacing: 0.5px; }
    .watch-description { color: #666; font-size: 13px; margin-bottom: 20px; line-height: 1.6; font-weight: 300; }
    .watch-price { font-size: 20px; color: #fff; font-weight: 300; letter-spacing: 1px; }
    .watch-card-footer { padding: 0 30px 30px 30px; background: #111; display: flex; gap: 10px; }
    .enquire-btn { display: block; flex: 1; padding: 14px 20px; background: transparent; border: 1px solid #444; color: #fff; text-align: center; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; }
    .enquire-btn:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,255,255,0.15); }
    .wishlist-btn { background: #1a1a1a; border-color: #555; }
    .wishlist-btn:hover { background: #e74c3c; border-color: #e74c3c; color: #fff; }
    .wishlist-btn:disabled { opacity: 0.6; }
    .cart-btn { background: #1a1a1a; border-color: #27ae60; color: #27ae60; }
    .cart-btn:hover { background: #27ae60; border-color: #27ae60; color: #fff; }
    .cart-btn:disabled { opacity: 0.6; }
    .enquire-now-btn { background: #1a1a1a; border-color: #3498db; color: #3498db; }
    .enquire-now-btn:hover { background: #3498db; border-color: #3498db; color: #fff; }
    .empty-message { text-align: center; padding: 100px 20px; color: #666; }
    .empty-message p { font-size: 18px; margin-bottom: 20px; font-weight: 300; letter-spacing: 1px; }
    .btn { display: inline-block; background: transparent; border: 1px solid #444; color: #fff; padding: 14px 32px; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; }
    .btn:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,255,255,0.15); }
  </style>
</head>
<body>
  <div class="header">
    <h1>ShopSphere</h1>
    <div class="tagline">Luxury Timepieces</div>
    <div class="welcome">
      Welcome, <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a href="/php/logout.php">Logout</a>
    </div>
  </div>
  
  <nav class="nav">
    <ul>
      <li><a href="/php/index.php">Home</a></li>
      <li><a href="/php/catalog.php" class="active">Catalog</a></li>
      <li><a href="/php/wishlist.php">Wishlist</a></li>
      <li><a href="/php/cart.php">Cart</a></li>
      <li><a href="/php/my_orders.php">My Orders</a></li>
      <?php if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'admin@gmail.com'): ?>
        <li><a href="/admin/admin_dashboard.php">Admin</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <div class="container">
    <div class="catalog-header">
      <h2>Our Watch Collection</h2>
      <p>Discover luxury timepieces from the world's finest brands</p>
    </div>

    <?php if ($cart_message): ?>
      <div style="background: #27ae60; color: white; padding: 15px 30px; margin-bottom: 20px; font-size: 13px; letter-spacing: 1px; border: 1px solid #229954; text-align: center;">
        ✓ <?php echo htmlspecialchars($cart_message); ?> <a href="/php/cart.php" style="color: white; text-decoration: underline; margin-left: 10px;">View Cart</a>
      </div>
    <?php endif; ?>

    <?php if (empty($watches)): ?>
      <div class="empty-message">
        <p>No watches available at the moment.</p>
        <p style="font-size:14px; color:#95a5a6;">The catalog will be updated soon with premium timepieces.</p>
        <br>
        <a href="/php/index.php" class="btn">Back to Home</a>
      </div>
    <?php else: ?>
      <div class="catalog-grid">
        <?php foreach ($watches as $watch): ?>
          <div class="watch-card">
            <div class="watch-image-container">
              <img 
                src="<?php echo htmlspecialchars(str_starts_with($watch['image_url'], '/') ? $watch['image_url'] : '/' . $watch['image_url']); ?>" 
                alt="<?php echo htmlspecialchars($watch['name']); ?>"
                class="watch-image"
                onerror="this.style.display='none';this.parentElement.innerHTML='<div style=&quot;width:100%;height:320px;background:#1a1a1a;display:flex;align-items:center;justify-content:center;color:#666;font-size:12px;letter-spacing:2px;&quot;><?php echo strtoupper(htmlspecialchars($watch['brand'])); ?></div>';"
              >
            </div>
            <div class="watch-info">
              <div class="watch-brand"><?php echo htmlspecialchars($watch['brand']); ?></div>
              <div class="watch-name"><?php echo htmlspecialchars($watch['name']); ?></div>
              <div class="watch-description"><?php echo htmlspecialchars($watch['description']); ?></div>
              <div class="watch-price">£<?php echo number_format($watch['price'], 0); ?></div>
            </div>
            <div class="watch-card-footer">
              <form method="POST" style="flex: 1;">
                <input type="hidden" name="watch_id" value="<?php echo $watch['id']; ?>">
                <button type="submit" name="add_to_cart" class="enquire-btn cart-btn" style="cursor:pointer; width: 100%;">
                  🛒 Add to Cart
                </button>
              </form>
              <button class="enquire-btn wishlist-btn" data-watch-id="<?php echo $watch['id']; ?>" onclick="addToWishlist(<?php echo $watch['id']; ?>, this)" style="cursor:pointer;">
                ❤️ Wishlist
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  
  <script>
    // Load user's wishlist on page load
    document.addEventListener('DOMContentLoaded', loadWishlistStatus);
    
    async function loadWishlistStatus() {
      try {
        const userId = <?php echo $_SESSION['user_id']; ?>;
        const response = await fetch(`http://localhost:7072/api/get_wishlist?user_id=${userId}`);
        const data = await response.json();
        
        if (data.success && data.items) {
          const wishlistWatchIds = data.items.map(item => item.watch_id);
          
          // Update buttons for items already in wishlist
          document.querySelectorAll('.wishlist-btn').forEach(btn => {
            const watchId = parseInt(btn.getAttribute('data-watch-id'));
            if (wishlistWatchIds.includes(watchId)) {
              btn.innerHTML = '✓ In Wishlist';
              btn.style.background = '#27ae60';
              btn.style.borderColor = '#27ae60';
              btn.style.color = '#fff';
              btn.disabled = true;
              btn.style.cursor = 'not-allowed';
              btn.style.transform = 'none';
            }
          });
        }
      } catch (error) {
        console.error('Error loading wishlist status:', error);
      }
    }
    

    
    async function addToWishlist(watchId, buttonElement) {
      try {
        const response = await fetch('http://localhost:7072/api/add_to_wishlist', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ 
            user_id: <?php echo $_SESSION['user_id']; ?>,
            watch_id: watchId 
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Update button state
          buttonElement.innerHTML = '✓ In Wishlist';
          buttonElement.style.background = '#27ae60';
          buttonElement.style.borderColor = '#27ae60';
          buttonElement.style.color = '#fff';
          buttonElement.disabled = true;
          buttonElement.style.cursor = 'not-allowed';
          buttonElement.style.transform = 'none';
          alert('✓ ' + data.message);
        } else {
          alert('✗ ' + (data.error || 'Failed to add to wishlist'));
        }
        
      } catch (error) {
        alert('✗ Error adding to wishlist');
        console.error(error);
      }
    }

  </script>
</body>
</html>
