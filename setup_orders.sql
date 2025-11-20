-- setup_orders.sql
-- Create tables for order management system

-- Drop tables if they exist (in correct order due to foreign keys)
IF OBJECT_ID('dbo.order_items', 'U') IS NOT NULL 
    DROP TABLE dbo.order_items;
IF OBJECT_ID('dbo.orders', 'U') IS NOT NULL 
    DROP TABLE dbo.orders;
GO

-- Create orders table
CREATE TABLE orders (
    id INT PRIMARY KEY IDENTITY(1,1),
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    
    -- Shipping information
    shipping_name VARCHAR(255) NOT NULL,
    shipping_address VARCHAR(500) NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_postcode VARCHAR(20) NOT NULL,
    shipping_country VARCHAR(100) NOT NULL,
    
    -- Payment information
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    
    -- Timestamps
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME NOT NULL DEFAULT GETDATE(),
    
    CONSTRAINT FK_orders_user FOREIGN KEY (user_id) REFERENCES shopusers(id)
);
GO

-- Create order_items table
CREATE TABLE order_items (
    id INT PRIMARY KEY IDENTITY(1,1),
    order_id INT NOT NULL,
    watch_id INT NOT NULL,
    watch_name VARCHAR(255) NOT NULL,
    watch_brand VARCHAR(100) NOT NULL,
    watch_price DECIMAL(10,2) NOT NULL,
    watch_image_url VARCHAR(500),
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    
    CONSTRAINT FK_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT FK_order_items_watch FOREIGN KEY (watch_id) REFERENCES watches(id),
    CONSTRAINT CK_order_items_quantity CHECK (quantity > 0)
);
GO

-- Create indexes for better performance
CREATE INDEX IX_orders_user_id ON orders(user_id);
CREATE INDEX IX_orders_status ON orders(status);
CREATE INDEX IX_orders_created_at ON orders(created_at DESC);
CREATE INDEX IX_order_items_order_id ON order_items(order_id);
GO

PRINT 'Orders tables created successfully!';
PRINT 'Order statuses: pending, processing, shipped, delivered, cancelled';
GO
