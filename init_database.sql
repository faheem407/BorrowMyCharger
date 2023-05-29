DROP DATABASE IF EXISTS borrow_my_charger;
CREATE DATABASE borrow_my_charger;

CREATE TABLE borrow_my_charger.user (user_id INTEGER PRIMARY KEY AUTO_INCREMENT, fullname VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL UNIQUE, pass VARCHAR(60) NOT NULL, phone VARCHAR(11) NOT NULL, img_url VARCHAR(50) DEFAULT 'default.jpg');
CREATE TABLE borrow_my_charger.charge_point (cp_id INTEGER PRIMARY KEY AUTO_INCREMENT, address1 VARCHAR(50) NOT NULL, address2 VARCHAR(50) NOT NULL, post_code VARCHAR(8), lat FLOAT NOT NULL, lng FLOAT NOT NULL, cost FLOAT UNSIGNED NOT NULL, owner_id INTEGER NOT NULL UNIQUE, FOREIGN KEY (owner_id) REFERENCES borrow_my_charger.user(user_id));

LOAD DATA INFILE 'C:/xampp/htdocs/MVCtemplate/users.csv' INTO TABLE borrow_my_charger.user FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n' IGNORE 1 LINES (`user_id`, `fullname`, `email`, `pass`, `phone`) SET img_url='default.jpg';
LOAD DATA INFILE 'C:/xampp/htdocs/MVCtemplate/charge_points.csv' INTO TABLE borrow_my_charger.charge_point FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;