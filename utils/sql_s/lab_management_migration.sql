-- Lab Management schema migration
-- Brings a database at commit 80f65a2f4d9a40597b (current production) up to
-- date with the schema currently live in dev.
--
-- Reconstructed by diffing dev DB backups against the live dev database:
--   - Backups/2026_09_23_08_46_01.db (schema matches 80f65a2 exactly, minus
--     the not-yet-production "items" table already present in that backup)
--   - the current live Database.db
-- No pre-existing table (services, services_applied_products, etc.) changed
-- between those two points -- this migration only adds 7 new tables plus
-- their validation triggers and one unique index. Safe to run more than
-- once (every statement is idempotent).

BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS tabled_items(
	id INTEGER PRIMARY KEY,
	name VARCHAR(60) NOT NULL UNIQUE,
	i_class VARCHAR(60) NULL
);

CREATE TRIGGER IF NOT EXISTS i_tabled_item_not_null
BEFORE INSERT ON tabled_items
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.name IS NULL THEN RAISE(ABORT, 'O nome do item é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_tabled_item_not_null
BEFORE UPDATE ON tabled_items
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.name IS NULL THEN RAISE(ABORT, 'O nome do item é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_tabled_item_name_unique
BEFORE INSERT ON tabled_items
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM tabled_items WHERE name = NEW.name
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um item com esse nome.');
END;

CREATE TRIGGER IF NOT EXISTS u_tabled_item_name_unique
BEFORE UPDATE OF name ON tabled_items
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM tabled_items WHERE name = NEW.name AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um item com esse nome.');
END;


CREATE TABLE IF NOT EXISTS tabled_properties(
	id INTEGER PRIMARY KEY,
	t_item_id INTEGER NOT NULL,
	name VARCHAR(60) NOT NULL,
	i_class VARCHAR(60) NULL, is_primary INTEGER NOT NULL DEFAULT 0,
	FOREIGN KEY(t_item_id)
		REFERENCES tabled_items(id)
);

CREATE TRIGGER IF NOT EXISTS i_tabled_property_not_null
BEFORE INSERT ON tabled_properties
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.name IS NULL THEN RAISE(ABORT, 'O nome da propriedade é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_tabled_property_not_null
BEFORE UPDATE ON tabled_properties
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.name IS NULL THEN RAISE(ABORT, 'O nome da propriedade é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_tabled_property_t_item_id_fk
BEFORE INSERT ON tabled_properties
FOR EACH ROW
WHEN NEW.t_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_items WHERE id = NEW.t_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_tabled_property_t_item_id_fk
BEFORE UPDATE OF t_item_id ON tabled_properties
FOR EACH ROW
WHEN NEW.t_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_items WHERE id = NEW.t_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;


CREATE TABLE IF NOT EXISTS tabled_actions(
	id INTEGER PRIMARY KEY,
	t_item_id INTEGER NOT NULL,
	name VARCHAR(60) NOT NULL,
	i_class VARCHAR(60) NULL,
	FOREIGN KEY(t_item_id)
		REFERENCES tabled_items(id)
);

CREATE TRIGGER IF NOT EXISTS i_tabled_action_not_null
BEFORE INSERT ON tabled_actions
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.name IS NULL THEN RAISE(ABORT, 'O nome da ação é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_tabled_action_not_null
BEFORE UPDATE ON tabled_actions
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.name IS NULL THEN RAISE(ABORT, 'O nome da ação é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_tabled_action_t_item_id_fk
BEFORE INSERT ON tabled_actions
FOR EACH ROW
WHEN NEW.t_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_items WHERE id = NEW.t_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_tabled_action_t_item_id_fk
BEFORE UPDATE OF t_item_id ON tabled_actions
FOR EACH ROW
WHEN NEW.t_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_items WHERE id = NEW.t_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;


CREATE TABLE IF NOT EXISTS tabled_action_values(
	id INTEGER PRIMARY KEY,
	t_action_id INTEGER NOT NULL,
	value VARCHAR(255) NOT NULL,
	i_class VARCHAR(60) NULL,
	FOREIGN KEY(t_action_id)
		REFERENCES tabled_actions(id)
);

CREATE TRIGGER IF NOT EXISTS i_tabled_action_value_not_null
BEFORE INSERT ON tabled_action_values
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_action_id IS NULL THEN RAISE(ABORT, 'A ação é obrigatória.')
		WHEN NEW.value IS NULL THEN RAISE(ABORT, 'O valor é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_tabled_action_value_not_null
BEFORE UPDATE ON tabled_action_values
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_action_id IS NULL THEN RAISE(ABORT, 'A ação é obrigatória.')
		WHEN NEW.value IS NULL THEN RAISE(ABORT, 'O valor é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_tabled_action_value_t_action_id_fk
BEFORE INSERT ON tabled_action_values
FOR EACH ROW
WHEN NEW.t_action_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_actions WHERE id = NEW.t_action_id
)
BEGIN
	SELECT RAISE(ABORT, 'A ação indicada não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_tabled_action_value_t_action_id_fk
BEFORE UPDATE OF t_action_id ON tabled_action_values
FOR EACH ROW
WHEN NEW.t_action_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_actions WHERE id = NEW.t_action_id
)
BEGIN
	SELECT RAISE(ABORT, 'A ação indicada não existe.');
END;


CREATE TABLE IF NOT EXISTS lab_items(
	id INTEGER PRIMARY KEY,
	t_item_id INTEGER NOT NULL,
	service_id INTEGER NOT NULL,
	FOREIGN KEY(t_item_id)
		REFERENCES tabled_items(id),
	FOREIGN KEY(service_id)
		REFERENCES services(id)
);

CREATE TRIGGER IF NOT EXISTS i_lab_item_not_null
BEFORE INSERT ON lab_items
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.service_id IS NULL THEN RAISE(ABORT, 'O serviço é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_lab_item_not_null
BEFORE UPDATE ON lab_items
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.service_id IS NULL THEN RAISE(ABORT, 'O serviço é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_lab_item_t_item_id_fk
BEFORE INSERT ON lab_items
FOR EACH ROW
WHEN NEW.t_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_items WHERE id = NEW.t_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_item_t_item_id_fk
BEFORE UPDATE OF t_item_id ON lab_items
FOR EACH ROW
WHEN NEW.t_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_items WHERE id = NEW.t_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS i_lab_item_service_id_fk
BEFORE INSERT ON lab_items
FOR EACH ROW
WHEN NEW.service_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM services WHERE id = NEW.service_id
)
BEGIN
	SELECT RAISE(ABORT, 'O serviço indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_item_service_id_fk
BEFORE UPDATE OF service_id ON lab_items
FOR EACH ROW
WHEN NEW.service_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM services WHERE id = NEW.service_id
)
BEGIN
	SELECT RAISE(ABORT, 'O serviço indicado não existe.');
END;


CREATE TABLE IF NOT EXISTS lab_action_values(
	id INTEGER PRIMARY KEY,
	l_item_id INTEGER NOT NULL,
	t_action_id INTEGER NOT NULL,
	value VARCHAR(255) NULL, t_action_value_id INTEGER NULL,
	FOREIGN KEY(l_item_id)
		REFERENCES lab_items(id),
	FOREIGN KEY(t_action_id)
		REFERENCES tabled_actions(id)
);

CREATE TRIGGER IF NOT EXISTS i_lab_action_value_not_null
BEFORE INSERT ON lab_action_values
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.l_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.t_action_id IS NULL THEN RAISE(ABORT, 'A ação é obrigatória.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_lab_action_value_not_null
BEFORE UPDATE ON lab_action_values
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.l_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.t_action_id IS NULL THEN RAISE(ABORT, 'A ação é obrigatória.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_lab_action_value_l_item_id_fk
BEFORE INSERT ON lab_action_values
FOR EACH ROW
WHEN NEW.l_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM lab_items WHERE id = NEW.l_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_action_value_l_item_id_fk
BEFORE UPDATE OF l_item_id ON lab_action_values
FOR EACH ROW
WHEN NEW.l_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM lab_items WHERE id = NEW.l_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS i_lab_action_value_t_action_id_fk
BEFORE INSERT ON lab_action_values
FOR EACH ROW
WHEN NEW.t_action_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_actions WHERE id = NEW.t_action_id
)
BEGIN
	SELECT RAISE(ABORT, 'A ação indicada não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_action_value_t_action_id_fk
BEFORE UPDATE OF t_action_id ON lab_action_values
FOR EACH ROW
WHEN NEW.t_action_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_actions WHERE id = NEW.t_action_id
)
BEGIN
	SELECT RAISE(ABORT, 'A ação indicada não existe.');
END;

CREATE TRIGGER IF NOT EXISTS i_lab_action_value_t_action_value_id_fk
BEFORE INSERT ON lab_action_values
FOR EACH ROW
WHEN NEW.t_action_value_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_action_values WHERE id = NEW.t_action_value_id
)
BEGIN
	SELECT RAISE(ABORT, 'O valor tabelado indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_action_value_t_action_value_id_fk
BEFORE UPDATE OF t_action_value_id ON lab_action_values
FOR EACH ROW
WHEN NEW.t_action_value_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_action_values WHERE id = NEW.t_action_value_id
)
BEGIN
	SELECT RAISE(ABORT, 'O valor tabelado indicado não existe.');
END;


CREATE TABLE IF NOT EXISTS lab_property_values(
	id INTEGER PRIMARY KEY,
	l_item_id INTEGER NOT NULL,
	property_id INTEGER NOT NULL,
	value VARCHAR(255) NULL,
	FOREIGN KEY(l_item_id)
		REFERENCES lab_items(id),
	FOREIGN KEY(property_id)
		REFERENCES tabled_properties(id)
);

CREATE TRIGGER IF NOT EXISTS i_lab_property_value_not_null
BEFORE INSERT ON lab_property_values
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.l_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.property_id IS NULL THEN RAISE(ABORT, 'A propriedade é obrigatória.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_lab_property_value_not_null
BEFORE UPDATE ON lab_property_values
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.l_item_id IS NULL THEN RAISE(ABORT, 'O item é obrigatório.')
		WHEN NEW.property_id IS NULL THEN RAISE(ABORT, 'A propriedade é obrigatória.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_lab_property_value_l_item_id_fk
BEFORE INSERT ON lab_property_values
FOR EACH ROW
WHEN NEW.l_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM lab_items WHERE id = NEW.l_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_property_value_l_item_id_fk
BEFORE UPDATE OF l_item_id ON lab_property_values
FOR EACH ROW
WHEN NEW.l_item_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM lab_items WHERE id = NEW.l_item_id
)
BEGIN
	SELECT RAISE(ABORT, 'O item indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS i_lab_property_value_property_id_fk
BEFORE INSERT ON lab_property_values
FOR EACH ROW
WHEN NEW.property_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_properties WHERE id = NEW.property_id
)
BEGIN
	SELECT RAISE(ABORT, 'A propriedade indicada não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_property_value_property_id_fk
BEFORE UPDATE OF property_id ON lab_property_values
FOR EACH ROW
WHEN NEW.property_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_properties WHERE id = NEW.property_id
)
BEGIN
	SELECT RAISE(ABORT, 'A propriedade indicada não existe.');
END;

CREATE UNIQUE INDEX IF NOT EXISTS lab_property_values_l_item_property_unique
ON lab_property_values(l_item_id, property_id);

CREATE TRIGGER IF NOT EXISTS i_lab_property_value_unique
BEFORE INSERT ON lab_property_values
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM lab_property_values
	WHERE l_item_id = NEW.l_item_id AND property_id = NEW.property_id
)
BEGIN
	SELECT RAISE(ABORT, 'Já foi registado um valor para esta propriedade neste item.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_property_value_unique
BEFORE UPDATE OF l_item_id, property_id ON lab_property_values
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM lab_property_values
	WHERE l_item_id = NEW.l_item_id AND property_id = NEW.property_id AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já foi registado um valor para esta propriedade neste item.');
END;

COMMIT;
