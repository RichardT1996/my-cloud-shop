<?php
// view_users.php - display registered users from the shopusers table

$serverName = "tcp:shopspshere-dbserver.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "shopspheredb",
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

$sql = "SELECT id, name, email FROM shopusers ORDER BY id DESC";
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Registered Users - ShopSphere</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; min-height: 100vh; }
    .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; position: relative; }
    .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
    .header .tagline { font-size: 12px; color: #e74c3c; letter-spacing: 2px; text-transform: uppercase; }
    .welcome { position: absolute; top: 30px; right: 40px; color: #888; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
    .welcome span { color: #fff; margin-left: 5px; }
    .welcome a { color: #fff; text-decoration: none; margin-left: 15px; padding: 8px 16px; border: 1px solid #333; transition: all 0.3s ease; }
    .welcome a:hover { background: #fff; color: #000; border-color: #fff; }
    .nav { background: #111; border-bottom: 1px solid #222; padding: 0; }
    .nav ul { list-style: none; display: flex; justify-content: center; max-width: 1200px; margin: 0 auto; }
    .nav li { margin: 0; }
    .nav a { display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent; }
    .nav a:hover, .nav a.active { color: #fff; background: rgba(255,255,255,0.05); border-bottom-color: #fff; }
    .container { max-width: 1200px; margin: 60px auto; padding: 0 40px; }
    .page-title { font-size: 2.5em; font-weight: 300; letter-spacing: 3px; margin-bottom: 15px; text-align: center; }
    .page-subtitle { color: #888; text-align: center; margin-bottom: 50px; font-size: 13px; letter-spacing: 1px; }
    table { width: 100%; border-collapse: collapse; background: #111; border: 1px solid #222; }
    thead { background: #1a1a1a; border-bottom: 1px solid #222; }
    th { padding: 20px; text-align: left; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #888; font-weight: 400; border-bottom: 1px solid #222; }
    td { padding: 20px; border-bottom: 1px solid #222; color: #ccc; font-size: 13px; }
    tr:hover { background: rgba(255,255,255,0.02); }
    .actions { margin-top: 30px; text-align: center; }
    .btn { display: inline-block; padding: 12px 24px; background: transparent; border: 1px solid #444; color: #fff; text-decoration: none; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; margin: 0 5px; }
    .btn:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-2px); }
  </style>
</head>
<body>
  <div class="header">
    <h1>ShopSphere</h1>
    <div class="tagline">Admin Dashboard</div>
    <div class="welcome">
      Admin, <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a href="logout.php">Logout</a>
    </div>
  </div>
  
  <nav class="nav">
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="catalog.php">Catalog</a></li>
      <li><a href="admin_dashboard.php">Manage Products</a></li>
      <li><a href="view_users.php" class="active">Users</a></li>
      <li><a href="admin_orders.php">Orders</a></li>
    </ul>
  </nav>

  <div class="container">
    <h1 class="page-title">Registered Users</h1>
    <p class="page-subtitle">Manage customer accounts</p>
    <?php if (($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) === null) : ?>
      <p>No users found.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // first row already fetched above
            do {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($row['id']) . '</td>';
              echo '<td>' . htmlspecialchars($row['name']) . '</td>';
              echo '<td>' . htmlspecialchars($row['email']) . '</td>';
              echo '</tr>';
            } while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC));
          ?>
        </tbody>
      </table>
    <?php endif; ?>

    <div class="actions">
      <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
    </div>
  </div>

<?php
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
</body>
</html>
