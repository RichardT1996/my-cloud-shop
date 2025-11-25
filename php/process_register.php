<?php
// MySQL database connection
require_once '../db_config.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation
    if (!empty($name) && !empty($email) && !empty($password)) {
        try {
            // Connect to database
            $conn = getDbConnection();
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into database
            $sql = "INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $hashed_password]);
            
            // Redirect to success page
            header("Location: /php/success.php");
            exit();
            
        } catch (PDOException $e) {
            // Handle duplicate email error
            if ($e->getCode() == 23000) {
                $error_message = "Email address already registered";
            } else {
                $error_message = "Database error: " . $e->getMessage();
                error_log("Registration error: " . $e->getMessage());
            }
            header("Location: /php/register.php?error=" . urlencode($error_message));
            exit();
        } catch (Exception $e) {
            $error_message = "System error: " . $e->getMessage();
            error_log("Registration error: " . $e->getMessage());
            header("Location: /php/register.php?error=" . urlencode($error_message));
            exit();
        }
    } else {
        header("Location: /php/register.php?error=Please+fill+all+fields");
        exit();
    }
} else {
    // If someone tries to access this page directly
    header("Location: /php/register.php");
    exit();
}
?>
