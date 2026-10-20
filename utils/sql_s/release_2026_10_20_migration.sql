-- ============================================================
-- Release 2026-10-20: Eletricidade + lab action notes / done check.
-- Run on a DB that already has release_2026_10_08_full_migration.sql
-- (or a, b and c) — production as of 2026-10-20.
--
--  1. New service type Eletricidade (id 4 — the frontend counts on that id
--     for its colour and its own calendar section).
--  2. Eletricidade, like Laboratório, can be finished without kms.
--  3. Lab actions: the "action values" list (tabled_action_values) is
--     replaced by a free note per action on the service, plus a done
--     check. Any value already picked on a service is kept as its note.
--
-- The backend must go up together with this file: the old
-- /api/action_tabled_values endpoints are gone and the API now reads
-- lab_action_values.note / is_done.
--
-- One transaction: if anything fails, nothing is applied. Running it a
-- second time fails at step 3 (tabled_action_values no longer exists)
-- and changes nothing.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_10_20_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- ------------------------------------------------------------
-- 1. Service type Eletricidade (id 4)
-- ------------------------------------------------------------

INSERT INTO service_types (id, name)
SELECT 4, 'Eletricidade'
WHERE NOT EXISTS (SELECT 1 FROM service_types WHERE id = 4);

-- Stops here if id 4 is already taken by another type.
CREATE TEMP TABLE _release_check(ok INTEGER NOT NULL CHECK(ok = 1));
INSERT INTO _release_check SELECT (SELECT name FROM service_types WHERE id = 4) = 'Eletricidade';
DROP TABLE _release_check;

-- ------------------------------------------------------------
-- 2. kms not required to finish Laboratório (2) or Eletricidade (4)
-- ------------------------------------------------------------

DROP TRIGGER IF EXISTS i_service_kms_required_to_finish;
CREATE TRIGGER i_service_kms_required_to_finish
BEFORE INSERT ON services
FOR EACH ROW
WHEN NEW.is_finished = 1
	AND NEW.car_id IS NOT NULL
	AND NEW.service_type_id NOT IN (2, 4)
	AND NEW.kms IS NULL
BEGIN
	SELECT RAISE(ABORT, 'Necessita preencher os kms para finalizar a viatura.');
END;

DROP TRIGGER IF EXISTS u_service_kms_required_to_finish;
CREATE TRIGGER u_service_kms_required_to_finish
BEFORE UPDATE OF is_finished, car_id, service_type_id, kms ON services
FOR EACH ROW
WHEN NEW.is_finished = 1
	AND NEW.car_id IS NOT NULL
	AND NEW.service_type_id NOT IN (2, 4)
	AND NEW.kms IS NULL
BEGIN
	SELECT RAISE(ABORT, 'Necessita preencher os kms para finalizar a viatura.');
END;

-- ------------------------------------------------------------
-- 3. Lab action notes + done check
-- ------------------------------------------------------------

-- 3a. Keep the picked values: copied into the free-text column (which
--     becomes the note) where that was still empty. Finished services
--     are locked against changes, so the lock is lifted just for this
--     copy and put back exactly as it was.
DROP TRIGGER u_lab_action_value_service_finished;

UPDATE lab_action_values
SET value = (SELECT tav.value FROM tabled_action_values tav WHERE tav.id = lab_action_values.t_action_value_id)
WHERE t_action_value_id IS NOT NULL
	AND (value IS NULL OR TRIM(value) = '')
	AND EXISTS (SELECT 1 FROM tabled_action_values tav WHERE tav.id = lab_action_values.t_action_value_id);

CREATE TRIGGER u_lab_action_value_service_finished
BEFORE UPDATE ON lab_action_values
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM lab_items li
	JOIN services s ON s.id = li.service_id
	WHERE li.id = OLD.l_item_id AND s.is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;

-- 3b. The link to the old value list goes.
DROP TRIGGER i_lab_action_value_t_action_value_id_fk;
DROP TRIGGER u_lab_action_value_t_action_value_id_fk;
ALTER TABLE lab_action_values DROP COLUMN t_action_value_id;

-- 3c. An action can't be deleted while a service uses it (there are no
--     value lists left to check).
DROP TRIGGER IF EXISTS d_tabled_action_in_use;
CREATE TRIGGER d_tabled_action_in_use
BEFORE DELETE ON tabled_actions
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM lab_action_values WHERE t_action_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar a ação porque está a ser usada em serviços.');
END;

-- 3d. The value lists themselves.
DROP TRIGGER i_tabled_action_value_not_null;
DROP TRIGGER u_tabled_action_value_not_null;
DROP TRIGGER i_tabled_action_value_t_action_id_fk;
DROP TRIGGER u_tabled_action_value_t_action_id_fk;
DROP TABLE tabled_action_values;

-- 3e. value -> note, and the done check.
ALTER TABLE lab_action_values RENAME COLUMN value TO note;
ALTER TABLE lab_action_values ADD COLUMN is_done INTEGER NOT NULL DEFAULT 0;

CREATE TRIGGER i_lab_action_value_is_done_valid
BEFORE INSERT ON lab_action_values
FOR EACH ROW
WHEN NEW.is_done IS NULL OR NEW.is_done NOT IN (0, 1)
BEGIN
	SELECT RAISE(ABORT, 'O estado de concluído da ação deve ser verdadeiro ou falso.');
END;

CREATE TRIGGER u_lab_action_value_is_done_valid
BEFORE UPDATE OF is_done ON lab_action_values
FOR EACH ROW
WHEN NEW.is_done IS NULL OR NEW.is_done NOT IN (0, 1)
BEGIN
	SELECT RAISE(ABORT, 'O estado de concluído da ação deve ser verdadeiro ou falso.');
END;

COMMIT;
