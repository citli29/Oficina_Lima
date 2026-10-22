-- ============================================================
-- Release 2026-10-22: marcação type + lab items planned on a marcação.
-- Run on a DB that already has release_2026_10_21_migration.sql.
--
--  1. A marcação can have a service type (optional). The marcações list
--     and calendar show it until the marcação has a service.
--  2. Lab items and actions can be planned on a Laboratório marcação.
--     "Criar Serviço" copies them into the new service; the marcação
--     keeps its own copy. No notes, done check, properties or locks.
--     Deleting the marcação, one of its items, or the item type / action
--     in Gestão de Laboratório deletes them too (ON DELETE CASCADE —
--     the API turns PRAGMA foreign_keys on).
--
-- The description of a Laboratório marcação whose items all have actions
-- can be left empty. That rule lives in the backend (ScheduleService /
-- ScheduleLabService), not in this file: an empty description is stored
-- as '' and the existing NULL check is unchanged.
--
-- The backend must go up together with this file: the API reads and
-- writes schedules.service_type_id and the two new tables.
--
-- One transaction: if anything fails, nothing is applied. Running it a
-- second time fails at step 1 (the column already exists) and changes
-- nothing.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_10_22_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- ------------------------------------------------------------
-- 1. Marcação type (optional)
-- ------------------------------------------------------------

ALTER TABLE schedules ADD COLUMN service_type_id INTEGER NULL REFERENCES service_types(id) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- 2. Lab items/actions planned on a marcação
-- ------------------------------------------------------------

CREATE TABLE schedule_lab_items(
	id INTEGER PRIMARY KEY,
	schedule_id INTEGER NOT NULL,
	t_item_id INTEGER NOT NULL,
	FOREIGN KEY(schedule_id)
		REFERENCES schedules(id)
		ON DELETE CASCADE,
	FOREIGN KEY(t_item_id)
		REFERENCES tabled_items(id)
		ON DELETE CASCADE
);

CREATE TABLE schedule_lab_action_values(
	id INTEGER PRIMARY KEY,
	l_item_id INTEGER NOT NULL,
	t_action_id INTEGER NOT NULL,
	FOREIGN KEY(l_item_id)
		REFERENCES schedule_lab_items(id)
		ON DELETE CASCADE,
	FOREIGN KEY(t_action_id)
		REFERENCES tabled_actions(id)
		ON DELETE CASCADE
);

CREATE INDEX idx_schedule_lab_items_schedule_id ON schedule_lab_items(schedule_id);
CREATE INDEX idx_schedule_lab_action_values_l_item_id ON schedule_lab_action_values(l_item_id);

COMMIT;
