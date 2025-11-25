<?php
// admin_process.php - Process admin actions (add/edit/delete watches)
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /admin/login.php');
    exit();
}

// DB connection
require_once '../db_config.php';

try {
    $conn = getDbConnection();
} catch (Exception $e) {
    error_log("Admin process DB error: " . $e->getMessage());
    header('Location: /admin/admin_dashboard.php?error=' . urlencode('Database connection failed'));
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'add') {
    // Add new watch
    try {
        $brand = $_POST['brand'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $image_url = $_POST['image_url'];
        
        $sql = "INSERT INTO watches (name, brand, price, description, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $brand, $price, $description, $image_url]);
        
        header('Location: /admin/admin_dashboard.php?success=' . urlencode('Watch added successfully'));
        exit();
    } catch (Exception $e) {
        error_log("Add watch error: " . $e->getMessage());
        header('Location: /admin/admin_dashboard.php?error=' . urlencode('Failed to add watch'));
        exit();
    }
    
} elseif ($action === 'edit') {
    // Edit existing watch
    try {
        $id = $_POST['id'];
        $brand = $_POST['brand'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $image_url = $_POST['image_url'];
        
        $sql = "UPDATE watches SET name = ?, brand = ?, price = ?, description = ?, image_url = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $brand, $price, $description, $image_url, $id]);
        
        header('Location: /admin/admin_dashboard.php?success=' . urlencode('Watch updated successfully'));
        exit();
    } catch (Exception $e) {
        error_log("Update watch error: " . $e->getMessage());
        header('Location: /admin/admin_dashboard.php?error=' . urlencode('Failed to update watch'));
        exit();
    }
    
} elseif ($action === 'delete') {
    // Delete watch
    try {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM watches WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        
        header('Location: /admin/admin_dashboard.php?success=' . urlencode('Watch deleted successfully'));
        exit();
    } catch (Exception $e) {
        error_log("Delete watch error: " . $e->getMessage());
        header('Location: /admin/admin_dashboard.php?error=' . urlencode('Failed to delete watch'));
        exit();
    }
    
} else {
    header('Location: /admin/admin_dashboard.php?error=' . urlencode('Invalid action'));
    exit();
}
