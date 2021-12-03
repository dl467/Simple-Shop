CREATE TABLE IF NOT EXISTS Cart(
    id int AUTO_INCREMENT PRIMARY KEY,
    product_id int,
    user_id int,
    quantity int,
    unit_cost int DEFAULT 0,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (product_id) REFERENCES Products(id),
    UNIQUE KEY (product_id, user_id),
    check (quantity >= 0)
)