<?php
// login.php - simple login form
session_start();
// If already logged in, redirect to index or a dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - MyShop</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; }
    .header { background:#2c3e50; color:#fff; padding:20px; text-align:center; }
    .container { max-width:400px; margin:80px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.05); }
    .form-group { margin-bottom:12px; }
    label { display:block; margin-bottom:6px; }
    input[type="email"], input[type="password"] { width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; box-sizing:border-box; }
    .btn { background:#007bff; color:#fff; padding:10px 16px; border:none; border-radius:4px; cursor:pointer; width:100%; }
    .error { background:#f8d7da; color:#721c24; padding:10px; border-radius:4px; margin-bottom:12px; }
  </style>
</head>
<body>
  <div class="header"><h1>MyShop</h1><p>Log in to your account</p></div>
  <div class="container">
    <?php if (isset($_GET['error'])): ?>
      <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <form method="post" action="process_login.php">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group">
        <button type="submit" class="btn">Log In</button>
      </div>
    </form>
    <p style="text-align:center;"><a href="register.php">Create an account</a> | <a href="index.php">Home</a></p>
  </div>
</body>
</html>
