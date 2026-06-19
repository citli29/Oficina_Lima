	DROP TABLE IF EXISTS services_user_time;
	DROP TABLE IF EXISTS services_applied_products;

	DROP TABLE IF EXISTS services;
	DROP TABLE IF EXISTS schedules;

	DROP TABLE IF EXISTS cars;
	DROP TABLE IF EXISTS models;

	DROP TABLE IF EXISTS products;
	DROP TABLE IF EXISTS product_types;

	DROP TABLE IF EXISTS users;
	DROP TABLE IF EXISTS user_types;

	DROP TABLE IF EXISTS clients;
	DROP TABLE IF EXISTS makes;

	CREATE TABLE user_types(
		id INTEGER PRIMARY KEY,
		designation VARCHAR(30) NOT NULL
	);

	INSERT into user_types(id,designation) VALUES 
	(1,'Escritorio'),
	(2,'Oficina');

	CREATE TABLE users(
		id INTEGER PRIMARY KEY,
		name VARCHAR(40) NOT NULL,
		email VARCHAR(60) UNIQUE NOT NULL,
		password VARCHAR(60) NOT NULL,
		user_type_id INTEGER NOT NULL,
		profile_pic VARCHAR(256),
		nullified BOOLEAN NOT NULL DEFAULT 0,
		FOREIGN KEY(user_type_id)
		REFERENCES user_types(id)
	);

	INSERT INTO users(id, name, email, password, user_type_id) VALUES
	(1, 'teste', 'teste@email.com', 'teste', 1),
	(2, 'teste', 'teste2@email.com', 'teste',2);

	CREATE TABLE clients(
		id INTEGER PRIMARY KEY,
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
		id INTEGER PRIMARY KEY,
		name VARCHAR(60) NOT NULL UNIQUE,
		logo VARCHAR(256)
	);

	INSERT INTO makes(id,name) VALUES
	(1,'Renault'),
	(2,'Opel');

	CREATE TABLE models(
		id INTEGER PRIMARY KEY,
		name VARCHAR(60) NOT NULL,
		make_id INTEGER NOT NULL,
		-- more model info but not now
		UNIQUE(id, make_id),
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
		id INTEGER PRIMARY KEY,
		plate VARCHAR(20) UNIQUE NOT NULL,
		month INT,
		year INT,
		chassi_nr VARCHAR(60),
		cc INT,
		engine_code VARCHAR(60),
		color_code VARCHAR(60),
		model_id INTEGER,
		make_id INTEGER NOT NULL,
		FOREIGN KEY (make_id)
			REFERENCES makes(id),
		FOREIGN KEY (model_id, make_id)
			REFERENCES models(id, make_id)
			ON DELETE SET NULL
	);

	INSERT INTO cars(id,plate, make_id, model_id) VALUES
	(1,"AB-00-00", 1,1),
	(2,"AB-00-01", 1,2),
	(3,"AB-00-02", 1,3),
	(4,"AB-00-03", 2,4),
	(5,"AB-00-04", 2,5),
	(6,"AB-00-05", 1,3),
	(7,"AB-00-06", 2,6);

	CREATE TABLE schedules(
		id INTEGER PRIMARY KEY,
		schedule_date VARCHAR(20) NOT NULL,
		description VARCHAR(512) NOT NULL,
		car_id INT,
		model_id INT,
		client_id INT,
		FOREIGN KEY (car_id)
			REFERENCES cars(id)
			ON DELETE SET NULL,
		FOREIGN KEY (model_id)
			REFERENCES models(id)
			ON DELETE SET NULL,
		FOREIGN KEY (client_id)
			REFERENCES clients(id)
			ON DELETE SET NULL
	);

	INSERT INTO schedules(id,schedule_date, description, car_id, model_id, client_id) VALUES
	(1,'05-01-2024', 'Revisao', 3,NULL, 1),
	(2,'05-01-2025', 'Revisao', 3,NULL, 1),
	(3,'01-01-2026', 'Reparar injetores', NULL, NULL, NULL),
	(4,'05-01-2026', 'Revisao', 3,NULL, 1),
	(5,'05-01-2026', 'Problema de juntas', 4,NULL, 2),
	(6,'05-01-2026', 'Revisao', 5,NULL, 2);

	CREATE TABLE product_types(
		id INTEGER PRIMARY KEY,
		designation VARCHAR(60) NOT NULL UNIQUE
	);

	INSERT INTO product_types(id,designation) VALUES
	(1,'Consumiveis'), 
	(2,'Mao de Obra'), 
	(3,'Itens');

	CREATE TABLE products(
		id INTEGER PRIMARY KEY,
		designation VARCHAR(60) NOT NULL,
		reference VARCHAR(40) UNIQUE,
		product_type_id INTEGER,
		-- Mais informacoes de produtos
		FOREIGN KEY(product_type_id)
			REFERENCES product_types(id)
			ON DELETE SET NULL
	);

	INSERT INTO products(id, designation, reference, product_type_id) VALUES
	(1,'Filtro Ar', 'PA7553', 3),
	(2,'Filtro Oleo', 'FT6086', 3),
	(3,'Anticongelante Rosa', 'ACR', 1);

	-- adicionar o constraint de se car_id != NULL, kms nao podem ser NULL

	CREATE TABLE services(
		id INTEGER PRIMARY KEY,
		client_id INTEGER NOT NULL,
		kms INT,
		checkin_date VARCHAR(20), -- 00/00/2000
		checkout_date VARCHAR(20), -- 00/00/2000
		malfunction_description VARCHAR(512),
		service_description VARCHAR(512),
		car_id INT,
		schedule_id INT,
		FOREIGN KEY(client_id)
		REFERENCES clients(id),
		FOREIGN KEY(car_id)
			REFERENCES cars(id)
			ON DELETE SET NULL,
		FOREIGN KEY(schedule_id)
			REFERENCES schedules(id)
			ON DELETE SET NULL
	);

	INSERT INTO services(id,client_id,service_description) VALUES
	(1,1,'Revisao Oleo'),
	(2,1,'Revisao Oleo');

	CREATE TABLE services_user_time(
		id INTEGER PRIMARY KEY NOT NULL,
		service_id INTEGER NOT NULL,
		user_id INTEGER NOT NULL,
		minutes INTEGER NOT NULL,
		ut_date VARCHAR(20) NOT NULL,
		FOREIGN KEY(service_id)
		REFERENCES services(id),
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
		service_id INTEGER NOT NULL,
		product_id INTEGER NOT NULL,
		quantity INT,
		is_applied BOOLEAN NOT NULL DEFAULT FALSE,
		FOREIGN KEY(service_id)
		REFERENCES services(id),
		FOREIGN KEY(product_id)
		REFERENCES products(id)
	);

	INSERT INTO services_applied_products(id,service_id, product_id, is_applied) VALUES
	(1,1,1,0),
	(2,1,3,0),
	(3,2,2,0),
	(4,2,1,1);
