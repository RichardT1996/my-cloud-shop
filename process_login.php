<?php
// process_login.php - verifies user credentials via Azure Function App
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

// Call Azure Function App for authentication
$authUrl = 'https://shopsphere-authentication-hgcqhsergpe4cuhq.swedencentral-01.azurewebsites.net/api/login';
$authData = json_encode([
    'email' => $email,
    'password' => $password
]);

$ch = curl_init($authUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $authData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// If API call failed, fall back to local DB connection
if ($response === false || $httpCode !== 200) {
    error_log("Auth API failed: HTTP $httpCode - $curlError. Falling back to local DB.");
    
    // Fallback: DB connection (same settings as process_register.php)
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

    // Redirect admin to dashboard, regular users to catalog (fallback path)
    if ($user['email'] === 'admin@gmail.com') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: catalog.php');
    }
    exit();
}

// API call succeeded - parse response
error_log("✓ Auth API success: HTTP $httpCode - Using Azure Function for authentication");
$apiResponse = json_decode($response, true);

if (!$apiResponse || !isset($apiResponse['success']) || !$apiResponse['success']) {
    $errorMsg = isset($apiResponse['error']) ? $apiResponse['error'] : 'Authentication failed';
    header('Location: login.php?error=' . urlencode($errorMsg));
    exit();
}

// Verify password locally (the API returns the hashed password for security)
if (!isset($apiResponse['hashed_password'])) {
    header('Location: login.php?error=' . urlencode('Invalid API response'));
    exit();
}

$hashedPassword = $apiResponse['hashed_password'];
if (!password_verify($password, $hashedPassword)) {
    header('Location: login.php?error=' . urlencode('Incorrect password'));
    exit();
}

// Success: set session from API response
$user = $apiResponse['user'];
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];

// Redirect based on admin status
if (isset($user['is_admin']) && $user['is_admin']) {
    header('Location: admin_dashboard.php');
} else {
    header('Location: catalog.php');
}
exit();
