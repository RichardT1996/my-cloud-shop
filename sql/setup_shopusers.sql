-- Create shopusers table
-- This table stores user account information for the ShopSphere application

-- Drop table if exists
DROP TABLE IF EXISTS shopusers;

-- Create shopusers table
CREATE TABLE shopusers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index on email for faster lookups
CREATE INDEX IX_shopusers_email ON shopusers(email);

SELECT 'shopusers table created successfully' AS message;
