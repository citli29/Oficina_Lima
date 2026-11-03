-- ============================================================
-- Release 2026-11-03: orçamentos (quotes) + product name rule.
-- Run on a DB that already has release_2026_10_22_migration.sql.
--
--  1. Orçamentos: each service can have orçamentos (budgets), each with
--     its own products (budget_products), a title, a note, a done flag
--     ("Orçamentado") and the creation time.
--     - Orçamentado locks the orçamento: only the note can change; no
--       products can be added, changed or removed, and it can't be
--       deleted until it's un-marked.
--     - Deleting an orçamento deletes its products (trigger).
--     - created_at is set on insert (UTC) and can't be changed.
--  2. Services and products used by an orçamento can't be deleted
--     (d_service_in_use / d_product_in_use recreated with the check).
--  3. Products without a reference: the same name is now allowed when the
--     product type is different (was: unique name whatever the type).
--
-- The backend and frontend must go up together with this file: the API
-- reads and writes budgets / budget_products (/api/budgets,
-- /api/services/{id}/budgets...). Orçamento notifications use the
-- existing notifications table (no change needed there).
--
-- One transaction: if anything fails, nothing is applied. Running it a
-- second time fails at step 1 (the table already exists) and changes
-- nothing.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_11_03_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- ------------------------------------------------------------
-- 1. Orçamentos: tables + indexes
-- ------------------------------------------------------------

CREATE TABLE budgets(
	id INTEGER PRIMARY KEY,
	title VARCHAR(96) NOT NULL DEFAULT '',
	done BOOLEAN NOT NULL DEFAULT FALSE,
	service_id INTEGER NOT NULL,
	note TEXT NOT NULL DEFAULT '',
	created_at DATETIME,
	FOREIGN KEY(service_id)
		REFERENCES services(id)
);

CREATE TABLE budget_products(
	id INTEGER PRIMARY KEY,
	budget_id INTEGER NOT NULL,
	product_id INTEGER NOT NULL,
	quantity INTEGER NOT NULL DEFAULT 1,
	FOREIGN KEY(budget_id)
		REFERENCES budgets(id),
	FOREIGN KEY(product_id)
		REFERENCES products(id)
);

CREATE INDEX idx_budgets_service_id ON budgets(service_id);
CREATE INDEX idx_budget_products_budget_id ON budget_products(budget_id);
CREATE INDEX idx_budget_products_product_id ON budget_products(product_id);

-- ------------------------------------------------------------
-- 1b. Orçamentos: triggers on budgets
--     (not null, done 0/1, service exists, created_at set + locked,
--      Orçamentado locks, products deleted with their orçamento)
-- ------------------------------------------------------------

CREATE TRIGGER d_budget_done
BEFORE DELETE ON budgets
FOR EACH ROW
WHEN OLD.done = 1
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar um orçamento já orçamentado.');
END;

CREATE TRIGGER d_budget_products_cascade
AFTER DELETE ON budgets
FOR EACH ROW
BEGIN
	DELETE FROM budget_products WHERE budget_id = OLD.id;
END;

CREATE TRIGGER i_budget_created_at
AFTER INSERT ON budgets
FOR EACH ROW
WHEN NEW.created_at IS NULL
BEGIN
	UPDATE budgets SET created_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER i_budget_done_valid
BEFORE INSERT ON budgets
FOR EACH ROW
WHEN NEW.done IS NOT NULL AND NEW.done NOT IN (0, 1)
BEGIN
	SELECT RAISE(ABORT, 'O estado do orçamento deve ser verdadeiro ou falso.');
END;

CREATE TRIGGER i_budget_not_null
BEFORE INSERT ON budgets
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.title IS NULL THEN RAISE(ABORT, 'O título é obrigatório.')
		WHEN NEW.note IS NULL THEN RAISE(ABORT, 'A nota não pode ser nula.')
		WHEN NEW.done IS NULL THEN RAISE(ABORT, 'O estado do orçamento é obrigatório.')
		WHEN NEW.service_id IS NULL THEN RAISE(ABORT, 'O serviço é obrigatório.')
	END;
END;

