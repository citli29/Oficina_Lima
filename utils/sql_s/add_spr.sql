--CREATE TABLE services_products_requested (
    --id INTEGER PRIMARY KEY,
    --service_id INTEGER NOT NULL,
    --quantity INTEGER NOT NULL,
    --product_id INTEGER NOT NULL,
    --is_ordered INTEGER NOT NULL DEFAULT 0,
    --is_delivered INTEGER NOT NULL DEFAULT 0,
--
    --FOREIGN KEY (service_id) REFERENCES services(id),
    ----FOREIGN KEY (product_id) REFERENCES products(id)
--);
--
--
--CREATE TABLE service_types (
    --id INTEGER PRIMARY KEY,
    --name VARCHAR(255) NOT NULL
--);
--
--INSERT INTO service_types(id, name) VALUES (1,"Mecânica");
--INSERT INTO service_types(id, name) VALUES (2,"Laboratório");
--
--ALTER TABLE services ADD COLUMN service_type_id 
--INTEGER NOT NULL DEFAULT 1 REFERENCES service_types(id);

--CREATE TABLE notification_types( 
	--id INTEGER PRIMARY KEY,
	--name VARCHAR(255) NOT NULL
--);

--INSERT INTO notification_types(id, name) VALUES (1,"Geral");
--INSERT INTO notification_types(id, name) VALUES (2,"Escritório");
--INSERT INTO notification_types(id, name) VALUES (3,"Oficina");

--CREATE TABLE notifications( 
--	id INTEGER PRIMARY KEY,
--	notification_type_id iNTEGER NOT NULL DEFAULT 1,
--	title TEXT NOT NULL,
--	message TEXT NOT NULL,
--	data TEXT NOT NULL,
--	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--	is_checked INTEGER NOT NULL DEFAULT 0,

--	FOREIGN KEY (notification_type_id) REFERENCES notification_types(id)
--);

--INSERT INTO notifications(title,message,data) VALUES ("ititulo","emessage","{url:gang}");

--ALTER TABLE services ADD COLUMN r_name VARCHAR(255);
--ALTER TABLE services ADD COLUMN r_phone VARCHAR(50);

--ALTER TABLE services ADD COLUMN checkout_predict VARCHAR(20);
--ALTER TABLE services ADD COLUMN signed_service VARCHAR(512);

