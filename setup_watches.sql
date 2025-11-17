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
('Submariner Date', 'Rolex', 12500.00, 'Iconic dive watch with date function and 300m water resistance', 'images/rolex-submariner.jpg'),
('Speedmaster Professional', 'Omega', 6800.00, 'Legendary moonwatch with chronograph functionality', 'images/omega-speedmaster.jpg'),
('Royal Oak', 'Audemars Piguet', 28000.00, 'Luxury sports watch with distinctive octagonal bezel', 'images/ap-royal-oak.jpg'),
('Nautilus', 'Patek Philippe', 35000.00, 'Elegant sports watch with integrated bracelet design', 'images/patek-nautilus.jpg'),
('Datejust 41', 'Rolex', 9500.00, 'Classic dress watch with automatic movement and date display', 'images/rolex-datejust.jpg'),
('Seamaster Diver 300M', 'Omega', 5200.00, 'Professional diving watch with helium escape valve', 'images/omega-seamaster.jpg'),
('Tank Must', 'Cartier', 3400.00, 'Iconic rectangular watch with Roman numerals', 'images/cartier-tank.jpg'),
('Reverso Classic', 'Jaeger-LeCoultre', 7800.00, 'Reversible case design, art deco inspired timepiece', 'images/jlc-reverso.jpg'),
('Oyster Perpetual 36', 'Rolex', 5900.00, 'Entry-level Rolex with timeless design and reliability', 'images/rolex-oyster-perpetual.jpg'),
('Aqua Terra 150M', 'Omega', 6100.00, 'Versatile watch suitable for both dress and sport occasions', 'images/omega-aqua-terra.jpg'),
('Santos de Cartier', 'Cartier', 7200.00, 'Aviation-inspired square case with exposed screws', 'images/cartier-santos.jpg'),
('Day-Date 40', 'Rolex', 36000.00, 'Prestigious presidents watch with day and date display', 'images/rolex-day-date.jpg');

-- Verify the data
SELECT * FROM watches ORDER BY brand, name;
