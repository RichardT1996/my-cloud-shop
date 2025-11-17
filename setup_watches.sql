-- SQL Script to create watches table and add sample data
-- Run this in your Azure SQL Database (myDatabase)

-- Create watches table
CREATE TABLE watches (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(200) NOT NULL,
    brand NVARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description NVARCHAR(500),
    image_url NVARCHAR(500),
    created_at DATETIME DEFAULT GETDATE()
);

-- Insert sample watches
INSERT INTO watches (name, brand, price, description, image_url) VALUES
('Submariner Date', 'Rolex', 12500.00, 'Iconic dive watch with date function and 300m water resistance', 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=400'),
('Speedmaster Professional', 'Omega', 6800.00, 'Legendary moonwatch with chronograph functionality', 'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?w=400'),
('Royal Oak', 'Audemars Piguet', 28000.00, 'Luxury sports watch with distinctive octagonal bezel', 'https://images.unsplash.com/photo-1622434641406-a158123450f9?w=400'),
('Nautilus', 'Patek Philippe', 35000.00, 'Elegant sports watch with integrated bracelet design', 'https://images.unsplash.com/photo-1594534475808-b18fc33b045e?w=400'),
('Datejust 41', 'Rolex', 9500.00, 'Classic dress watch with automatic movement and date display', 'https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?w=400'),
('Seamaster Diver 300M', 'Omega', 5200.00, 'Professional diving watch with helium escape valve', 'https://images.unsplash.com/photo-1606390730160-e199e4c2c8ba?w=400'),
('Tank Must', 'Cartier', 3400.00, 'Iconic rectangular watch with Roman numerals', 'https://images.unsplash.com/photo-1611103829433-64d2c7e9797d?w=400'),
('Reverso Classic', 'Jaeger-LeCoultre', 7800.00, 'Reversible case design, art deco inspired timepiece', 'https://images.unsplash.com/photo-1547996160-81dfa63595aa?w=400'),
('Oyster Perpetual 36', 'Rolex', 5900.00, 'Entry-level Rolex with timeless design and reliability', 'https://images.unsplash.com/photo-1609587312208-cea54be969e7?w=400'),
('Aqua Terra 150M', 'Omega', 6100.00, 'Versatile watch suitable for both dress and sport occasions', 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=400'),
('Santos de Cartier', 'Cartier', 7200.00, 'Aviation-inspired square case with exposed screws', 'https://images.unsplash.com/photo-1533139502658-0198f920d8e8?w=400'),
('Day-Date 40', 'Rolex', 36000.00, 'Prestigious presidents watch with day and date display', 'https://images.unsplash.com/photo-1622434641406-a158123450f9?w=400');

-- Verify the data
SELECT * FROM watches ORDER BY brand, name;
