-- ============================================================
-- Release 2026-11-06: "Pedir" per product on orçamentos.
-- Run on a DB that already has release_2026_11_04_migration.sql.
--
--  1. budget_products.to_order (0/1, default 0): the mechanic marks a
--     product of an orçamento as "to be ordered" (Editar → Guardar on the
--     service page; "Pedir todo" ticks them all). Orçamentado locks it like
--     the quantity (the existing u_budget_product_budget_done trigger
--     already covers every column, nothing to change there).
--  2. i_/u_budget_product_to_order_valid: only 0 or 1.
--
-- The backend and frontend must go up together with this file (the
-- orçamento API and pages read and write to_order).
--
-- AFTER running this file, on the server:
--   php utils/admin_scripts/trash_triggers.php
-- (so the Lixo also saves to_order when an orçamento product is deleted).
--
-- One transaction: if anything fails, nothing is applied. Running it a
-- second time fails at step 1 (the column already exists) and changes
-- nothing.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_11_06_migration.sql
-- ============================================================

BEGIN;

-- 1. The column
ALTER TABLE budget_products ADD COLUMN to_order BOOLEAN NOT NULL DEFAULT 0;

-- 2. Only 0 / 1
CREATE TRIGGER i_budget_product_to_order_valid
BEFORE INSERT ON budget_products
FOR EACH ROW
WHEN NEW.to_order IS NULL OR NEW.to_order NOT IN (0, 1)
BEGIN
	SELECT RAISE(ABORT, 'O campo Pedir deve ser verdadeiro ou falso.');
END;

CREATE TRIGGER u_budget_product_to_order_valid
BEFORE UPDATE OF to_order ON budget_products
FOR EACH ROW
WHEN NEW.to_order IS NULL OR NEW.to_order NOT IN (0, 1)
BEGIN
	SELECT RAISE(ABORT, 'O campo Pedir deve ser verdadeiro ou falso.');
END;

COMMIT;
