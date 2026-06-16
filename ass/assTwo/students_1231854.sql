DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id VARCHAR(10) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(30) NOT NULL,
    dob DATE NOT NULL,
    flat VARCHAR(50),
    street VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL,
    postal_code VARCHAR(6) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL,
    photo1 VARCHAR(100) NOT NULL,
    photo2 VARCHAR(100) NOT NULL,
    photo3 VARCHAR(100) NOT NULL,
    default_photo VARCHAR(100) NOT NULL
) AUTO_INCREMENT = 1001;

CREATE TABLE orders (
    order_id VARCHAR(10) PRIMARY KEY,
    user_id VARCHAR(10) NOT NULL,
    order_date DATETIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE order_items (
    order_item_id VARCHAR(10) PRIMARY KEY,
    order_id VARCHAR(10) NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

/* Testing accounts visible section
Employee username/email: fares.moreb@icloud.com password: fares1234
Customer username/email: cristiano.ronaldo@icloud.com password: SUII123
Customer username/email: Lionel.Messi@icloud.com password: Messi123
*/

INSERT INTO users (user_id, first_name, last_name, email, mobile, dob, flat, street, city, country, postal_code, password_hash, role) VALUES
('1231854001', 'Fares', 'Moreb', 'fares.moreb@icloud.com', '0594441605', '2003-04-18', '1', 'University Street 3', 'Hebron', 'Palestine', '112233', '5807ddbe0cf39c82f6ba0bb790a0f7e6', 'employee'),
('1231854002', 'Cristiano', 'Ronaldo', 'cristiano.ronaldo@icloud.com', '0599000001', '1985-02-05', '7', 'Grape Market Street 12', 'Hebron', 'Palestine', '123456', '0332113c2ee94d6eaeac0b457633baa7', 'customer'),
('1231854003', 'Lionel', 'Messi', 'Lionel.Messi@icloud.com', '0599000002', '1987-06-24', '10', 'Old City Street 18', 'Hebron', 'Palestine', '654321', '6b55a829ee8665118e225c1bb345c002', 'customer');

INSERT INTO products (product_id, product_name, category, description, price, quantity, rating, photo1, photo2, photo3, default_photo) VALUES
(1001, 'Grape Vine Leaves', 'Grape Vine Leaves', 'Tender Palestinian grape vine leaves prepared for stuffed grape leaves, inspired by family cooking traditions in Hebron.', 10.50, 45, 4.9, 'Warak.png', 'Warak.png', 'Warak.png', 'Warak.png'),
(1002, 'Black Leaves', 'Grape Vine Leaves', 'Darker baladi grape leaves with a firm texture and a pleasantly sour taste for slow-cooked traditional dishes.', 12.00, 28, 4.8, 'BlackLeaves.png', 'BlackLeaves.png', 'BlackLeaves.png', 'BlackLeaves.png'),
(1003, 'White Leaves', 'Grape Vine Leaves', 'Lighter grape leaves with a softer flavor and medium thickness, packed for easy rolling and home cooking.', 9.00, 34, 4.7, 'WhiteLeaves.png', 'WhiteLeaves.png', 'WhiteLeaves.png', 'WhiteLeaves.png'),
(1004, 'Fresh Grapes from Hebron', 'Fresh Grapes', 'A fresh mixed grape pack from Hebron farms, selected for customers who want a simple Palestinian food gift.', 13.50, 50, 4.8, 'grapes.png', 'grapes.png', 'grapes.png', 'grapes.png'),
(1005, 'Dabouqi Grapes', 'Fresh Grapes', 'Dabouqi is a well-known Palestinian grape variety used for eating fresh, making juice, and preparing raisins.', 12.00, 36, 4.6, 'Dabouqi.png', 'Dabouqi.png', 'Dabouqi.png', 'Dabouqi.png'),
(1006, 'Jandali Grapes', 'Fresh Grapes', 'Small yellow Jandali grapes with tightly packed fruit and a clean sweet taste from local vineyards.', 13.00, 30, 4.5, 'Jandali.png', 'Jandali.png', 'Jandali.png', 'Jandali.png'),
(1007, 'Zini Grapes', 'Fresh Grapes', 'Zini is a white table grape with elongated fruit, suitable for fresh eating and elegant food baskets.', 15.00, 25, 4.7, 'Zini.png', 'Zini.png', 'Zini.png', 'Zini.png'),
(1008, 'Hamadani Grapes', 'Fresh Grapes', 'Late-ripening Hamadani grapes with round yellow fruit and a sweet flavor that reflects Hebron harvest season.', 14.00, 27, 4.6, 'Hamadani.png', 'Hamadani.png', 'Hamadani.png', 'Hamadani.png'),
(1009, 'Baytuni Black Grapes', 'Fresh Grapes', 'A dark traditional grape variety from Palestine, chosen for customers who prefer a richer table grape.', 16.00, 22, 4.8, 'Baytuni.png', 'Baytuni.png', 'Baytuni.png', 'Baytuni.png'),
(1010, 'Halwani Red Grapes', 'Fresh Grapes', 'Sweet red Halwani grapes packed as a colorful Baladi Store product from Hebron farms.', 15.00, 24, 4.7, 'Halwani.png', 'Halwani.png', 'Halwani.png', 'Halwani.png'),
(1011, 'Palestinian Raisins', 'Grape Pantry', 'Naturally sweet Palestinian raisins made from local grapes and packed for snacks, desserts, and gift baskets.', 8.50, 60, 4.5, 'Zabeeb.png', 'Zabeeb.png', 'Zabeeb.png', 'Zabeeb.png'),
(1012, 'Grape Molasses', 'Grape Pantry', 'Traditional grape molasses with a deep sweet flavor, served with bread, desserts, or as a local Hebron food gift.', 11.75, 40, 4.8, 'Molasses.png', 'Molasses.png', 'Molasses.png', 'Molasses.png');
