<?php
// view_users.php - display registered users from the shopusers table

$serverName = "tcp:mycardiffmet1.database.windows.net,1433";
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
  <title>Registered Users - MyShop</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; }
    .header { background:#2c3e50; color:#fff; padding:20px; text-align:center; }
    .container { max-width:1000px; margin:30px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.05); }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
    th { background:#fafafa; }
    .actions { margin-top:16px; }
    .btn { display:inline-block; background:#007bff; color:#fff; padding:8px 14px; text-decoration:none; border-radius:4px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>MyShop</h1>
    <p>Registered Users</p>
  </div>

  <div class="container">
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
      <a href="index.php" class="btn">Back to Home</a>
      <a href="register.php" class="btn">Register New User</a>
    </div>
  </div>

<?php
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
</body>
</html>
