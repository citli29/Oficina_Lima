DROP TABLE IF EXISTS utilizadores ;

CREATE TABLE utilizadores(
	id INT PRIMARY KEY,
	nome VARCHAR(40) NOT NULL,
	email VARCHAR(60) NOT NULL,
	password VARCHAR(60) NOT NULL
);

INSERT INTO utilizadores(id, nome, email, password) VALUES(1, 'teste', 'teste@email.com', 'teste');
SELECT * FROM utilizadores;
