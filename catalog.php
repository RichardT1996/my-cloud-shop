<?php
// catalog.php - display watches catalog for logged-in users
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle add to cart action
$cart_message = '';
$cart_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $watch_id = (int)$_POST['watch_id'];
    $user_id = $_SESSION['user_id'];
    
    $serverName = "tcp:mycardiffmet1.database.windows.net,1433";
    $connectionOptions = array(
        "Database" => "myDatabase",
        "Uid" => "myadmin",
        "PWD" => "password123!",
        "Encrypt" => 1,
        "TrustServerCertificate" => 0
    );
    
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn) {
        // Check if item already in cart
        $checkSql = "SELECT id, quantity FROM cart WHERE user_id = ? AND watch_id = ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, array($user_id, $watch_id));
        
        if ($checkStmt !== false) {
            $existing = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
            
            if ($existing) {
                // Update quantity
                $newQty = $existing['quantity'] + 1;
                $updateSql = "UPDATE cart SET quantity = ? WHERE id = ?";
                $updateStmt = sqlsrv_query($conn, $updateSql, array($newQty, $existing['id']));
                
                if ($updateStmt !== false) {
                    $cart_message = 'Cart updated! Quantity increased.';
                    sqlsrv_free_stmt($updateStmt);
                } else {
                    $cart_error = 'Failed to update cart: ' . print_r(sqlsrv_errors(), true);
                }
            } else {
                // Insert new item
                $insertSql = "INSERT INTO cart (user_id, watch_id, quantity, added_at) VALUES (?, ?, 1, GETDATE())";
                $insertStmt = sqlsrv_query($conn, $insertSql, array($user_id, $watch_id));
                
                if ($insertStmt !== false) {
                    $cart_message = 'Added to cart successfully!';
                    sqlsrv_free_stmt($insertStmt);
                } else {
                    $cart_error = 'Failed to add to cart: ' . print_r(sqlsrv_errors(), true);
                }
            }
            sqlsrv_free_stmt($checkStmt);
        } else {
            $cart_error = 'Database query error: ' . print_r(sqlsrv_errors(), true);
        }
        sqlsrv_close($conn);
    } else {
        $cart_error = 'Database connection failed: ' . print_r(sqlsrv_errors(), true);
    }
}

// DB connection
$serverName = "tcp:mydatabase-replica.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin",
    "PWD" => "password123!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    $errors = sqlsrv_errors();
    $msg = "Database connection failed";
    if ($errors != null) {
        foreach ($errors as $error) {
            $msg .= ": " . $error['message'];
        }
    }
    die("<p style='color:red;'>" . htmlspecialchars($msg) . "</p>");
}

// Fetch watches from database
$sql = "SELECT id, name, brand, price, description, image_url FROM watches ORDER BY brand, name";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    $errors = sqlsrv_errors();
    $msg = "Query failed";
    if ($errors != null) {
        foreach ($errors as $error) {
            $msg .= ": " . $error['message'];
        }
    }
    sqlsrv_close($conn);
    die("<p style='color:red;'>" . htmlspecialchars($msg) . "</p>");
}

$watches = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $watches[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
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
    .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; }
    .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
    .header p { font-size: 0.9em; color: #999; letter-spacing: 2px; text-transform: uppercase; font-weight: 300; }
    .user-bar { background: #111; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #222; }
    .user-bar .user-info { font-size: 13px; color: #999; font-weight: 300; }
    .user-bar .user-info strong { color: #fff; font-weight: 400; }
    .user-bar a { color: #fff; text-decoration: none; padding: 10px 24px; background: transparent; border: 1px solid #444; border-radius: 0; font-size: 11px; font-weight: 400; transition: all 0.3s ease; margin-left: 10px; letter-spacing: 1.5px; text-transform: uppercase; }
    .user-bar a:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(255,255,255,0.1); }
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
    <p>Premium Watch Collection</p>
  </div>
  
  <div class="user-bar">
    <div class="user-info">
      Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
    </div>
    <div>
      <a href="index.php">Home</a>
      <a href="wishlist.php">💝 My Wishlist</a>
      <a href="cart.php">🛒 Cart</a>
      <?php if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'admin@gmail.com'): ?>
        <a href="admin_dashboard.php">Manage Products</a>
      <?php endif; ?>
      <a href="logout.php">Log Out</a>
    </div>
  </div>

  <div class="container">
    <div class="catalog-header">
      <h2>Our Watch Collection</h2>
      <p>Discover luxury timepieces from the world's finest brands</p>
    </div>

    <?php if ($cart_message): ?>
      <div style="background: #27ae60; color: white; padding: 15px 30px; margin-bottom: 20px; font-size: 13px; letter-spacing: 1px; border: 1px solid #229954; text-align: center;">
        ✓ <?php echo htmlspecialchars($cart_message); ?> <a href="cart.php" style="color: white; text-decoration: underline; margin-left: 10px;">View Cart</a>
      </div>
    <?php endif; ?>
    
    <?php if ($cart_error): ?>
      <div style="background: #c0392b; color: white; padding: 15px 30px; margin-bottom: 20px; font-size: 13px; letter-spacing: 1px; border: 1px solid #a93226; text-align: center;">
        ✗ <?php echo htmlspecialchars($cart_error); ?>
      </div>
    <?php endif; ?>

    <?php if (empty($watches)): ?>
      <div class="empty-message">
        <p>No watches available at the moment.</p>
        <p style="font-size:14px; color:#95a5a6;">The catalog will be updated soon with premium timepieces.</p>
        <br>
        <a href="index.php" class="btn">Back to Home</a>
      </div>
    <?php else: ?>
      <div class="catalog-grid">
        <?php foreach ($watches as $watch): ?>
          <div class="watch-card">
            <div class="watch-image-container">
              <img 
                src="<?php echo htmlspecialchars($watch['image_url']); ?>" 
                alt="<?php echo htmlspecialchars($watch['name']); ?>"
                class="watch-image"
                onerror="this.src='https://via.placeholder.com/320x320/1a1a1a/666666?text=<?php echo urlencode($watch['brand']); ?>'"
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
        const response = await fetch(`https://wishlists-bvgrckbzfmf2gzd9.norwayeast-01.azurewebsites.net/api/get_wishlist?user_id=${userId}`);
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
        const response = await fetch('https://wishlists-bvgrckbzfmf2gzd9.norwayeast-01.azurewebsites.net/api/add_to_wishlist', {
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
