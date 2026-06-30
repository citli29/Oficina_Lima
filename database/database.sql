	DROP TABLE IF EXISTS services_user_time;
	DROP TABLE IF EXISTS services_applied_products;

	DROP TABLE IF EXISTS services;
	DROP TABLE IF EXISTS schedules;

	DROP TABLE IF EXISTS cars; --
	DROP TABLE IF EXISTS models; --

	DROP TABLE IF EXISTS products; --
	DROP TABLE IF EXISTS product_types; --

	DROP TABLE IF EXISTS users;
	DROP TABLE IF EXISTS user_types;

	DROP TABLE IF EXISTS clients;  
	DROP TABLE IF EXISTS makes; --

	CREATE TABLE user_types(
		id INTEGER PRIMARY KEY,
		name VARCHAR(30) NOT NULL
	);


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

	CREATE TABLE clients(
		id INTEGER PRIMARY KEY,
		name VARCHAR(60) NOT NULL,
		phone VARCHAR(20) NOT NULL,
		address VARCHAR(80), 
		email VARCHAR(60),
		zip_code VARCHAR(60),
		tax_nr VARCHAR(20)
	);


	CREATE TABLE makes(
		id INTEGER PRIMARY KEY,
		name VARCHAR(60) NOT NULL UNIQUE,
		logo VARCHAR(256)
	);


	CREATE TABLE models(
		id INTEGER PRIMARY KEY,
		name VARCHAR(60) NOT NULL,
		make_id INTEGER NOT NULL,
		-- more model info but not now
		UNIQUE(name, make_id),
		FOREIGN KEY(make_id)
		REFERENCES makes(id)
	);



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
		FOREIGN KEY (model_id)
			REFERENCES models(id)
			ON DELETE SET NULL
	);

	CREATE TABLE schedules(
		id INTEGER PRIMARY KEY,
		date VARCHAR(20) NOT NULL,
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

	CREATE TRIGGER schedule_check_model_insert
	BEFORE INSERT ON schedules
	FOR EACH ROW
	WHEN NEW.car_id IS NOT NULL
	 AND NEW.model_id IS NOT NULL
	BEGIN
	    SELECT
		CASE
		    WHEN NOT EXISTS (
			SELECT 1
			FROM cars
			WHERE id = NEW.car_id
			  AND model_id = NEW.model_id
		    )
		    THEN RAISE(ABORT, 'Selected car does not belong to the selected model')
		END;
	END;

	CREATE TRIGGER schedule_check_model_update
	BEFORE UPDATE OF car_id, model_id ON schedules
	FOR EACH ROW
	WHEN NEW.car_id IS NOT NULL
	 AND NEW.model_id IS NOT NULL
	BEGIN
	    SELECT
		CASE
		    WHEN NOT EXISTS (
			SELECT 1
			FROM cars
			WHERE id = NEW.car_id
			  AND model_id = NEW.model_id
		    )
		    THEN RAISE(ABORT, 'Selected car does not belong to the selected model')
		END;
	END;


	CREATE TABLE product_types(
		id INTEGER PRIMARY KEY,
		name VARCHAR(60) NOT NULL UNIQUE
	);


	CREATE TABLE products (
	    id INTEGER PRIMARY KEY,
	    name VARCHAR(60) NOT NULL,
	    reference VARCHAR(40) UNIQUE,
	    product_type_id INTEGER,
	    FOREIGN KEY (product_type_id)
		REFERENCES product_types(id)
		ON DELETE SET NULL
	);

	CREATE UNIQUE INDEX unique_name_when_reference_null
	ON products(name)
	WHERE reference IS NULL;

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

	CREATE TRIGGER check_car_model_insert
	BEFORE INSERT ON cars
	FOR EACH ROW
	WHEN NEW.model_id IS NOT NULL
	BEGIN
	    SELECT
		CASE
		    WHEN NOT EXISTS (
			SELECT 1
			FROM models
			WHERE id = NEW.model_id
			  AND make_id = NEW.make_id
		    )
		    THEN RAISE(ABORT, 'Invalid model for make')
		END;
	END;

	CREATE TRIGGER check_car_model_update
	BEFORE UPDATE OF model_id, make_id ON cars
	FOR EACH ROW
	WHEN NEW.model_id IS NOT NULL
	BEGIN
	    SELECT
		CASE
		    WHEN NOT EXISTS (
			SELECT 1
			FROM models
			WHERE id = NEW.model_id
			  AND make_id = NEW.make_id
		    )
		    THEN RAISE(ABORT, 'Model does not belong to make')
		END;
	END;

	INSERT into user_types(id,name) VALUES 
	(1,'Escritorio'),
	(2,'Oficina'),
	(3,'Admin');

	INSERT INTO users(id, name, email, password, user_type_id) VALUES
	(1, 'teste', 'teste@email.com', 'teste', 1),
	(2, 'teste', 'teste2@email.com', 'teste',2),
	(3, 'teste', 'teste3@email.com', 'teste',3);
	
	INSERT INTO clients(id,name, phone, email) VALUES
	(1,'Antonio', '911','antonio@email.com'),
	(2,'Joaquim', '912','joaquim@email.com'),
	(3,'Maria', '913','maria@email.com'),
	(4,'Bruno', '914','bruno@email.com'),
	(5,'Joao', '915','joao@email.com'),
	(6,'Silvia', '916','silvia@email.com'),
	(7,'Patricia', '917','patricia@email.com'),
	(8,'Rui', '918','rui@email.com'),
	(9,'Fernanda', '919','fernanda@email.com'),
	(10,'Pedro', '920','pedro@email.com'),
	(11,'Beatriz', '921','beatriz@email.com'),
	(12,'Ricardo', '922','ricardo@email.com'),
	(13,'Pedro', '923','pedro@email.com');

	INSERT INTO makes(id,name) VALUES
	(1,'Renault'),
	(2,'Ford'),
	(3,'Mazda'),
	(4,'Opel');

	INSERT INTO models(id,name, make_id) VALUES
	(1,'Clio', 1),
	(2,'Megane', 1),
	(3,'Express', 1),
	(4,'Fiesta', 2),
	(5,'Focus', 2),
	(6,'Puma', 2),
	(7,'Mx-5', 3),
	(8,'Mx-7', 3),
	(9,'Cx-80', 3),
	(10,'Corsa', 4),
	(11,'Combo', 4),
	(12,'Astra', 4);

	INSERT INTO cars(id,plate,year,month, make_id, model_id) VALUES
	(1,"AB-00-00",2002,4,1,1),
	(2,"AB-00-01",2004,6,1,1),
	(3,"AB-00-02",2010,7,1,2),
	(4,"AB-00-03",2006,5,1,3),
	(5,"AB-00-04",2008,1,2,4),
	(6,"AB-00-05",2006,1,2,5),
	(7,"AB-00-06",2007,3,2,6),
	(8,"AB-00-07",2008,9,3,7),
	(9,"AB-00-08",2007,11,3,8),
	--(10,"AB-00-09",2004,12,3,9),
	(10,"AB-00-09",2004,12,3,NULL),
	(11,"AB-00-10",2004,10,3,7),
	(12,"AB-00-11",2004,11,3,7),
	(13,"AB-00-12",2005,1,4,12);

	INSERT INTO schedules(id,date, description, car_id, model_id, client_id) VALUES
	(1,'05-01-2024', 'Revisao', 1,NULL, 1),
	(2,'05-02-2025', 'Revisao', 1,NULL, 1),
	(3,'05-05-2025', 'Carro chia', 2,NULL, 2),
	(4,'05-05-2025', 'Perda de Potencia', 3,NULL, 3),
	(5,'06-05-2025', 'Motor sobreaquece', 4,NULL, 4),
	(6,'06-05-2025', 'Vibracao excessiva do motor', 5,NULL, 5),
	(7,'06-05-2025', 'Travoes fazem ruido', 6,NULL, 6),
	(8,'07-05-2025', 'Luz do ABS acesa', 7,NULL, 7),
	(9,'07-05-2025', 'Direcao pesada', 4,NULL, 8),
	(10,'07-05-2025', 'Direcao vibra', 5,NULL, 9),
	(11,'08-05-2025', 'Volante desalinhado', 6,NULL, 10),
	(12,'08-05-2025', 'Desgaste irregular dos pneus', 7,NULL, 11),
	(13,'08-05-2025', 'Ruido na caixa de velocidades', 6,NULL, 13),
	(14,'09-05-2025', 'Vidros eletricos nao funcionam', 9,NULL, 10),
	(15,'09-05-2025', 'Escape faz muito barulho', 8,NULL, 9),
	(16,'09-05-2025', 'Cheiro a fases', 9,NULL, 9),
	(17,'11-05-2025', 'Perda de anticongelante', NULL,8, 8),
	(18,'11-05-2025', 'Fuga de oleo', NULL,3, 7),
	(19,'11-05-2025', 'Luzes de travao nao funcionam', 11,NULL, 11),
	(20,'12-05-2025', 'Ruido ao acelerar', 13,NULL, 10);

	INSERT INTO product_types(id,name) VALUES
	(1,'Liquidos'), 
	(2,'Pecas'), 
	(3,'Filtros'), 
	(4,'Eletricos'), 
	(5,'Travagem'), 
	(6,'Pneus');

	INSERT INTO products(id, name, reference, product_type_id) VALUES
	(1,'Filtro Ar','PA7553',3),
	(2,'Filtro Oleo','FT6086',3),
	(3,'Anticongelante Rosa','ACR001',1),
	(4,'Oleo Motor 5W30 5L','OM530',1),
	(5,'Filtro Habitaculo','FH2210',3),
	(6,'Filtro Combustivel','FC905',3),
	(7,'Pastilhas de Travao Dianteiras','PTD450',5),
	(8,'Pastilhas de Travao Traseiras','PTT310',5),
	(9,'Disco de Travao Dianteiro','DTD220',5),
	(10,'Disco de Travao Traseiro','DTT180',5),
	(11,'Sensor ABS','SABS210',4),
	(12,'Bomba de Agua','BA450',2),
	(13,'Termostato','TERM98',2),
	(14,'Radiador','RAD320',2),
	(15,'Correia de Distribuicao','CD890',2),
	(16,'Kit Embraiagem','KE550',2),
	(17,'Rolamento de Roda','RR120',2),
	(18,'Amortecedor Dianteiro','AD450',2),
	(19,'Amortecedor Traseiro','AT420',2),
	(20,'Terminal de Direcao','TD100',2),
	(21,'Braço de Suspensao','BS220',2),
	(22,'Pneu 205/55 R16','P2055516',6),
	(23,'Valvula TPMS','TPMS20',4),
	(24,'Motor do Vidro Eletrico','MVE200',4),
	(25,'Elevador de Vidro','EV320',2),
	(26,'Silencioso Escape','SE450',2),
	(27,'Tubo de Escape','TE180',2),
	(28,'Junta da Tampa de Valvulas','JTV150',2),
	(29,'Retentor da Cambota','RC110',2),
	(30,'Liquido de Travoes DOT4','DOT400',1),
	(31,'Lampada P21W','LP21',4),
	(32,'Lampada H7','LH700',4),
	(33,'Velas de Ignicao','VI320',2),
	(34,'Bobina de Ignicao','BI440',4),
	(35,'Alternador','ALT900',4),
	(36,'Bateria 70Ah','BAT70',4),
	(37,'Sensor de Temperatura','ST120',4),
	(38,'Ventoinha do Radiador','VR310',4),
	(39,'Jogo de Escovas Limpa-vidros','EL450',2),
	(40,'Massa Lubrificante','ML100',1);

INSERT INTO services
(id, client_id, kms, checkin_date, checkout_date, malfunction_description, service_description, car_id, schedule_id)
VALUES
(1,1,125000,'05-01-2024','05-01-2024','Revisao','Mudanca de oleo e filtros',1,1),
(2,1,138000,'05-02-2025','05-02-2025','Revisao','Revisao completa',1,2),
(3,2,98000,'05-05-2025','06-05-2025','Carro chia','Substituicao de pastilhas dianteiras',2,3),
(4,3,184000,'05-05-2025','06-05-2025','Perda de Potencia','Substituicao filtro combustivel e velas',3,4),
(5,4,210000,'06-05-2025','07-05-2025','Motor sobreaquece','Substituicao termostato',4,5),
(6,5,156000,'06-05-2025','07-05-2025','Vibracao excessiva do motor','Troca de bobina e velas',5,6),
(7,6,142000,'06-05-2025','06-05-2025','Travoes fazem ruido','Pastilhas novas',6,7),
(8,7,118000,'07-05-2025','08-05-2025','Luz do ABS acesa','Substituicao sensor ABS',7,8),
(9,8,245000,'07-05-2025','08-05-2025','Direcao pesada','Substituicao terminal direcao',4,9),
(10,9,131000,'07-05-2025','08-05-2025','Direcao vibra','Balanceamento e rolamento',5,10),
(11,10,99000,'08-05-2025','09-05-2025','Volante desalinhado','Alinhamento direcao',6,11),
(12,11,87000,'08-05-2025','09-05-2025','Desgaste irregular dos pneus','Substituicao pneus',7,12),
(13,13,223000,'08-05-2025','10-05-2025','Ruido caixa velocidades','Substituicao kit embraiagem',6,13),
(14,10,150000,'09-05-2025','09-05-2025','Vidros eletricos nao funcionam','Substituicao motor vidro',9,14),
(15,9,167000,'09-05-2025','10-05-2025','Escape faz muito barulho','Substituicao silencioso',8,15),
(16,9,168500,'09-05-2025','10-05-2025','Cheiro a gases','Reparacao tubo escape',9,16),
(17,8,192000,'11-05-2025','12-05-2025','Perda de anticongelante','Substituicao bomba agua',10,17),
(18,7,204000,'11-05-2025','12-05-2025','Fuga de oleo','Substituicao junta tampa valvulas',12,18),
(19,11,76000,'11-05-2025','11-05-2025','Luzes de travao nao funcionam','Substituicao lampada',11,19),
(20,10,111000,'12-05-2025','13-05-2025','Ruido ao acelerar','Substituicao rolamento',13,20);

INSERT INTO services_user_time
(id, service_id, user_id, minutes, ut_date)
VALUES
(1,1,2,60,'05-01-2024'),
(2,2,2,90,'05-02-2025'),
(3,3,2,80,'05-05-2025'),
(4,4,2,120,'05-05-2025'),
(5,5,2,150,'06-05-2025'),
(6,6,2,110,'06-05-2025'),
(7,7,2,70,'06-05-2025'),
(8,8,2,60,'07-05-2025'),
(9,9,2,90,'07-05-2025'),
(10,10,2,80,'07-05-2025'),
(11,11,2,45,'08-05-2025'),
(12,12,2,75,'08-05-2025'),
(13,13,2,240,'08-05-2025'),
(14,14,2,60,'09-05-2025'),
(15,15,2,90,'09-05-2025'),
(16,16,2,70,'09-05-2025'),
(17,17,2,150,'11-05-2025'),
(18,18,2,120,'11-05-2025'),
(19,19,2,20,'11-05-2025'),
(20,20,2,80,'12-05-2025');

INSERT INTO services_applied_products
(id, service_id, product_id, quantity, is_applied)
VALUES
(1,1,4,1,1),
(2,1,2,1,1),
(3,1,1,1,1),
(4,2,4,1,1),
(5,2,2,1,1),
(6,2,5,1,1),
(7,3,7,1,1),
(8,4,6,1,1),
(9,4,33,4,1),
(10,5,13,1,1),
(11,5,3,1,1),
(12,6,34,1,1),
(13,6,33,4,1),
(14,7,7,1,1),
(15,8,11,1,1),
(16,9,20,2,1),
(17,10,17,1,1),
(18,11,20,1,1),
(19,12,22,2,1),
(20,13,16,1,1),
(22,15,26,1,1),
(23,16,27,1,1),
(24,17,12,1,1),
(25,17,3,1,1),
(26,18,28,1,1),
(27,18,4,1,1),
(28,19,31,2,1),
(29,20,17,1,1);
