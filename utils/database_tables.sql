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
	password VARCHAR(60) NOT NULL DEFAULT "pass",
	user_type_id INTEGER NOT NULL,
	profile_pic VARCHAR(256),
	nullified BOOLEAN NOT NULL DEFAULT 0,
	FOREIGN KEY(user_type_id)
	REFERENCES user_types(id)
);

-- No real business logic
CREATE TABLE clients(
	id INTEGER PRIMARY KEY,
	name VARCHAR(60) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	address VARCHAR(80), 
	email VARCHAR(60),
	zip_code VARCHAR(60),
	tax_nr VARCHAR(20)
);

-- No real business logic
CREATE TABLE makes(
	id INTEGER PRIMARY KEY,
	name VARCHAR(60) NOT NULL UNIQUE,
	logo VARCHAR(256)
);


-- No real business logic
CREATE TABLE models(
	id INTEGER PRIMARY KEY,
	name VARCHAR(60) NOT NULL,
	make_id INTEGER NOT NULL,
	UNIQUE(name, make_id),
	FOREIGN KEY(make_id)
	REFERENCES makes(id)
);

-- No real business logic
-- 0 < Month < 13
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

-- if we have car, dont need model_id
CREATE TABLE schedules(

	id INTEGER PRIMARY KEY,
	date VARCHAR(20) NOT NULL CHECK(
		date(date) IS NOT NULL
	),
	description VARCHAR(512) NOT NULL,
	car_id INT,
	model_id INT,
	client_id INT,
	CHECK(
		NOT (car_id IS NOT NULL AND model_id IS NOT NULL)
	),
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

-- no business logic
CREATE TABLE product_types(
	id INTEGER PRIMARY KEY,
	name VARCHAR(60) NOT NULL UNIQUE
);

-- if the product doesnt have reference, it cant have the same name as 
-- other product with the same name and no reference
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

-- checkin < checkout
-- if checkout and car_id need to have kms
CREATE TABLE services(
	id INTEGER PRIMARY KEY,
	client_id INTEGER NOT NULL,
	kms INT,
	checkin_date VARCHAR(20) NOT NULL CHECK(
		date(checkin_date) IS NOT NULL
	), 
	checkout_date VARCHAR(20) CHECK(
		checkout_date IS NULL 
		OR date(checkout_date) IS NOT NULL
	),
	malfunction_description VARCHAR(512),
	service_description VARCHAR(512),
	car_id INT,
	schedule_id INT,
	CHECK(
		checkout_date IS NULL 
		OR 
		checkin_date <= checkout_date
	)
	FOREIGN KEY(client_id)
	REFERENCES clients(id),
	FOREIGN KEY(car_id)
		REFERENCES cars(id)
		ON DELETE SET NULL,
	FOREIGN KEY(schedule_id)
		REFERENCES schedules(id)
		ON DELETE SET NULL
);

CREATE TRIGGER i_no_checkout_withou_kms_if_car
BEFORE INSERT ON services
FOR EACH ROW
WHEN 
	NEW.car_id IS NOT NULL 
	AND NEW.checkout_date IS NOT NULL
	AND NEW.kms IS NULL
BEGIN
	SELECT RAISE(ABORT, 'Cannot checkout without kms');
END;

CREATE TRIGGER u_no_checkout_withou_kms_if_car
BEFORE UPDATE ON services
FOR EACH ROW
WHEN 
	NEW.car_id IS NOT NULL 
	AND NEW.checkout_date IS NOT NULL
	AND NEW.kms IS NULL
BEGIN
	SELECT RAISE(ABORT, 'Cannot checkout without kms');
END;

-- ut date needs to be >= service.checkin
-- minutes needs to be > 0
CREATE TABLE services_user_time(
	id INTEGER PRIMARY KEY NOT NULL,
	service_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	minutes INTEGER NOT NULL CHECK(
		minutes > 0
	),
	ut_date VARCHAR(20) NOT NULL CHECK(
		date(ut_date) IS NOT NULL
	),
	FOREIGN KEY(service_id)
	REFERENCES services(id),
	FOREIGN KEY(user_id)
	REFERENCES users(id)
);

CREATE TRIGGER i_check_services_user_time
BEFORE INSERT ON services_user_time
FOR EACH ROW
BEGIN
    SELECT CASE
	WHEN EXISTS (
	    SELECT 1
	    FROM services s
	    WHERE s.id = NEW.service_id
	      AND NOT (
		  NEW.ut_date >= s.checkin_date
		  AND NEW.ut_date <= s.checkout_date
	      )
	)
	THEN RAISE(ABORT, 'ut_date must be between checkin_date and checkout_date')
    END;
END;

CREATE TRIGGER u_check_services_user_time
BEFORE UPDATE ON services_user_time
FOR EACH ROW
BEGIN
    SELECT CASE
	WHEN EXISTS (
	    SELECT 1
	    FROM services s
	    WHERE s.id = NEW.service_id
	      AND NOT (
		  NEW.ut_date >= s.checkin_date
		  AND NEW.ut_date <= s.checkout_date
	      )
	)
	THEN RAISE(ABORT, 'ut_date must be between checkin_date and checkout_date')
    END;
END;

-- if is_applied then quantity NOT NULL
CREATE TABLE  services_applied_products(
	id INTEGER PRIMARY KEY,
	service_id INTEGER NOT NULL,
	product_id INTEGER NOT NULL,
	quantity INT NOT NULL DEFAULT 0
	CHECK (typeof(quantity) = 'integer'),
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
