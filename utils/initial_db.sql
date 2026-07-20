PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE user_types(
	id INTEGER PRIMARY KEY,
	name VARCHAR(30) NOT NULL
);
INSERT INTO user_types VALUES(1,'Mecanico');
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
INSERT INTO users VALUES(1,'Rogerio','1','pass',1,NULL,0);
INSERT INTO users VALUES(2,'Diogo','2','pass',1,NULL,0);
INSERT INTO users VALUES(3,'Antonio (Pai)','3','pass',1,NULL,0);
INSERT INTO users VALUES(4,'Antonio (Filho)','4','pass',1,NULL,0);
CREATE TABLE clients(
	id INTEGER PRIMARY KEY,
	name VARCHAR(60) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	address VARCHAR(80), 
	email VARCHAR(60),
	zip_code VARCHAR(60),
	tax_nr VARCHAR(20)
);
INSERT INTO clients VALUES(1,'Paula --','939187713',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(2,'Jose Silva','916633155',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(3,'Paulo Pinto','911130348',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(4,'Printerman','--',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(5,'Luiz Humberto','919229876',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(6,'Benjamim Vieira','963023272',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(7,'Cristina - Magrelos','--',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(8,'Jose Luis','964768953',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(9,'Sumar','--',NULL,NULL,NULL,NULL);
INSERT INTO clients VALUES(10,'Antonio','912959817',NULL,NULL,NULL,NULL);
CREATE TABLE makes(
	id INTEGER PRIMARY KEY,
	name VARCHAR(60) NOT NULL UNIQUE,
	logo VARCHAR(256)
);
INSERT INTO makes VALUES(1,'BMW',NULL);
INSERT INTO makes VALUES(2,'Volvo',NULL);
INSERT INTO makes VALUES(3,'Ford',NULL);
INSERT INTO makes VALUES(4,'Volkswagen',NULL);
INSERT INTO makes VALUES(5,'Dacia',NULL);
INSERT INTO makes VALUES(6,'Renault',NULL);
INSERT INTO makes VALUES(7,'Mercedes',NULL);
INSERT INTO makes VALUES(8,'Peugeot',NULL);
INSERT INTO makes VALUES(9,'Land Rover',NULL);
INSERT INTO makes VALUES(10,'Fiat',NULL);
INSERT INTO makes VALUES(11,'Opel',NULL);
CREATE TABLE models(
	id INTEGER PRIMARY KEY,
	name VARCHAR(60) NOT NULL,
	make_id INTEGER NOT NULL,
	UNIQUE(name, make_id),
	FOREIGN KEY(make_id)
	REFERENCES makes(id)
);
INSERT INTO models VALUES(1,'Focus',3);
INSERT INTO models VALUES(2,'Golf',4);
INSERT INTO models VALUES(3,'Clio',6);
INSERT INTO models VALUES(4,'Capture',6);
INSERT INTO models VALUES(5,'508',8);
INSERT INTO models VALUES(6,'Combo',11);
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
INSERT INTO cars VALUES(1,'97-UN-53',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1);
INSERT INTO cars VALUES(2,'51-IV-40',NULL,NULL,NULL,NULL,NULL,NULL,NULL,2);
INSERT INTO cars VALUES(3,'94-47-TC',NULL,NULL,NULL,NULL,NULL,NULL,1,3);
INSERT INTO cars VALUES(4,'94-VV-30',NULL,NULL,NULL,NULL,NULL,NULL,2,4);
INSERT INTO cars VALUES(5,'05-OF-94',NULL,NULL,NULL,NULL,NULL,NULL,NULL,5);
INSERT INTO cars VALUES(6,'36-89-VD',NULL,NULL,NULL,NULL,NULL,NULL,3,6);
INSERT INTO cars VALUES(7,'50-RT-49',NULL,NULL,NULL,NULL,NULL,NULL,4,6);
INSERT INTO cars VALUES(8,'39-NQ-49',NULL,NULL,NULL,NULL,NULL,NULL,NULL,7);
INSERT INTO cars VALUES(9,'76-QI-82',NULL,NULL,NULL,NULL,NULL,NULL,5,8);
INSERT INTO cars VALUES(10,'73-SL-00',NULL,NULL,NULL,NULL,NULL,NULL,6,11);
INSERT INTO cars VALUES(11,'BA-82-IC',NULL,NULL,NULL,NULL,NULL,NULL,NULL,9);
INSERT INTO cars VALUES(12,'49-VT-96',NULL,NULL,NULL,NULL,NULL,NULL,NULL,10);
INSERT INTO cars VALUES(13,'BM-27-QR',NULL,NULL,NULL,NULL,NULL,NULL,6,11);
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
INSERT INTO schedules VALUES(1,'2026-07-21',replace('Revisao\nOleo\nFiltros x4\nCheiro Travoes\nVerificar Geral','\n',char(10)),1,NULL,1);
INSERT INTO schedules VALUES(2,'2026-07-21',replace('Verif. Elim. \nFAP - Central\nVer Turbo - Inj','\n',char(10)),2,NULL,2);
INSERT INTO schedules VALUES(3,'2026-07-21',replace('Travoes \nVerificar estado geral\nPois vai viajar 1000Kms\nPrecisa Levantar 24/7','\n',char(10)),3,NULL,NULL);
INSERT INTO schedules VALUES(4,'2026-07-22',replace('Fuga Oleo Inj\nFalha a frio\nGrila','\n',char(10)),4,NULL,3);
INSERT INTO schedules VALUES(5,'2026-07-23',replace('Radiador --\nou AC --\nPedir','\n',char(10)),5,NULL,4);
INSERT INTO schedules VALUES(6,'2026-07-23',replace('Ver Ruidos e Folgas\nAmortecedor x4','\n',char(10)),6,NULL,10);
INSERT INTO schedules VALUES(7,'2026-07-24',replace('Revisao\nOleo + Filtros','\n',char(10)),7,NULL,5);
INSERT INTO schedules VALUES(8,'2026-07-27',replace('Luz motor acesa\nFAP','\n',char(10)),8,NULL,6);
INSERT INTO schedules VALUES(9,'2026-07-27',replace('Desativar Sensor Pneus\nLEMBRAR','\n',char(10)),9,NULL,7);
INSERT INTO schedules VALUES(10,'2026-07-28',replace('FAP/ Centralina\nTurbo/ Injecao Verificar','\n',char(10)),2,NULL,2);
INSERT INTO schedules VALUES(11,'2026-07-28',replace('Embraiagem\nTurbo','\n',char(10)),10,NULL,4);
INSERT INTO schedules VALUES(12,'2026-07-28',replace('Barulho Frente\nLEMBRAR','\n',char(10)),11,NULL,8);
INSERT INTO schedules VALUES(13,'2026-07-30',replace('Direcao\nFoles','\n',char(10)),12,NULL,4);
INSERT INTO schedules VALUES(14,'2026-07-30',replace('Filtro Oleo\nFiltro Gasoleo\nLEMBRAR','\n',char(10)),13,NULL,9);
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
INSERT INTO products VALUES(1,'Óleo do Motor 5W30',NULL,1);
INSERT INTO products VALUES(2,'Óleo do Motor 5W40',NULL,1);
INSERT INTO products VALUES(3,'Óleo do Motor 10W40',NULL,1);
INSERT INTO products VALUES(4,'Óleo do Motor 0W20',NULL,1);
INSERT INTO products VALUES(5,'Óleo de Caixa Manual',NULL,1);
INSERT INTO products VALUES(6,'Óleo de Caixa Automática',NULL,1);
INSERT INTO products VALUES(7,'Óleo de Direção Assistida',NULL,1);
INSERT INTO products VALUES(8,'Óleo Diferencial',NULL,1);
INSERT INTO products VALUES(9,'Massa Lubrificante',NULL,1);
INSERT INTO products VALUES(10,'Spray Lubrificante',NULL,1);
INSERT INTO products VALUES(11,'Filtro de Óleo',NULL,1);
INSERT INTO products VALUES(12,'Filtro de Ar',NULL,1);
INSERT INTO products VALUES(13,'Filtro de Habitáculo',NULL,1);
INSERT INTO products VALUES(14,'Filtro de Combustível',NULL,1);
INSERT INTO products VALUES(15,'Pré-Filtro de Combustível',NULL,1);
INSERT INTO products VALUES(16,'Pastilhas de Travão Dianteiras',NULL,1);
INSERT INTO products VALUES(17,'Pastilhas de Travão Traseiras',NULL,1);
INSERT INTO products VALUES(18,'Discos de Travão Dianteiros',NULL,1);
INSERT INTO products VALUES(19,'Discos de Travão Traseiros',NULL,1);
INSERT INTO products VALUES(20,'Maxilas de Travão',NULL,1);
INSERT INTO products VALUES(21,'Bombito de Travão',NULL,1);
INSERT INTO products VALUES(22,'Cabo de Travão de Mão',NULL,1);
INSERT INTO products VALUES(23,'Sensor de Desgaste das Pastilhas',NULL,1);
INSERT INTO products VALUES(24,'Líquido de Travões DOT 4',NULL,1);
INSERT INTO products VALUES(25,'Líquido de Travões DOT 5.1',NULL,1);
INSERT INTO products VALUES(26,'Amortecedor Dianteiro',NULL,1);
INSERT INTO products VALUES(27,'Amortecedor Traseiro',NULL,1);
INSERT INTO products VALUES(28,'Mola de Suspensão',NULL,1);
INSERT INTO products VALUES(29,'Batente de Amortecedor',NULL,1);
INSERT INTO products VALUES(30,'Apoio de Amortecedor',NULL,1);
INSERT INTO products VALUES(31,'Casquilho da Barra Estabilizadora',NULL,1);
INSERT INTO products VALUES(32,'Barra Estabilizadora',NULL,1);
INSERT INTO products VALUES(33,'Tirante da Barra Estabilizadora',NULL,1);
INSERT INTO products VALUES(34,'Terminal de Direção',NULL,1);
INSERT INTO products VALUES(35,'Barra de Direção',NULL,1);
INSERT INTO products VALUES(36,'Caixa de Direção',NULL,1);
INSERT INTO products VALUES(37,'Bomba de Direção Assistida',NULL,1);
INSERT INTO products VALUES(38,'Fole da Direção',NULL,1);
INSERT INTO products VALUES(39,'Correia de Distribuição',NULL,1);
INSERT INTO products VALUES(40,'Kit de Distribuição',NULL,1);
INSERT INTO products VALUES(41,'Corrente de Distribuição',NULL,1);
INSERT INTO products VALUES(42,'Tensor da Distribuição',NULL,1);
INSERT INTO products VALUES(43,'Bomba de Água',NULL,1);
INSERT INTO products VALUES(44,'Correia de Acessórios',NULL,1);
INSERT INTO products VALUES(45,'Tensor da Correia',NULL,1);
INSERT INTO products VALUES(46,'Polia',NULL,1);
INSERT INTO products VALUES(47,'Junta da Tampa de Válvulas',NULL,1);
INSERT INTO products VALUES(48,'Junta da Cabeça',NULL,1);
INSERT INTO products VALUES(49,'Retentor da Cambota',NULL,1);
INSERT INTO products VALUES(50,'Retentor da Árvore de Cames',NULL,1);
INSERT INTO products VALUES(51,'Bomba de Óleo',NULL,1);
INSERT INTO products VALUES(52,'Termóstato',NULL,1);
INSERT INTO products VALUES(53,'Radiador',NULL,1);
INSERT INTO products VALUES(54,'Ventoinha do Radiador',NULL,1);
INSERT INTO products VALUES(55,'Intercooler',NULL,1);
INSERT INTO products VALUES(56,'Turbo',NULL,1);
INSERT INTO products VALUES(57,'Válvula EGR',NULL,1);
INSERT INTO products VALUES(58,'Coletor de Admissão',NULL,1);
INSERT INTO products VALUES(59,'Coletor de Escape',NULL,1);
INSERT INTO products VALUES(60,'Sonda Lambda',NULL,1);
INSERT INTO products VALUES(61,'Sensor MAP',NULL,1);
INSERT INTO products VALUES(62,'Sensor MAF',NULL,1);
INSERT INTO products VALUES(63,'Sensor de Temperatura',NULL,1);
INSERT INTO products VALUES(64,'Sensor da Cambota',NULL,1);
INSERT INTO products VALUES(65,'Sensor da Árvore de Cames',NULL,1);
INSERT INTO products VALUES(66,'Velas de Ignição',NULL,1);
INSERT INTO products VALUES(67,'Bobina de Ignição',NULL,1);
INSERT INTO products VALUES(68,'Cabos de Velas',NULL,1);
INSERT INTO products VALUES(69,'Bomba de Combustível',NULL,1);
INSERT INTO products VALUES(70,'Injetor',NULL,1);
INSERT INTO products VALUES(71,'Rampa de Injeção',NULL,1);
INSERT INTO products VALUES(72,'Regulador de Pressão',NULL,1);
INSERT INTO products VALUES(73,'Kit de Embraiagem',NULL,1);
INSERT INTO products VALUES(74,'Disco de Embraiagem',NULL,1);
INSERT INTO products VALUES(75,'Prato de Embraiagem',NULL,1);
INSERT INTO products VALUES(76,'Rolamento de Encosto',NULL,1);
INSERT INTO products VALUES(77,'Volante do Motor',NULL,1);
INSERT INTO products VALUES(78,'Semieixo',NULL,1);
INSERT INTO products VALUES(79,'Junta Homocinética',NULL,1);
INSERT INTO products VALUES(80,'Fole da Transmissão',NULL,1);
INSERT INTO products VALUES(81,'Catalisador',NULL,1);
INSERT INTO products VALUES(82,'Filtro de Partículas (FAP)',NULL,1);
INSERT INTO products VALUES(83,'Silenciador',NULL,1);
INSERT INTO products VALUES(84,'Tubo de Escape',NULL,1);
INSERT INTO products VALUES(85,'Abraçadeira de Escape',NULL,1);
INSERT INTO products VALUES(86,'Anticongelante',NULL,1);
INSERT INTO products VALUES(87,'Líquido de Refrigeração',NULL,1);
INSERT INTO products VALUES(88,'Tampa do Radiador',NULL,1);
INSERT INTO products VALUES(89,'Depósito de Expansão',NULL,1);
INSERT INTO products VALUES(90,'Mangueira do Radiador',NULL,1);
INSERT INTO products VALUES(91,'Bateria',NULL,1);
INSERT INTO products VALUES(92,'Alternador',NULL,1);
INSERT INTO products VALUES(93,'Motor de Arranque',NULL,1);
INSERT INTO products VALUES(94,'Fusível',NULL,1);
INSERT INTO products VALUES(95,'Relé',NULL,1);
INSERT INTO products VALUES(96,'Interruptor de Luzes',NULL,1);
INSERT INTO products VALUES(97,'Interruptor de Travão',NULL,1);
INSERT INTO products VALUES(98,'Interruptor de Marcha-Atrás',NULL,1);
INSERT INTO products VALUES(99,'Lâmpada H1',NULL,1);
INSERT INTO products VALUES(100,'Lâmpada H4',NULL,1);
INSERT INTO products VALUES(101,'Lâmpada H7',NULL,1);
INSERT INTO products VALUES(102,'Lâmpada LED',NULL,1);
INSERT INTO products VALUES(103,'Lâmpada W5W',NULL,1);
INSERT INTO products VALUES(104,'Pisca',NULL,1);
INSERT INTO products VALUES(105,'Farol Dianteiro',NULL,1);
INSERT INTO products VALUES(106,'Farolim Traseiro',NULL,1);
INSERT INTO products VALUES(107,'Escova Limpa-Para-Brisas',NULL,1);
INSERT INTO products VALUES(108,'Braço Limpa-Para-Brisas',NULL,1);
INSERT INTO products VALUES(109,'Bomba do Limpa-Vidros',NULL,1);
INSERT INTO products VALUES(110,'Líquido Limpa-Vidros',NULL,1);
INSERT INTO products VALUES(111,'Pneu 15"',NULL,1);
INSERT INTO products VALUES(112,'Pneu 16"',NULL,1);
INSERT INTO products VALUES(113,'Pneu 17"',NULL,1);
INSERT INTO products VALUES(114,'Válvula de Pneu',NULL,1);
INSERT INTO products VALUES(115,'Peso de Equilibragem',NULL,1);
INSERT INTO products VALUES(116,'Jante em Aço',NULL,1);
INSERT INTO products VALUES(117,'Jante em Liga Leve',NULL,1);
INSERT INTO products VALUES(118,'Gás R134a',NULL,1);
INSERT INTO products VALUES(119,'Gás R1234yf',NULL,1);
INSERT INTO products VALUES(120,'Compressor do Ar Condicionado',NULL,1);
INSERT INTO products VALUES(121,'Condensador',NULL,1);
INSERT INTO products VALUES(122,'Evaporador',NULL,1);
INSERT INTO products VALUES(123,'Filtro Secador',NULL,1);
INSERT INTO products VALUES(124,'Abraçadeira',NULL,1);
INSERT INTO products VALUES(125,'Anilha de Vedação',NULL,1);
INSERT INTO products VALUES(126,'Junta',NULL,1);
INSERT INTO products VALUES(127,'O-Ring',NULL,1);
INSERT INTO products VALUES(128,'Parafuso',NULL,1);
INSERT INTO products VALUES(129,'Porca',NULL,1);
INSERT INTO products VALUES(130,'Rebite',NULL,1);
INSERT INTO products VALUES(131,'Silicone de Junta',NULL,1);
INSERT INTO products VALUES(132,'Cola de Roscas',NULL,1);
INSERT INTO products VALUES(133,'Spray de Travões',NULL,1);
INSERT INTO products VALUES(134,'Limpa Contactos',NULL,1);
INSERT INTO products VALUES(135,'Spray Desengripante',NULL,1);
INSERT INTO products VALUES(136,'Desengordurante',NULL,1);
INSERT INTO products VALUES(137,'Limpa Injetores',NULL,1);
INSERT INTO products VALUES(138,'Limpa FAP',NULL,1);
INSERT INTO products VALUES(139,'Aditivo para Gasóleo',NULL,1);
INSERT INTO products VALUES(140,'Aditivo para Gasolina',NULL,1);
INSERT INTO products VALUES(141,'Aditivo para Óleo',NULL,1);
INSERT INTO products VALUES(142,'Espelho Retrovisor',NULL,1);
INSERT INTO products VALUES(143,'Puxador de Porta',NULL,1);
INSERT INTO products VALUES(144,'Elevador de Vidro',NULL,1);
INSERT INTO products VALUES(145,'Motor do Vidro Elétrico',NULL,1);
INSERT INTO products VALUES(146,'Fechadura da Porta',NULL,1);
INSERT INTO products VALUES(147,'Para-choques Dianteiro',NULL,1);
INSERT INTO products VALUES(148,'Para-choques Traseiro',NULL,1);
INSERT INTO products VALUES(149,'Grelha Frontal',NULL,1);
INSERT INTO products VALUES(150,'Capô',NULL,1);
INSERT INTO products VALUES(151,'Guarda-Lamas',NULL,1);
INSERT INTO products VALUES(152,'Buzina',NULL,1);
INSERT INTO products VALUES(153,'Sensor de Estacionamento',NULL,1);
INSERT INTO products VALUES(154,'Câmara de Marcha-Atrás',NULL,1);
INSERT INTO products VALUES(155,'Tapete de Borracha',NULL,1);
INSERT INTO products VALUES(156,'Extintor',NULL,1);
INSERT INTO products VALUES(157,'Triângulo de Pré-Sinalização',NULL,1);
INSERT INTO products VALUES(158,'Colete Refletor',NULL,1);
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
CREATE TABLE services_applied_products(
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
DELETE FROM sqlite_sequence;
CREATE UNIQUE INDEX unique_name_when_reference_null
ON products(name)
WHERE reference IS NULL;
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
COMMIT;
