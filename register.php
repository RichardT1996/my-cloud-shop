<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - ShopSphere</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; min-height: 100vh; display: flex; flex-direction: column; }
    .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; }
    .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
    .header p { font-size: 0.9em; color: #999; letter-spacing: 2px; text-transform: uppercase; font-weight: 300; }
    .register-form { max-width: 450px; margin: 80px auto; padding: 50px; background: #111; border: 1px solid #222; }
    .form-title { text-align: center; margin-bottom: 40px; color: #fff; font-size: 1.5em; font-weight: 300; letter-spacing: 3px; text-transform: uppercase; }
    .form-group { margin-bottom: 25px; }
    label { display: block; margin-bottom: 10px; color: #999; font-weight: 300; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; }
    input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 14px 0; border: none; border-bottom: 1px solid #333; box-sizing: border-box; font-size: 15px; background: transparent; color: #fff; transition: all 0.3s ease; font-weight: 300; }
    input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus { outline: none; border-bottom-color: #666; }
    input[type="text"]::placeholder, input[type="email"]::placeholder, input[type="password"]::placeholder { color: #444; }
    .btn { background: transparent; border: 1px solid #444; color: #fff; padding: 14px 20px; cursor: pointer; width: 100%; font-size: 11px; font-weight: 400; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 2px; margin-top: 10px; }
    .btn:hover { background: #fff; color: #000; border-color: #fff; }
    .error { background: #1a0000; border: 1px solid #330000; color: #ff6b6b; padding: 14px; margin-bottom: 25px; font-weight: 300; font-size: 13px; letter-spacing: 0.5px; }
    .links { text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #222; }
    .links a { color: #999; text-decoration: none; font-weight: 300; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; transition: all 0.3s ease; }
    .links a:hover { color: #fff; }
  </style>
</head>
<body>
  <div class="header">
    <h1>ShopSphere</h1>
    <p>Create Your Account</p>
  </div>

  <div class="register-form">
    <h2 class="form-title">Register</h2>

    <!-- Display error message if present -->
    <?php if (isset($_GET['error'])): ?>
      <div class="error">
        <?php echo htmlspecialchars($_GET['error']); ?>
      </div>
    <?php endif; ?>

    <!-- Form submits to process_register.php -->
    <form method="post" action="process_register.php">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" required>
      </div>

      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <div class="form-group">
        <input type="submit" class="btn" value="Create Account">
      </div>
    </form>

    <div class="links">
      <a href="login.php">Sign In</a>
      <span style="color:#333;">|</span>
      <a href="index.php">Back to Home</a>
    </div>
  </div>
</body>
</html>
