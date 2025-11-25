-- setup_cart.sql
-- Create cart table for shopping cart functionality

-- Drop table if exists
DROP TABLE IF EXISTS cart;

-- Create cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    watch_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_cart_user FOREIGN KEY (user_id) REFERENCES shopusers(id),
    CONSTRAINT FK_cart_watch FOREIGN KEY (watch_id) REFERENCES watches(id),
    CONSTRAINT UQ_cart_user_watch UNIQUE (user_id, watch_id),
    CONSTRAINT CK_cart_quantity CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for faster lookups
CREATE INDEX IX_cart_user_id ON cart(user_id);
CREATE INDEX IX_cart_watch_id ON cart(watch_id);

SELECT 'Cart table created successfully' AS message;
