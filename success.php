<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Successful - ShopSphere</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; }
    .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; }
    .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
    .header .tagline { font-size: 12px; color: #888; letter-spacing: 2px; text-transform: uppercase; }
    .container { max-width: 700px; margin: 0 auto; padding: 100px 40px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: calc(100vh - 150px); }
    .success-card { background: #111; border: 1px solid #222; padding: 60px 50px; text-align: center; width: 100%; }
    .success-icon { width: 80px; height: 80px; background: #1a1a1a; border: 2px solid #333; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; font-size: 40px; }
    .success-card h2 { font-size: 2em; color: #fff; margin-bottom: 20px; font-weight: 300; letter-spacing: 3px; text-transform: uppercase; }
    .success-card p { font-size: 1em; color: #888; margin-bottom: 40px; font-weight: 300; letter-spacing: 1px; line-height: 1.6; }
    .btn-group { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
    .btn { display: inline-block; padding: 15px 40px; background: transparent; border: 1px solid #444; color: #fff; text-decoration: none; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; }
    .btn:hover { background: #fff; color: #000; border-color: #fff; }
    .btn-primary { border-color: #fff; }
  </style>
</head>
<body>
  <div class="header">
    <h1>SHOPSPHERE</h1>
    <div class="tagline">Luxury Timepieces</div>
  </div>

  <div class="container">
    <div class="success-card">
      <div class="success-icon">✓</div>
      <h2>Registration Successful!</h2>
      <p>Your account has been created successfully. Welcome to ShopSphere - explore our exclusive collection of luxury timepieces.</p>

      <div class="btn-group">
        <a href="login.php" class="btn btn-primary">Sign In Now</a>
        <a href="index.php" class="btn">Back to Home</a>
      </div>
    </div>
  </div>
</body>
</html>
