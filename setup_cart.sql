-- setup_cart.sql
-- Create cart table for shopping cart functionality

-- Drop table if exists
IF OBJECT_ID('dbo.cart', 'U') IS NOT NULL 
    DROP TABLE dbo.cart;
GO

-- Create cart table
CREATE TABLE cart (
    id INT PRIMARY KEY IDENTITY(1,1),
    user_id INT NOT NULL,
    watch_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at DATETIME NOT NULL DEFAULT GETDATE(),
    CONSTRAINT FK_cart_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT FK_cart_watch FOREIGN KEY (watch_id) REFERENCES watches(id),
    CONSTRAINT UQ_cart_user_watch UNIQUE (user_id, watch_id),
    CONSTRAINT CK_cart_quantity CHECK (quantity > 0)
);
GO

-- Create index for faster lookups
CREATE INDEX IX_cart_user_id ON cart(user_id);
CREATE INDEX IX_cart_watch_id ON cart(watch_id);
GO

PRINT 'Cart table created successfully';
