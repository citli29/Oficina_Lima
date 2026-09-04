--CREATE TABLE services_products_requested (
--    id INTEGER PRIMARY KEY,
--    service_id INTEGER NOT NULL,
--    quantity INTEGER NOT NULL,
--    product_id INTEGER NOT NULL,
--    is_ordered INTEGER NOT NULL DEFAULT 0,
--    is_delivered INTEGER NOT NULL DEFAULT 0,
--
--    FOREIGN KEY (service_id) REFERENCES services(id),
--    FOREIGN KEY (product_id) REFERENCES products(id)
--);


CREATE TABLE service_types (
    id INTEGER PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

INSERT INTO service_types(id, name) VALUES (1,"Mecânica");
INSERT INTO service_types(id, name) VALUES (2,"Laboratório");

ALTER TABLE services 
ADD COLUMN service_type_id INTEGER NOT NULL DEFAULT 1 REFERENCES service_types(id);
