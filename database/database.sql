	DROP TABLE IF EXISTS user_types;
	DROP TABLE IF EXISTS users;
	DROP TABLE IF EXISTS clients;

	DROP TABLE IF EXISTS makes;
	DROP TABLE IF EXISTS models;
	DROP TABLE IF EXISTS cars;

	DROP TABLE IF EXISTS product_types;
	DROP TABLE IF EXISTS products;

	DROP TABLE IF EXISTS schedules;
	DROP TABLE IF EXISTS services;
	DROP TABLE IF EXISTS services_user_time;
	DROP TABLE IF EXISTS applied_products;
	DROP TABLE IF EXISTS services_applied_products;

	PRAGMA FOREIGN_KEY = on;

	CREATE TABLE user_types(
		id INT PRIMARY KEY,
		designation VARCHAR(30) NOT NULL
	);
	INSERT into user_types(id,designation) VALUES 
	(1,'Escritorio'),
	(2,'Oficina');

	CREATE TABLE users(
		id INT PRIMARY KEY,
		name VARCHAR(40) NOT NULL,
		email VARCHAR(60) UNIQUE NOT NULL,
		password VARCHAR(60) NOT NULL,
		user_type_id INT NOT NULL,
		profile_pic VARCHAR(256),
		nullified BOOLEAN NOT NULL DEFAULT 0,
		CONSTRAINT fk_u_type
		FOREIGN KEY(user_type_id)
		REFERENCES user_types(id)
	);

	INSERT INTO users(id, name, email, password, user_type_id) VALUES
	(1, 'teste', 'teste@email.com', 'teste', 1),
	(2, 'teste', 'teste2@email.com', 'teste',2);

	CREATE TABLE clients(
		id INT PRIMARY KEY,
		name VARCHAR(60) NOT NULL,
		phone VARCHAR(20) NOT NULL,
		address VARCHAR(80), 
		email VARCHAR(60),
		zip_code VARCHAR(60),
		tax_nr VARCHAR(20)
	);

	INSERT INTO clients(id,name, phone) VALUES
	(1,'client1', 'phone1'),
	(2,'client2', 'phone2');

	CREATE TABLE makes(
		id INT PRIMARY KEY,
		name VARCHAR(60) NOT NULL,
		logo VARCHAR(256)
	);

	INSERT INTO makes(id,name) VALUES
	(1,'Renault'),
	(2,'Opel');

	CREATE TABLE models(
		id INT PRIMARY KEY,
		name VARCHAR(60) NOT NULL,
		make_id INT NOT NULL,
		-- more model info but not now
		CONSTRAINT fk_make
		FOREIGN KEY(make_id)
		REFERENCES makes(id)
	);


	INSERT INTO models(id,name, make_id) VALUES
	(1,'Clio', 1),
	(2,'Megane', 1),
	(3,'Express', 1),
	(4,'Corsa', 2),
	(5,'Combo', 2),
	(6,'Astra', 2);

	CREATE TABLE cars(
		id INT PRIMARY KEY,
		plate VARCHAR(20) UNIQUE NOT NULL,
		model_id INT NOT NULL,
		chassi_nr VARCHAR(60),
		year INT,
		month INT,
		cc INT,
		engine_code VARCHAR(60),
		color_code VARCHAR(60),
		CONSTRAINT fk_models
		FOREIGN KEY(model_id)
		REFERENCES models(id)
	);

	INSERT INTO cars(id,plate, model_id) VALUES
	(1,"AB-00-00", 1),
	(2,"AB-00-01", 2),
	(3,"AB-00-02", 3),
	(4,"AB-00-03", 4),
	(5,"AB-00-04", 5),
	(6,"AB-00-05", 3),
	(7,"AB-00-06", 6);

	CREATE TABLE schedules(
		id INT PRIMARY KEY,
		schedule_date VARCHAR(20) NOT NULL,
		description VARCHAR(512) NOT NULL,
		car_id INT,
		model_id INT,
		client_id INT,
		CONSTRAINT fk_car
		FOREIGN KEY (car_id)
		REFERENCES cars(id),
		CONSTRAINT fk_model
		FOREIGN KEY (model_id)
		REFERENCES models(id),
		CONSTRAINT fk_client
		FOREIGN KEY (client_id)
		REFERENCES client(id)
	);

	INSERT INTO schedules(id,schedule_date, description, car_id, model_id, client_id) VALUES
	(1,'05-01-2024', 'Revisao', 3,NULL, 1),
	(2,'05-01-2025', 'Revisao', 3,NULL, 1),
	(3,'01-01-2026', 'Reparar injetores', NULL, NULL, NULL),
	(4,'05-01-2026', 'Revisao', 3,NULL, 1),
	(5,'05-01-2026', 'Problema de juntas', 4,NULL, 2),
	(6,'05-01-2026', 'Revisao', 5,NULL, 2);

	CREATE TABLE product_types(
		id INT PRIMARY KEY,
		designation VARCHAR(60)
	);

	INSERT INTO product_types(id,designation) VALUES
	(1,'Consumiveis'), 
	(2,'Mao de Obra'), 
	(3,'Itens');

	CREATE TABLE products(
		id INT PRIMARY KEY,
		designation VARCHAR(60),
		reference VARCHAR(40),
		product_type_id INT NOT NULL,
		-- Mais informacoes de produtos
		CONSTRAINT fk_ptype
		FOREIGN KEY(product_type_id)
		REFERENCES product_types(id)
	);

	INSERT INTO products(id, designation, reference, product_type_id) VALUES
	(1,'Filtro Ar', 'PA7553', 3),
	(2,'Filtro Oleo', 'FT6086', 3),
	(3,'Anticongelante Rosa', 'ACR', 1);

	-- adicionar o constraint de se car_id != NULL, kms nao podem ser NULL

	CREATE TABLE services(
		id INT PRIMARY KEY,
		client_id INT NOT NULL,
		kms INT,
		checkin_date VARCHAR(20), -- 00/00/2000
		checkout_date VARCHAR(20), -- 00/00/2000
		malfunction_description VARCHAR(512),
		service_description VARCHAR(512),
		car_id INT,
		schedule_id INT,
		CONSTRAINT fk_client
		FOREIGN KEY(client_id)
		REFERENCES clients(id),
		CONSTRAINT fk_car
		FOREIGN KEY(car_id)
		REFERENCES cars(id),
		CONSTRAINT fk_schedule
		FOREIGN KEY(schedule_id)
		REFERENCES schedules(id)
	);

	INSERT INTO services(id,client_id,service_description) VALUES
	(1,1,'Revisao Oleo'),
	(2,1,'Revisao Oleo');

	CREATE TABLE services_user_time(
		id INTEGER PRIMARY KEY NOT NULL,
		service_id INT NOT NULL,
		user_id INT NOT NULL,
		minutes INT NOT NULL,
		ut_date VARCHAR(20) NOT NULL,
		CONSTRAINT fk_service
		FOREIGN KEY(service_id)
		REFERENCES service(id),
		CONSTRAINT fk_user
		FOREIGN KEY(user_id)
		REFERENCES users(id)
	);

	INSERT INTO services_user_time(id,service_id,user_id, minutes, ut_date) VALUES
	--(0,1,1,90,"01/01/2001"),
	(1,1,1,90,"01/01/2001"),
	(2,1,2,90,"01/01/2001"),
	(3,2,1,15,"02/01/2001"),
	(4,2,2,90,"01/01/2001"),
	(5,2,1,15,"02/01/2001");

	-- if is_applied then quantity NOT NULL
	CREATE TABLE  services_applied_products(
		id INTEGER PRIMARY KEY,
		service_id INT NOT NULL,
		product_id INT NOT NULL,
		quantity INT,
		is_applied BOOLEAN NOT NULL DEFAULT FALSE,
		CONSTRAINT fk_service
		FOREIGN KEY(service_id)
		REFERENCES services(id),
		CONSTRAINT fk_product
		FOREIGN KEY(product_id)
		REFERENCES products(id)
	);

	INSERT INTO services_applied_products(service_id, product_id) VALUES
	(1,1),
	(1,3),
	(2,2),
	(2,1);
