-- Create shopusers table
-- This table stores user account information for the ShopSphere application

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='shopusers' AND xtype='U')
BEGIN
    CREATE TABLE shopusers (
        id INT PRIMARY KEY IDENTITY(1,1),
        name NVARCHAR(100) NOT NULL,
        email NVARCHAR(255) NOT NULL UNIQUE,
        password NVARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE()
    );

    PRINT 'shopusers table created successfully';
END
ELSE
BEGIN
    PRINT 'shopusers table already exists';
END
GO

-- Create index on email for faster lookups
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_shopusers_email' AND object_id = OBJECT_ID('shopusers'))
BEGIN
    CREATE INDEX IX_shopusers_email ON shopusers(email);
    PRINT 'Index on email created successfully';
END
GO
