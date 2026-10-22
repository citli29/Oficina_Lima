-- ============================================================
-- Release 2026-10-21: lab note presets + Laboratório finish rule.
-- Run on a DB that already has release_2026_10_20_migration.sql.
--
--  1. Note presets per lab action (tabled_action_values) come back,
--     in a new shape: (id, t_action_id, value), no i_class, and not
--     referenced by lab_action_values — picking a preset only copies
--     its text into the action's note. Presets are deleted with their
--     action.
--  2. Laboratório (type 2) can be finished without Serviço Realizado
--     when it has at least one lab action (lab items alone don't
--     count), but only once every lab action is ticked as done.
--
-- The backend must go up together with this file: the
-- /api/action_tabled_values endpoints are back and read the new table.
--
-- One transaction: if anything fails, nothing is applied. Running it a
-- second time fails at step 1 (tabled_action_values already exists)
-- and changes nothing.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_10_21_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- ------------------------------------------------------------
-- 1. Note presets (tabled_action_values)
-- ------------------------------------------------------------

CREATE TABLE tabled_action_values(
	id INTEGER PRIMARY KEY,
	t_action_id INTEGER NOT NULL,
	value VARCHAR(255) NOT NULL,
	FOREIGN KEY(t_action_id)
		REFERENCES tabled_actions(id)
);

CREATE INDEX idx_tabled_action_values_t_action_id ON tabled_action_values(t_action_id);

CREATE TRIGGER i_tabled_action_value_not_null
BEFORE INSERT ON tabled_action_values
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_action_id IS NULL THEN RAISE(ABORT, 'A ação é obrigatória.')
		WHEN NEW.value IS NULL OR TRIM(NEW.value) = '' THEN RAISE(ABORT, 'O valor é obrigatório.')
	END;
END;

CREATE TRIGGER u_tabled_action_value_not_null
BEFORE UPDATE ON tabled_action_values
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.t_action_id IS NULL THEN RAISE(ABORT, 'A ação é obrigatória.')
		WHEN NEW.value IS NULL OR TRIM(NEW.value) = '' THEN RAISE(ABORT, 'O valor é obrigatório.')
	END;
END;

CREATE TRIGGER i_tabled_action_value_t_action_id_fk
BEFORE INSERT ON tabled_action_values
FOR EACH ROW
WHEN NEW.t_action_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_actions WHERE id = NEW.t_action_id
)
BEGIN
	SELECT RAISE(ABORT, 'A ação indicada não existe.');
END;

CREATE TRIGGER u_tabled_action_value_t_action_id_fk
BEFORE UPDATE OF t_action_id ON tabled_action_values
FOR EACH ROW
WHEN NEW.t_action_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM tabled_actions WHERE id = NEW.t_action_id
)
BEGIN
	SELECT RAISE(ABORT, 'A ação indicada não existe.');
END;

-- Presets go with their action (d_tabled_action_in_use still blocks
-- deleting an action that is used on a service).
CREATE TRIGGER d_tabled_action_values_cascade
AFTER DELETE ON tabled_actions
FOR EACH ROW
BEGIN
	DELETE FROM tabled_action_values WHERE t_action_id = OLD.id;
END;

-- ------------------------------------------------------------
-- 2. Laboratório finish rule
-- ------------------------------------------------------------

-- Serviço Realizado not required for Laboratório with lab actions.
-- Also fires on a type change now. The INSERT version is unchanged.
DROP TRIGGER IF EXISTS u_service_description_required_to_finish;
CREATE TRIGGER u_service_description_required_to_finish
BEFORE UPDATE OF is_finished, service_description, service_type_id ON services
FOR EACH ROW
WHEN NEW.is_finished = 1
	AND TRIM(COALESCE(NEW.service_description, '')) = ''
	AND (OLD.is_finished != 1
		OR OLD.service_description IS NOT NEW.service_description
		OR OLD.service_type_id IS NOT NEW.service_type_id)
	AND NOT (NEW.service_type_id = 2 AND EXISTS (
		SELECT 1 FROM lab_action_values lav
		JOIN lab_items li ON li.id = lav.l_item_id
		WHERE li.service_id = NEW.id
	))
BEGIN
	SELECT RAISE(ABORT, 'Necessita preencher o serviço realizado para terminar o serviço.');
END;

-- Every lab action must be done to finish a Laboratório service.
CREATE TRIGGER u_service_lab_actions_done_to_finish
BEFORE UPDATE OF is_finished, service_type_id ON services
FOR EACH ROW
WHEN NEW.is_finished = 1
	AND NEW.service_type_id = 2
	AND (OLD.is_finished != 1 OR OLD.service_type_id IS NOT NEW.service_type_id)
	AND EXISTS (
		SELECT 1 FROM lab_action_values lav
		JOIN lab_items li ON li.id = lav.l_item_id
		WHERE li.service_id = NEW.id AND lav.is_done = 0
	)
BEGIN
	SELECT RAISE(ABORT, 'Existem ações do laboratório por concluir. Marque-as como feitas para terminar o serviço.');
END;

COMMIT;
