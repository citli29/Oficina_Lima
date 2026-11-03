-- ============================================================
-- Release 2026-11-03b: Gestão de Tipos de Serviço.
-- Run on a DB that already has release_2026_11_03_migration.sql.
--
--  1. service_types.is_locked (0/1). The four types that exist today
--     (1 Mecânica, 2 Laboratório, 3 Deslocações, 4 Eletricidade) have
--     their own rules in code and triggers, so they're locked: they can't
--     be renamed or deleted. New types made from the Gestão page are
--     unlocked (the API never sets is_locked).
--     To lock a future type by hand: UPDATE service_types SET is_locked = 1
--     WHERE id = ...; (locking is allowed, unlocking is refused).
--  2. A service type used by services or marcações can't be deleted.
--
-- The backend and frontend must go up together with this file: the API
-- adds POST/GET/PUT/DELETE /api/service_types[/{id}] and the list returns
-- is_locked + usage counts.
--
-- One transaction: if anything fails, nothing is applied. Running it a
-- second time fails at step 1 (the column already exists) and changes
-- nothing.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_11_03b_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- 1. Lock flag + the current types locked.
ALTER TABLE service_types ADD COLUMN is_locked BOOLEAN NOT NULL DEFAULT 0;
UPDATE service_types SET is_locked = 1 WHERE id IN (1, 2, 3, 4);

CREATE TRIGGER i_service_type_is_locked_valid
BEFORE INSERT ON service_types
FOR EACH ROW
WHEN NEW.is_locked IS NULL OR NEW.is_locked NOT IN (0, 1)
BEGIN
	SELECT RAISE(ABORT, 'O bloqueio do tipo de serviço deve ser verdadeiro ou falso.');
END;

CREATE TRIGGER u_service_type_is_locked_valid
BEFORE UPDATE OF is_locked ON service_types
FOR EACH ROW
WHEN NEW.is_locked IS NULL OR NEW.is_locked NOT IN (0, 1)
BEGIN
	SELECT RAISE(ABORT, 'O bloqueio do tipo de serviço deve ser verdadeiro ou falso.');
END;

CREATE TRIGGER u_service_type_locked
BEFORE UPDATE ON service_types
FOR EACH ROW
WHEN OLD.is_locked = 1
BEGIN
	SELECT RAISE(ABORT, 'Este tipo de serviço tem regras próprias no sistema e não pode ser alterado.');
END;

CREATE TRIGGER d_service_type_locked
BEFORE DELETE ON service_types
FOR EACH ROW
WHEN OLD.is_locked = 1
BEGIN
	SELECT RAISE(ABORT, 'Este tipo de serviço tem regras próprias no sistema e não pode ser apagado.');
END;

-- 2. Not deletable while in use.
CREATE TRIGGER d_service_type_in_use
BEFORE DELETE ON service_types
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM services WHERE service_type_id = OLD.id)
	OR EXISTS (SELECT 1 FROM schedules WHERE service_type_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o tipo de serviço porque está a ser usado em serviços ou marcações.');
END;

COMMIT;
