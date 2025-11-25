<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Successful - ShopSphere</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; min-height: 100vh; display: flex; flex-direction: column; }
    .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; }
    .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
    .header p { font-size: 0.9em; color: #999; letter-spacing: 2px; text-transform: uppercase; font-weight: 300; }
    .success-container { max-width: 600px; margin: 80px auto; padding: 60px 50px; background: #111; border: 1px solid #222; text-align: center; }
    .success-icon { font-size: 64px; margin-bottom: 30px; color: #4CAF50; }
    .success-title { color: #fff; font-size: 1.8em; font-weight: 300; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 20px; }
    .success-message { color: #999; font-weight: 300; font-size: 15px; line-height: 1.8; margin-bottom: 40px; letter-spacing: 0.5px; }
    .button-group { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-top: 40px; padding-top: 40px; border-top: 1px solid #222; }
    .btn { background: transparent; border: 1px solid #444; color: #fff; padding: 14px 28px; cursor: pointer; font-size: 11px; font-weight: 400; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 2px; text-decoration: none; display: inline-block; }
    .btn:hover { background: #fff; color: #000; border-color: #fff; }
    .btn-primary { border-color: #666; }
    .btn-primary:hover { background: #666; color: #fff; border-color: #666; }
  </style>
</head>
<body>
  <div class="header">
    <h1>ShopSphere</h1>
    <p>Account Created</p>
  </div>

  <div class="success-container">
    <div class="success-icon">✓</div>
    <h2 class="success-title">Registration Successful</h2>
    <p class="success-message">
      Your account has been created successfully.<br>
      Welcome to ShopSphere. You can now sign in to explore our luxury watch collection.
    </p>

    <div class="button-group">
      <a href="/php/login.php" class="btn btn-primary">Sign In</a>
      <a href="/php/index.php" class="btn">Browse Watches</a>
    </div>
  </div>
</body>
</html>
