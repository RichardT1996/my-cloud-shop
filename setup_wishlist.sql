-- SQL Script to create wishlist table
-- Run this in your Azure SQL Database (myDatabase)

-- Create wishlist table
CREATE TABLE wishlist (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    watch_id INT NOT NULL,
    added_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES shopusers(id) ON DELETE CASCADE,
    FOREIGN KEY (watch_id) REFERENCES watches(id) ON DELETE CASCADE,
    UNIQUE (user_id, watch_id)  -- Prevent duplicate entries
);

-- Create index for faster queries
CREATE INDEX idx_wishlist_user ON wishlist(user_id);
CREATE INDEX idx_wishlist_watch ON wishlist(watch_id);

-- Verify the table
SELECT * FROM wishlist;
