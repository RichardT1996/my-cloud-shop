<?php
// catalog.php - display watches catalog for logged-in users
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
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
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 30px 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
    .header h1 { margin-bottom: 8px; font-size: 2.5em; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
    .header p { font-size: 1.1em; opacity: 0.95; }
    .user-bar { background: rgba(0,0,0,0.15); backdrop-filter: blur(10px); color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .user-bar .user-info { font-size: 14px; }
    .user-bar a { color: #fff; text-decoration: none; padding: 10px 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 25px; font-size: 14px; font-weight: 600; transition: all 0.3s ease; margin-left: 8px; }
    .user-bar a:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4); }
    .user-bar a:first-child { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .user-bar a:first-child:hover { box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4); }
    .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .catalog-header { text-align: center; margin-bottom: 40px; }
    .catalog-header h2 { color: #fff; margin-bottom: 12px; font-size: 2em; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
    .catalog-header p { color: rgba(255,255,255,0.9); font-size: 1.1em; }
    .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
    .watch-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.12); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.8); }
    .watch-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 16px 40px rgba(0,0,0,0.2); }
    .watch-image-container { width: 100%; height: 300px; background: linear-gradient(135deg, #fdfcfb 0%, #e2d1c3 100%); display: flex; align-items: center; justify-content: center; padding: 30px; position: relative; overflow: hidden; }
    .watch-image-container::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%); }
    .watch-image { max-width: 100%; max-height: 100%; object-fit: contain; position: relative; z-index: 1; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1)); }
    .watch-info { padding: 24px; background: linear-gradient(to bottom, #fff 0%, #f8f9fa 100%); }
    .watch-brand { color: #6c757d; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6px; font-weight: 700; }
    .watch-name { font-size: 20px; color: #2c3e50; margin-bottom: 10px; font-weight: 700; line-height: 1.3; }
    .watch-description { color: #6c757d; font-size: 14px; margin-bottom: 16px; line-height: 1.5; }
    .watch-price { font-size: 26px; color: #10b981; font-weight: 800; text-shadow: 1px 1px 2px rgba(16, 185, 129, 0.2); }
    .empty-message { text-align: center; padding: 80px 20px; color: #fff; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 16px; margin-top: 40px; }
    .empty-message p { font-size: 20px; margin-bottom: 20px; }
    .btn { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 14px 32px; text-decoration: none; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6); }
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
      <a href="index.php" style="background:#3498db; margin-right:8px;">Home</a>
      <a href="logout.php">Log Out</a>
    </div>
  </div>

  <div class="container">
    <div class="catalog-header">
      <h2>Our Watch Collection</h2>
      <p>Discover luxury timepieces from the world's finest brands</p>
    </div>

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
                onerror="this.src='https://via.placeholder.com/280x220/cccccc/666666?text=Watch+Image'"
              >
            </div>
            <div class="watch-info">
              <div class="watch-brand"><?php echo htmlspecialchars($watch['brand']); ?></div>
              <div class="watch-name"><?php echo htmlspecialchars($watch['name']); ?></div>
              <div class="watch-description"><?php echo htmlspecialchars($watch['description']); ?></div>
              <div class="watch-price">$<?php echo number_format($watch['price'], 2); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
