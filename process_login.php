<?php
// process_login.php - verifies user credentials and starts a session
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($email === '' || $password === '') {
    header('Location: login.php?error=' . urlencode('Please provide both email and password'));
    exit();
}

// DB connection (same settings as process_register.php)
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
    header('Location: login.php?error=' . urlencode($msg));
    exit();
}

// Look up user by email
$sql = "SELECT id, name, email, password FROM shopusers WHERE email = ?";
$params = array($email);
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    $errors = sqlsrv_errors();
    $msg = "Query failed";
    if ($errors != null) {
        foreach ($errors as $error) {
            $msg .= ": " . $error['message'];
        }
    }
    sqlsrv_close($conn);
    header('Location: login.php?error=' . urlencode($msg));
    exit();
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$user) {
    // No user found
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    header('Location: login.php?error=' . urlencode('No account found for that email'));
    exit();
}

// Verify password (stored hashed)
$hashed = $user['password'];
if (!password_verify($password, $hashed)) {
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    header('Location: login.php?error=' . urlencode('Incorrect password'));
    exit();
}

// Success: set session and redirect
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

// Redirect admin to dashboard, regular users to catalog
if ($user['email'] === 'admin@gmail.com') {
    header('Location: admin_dashboard.php');
} else {
    header('Location: catalog.php');
}
exit();
