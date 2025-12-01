<?php
// admin_process.php - Process admin actions (add/edit/delete watches)
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// DB connection
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
    header('Location: admin_dashboard.php?error=' . urlencode($msg));
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'add') {
    // Add new watch
    $brand = $_POST['brand'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];
    
    $sql = "INSERT INTO watches (name, brand, price, description, image_url) VALUES (?, ?, ?, ?, ?)";
    $params = array($name, $brand, $price, $description, $image_url);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        header('Location: admin_dashboard.php?success=' . urlencode('Watch added successfully'));
        exit();
    } else {
        $errors = sqlsrv_errors();
        $msg = "Failed to add watch";
        if ($errors != null) {
            foreach ($errors as $error) {
                $msg .= ": " . $error['message'];
            }
        }
        sqlsrv_close($conn);
        header('Location: admin_dashboard.php?error=' . urlencode($msg));
        exit();
    }
    
} elseif ($action === 'edit') {
    // Edit existing watch
    $id = $_POST['id'];
    $brand = $_POST['brand'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];
    
    $sql = "UPDATE watches SET name = ?, brand = ?, price = ?, description = ?, image_url = ? WHERE id = ?";
    $params = array($name, $brand, $price, $description, $image_url, $id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        header('Location: admin_dashboard.php?success=' . urlencode('Watch updated successfully'));
        exit();
    } else {
        $errors = sqlsrv_errors();
        $msg = "Failed to update watch";
        if ($errors != null) {
            foreach ($errors as $error) {
                $msg .= ": " . $error['message'];
            }
        }
        sqlsrv_close($conn);
        header('Location: admin_dashboard.php?error=' . urlencode($msg));
        exit();
    }
    
} elseif ($action === 'delete') {
    // Delete watch
    $id = $_POST['id'];
    
    $sql = "DELETE FROM watches WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        header('Location: admin_dashboard.php?success=' . urlencode('Watch deleted successfully'));
        exit();
    } else {
        $errors = sqlsrv_errors();
        $msg = "Failed to delete watch";
        if ($errors != null) {
            foreach ($errors as $error) {
                $msg .= ": " . $error['message'];
            }
        }
        sqlsrv_close($conn);
        header('Location: admin_dashboard.php?error=' . urlencode($msg));
        exit();
    }
    
} else {
    sqlsrv_close($conn);
    header('Location: admin_dashboard.php?error=' . urlencode('Invalid action'));
    exit();
}
