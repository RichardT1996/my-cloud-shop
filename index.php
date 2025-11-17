<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShopSphere - Welcome</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 40px 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
    .header h1 { font-size: 3em; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); margin-bottom: 10px; }
    .header p { font-size: 1.2em; opacity: 0.95; }
    .container { max-width: 1200px; margin: 0 auto; padding: 60px 20px; }
    .user-info { text-align: center; padding: 20px 30px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); margin-bottom: 30px; border-radius: 15px; color: #2c3e50; font-size: 1.1em; box-shadow: 0 8px 24px rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.8); }
    .user-info strong { color: #667eea; font-size: 1.2em; }
    .welcome-text { text-align: center; margin: 40px 0; color: #fff; }
    .welcome-text h2 { font-size: 2.5em; margin-bottom: 15px; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
    .welcome-text > p { font-size: 1.3em; margin-bottom: 40px; opacity: 0.95; }
    .actions { text-align: center; margin-top: 30px; display: flex; justify-content: center; flex-wrap: wrap; gap: 15px; }
    .btn { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 16px 40px; text-decoration: none; border-radius: 30px; font-size: 18px; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4); text-transform: uppercase; letter-spacing: 0.5px; }
    .btn:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(102, 126, 234, 0.6); }
    .btn-logout { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); box-shadow: 0 8px 20px rgba(245, 87, 108, 0.4); }
    .btn-logout:hover { box-shadow: 0 12px 30px rgba(245, 87, 108, 0.6); }
    .btn-secondary { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4); }
    .btn-secondary:hover { box-shadow: 0 12px 30px rgba(79, 172, 254, 0.6); }
  </style>
</head>
<body>
  <div class="header">
    <div class="container">
      <h1>ShopSphere</h1>
      <p>Your one-stop destination for amazing products</p>
    </div>
  </div>

  <div class="container">
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="user-info">
        <strong>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</strong> 
        (<?php echo htmlspecialchars($_SESSION['user_email']); ?>)
      </div>
    <?php endif; ?>
    <div class="welcome-text">
      <h2>Welcome to ShopSphere</h2>
      <p>Join our community today to access exclusive deals and features!</p>
      <div class="actions">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="catalog.php" class="btn">Browse Watches</a>
          <a href="logout.php" class="btn btn-logout">Sign Out</a>
        <?php else: ?>
          <a href="login.php" class="btn">Sign In</a>
          <a href="register.php" class="btn">Create Account</a>
        <?php endif; ?>
        <a href="view_users.php" class="btn btn-secondary">View Members</a>
      </div>
    </div>
  </div>
</body>
</html>
