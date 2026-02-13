CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    description TEXT
);

-- Insert sample data
INSERT INTO products (name, price, image, description) VALUES
('Gaming Mouse', 45.00, 'mouse.jpg', 'High precision optical sensor'),
('Mechanical Keyboard', 85.00, 'kb.jpg', 'RGB backlit blue switches'),
('Gaming Monitor', 199.00, 'monitor.jpg', '144Hz 1ms response time');