<?php
// Simple database connection
$serverName = "tcp:shopspshere-dbserver.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "shopspheredb",
    "Uid" => "myadmin",
    "PWD" => "password123!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (!empty($name) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        // Password validation
        $password_errors = [];
        
        if (strlen($password) < 8) {
            $password_errors[] = 'Password must be at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $password_errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $password_errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $password_errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $password_errors[] = 'Password must contain at least one special character';
        }
        
        // Check if passwords match
        if ($password !== $confirm_password) {
            $password_errors[] = 'Passwords do not match';
        }
        
        // If there are validation errors, redirect back
        if (!empty($password_errors)) {
            header("Location: register.php?error=" . urlencode(implode('; ', $password_errors)));
            exit();
        }
        // Connect to database
        $conn = sqlsrv_connect($serverName, $connectionOptions);

        if ($conn) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into database
            $sql = "INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)";
            $params = array($name, $email, $hashed_password);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt) {
                // Redirect to success page
                header("Location: success.php");
                exit();
            } else {
                // Get detailed error information
                $errors = sqlsrv_errors();
                $error_message = "Database error: ";
                if ($errors != null) {
                    foreach ($errors as $error) {
                        $error_message .= "SQLSTATE: " . $error['SQLSTATE'] . ", ";
                        $error_message .= "Code: " . $error['code'] . ", ";
                        $error_message .= "Message: " . $error['message'];
                    }
                }
                // Redirect back with detailed error
                header("Location: register.php?error=" . urlencode($error_message));
                exit();
            }

            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
        } else {
            $connection_errors = sqlsrv_errors();
            $conn_error_message = "Database connection failed: ";
            if ($connection_errors != null) {
                foreach ($connection_errors as $error) {
                    $conn_error_message .= $error['message'];
                }
            }
            header("Location: register.php?error=" . urlencode($conn_error_message));
            exit();
        }
    } else {
        header("Location: register.php?error=Please+fill+all+fields");
        exit();
    }
} else {
    // If someone tries to access this page directly
    header("Location: register.php");
    exit();
}
?>
