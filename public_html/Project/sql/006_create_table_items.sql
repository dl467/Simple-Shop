
CREATE TABLE IF NOT EXISTS Products(
    id int AUTO_INCREMENT PRIMARY  KEY,
    name varchar(30) UNIQUE, 
    description text,
    category text, 
    stock int DEFAULT  0,
    cost int DEFAULT  99999,
    visibility boolean, 
    image text, 
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
)