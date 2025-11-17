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
  <title>Login - ShopSphere</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; flex-direction: column; }
    .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 30px 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
    .header h1 { font-size: 2.5em; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); margin-bottom: 8px; }
    .header p { font-size: 1.1em; opacity: 0.95; }
    .container { max-width: 450px; margin: 60px auto; padding: 40px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.8); }
    .form-title { text-align: center; margin-bottom: 30px; color: #2c3e50; font-size: 1.8em; font-weight: 700; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 600; font-size: 14px; }
    input[type="email"], input[type="password"] { width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 10px; box-sizing: border-box; font-size: 15px; transition: all 0.3s ease; background: #fff; }
    input[type="email"]:focus, input[type="password"]:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
    .btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 14px 20px; border: none; border-radius: 10px; cursor: pointer; width: 100%; font-size: 16px; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); text-transform: uppercase; letter-spacing: 0.5px; }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6); }
    .btn:active { transform: translateY(0); }
    .error { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: #fff; padding: 14px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3); }
    .links { text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e0e0e0; }
    .links a { color: #667eea; text-decoration: none; font-weight: 600; margin: 0 12px; transition: all 0.3s ease; }
    .links a:hover { color: #764ba2; text-decoration: underline; }
  </style>
</head>
<body>
  <div class="header"><h1>ShopSphere</h1><p>Log in to your account</p></div>
  <div class="container">
    <h2 class="form-title">Welcome Back</h2>
    <?php if (isset($_GET['error'])): ?>
      <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <form method="post" action="process_login.php">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="your@email.com" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
      </div>
      <div class="form-group">
        <button type="submit" class="btn">Sign In</button>
      </div>
    </form>
    <div class="links">
      <a href="register.php">Create an account</a>
      <span style="color:#ccc;">|</span>
      <a href="index.php">Back to Home</a>
    </div>
  </div>
</body>
</html>
