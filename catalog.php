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
    body { font-family: Arial, sans-serif; background: #f4f4f4; }
    .header { background: #2c3e50; color: #fff; padding: 20px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .header h1 { margin-bottom: 5px; }
    .user-bar { background: #34495e; color: #fff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
    .user-bar .user-info { font-size: 14px; }
    .user-bar a { color: #fff; text-decoration: none; padding: 8px 16px; background: #e74c3c; border-radius: 4px; font-size: 14px; }
    .user-bar a:hover { background: #c0392b; }
    .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
    .catalog-header { text-align: center; margin-bottom: 30px; }
    .catalog-header h2 { color: #2c3e50; margin-bottom: 10px; }
    .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
    .watch-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; }
    .watch-card:hover { transform: translateY(-4px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
    .watch-image-container { width: 100%; height: 280px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .watch-image { max-width: 100%; max-height: 100%; object-fit: contain; }
    .watch-info { padding: 16px; }
    .watch-brand { color: #7f8c8d; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .watch-name { font-size: 18px; color: #2c3e50; margin-bottom: 8px; font-weight: bold; }
    .watch-description { color: #7f8c8d; font-size: 14px; margin-bottom: 12px; line-height: 1.4; }
    .watch-price { font-size: 22px; color: #27ae60; font-weight: bold; }
    .empty-message { text-align: center; padding: 60px 20px; color: #7f8c8d; }
    .empty-message p { font-size: 18px; margin-bottom: 20px; }
    .btn { display: inline-block; background: #007bff; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 4px; }
    .btn:hover { background: #0056b3; }
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
