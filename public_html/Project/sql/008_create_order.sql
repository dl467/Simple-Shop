CREATE TABLE IF NOT EXISTS Orders(
    id int AUTO_INCREMENT PRIMARY KEY,
    user_id int,
    total_price int,
    payment_method text,
    address text,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    check (total_price >= 0)
)