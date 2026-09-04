CREATE TABLE services_products_requested (
    id INTEGER PRIMARY KEY,
    service_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    is_ordered INTEGER NOT NULL DEFAULT 0,
    is_delivered INTEGER NOT NULL DEFAULT 0,

    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