CREATE TRIGGER i_budget_service_id_fk
BEFORE INSERT ON budgets
FOR EACH ROW
WHEN NEW.service_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM services WHERE id = NEW.service_id
)
BEGIN
	SELECT RAISE(ABORT, 'O serviço indicado não existe.');
END;

CREATE TRIGGER u_budget_created_at_locked
BEFORE UPDATE OF created_at ON budgets
FOR EACH ROW
WHEN NEW.created_at IS NOT OLD.created_at AND OLD.created_at IS NOT NULL
BEGIN
	SELECT RAISE(ABORT, 'A data de criação do orçamento não pode ser alterada.');
END;

CREATE TRIGGER u_budget_done_locked
BEFORE UPDATE OF title, service_id ON budgets
FOR EACH ROW
WHEN OLD.done = 1 AND NEW.done = 1
	AND (NEW.title IS NOT OLD.title OR NEW.service_id IS NOT OLD.service_id)
BEGIN
	SELECT RAISE(ABORT, 'O orçamento já está orçamentado: só a nota pode ser alterada.');
END;

CREATE TRIGGER u_budget_done_valid
BEFORE UPDATE OF done ON budgets
FOR EACH ROW
WHEN NEW.done IS NOT NULL AND NEW.done NOT IN (0, 1)
BEGIN
	SELECT RAISE(ABORT, 'O estado do orçamento deve ser verdadeiro ou falso.');
END;

CREATE TRIGGER u_budget_not_null
BEFORE UPDATE ON budgets
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.title IS NULL THEN RAISE(ABORT, 'O título é obrigatório.')
		WHEN NEW.note IS NULL THEN RAISE(ABORT, 'A nota não pode ser nula.')
		WHEN NEW.done IS NULL THEN RAISE(ABORT, 'O estado do orçamento é obrigatório.')
		WHEN NEW.service_id IS NULL THEN RAISE(ABORT, 'O serviço é obrigatório.')
	END;
END;

CREATE TRIGGER u_budget_service_id_fk
BEFORE UPDATE OF service_id ON budgets
FOR EACH ROW
WHEN NEW.service_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM services WHERE id = NEW.service_id
)
BEGIN
	SELECT RAISE(ABORT, 'O serviço indicado não existe.');
END;

-- ------------------------------------------------------------
-- 1c. Orçamentos: triggers on budget_products
--     (not null, quantity integer >= 1, budget/product exist,
--      no changes while the orçamento is Orçamentado)
-- ------------------------------------------------------------

CREATE TRIGGER d_budget_product_budget_done
BEFORE DELETE ON budget_products
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM budgets WHERE id = OLD.budget_id AND done = 1)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível remover produtos de um orçamento já orçamentado.');
END;

CREATE TRIGGER i_budget_product_budget_done
BEFORE INSERT ON budget_products
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM budgets WHERE id = NEW.budget_id AND done = 1)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível adicionar produtos a um orçamento já orçamentado.');
END;

CREATE TRIGGER i_budget_product_budget_id_fk
BEFORE INSERT ON budget_products
FOR EACH ROW
WHEN NEW.budget_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM budgets WHERE id = NEW.budget_id
)
BEGIN
	SELECT RAISE(ABORT, 'O orçamento indicado não existe.');
END;

CREATE TRIGGER i_budget_product_not_null
BEFORE INSERT ON budget_products
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.budget_id IS NULL THEN RAISE(ABORT, 'O orçamento é obrigatório.')
		WHEN NEW.product_id IS NULL THEN RAISE(ABORT, 'O produto é obrigatório.')
		WHEN NEW.quantity IS NULL THEN RAISE(ABORT, 'A quantidade é obrigatória.')
	END;
END;

CREATE TRIGGER i_budget_product_product_id_fk
BEFORE INSERT ON budget_products
FOR EACH ROW
WHEN NEW.product_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM products WHERE id = NEW.product_id
)
BEGIN
	SELECT RAISE(ABORT, 'O produto indicado não existe.');
END;

CREATE TRIGGER i_budget_product_quantity_valid
BEFORE INSERT ON budget_products
FOR EACH ROW
WHEN NEW.quantity IS NOT NULL AND (typeof(NEW.quantity) != 'integer' OR NEW.quantity < 1)
BEGIN
	SELECT RAISE(ABORT, 'A quantidade deve ser um número inteiro maior que zero.');
