-- SQL Script to create wishlist table
-- Run this in your MySQL Database

-- Drop table if exists
DROP TABLE IF EXISTS wishlist;

-- Create wishlist table
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    watch_id INT NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES shopusers(id) ON DELETE CASCADE,
    FOREIGN KEY (watch_id) REFERENCES watches(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_watch (user_id, watch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for faster queries
CREATE INDEX idx_wishlist_user ON wishlist(user_id);
CREATE INDEX idx_wishlist_watch ON wishlist(watch_id);

SELECT 'Wishlist table created successfully' AS message;
