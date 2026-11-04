-- ============================================================
-- Release 2026-11-04: shop login + case-insensitive product references.
-- Run on a DB that already has release_2026_11_03b_migration.sql.
--
--  1. shop_accounts: one login per device group (oficina, escritório,
--     admin). notification_type_id locks which notifications the account
--     sees (NULL = it chooses); is_admin opens the admin-only pages
--     (Cópias de Segurança, Lixo).
--  2. auth_tokens: one row per logged-in device (Gestão → Dispositivos).
--     Tokens go with their account (ON DELETE CASCADE, needs PHP's
--     PRAGMA foreign_keys = on).
--  3. login_attempts: 5 failed logins per IP → 15 min lock.
--  4. Product references are unique ignoring case and outer spaces
--     ("ABC 1" = "abc 1 "). Only blocks new inserts/updates: check prod
--     for existing duplicates BEFORE running this:
--       SELECT lower(trim(reference)) r, group_concat(id) FROM products
--       WHERE reference IS NOT NULL GROUP BY r HAVING count(*) > 1;
--
-- The backend and frontend must go up together with this file: every
-- route except POST /api/auth/login now needs a token, and the
-- service sub-rows (products, times, punches) use real ids.
--
-- AFTER running this file, on the server:
--   a. php utils/trash_triggers.php      (creates deleted_rows + the
--      trash_<table> triggers; must run after all the SQL above)
--   b. php utils/set_shop_password.php   (once per account; ≥12 chars)
--   c. By hand, per account:
--        UPDATE shop_accounts SET is_admin = 1 WHERE username = 'admin';
--        UPDATE shop_accounts SET notification_type_id = 3 WHERE username = 'oficina';
--        UPDATE shop_accounts SET notification_type_id = 2 WHERE username = 'escritorio';
--      (check the notification_types ids on prod first)
-- Without step b nobody can log in.
--
-- One transaction: if anything fails, nothing is applied. Running it a
-- second time fails at step 1 (the table already exists) and changes
-- nothing.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_11_04_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- 1. Accounts.
CREATE TABLE shop_accounts(
	id INTEGER PRIMARY KEY,
	username VARCHAR(60) UNIQUE NOT NULL,
	password_hash VARCHAR(255) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	notification_type_id INTEGER REFERENCES notification_types(id),
	is_admin BOOLEAN NOT NULL DEFAULT 0
);

-- 2. Logged-in devices.
CREATE TABLE auth_tokens(
	id INTEGER PRIMARY KEY,
	shop_account_id INTEGER NOT NULL,
	token_hash CHAR(64) UNIQUE NOT NULL,
	user_agent VARCHAR(255),
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	last_used_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	name VARCHAR(60),
	ip VARCHAR(45),
	FOREIGN KEY(shop_account_id) REFERENCES shop_accounts(id) ON DELETE CASCADE
);

CREATE INDEX idx_auth_tokens_shop_account_id ON auth_tokens(shop_account_id);

-- 3. Failed-login lock per IP.
CREATE TABLE login_attempts(
	ip VARCHAR(45) PRIMARY KEY,
	failures INTEGER NOT NULL DEFAULT 0,
	last_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	locked_until DATETIME
);

-- 4. Case/space-insensitive reference uniqueness.
DROP TRIGGER i_product_reference_unique;
DROP TRIGGER u_product_reference_unique;

CREATE TRIGGER i_product_reference_unique
BEFORE INSERT ON products
FOR EACH ROW
WHEN NEW.reference IS NOT NULL AND EXISTS (
	SELECT 1 FROM products WHERE lower(trim(reference)) = lower(trim(NEW.reference))
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um produto com essa referência.');
END;

CREATE TRIGGER u_product_reference_unique
BEFORE UPDATE OF reference ON products
FOR EACH ROW
WHEN NEW.reference IS NOT NULL AND EXISTS (
	SELECT 1 FROM products WHERE lower(trim(reference)) = lower(trim(NEW.reference)) AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um produto com essa referência.');
END;

COMMIT;