END;

CREATE TRIGGER u_budget_product_budget_done
BEFORE UPDATE ON budget_products
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM budgets WHERE id IN (OLD.budget_id, NEW.budget_id) AND done = 1)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar produtos de um orçamento já orçamentado.');
END;

CREATE TRIGGER u_budget_product_budget_id_fk
BEFORE UPDATE OF budget_id ON budget_products
FOR EACH ROW
WHEN NEW.budget_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM budgets WHERE id = NEW.budget_id
)
BEGIN
	SELECT RAISE(ABORT, 'O orçamento indicado não existe.');
END;

CREATE TRIGGER u_budget_product_not_null
BEFORE UPDATE ON budget_products
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.budget_id IS NULL THEN RAISE(ABORT, 'O orçamento é obrigatório.')
		WHEN NEW.product_id IS NULL THEN RAISE(ABORT, 'O produto é obrigatório.')
		WHEN NEW.quantity IS NULL THEN RAISE(ABORT, 'A quantidade é obrigatória.')
	END;
END;

CREATE TRIGGER u_budget_product_product_id_fk
BEFORE UPDATE OF product_id ON budget_products
FOR EACH ROW
WHEN NEW.product_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM products WHERE id = NEW.product_id
)
BEGIN
	SELECT RAISE(ABORT, 'O produto indicado não existe.');
END;

CREATE TRIGGER u_budget_product_quantity_valid
BEFORE UPDATE OF quantity ON budget_products
FOR EACH ROW
WHEN NEW.quantity IS NOT NULL AND (typeof(NEW.quantity) != 'integer' OR NEW.quantity < 1)
BEGIN
	SELECT RAISE(ABORT, 'A quantidade deve ser um número inteiro maior que zero.');
END;

-- ------------------------------------------------------------
-- 2. Services / products used by an orçamento can't be deleted
-- ------------------------------------------------------------

DROP TRIGGER d_service_in_use;
CREATE TRIGGER d_service_in_use
BEFORE DELETE ON services
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM services_products_requested WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM services_applied_products WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM services_user_time WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM services_user_time_punches WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM lab_items WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM service_associations WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM budgets WHERE service_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o serviço porque tem produtos, tempos, orçamentos, laboratório ou está numa associação.');
END;

DROP TRIGGER d_product_in_use;
CREATE TRIGGER d_product_in_use
BEFORE DELETE ON products
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM services_products_requested WHERE product_id = OLD.id) OR EXISTS (SELECT 1 FROM services_applied_products WHERE product_id = OLD.id) OR EXISTS (SELECT 1 FROM budget_products WHERE product_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o produto porque está associado a serviços ou orçamentos.');
END;

-- ------------------------------------------------------------
-- 3. Products without reference: unique by name + product type
-- ------------------------------------------------------------

DROP INDEX unique_name_when_reference_null;
CREATE UNIQUE INDEX unique_name_when_reference_null
ON products(name, product_type_id)
WHERE reference IS NULL;

DROP TRIGGER i_product_name_unique_when_no_reference;
CREATE TRIGGER i_product_name_unique_when_no_reference
BEFORE INSERT ON products
FOR EACH ROW
WHEN NEW.reference IS NULL AND EXISTS (
	SELECT 1 FROM products
	WHERE lower(trim(name)) = lower(trim(NEW.name))
		AND reference IS NULL
		AND product_type_id IS NEW.product_type_id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um produto com esse nome e tipo sem referência associada.');
END;

DROP TRIGGER u_product_name_unique_when_no_reference;
CREATE TRIGGER u_product_name_unique_when_no_reference
BEFORE UPDATE OF name, reference, product_type_id ON products
FOR EACH ROW
WHEN (NEW.name IS NOT OLD.name OR NEW.reference IS NOT OLD.reference OR NEW.product_type_id IS NOT OLD.product_type_id)
	AND NEW.reference IS NULL AND EXISTS (
	SELECT 1 FROM products
	WHERE lower(trim(name)) = lower(trim(NEW.name))
		AND reference IS NULL
		AND product_type_id IS NEW.product_type_id
		AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um produto com esse nome e tipo sem referência associada.');
END;

COMMIT;
