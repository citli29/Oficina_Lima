-- ============================================================
-- Release 2026-10-08 (b): indexes + notification cleanup.
-- Run AFTER release_2026_10_08_migration.sql (needs service_associations
-- and events).
--
--  1. Indexes on the columns the app (and the triggers) look rows up by.
--     Without them each of those lookups reads the whole table — fine at
--     today's size, slow after a few years of services, notifications and
--     time entries. They don't change any behaviour, only speed.
--  2. Notifications older than 30 days are deleted: once now, and from
--     then on automatically whenever a new notification is created (so the
--     table the app polls every 5 s stops growing forever). This deletes
--     them whether they were read or not.
--
-- One transaction: if anything fails, nothing is applied.
-- Safe to run more than once.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_10_08b_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- ------------------------------------------------------------
-- 1. Indexes
-- ------------------------------------------------------------

-- services: client/car/schedule lookups (lists, history, triggers) and
-- the date filters/sort of the services list and calendar.
CREATE INDEX IF NOT EXISTS idx_services_client_id ON services(client_id);
CREATE INDEX IF NOT EXISTS idx_services_car_id ON services(car_id);
CREATE INDEX IF NOT EXISTS idx_services_schedule_id ON services(schedule_id);
CREATE INDEX IF NOT EXISTS idx_services_checkin_date ON services(checkin_date);

-- schedules: calendar/list date range, and car/client lookups.
CREATE INDEX IF NOT EXISTS idx_schedules_date ON schedules(date);
CREATE INDEX IF NOT EXISTS idx_schedules_car_id ON schedules(car_id);
CREATE INDEX IF NOT EXISTS idx_schedules_client_id ON schedules(client_id);

-- service_associations: every "other services in this association" lookup.
CREATE INDEX IF NOT EXISTS idx_service_associations_cluster_nr ON service_associations(cluster_nr);

-- Per-service tables: loaded by service_id on every service page, and
-- searched by service_id in the notification triggers.
CREATE INDEX IF NOT EXISTS idx_spr_service_id ON services_products_requested(service_id);
CREATE INDEX IF NOT EXISTS idx_spr_product_id ON services_products_requested(product_id);
CREATE INDEX IF NOT EXISTS idx_sap_service_id ON services_applied_products(service_id);
CREATE INDEX IF NOT EXISTS idx_sap_product_id ON services_applied_products(product_id);
CREATE INDEX IF NOT EXISTS idx_sut_service_id ON services_user_time(service_id);
CREATE INDEX IF NOT EXISTS idx_sut_ut_date ON services_user_time(ut_date);
CREATE INDEX IF NOT EXISTS idx_sutp_service_id ON services_user_time_punches(service_id);
CREATE INDEX IF NOT EXISTS idx_sutp_ut_date ON services_user_time_punches(ut_date);
CREATE INDEX IF NOT EXISTS idx_lab_items_service_id ON lab_items(service_id);
CREATE INDEX IF NOT EXISTS idx_lab_action_values_l_item_id ON lab_action_values(l_item_id);

-- events: the calendars ask for events overlapping a date range.
CREATE INDEX IF NOT EXISTS idx_events_start_end ON events(start_date, end_date);

-- notifications: the 5 s unread poll (is_checked + type, newest first),
-- and the 30-day cleanup below (created_at).
CREATE INDEX IF NOT EXISTS idx_notifications_unread ON notifications(is_checked, notification_type_id, created_at);
CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at);

-- ------------------------------------------------------------
-- 2. Notifications older than 30 days
-- ------------------------------------------------------------

-- created_at is CURRENT_TIMESTAMP (UTC), same clock as datetime('now').
DELETE FROM notifications WHERE created_at < datetime('now', '-30 days');

CREATE TRIGGER IF NOT EXISTS notifications_delete_older_than_30_days
AFTER INSERT ON notifications
FOR EACH ROW
BEGIN
	DELETE FROM notifications WHERE created_at < datetime('now', '-30 days');
END;

COMMIT;
