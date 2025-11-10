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
    body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
    .header { background: #2c3e50; color: #fff; padding: 20px; text-align: center; }
    .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    .welcome-text { text-align: center; margin: 40px 0; color: #555; font-size: 1.2em; }
    .actions { text-align: center; margin-top: 20px; }
    .btn { display: inline-block; background: #007bff; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-size: 18px; margin: 0 8px; }
    .btn:hover { background: #0056b3; }
    .btn-logout { background: #dc3545; }
    .btn-logout:hover { background: #c82333; }
    .user-info { text-align: center; padding: 10px; background: #e8f4f8; margin-bottom: 20px; border-radius: 4px; }
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
          <a href="logout.php" class="btn btn-logout">Log Out</a>
        <?php else: ?>
          <a href="login.php" class="btn">Log In</a>
          <a href="register.php" class="btn">Create Your Account</a>
        <?php endif; ?>
        <a href="view_users.php" class="btn">View Registered Users</a>
      </div>
    </div>
  </div>
</body>
</html>
