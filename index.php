<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShopSphere - Premium Watch Collection</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; }
    .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; position: relative; }
    .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
    .header .tagline { font-size: 12px; color: #888; letter-spacing: 2px; text-transform: uppercase; }
    .nav { background: #0a0a0a; border-bottom: 1px solid #222; padding: 20px 0; }
    .nav-container { max-width: 1400px; margin: 0 auto; padding: 0 40px; display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }
    .nav a { color: #888; text-decoration: none; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; padding: 10px 20px; transition: all 0.3s ease; border: 1px solid transparent; }
    .nav a:hover, .nav a.active { color: #fff; border-color: #333; }
    .welcome { position: absolute; top: 30px; right: 40px; color: #888; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
    .welcome span { color: #fff; margin-left: 5px; }
    .welcome a { color: #fff; text-decoration: none; margin-left: 15px; padding: 8px 16px; border: 1px solid #333; transition: all 0.3s ease; }
    .welcome a:hover { background: #fff; color: #000; border-color: #fff; }
    .container { max-width: 1400px; margin: 0 auto; padding: 80px 40px; }
    .hero { text-align: center; margin-bottom: 100px; padding: 60px 0; border-bottom: 1px solid #222; }
    .hero h2 { font-size: 2.5em; color: #fff; margin-bottom: 20px; font-weight: 300; letter-spacing: 4px; line-height: 1.4; }
    .hero p { font-size: 1.1em; color: #888; margin-bottom: 40px; font-weight: 300; letter-spacing: 1px; line-height: 1.8; max-width: 800px; margin-left: auto; margin-right: auto; }
    .cta-buttons { display: flex; justify-content: center; gap: 20px; margin-top: 40px; flex-wrap: wrap; }
    .btn { display: inline-block; padding: 15px 40px; background: transparent; border: 1px solid #444; color: #fff; text-decoration: none; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; }
    .btn:hover { background: #fff; color: #000; border-color: #fff; }
    .btn-primary { border-color: #fff; }
    .featured { margin-top: 80px; }
    .featured h3 { text-align: center; font-size: 1.8em; color: #fff; margin-bottom: 50px; font-weight: 300; letter-spacing: 3px; text-transform: uppercase; }
    .watch-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 50px; }
    .watch-card { background: #111; border: 1px solid #222; overflow: hidden; transition: all 0.4s ease; }
    .watch-card:hover { border-color: #444; transform: translateY(-5px); box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
    .watch-image-container { width: 100%; height: 400px; background: #1a1a1a; display: flex; align-items: center; justify-content: center; padding: 50px; border-bottom: 1px solid #222; }
    .watch-image { max-width: 85%; max-height: 85%; object-fit: contain; filter: brightness(1.05) contrast(1.1); }
    .watch-info { padding: 35px; }
    .watch-brand { color: #888; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; font-weight: 400; }
    .watch-name { font-size: 20px; color: #fff; margin-bottom: 15px; font-weight: 400; letter-spacing: 0.5px; }
    .watch-price { font-size: 22px; color: #fff; font-weight: 300; letter-spacing: 1px; margin-top: 20px; }
    .login-prompt { text-align: center; padding: 40px; background: #111; border: 1px solid #222; margin-top: 60px; }
    .login-prompt h4 { color: #fff; font-size: 1.3em; margin-bottom: 15px; font-weight: 300; letter-spacing: 2px; }
    .login-prompt p { color: #888; margin-bottom: 30px; font-weight: 300; letter-spacing: 1px; }
  </style>
</head>
<body>
  <div class="header">
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="welcome">
        WELCOME, <span><?php echo strtoupper(htmlspecialchars($_SESSION['user_name'])); ?></span>
        <a href="logout.php">LOGOUT</a>
      </div>
    <?php endif; ?>
    <h1>SHOPSPHERE</h1>
    <div class="tagline">Luxury Timepieces</div>
  </div>

  <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="nav">
      <div class="nav-container">
        <a href="index.php" class="active">HOME</a>
        <?php if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'admin@gmail.com'): ?>
          <a href="catalog.php">CATALOG</a>
          <a href="admin_dashboard.php">MANAGE PRODUCTS</a>
          <a href="view_users.php">USERS</a>
          <a href="admin_orders.php">ORDERS</a>
        <?php else: ?>
          <a href="catalog.php">CATALOG</a>
          <a href="wishlist.php">WISHLIST</a>
          <a href="cart.php">CART</a>
          <a href="my_orders.php">MY ORDERS</a>
        <?php endif; ?>
      </div>
    </nav>
  <?php endif; ?>

  <div class="container">
    <div class="hero">
      <h2>Discover the World's Finest Timepieces</h2>
      <p>ShopSphere curates an exclusive collection of luxury watches from the most prestigious brands. Each timepiece represents the pinnacle of craftsmanship, precision engineering, and timeless design. From classic dress watches to modern sports chronographs, find your perfect companion for life's most important moments.</p>
      
      <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="cta-buttons">
          <a href="login.php" class="btn btn-primary">Sign In</a>
          <a href="register.php" class="btn">Create Account</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="featured">
      <h3>Featured Collection</h3>
      <div class="watch-grid">
        <div class="watch-card">
          <div class="watch-image-container">
            <img src="images/rolex-submariner.jpg" alt="Rolex Submariner" class="watch-image" onerror="this.src='https://via.placeholder.com/350x350/1a1a1a/666666?text=Rolex'">
          </div>
          <div class="watch-info">
            <div class="watch-brand">Rolex</div>
            <div class="watch-name">Submariner Date</div>
            <div class="watch-price">£9,250</div>
          </div>
        </div>

        <div class="watch-card">
          <div class="watch-image-container">
            <img src="images/omega-speedmaster.jpg" alt="Omega Speedmaster" class="watch-image" onerror="this.src='https://via.placeholder.com/350x350/1a1a1a/666666?text=Omega'">
          </div>
          <div class="watch-info">
            <div class="watch-brand">Omega</div>
            <div class="watch-name">Speedmaster Professional</div>
            <div class="watch-price">£6,100</div>
          </div>
        </div>

        <div class="watch-card">
          <div class="watch-image-container">
            <img src="images/ap-royal-oak.jpg" alt="Audemars Piguet Royal Oak" class="watch-image" onerror="this.src='https://via.placeholder.com/350x350/1a1a1a/666666?text=AP'">
          </div>
          <div class="watch-info">
            <div class="watch-brand">Audemars Piguet</div>
            <div class="watch-name">Royal Oak</div>
            <div class="watch-price">£28,500</div>
          </div>
        </div>
      </div>
    </div>

    <?php if (!isset($_SESSION['user_id'])): ?>
      <div class="login-prompt">
        <h4>Explore Our Full Collection</h4>
        <p>Sign in to browse our complete catalog, save favorites to your wishlist, and access exclusive member benefits.</p>
        <div class="cta-buttons">
          <a href="login.php" class="btn btn-primary">Sign In to Continue</a>
          <a href="register.php" class="btn">Create Free Account</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
