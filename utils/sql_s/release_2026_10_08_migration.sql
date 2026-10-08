-- ============================================================
-- Release 2026-10-08: brings the production DB (as of its schema on
-- 2026-10-08 — September changes already applied) up to date with dev.
--
-- All of the migration files below, in order, in ONE transaction: if any
-- statement fails, nothing is applied. Each section is the matching
-- *_migration.sql file in this folder, with its own BEGIN/COMMIT removed.
--
--   1. service_associations_migration.sql
--   2. service_association_sync_migration.sql
--   3. service_header_sync_migration.sql
--   4. service_header_sync_car_client_migration.sql
--   5. service_header_sync_malfunction_migration.sql
--   6. service_association_finished_notification_migration.sql
--   7. events_migration.sql
--   8. lab_finished_lock_migration.sql
--   9. notification_url_services_migration.sql
--  10. service_description_required_to_finish_migration.sql
--
-- Safe to run more than once.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_10_08_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- ------------------------------------------------------------
-- 1. service_associations_migration.sql
-- ------------------------------------------------------------

-- service_associations: groups services into "clusters" for the new linking
-- feature. service_id is the PRIMARY KEY (not a separate surrogate id) since
-- a service can only ever belong to one cluster; cluster_nr is a plain
-- integer tag (a new cluster = current max + 1, merges keep the lower nr).
--
-- Note: service_id being an INTEGER PRIMARY KEY means SQLite would otherwise
-- silently auto-assign a rowid if NULL is ever inserted for it (the usual
-- surrogate-id auto-increment behavior) -- the not-null trigger below exists
-- specifically to turn that silent footgun into a clear error instead.
--
-- Safe to run more than once (every statement is idempotent).


CREATE TABLE IF NOT EXISTS service_associations(
	service_id INTEGER PRIMARY KEY,
	cluster_nr INTEGER NOT NULL,
	FOREIGN KEY(service_id)
		REFERENCES services(id)
);

CREATE TRIGGER IF NOT EXISTS i_service_association_not_null
BEFORE INSERT ON service_associations
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.service_id IS NULL THEN RAISE(ABORT, 'O serviço é obrigatório.')
		WHEN NEW.cluster_nr IS NULL THEN RAISE(ABORT, 'O número de cluster é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_service_association_not_null
BEFORE UPDATE ON service_associations
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.service_id IS NULL THEN RAISE(ABORT, 'O serviço é obrigatório.')
		WHEN NEW.cluster_nr IS NULL THEN RAISE(ABORT, 'O número de cluster é obrigatório.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_service_association_service_id_fk
BEFORE INSERT ON service_associations
FOR EACH ROW
WHEN NEW.service_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM services WHERE id = NEW.service_id
)
BEGIN
	SELECT RAISE(ABORT, 'O serviço indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_service_association_service_id_fk
BEFORE UPDATE OF service_id ON service_associations
FOR EACH ROW
WHEN NEW.service_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM services WHERE id = NEW.service_id
)
BEGIN
	SELECT RAISE(ABORT, 'O serviço indicado não existe.');
END;



-- ------------------------------------------------------------
-- 2. service_association_sync_migration.sql
-- ------------------------------------------------------------

-- Enforces that every service in a service_associations cluster shares the
-- same car and client, and keeps a handful of shared-context fields
-- (checkin, contact name/phone, estimated checkout, kms, checkout) synced
-- across the whole cluster whenever a service joins one.
--
-- Scope: only fires on INSERT (i.e. when a service is newly added to a
-- cluster, via a brand new pairing or joining an existing one) — moving or
-- merging clusters (UPDATE on service_associations) isn't covered here.
--
-- Safe to run more than once (every statement is idempotent).


-- Blocks adding a service to a cluster whose other members have a
-- different car_id or client_id. NULL is treated as its own distinct
-- value (via IS/IS NOT), so "no car assigned" only matches another
-- service that also has no car assigned.
CREATE TRIGGER IF NOT EXISTS i_service_association_match_car_client
BEFORE INSERT ON service_associations
FOR EACH ROW
WHEN EXISTS (
	SELECT 1
	FROM service_associations sa
	JOIN services s_existing ON s_existing.id = sa.service_id
	WHERE sa.cluster_nr = NEW.cluster_nr
		AND sa.service_id != NEW.service_id
		AND (
			s_existing.car_id IS NOT (SELECT car_id FROM services WHERE id = NEW.service_id)
			OR s_existing.client_id IS NOT (SELECT client_id FROM services WHERE id = NEW.service_id)
		)
)
BEGIN
	SELECT RAISE(ABORT, 'O veículo ou cliente deste serviço não coincide com os restantes serviços da associação.');
END;

-- Once a service is (validly) added to a cluster, copies checkin/contact/
-- estimated-checkout/kms/checkout from the cluster's lowest-id member onto
-- every member of that cluster, so they all end up in sync.
CREATE TRIGGER IF NOT EXISTS i_service_association_sync_fields
AFTER INSERT ON service_associations
FOR EACH ROW
BEGIN
	UPDATE services
	SET
		checkin_date = (SELECT checkin_date FROM services WHERE id = (SELECT MIN(service_id) FROM service_associations WHERE cluster_nr = NEW.cluster_nr)),
		r_name = (SELECT r_name FROM services WHERE id = (SELECT MIN(service_id) FROM service_associations WHERE cluster_nr = NEW.cluster_nr)),
		r_phone = (SELECT r_phone FROM services WHERE id = (SELECT MIN(service_id) FROM service_associations WHERE cluster_nr = NEW.cluster_nr)),
		checkout_predict = (SELECT checkout_predict FROM services WHERE id = (SELECT MIN(service_id) FROM service_associations WHERE cluster_nr = NEW.cluster_nr)),
		kms = (SELECT kms FROM services WHERE id = (SELECT MIN(service_id) FROM service_associations WHERE cluster_nr = NEW.cluster_nr)),
		checkout_date = (SELECT checkout_date FROM services WHERE id = (SELECT MIN(service_id) FROM service_associations WHERE cluster_nr = NEW.cluster_nr))
	WHERE id IN (SELECT service_id FROM service_associations WHERE cluster_nr = NEW.cluster_nr);
END;



-- ------------------------------------------------------------
-- 3. service_header_sync_migration.sql
-- ------------------------------------------------------------

-- Keeps the service-header fields (Nome, Telemóvel, Entrada, Prev. Saída,
-- Kms., Saída, Marcação) synced across every service in the same
-- service_associations cluster whenever one of them is edited — everything
-- the header exposes except service type and the office-validation check,
-- which stay per-service on purpose.
--
-- Safe to run more than once (idempotent).


-- Only touches cluster mates whose values actually differ from NEW's —
-- this isn't just an optimization: it's what keeps this from ping-ponging
-- back and forth if recursive_triggers is ever turned on, since an UPDATE
-- that changes zero rows never re-fires this trigger on those rows.
CREATE TRIGGER IF NOT EXISTS u_service_sync_cluster_header_fields
AFTER UPDATE OF r_name, r_phone, checkin_date, checkout_predict, kms, checkout_date, schedule_id ON services
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM service_associations WHERE service_id = NEW.id)
BEGIN
	UPDATE services
	SET
		r_name = NEW.r_name,
		r_phone = NEW.r_phone,
		checkin_date = NEW.checkin_date,
		checkout_predict = NEW.checkout_predict,
		kms = NEW.kms,
		checkout_date = NEW.checkout_date,
		schedule_id = NEW.schedule_id
	WHERE id IN (
		SELECT service_id FROM service_associations
		WHERE cluster_nr = (SELECT cluster_nr FROM service_associations WHERE service_id = NEW.id)
			AND service_id != NEW.id
	)
	AND (
		r_name IS NOT NEW.r_name
		OR r_phone IS NOT NEW.r_phone
		OR checkin_date IS NOT NEW.checkin_date
		OR checkout_predict IS NOT NEW.checkout_predict
		OR kms IS NOT NEW.kms
		OR checkout_date IS NOT NEW.checkout_date
		OR schedule_id IS NOT NEW.schedule_id
	);
END;



-- ------------------------------------------------------------
-- 4. service_header_sync_car_client_migration.sql
-- ------------------------------------------------------------

-- Extends u_service_sync_cluster_header_fields to also sync car_id and
-- client_id across the cluster when either is edited on an already-
-- clustered service: the i_service_association_match_car_client trigger
-- already guarantees every member starts out sharing the same car and
-- client, so correcting one going forward should correct all of them.
--
-- CREATE TRIGGER IF NOT EXISTS is a no-op against an existing trigger of
-- the same name even when its body has changed, so this drops and
-- recreates it to actually pick up the new columns — still safe to run
-- more than once.


DROP TRIGGER IF EXISTS u_service_sync_cluster_header_fields;

CREATE TRIGGER u_service_sync_cluster_header_fields
AFTER UPDATE OF r_name, r_phone, checkin_date, checkout_predict, kms, checkout_date, schedule_id, car_id, client_id ON services
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM service_associations WHERE service_id = NEW.id)
BEGIN
	UPDATE services
	SET
		r_name = NEW.r_name,
		r_phone = NEW.r_phone,
		checkin_date = NEW.checkin_date,
		checkout_predict = NEW.checkout_predict,
		kms = NEW.kms,
		checkout_date = NEW.checkout_date,
		schedule_id = NEW.schedule_id,
		car_id = NEW.car_id,
		client_id = NEW.client_id
	WHERE id IN (
		SELECT service_id FROM service_associations
		WHERE cluster_nr = (SELECT cluster_nr FROM service_associations WHERE service_id = NEW.id)
			AND service_id != NEW.id
	)
	AND (
		r_name IS NOT NEW.r_name
		OR r_phone IS NOT NEW.r_phone
		OR checkin_date IS NOT NEW.checkin_date
		OR checkout_predict IS NOT NEW.checkout_predict
		OR kms IS NOT NEW.kms
		OR checkout_date IS NOT NEW.checkout_date
		OR schedule_id IS NOT NEW.schedule_id
		OR car_id IS NOT NEW.car_id
		OR client_id IS NOT NEW.client_id
	);
END;



-- ------------------------------------------------------------
-- 5. service_header_sync_malfunction_migration.sql
-- ------------------------------------------------------------

-- Extends u_service_sync_cluster_header_fields to also sync
-- malfunction_description ("Descrição de Avaria") across the cluster.
--
-- CREATE TRIGGER IF NOT EXISTS is a no-op against an existing trigger of
-- the same name even when its body has changed, so this drops and
-- recreates it to actually pick up the new column — still safe to run
-- more than once.


DROP TRIGGER IF EXISTS u_service_sync_cluster_header_fields;

CREATE TRIGGER u_service_sync_cluster_header_fields
AFTER UPDATE OF r_name, r_phone, checkin_date, checkout_predict, kms, checkout_date, schedule_id, car_id, client_id, malfunction_description ON services
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM service_associations WHERE service_id = NEW.id)
BEGIN
	UPDATE services
	SET
		r_name = NEW.r_name,
		r_phone = NEW.r_phone,
		checkin_date = NEW.checkin_date,
		checkout_predict = NEW.checkout_predict,
		kms = NEW.kms,
		checkout_date = NEW.checkout_date,
		schedule_id = NEW.schedule_id,
		car_id = NEW.car_id,
		client_id = NEW.client_id,
		malfunction_description = NEW.malfunction_description
	WHERE id IN (
		SELECT service_id FROM service_associations
		WHERE cluster_nr = (SELECT cluster_nr FROM service_associations WHERE service_id = NEW.id)
			AND service_id != NEW.id
	)
	AND (
		r_name IS NOT NEW.r_name
		OR r_phone IS NOT NEW.r_phone
		OR checkin_date IS NOT NEW.checkin_date
		OR checkout_predict IS NOT NEW.checkout_predict
		OR kms IS NOT NEW.kms
		OR checkout_date IS NOT NEW.checkout_date
		OR schedule_id IS NOT NEW.schedule_id
		OR car_id IS NOT NEW.car_id
		OR client_id IS NOT NEW.client_id
		OR malfunction_description IS NOT NEW.malfunction_description
	);
END;



-- ------------------------------------------------------------
-- 6. service_association_finished_notification_migration.sql
-- ------------------------------------------------------------

-- Notifies the office when the LAST unfinished service in an association
-- gets finished, making the whole association finished. Separate from the
-- existing per-service service_finished_notification trigger, which fires
-- for every service regardless of association membership.
--
-- Fires on the same event (AFTER UPDATE OF is_finished, 0 -> 1) but only
-- when the service belongs to an association AND, after this update, no
-- member of that same cluster is still unfinished.
--
-- Dropped and recreated (not CREATE ... IF NOT EXISTS) so running this
-- again also replaces an earlier version of the trigger — the first one
-- linked to 'service/<id>' instead of 'services/<id>'. Safe to run more
-- than once.


DROP TRIGGER IF EXISTS service_association_finished_notification;

CREATE TRIGGER service_association_finished_notification
AFTER UPDATE OF is_finished ON services
FOR EACH ROW
WHEN NEW.is_finished = 1 AND OLD.is_finished = 0
AND EXISTS (
	SELECT 1 FROM service_associations WHERE service_id = NEW.id
)
AND NOT EXISTS (
	SELECT 1
	FROM service_associations sa
	JOIN services s ON s.id = sa.service_id
	WHERE sa.cluster_nr = (SELECT cluster_nr FROM service_associations WHERE service_id = NEW.id)
		AND s.is_finished = 0
)
BEGIN
	INSERT INTO notifications (
		notification_type_id,
		title,
		message,
		data
	)
	VALUES (
		(SELECT id FROM notification_types WHERE name = 'Escritório'),
		'Associação de serviços finalizada',
		'Todos os ' ||
		(
			SELECT COUNT(*) FROM service_associations
			WHERE cluster_nr = (SELECT cluster_nr FROM service_associations WHERE service_id = NEW.id)
		) ||
		' serviços da associação #' ||
		(SELECT cluster_nr FROM service_associations WHERE service_id = NEW.id) ||
		' foram finalizados (última folha: ' || NEW.id || ')' ||
		CASE WHEN COALESCE(
			(
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM cars c
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE c.id = NEW.car_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM cars c
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE c.id = NEW.car_id
			)
		ELSE ''
		END ||
		'.',
		json_object(
			'url', 'services/' || NEW.id
		)
	);
END;



-- ------------------------------------------------------------
-- 7. events_migration.sql
-- ------------------------------------------------------------

-- Custom calendar entries (holidays, vacations, shop closures, etc.),
-- unrelated to services/schedules. A null start_time/end_time pair means
-- the event is an all-day entry rather than a specific time window.


CREATE TABLE IF NOT EXISTS events(
	id INTEGER PRIMARY KEY,
	title VARCHAR(120) NOT NULL,
	description VARCHAR(1024),
	start_date VARCHAR(20) NOT NULL CHECK(
		date(start_date) IS NOT NULL
	),
	start_time VARCHAR(10) CHECK(
		start_time IS NULL OR time(start_time) IS NOT NULL
	),
	end_date VARCHAR(20) NOT NULL CHECK(
		date(end_date) IS NOT NULL
	),
	end_time VARCHAR(10) CHECK(
		end_time IS NULL OR time(end_time) IS NOT NULL
	),
	color VARCHAR(20),
	user_id INTEGER,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

	FOREIGN KEY(user_id)
		REFERENCES users(id)
		ON DELETE SET NULL
);

CREATE TRIGGER IF NOT EXISTS i_event_not_null
BEFORE INSERT ON events
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.title IS NULL THEN RAISE(ABORT, 'O título do evento é obrigatório.')
		WHEN NEW.start_date IS NULL THEN RAISE(ABORT, 'A data de início é obrigatória.')
		WHEN NEW.end_date IS NULL THEN RAISE(ABORT, 'A data de fim é obrigatória.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_event_not_null
BEFORE UPDATE ON events
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.title IS NULL THEN RAISE(ABORT, 'O título do evento é obrigatório.')
		WHEN NEW.start_date IS NULL THEN RAISE(ABORT, 'A data de início é obrigatória.')
		WHEN NEW.end_date IS NULL THEN RAISE(ABORT, 'A data de fim é obrigatória.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_event_dates_valid
BEFORE INSERT ON events
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.start_date IS NOT NULL AND date(NEW.start_date) IS NULL
			THEN RAISE(ABORT, 'A data de início é inválida.')
		WHEN NEW.end_date IS NOT NULL AND date(NEW.end_date) IS NULL
			THEN RAISE(ABORT, 'A data de fim é inválida.')
		WHEN NEW.start_time IS NOT NULL AND time(NEW.start_time) IS NULL
			THEN RAISE(ABORT, 'A hora de início é inválida.')
		WHEN NEW.end_time IS NOT NULL AND time(NEW.end_time) IS NULL
			THEN RAISE(ABORT, 'A hora de fim é inválida.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS u_event_dates_valid
BEFORE UPDATE OF start_date, end_date, start_time, end_time ON events
FOR EACH ROW
BEGIN
	SELECT CASE
		WHEN NEW.start_date IS NOT NULL AND date(NEW.start_date) IS NULL
			THEN RAISE(ABORT, 'A data de início é inválida.')
		WHEN NEW.end_date IS NOT NULL AND date(NEW.end_date) IS NULL
			THEN RAISE(ABORT, 'A data de fim é inválida.')
		WHEN NEW.start_time IS NOT NULL AND time(NEW.start_time) IS NULL
			THEN RAISE(ABORT, 'A hora de início é inválida.')
		WHEN NEW.end_time IS NOT NULL AND time(NEW.end_time) IS NULL
			THEN RAISE(ABORT, 'A hora de fim é inválida.')
	END;
END;

CREATE TRIGGER IF NOT EXISTS i_event_date_range_valid
BEFORE INSERT ON events
FOR EACH ROW
WHEN NEW.start_date IS NOT NULL AND NEW.end_date IS NOT NULL
AND date(NEW.end_date) < date(NEW.start_date)
BEGIN
	SELECT RAISE(ABORT, 'A data de fim não pode ser anterior à data de início.');
END;

CREATE TRIGGER IF NOT EXISTS u_event_date_range_valid
BEFORE UPDATE OF start_date, end_date ON events
FOR EACH ROW
WHEN NEW.start_date IS NOT NULL AND NEW.end_date IS NOT NULL
AND date(NEW.end_date) < date(NEW.start_date)
BEGIN
	SELECT RAISE(ABORT, 'A data de fim não pode ser anterior à data de início.');
END;

-- Either both times are set (a timed event) or neither is (an all-day
-- event) — matches how services_user_time_punches pairs hours/minutes.
CREATE TRIGGER IF NOT EXISTS i_event_time_pair_complete
BEFORE INSERT ON events
FOR EACH ROW
WHEN (NEW.start_time IS NULL) != (NEW.end_time IS NULL)
BEGIN
	SELECT RAISE(ABORT, 'A hora de início e a hora de fim têm de ser preenchidas em conjunto.');
END;

CREATE TRIGGER IF NOT EXISTS u_event_time_pair_complete
BEFORE UPDATE OF start_time, end_time ON events
FOR EACH ROW
WHEN (NEW.start_time IS NULL) != (NEW.end_time IS NULL)
BEGIN
	SELECT RAISE(ABORT, 'A hora de início e a hora de fim têm de ser preenchidas em conjunto.');
END;

-- Time ordering only makes sense to check when both ends land on the same
-- calendar day — an overnight event (different start/end dates) is free to
-- have, say, start_time 22:00 and end_time 06:00.
CREATE TRIGGER IF NOT EXISTS i_event_time_order_valid
BEFORE INSERT ON events
FOR EACH ROW
WHEN NEW.start_time IS NOT NULL AND NEW.end_time IS NOT NULL
AND NEW.start_date = NEW.end_date
AND time(NEW.end_time) <= time(NEW.start_time)
BEGIN
	SELECT RAISE(ABORT, 'A hora de fim tem de ser depois da hora de início no mesmo dia.');
END;

CREATE TRIGGER IF NOT EXISTS u_event_time_order_valid
BEFORE UPDATE OF start_date, end_date, start_time, end_time ON events
FOR EACH ROW
WHEN NEW.start_time IS NOT NULL AND NEW.end_time IS NOT NULL
AND NEW.start_date = NEW.end_date
AND time(NEW.end_time) <= time(NEW.start_time)
BEGIN
	SELECT RAISE(ABORT, 'A hora de fim tem de ser depois da hora de início no mesmo dia.');
END;

CREATE TRIGGER IF NOT EXISTS i_event_user_id_fk
BEFORE INSERT ON events
FOR EACH ROW
WHEN NEW.user_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM users WHERE id = NEW.user_id
)
BEGIN
	SELECT RAISE(ABORT, 'O utilizador indicado não existe.');
END;

CREATE TRIGGER IF NOT EXISTS u_event_user_id_fk
BEFORE UPDATE OF user_id ON events
FOR EACH ROW
WHEN NEW.user_id IS NOT NULL AND NOT EXISTS (
	SELECT 1 FROM users WHERE id = NEW.user_id
)
BEGIN
	SELECT RAISE(ABORT, 'O utilizador indicado não existe.');
END;



-- ------------------------------------------------------------
-- 8. lab_finished_lock_migration.sql
-- ------------------------------------------------------------

-- Blocks all lab management writes (items, action values, property values)
-- once the owning service is finished (services.is_finished = 1).
-- lab_action_values and lab_property_values don't carry a service_id of
-- their own, so they're resolved through their lab_items.service_id.
-- Safe to run more than once: every trigger is CREATE ... IF NOT EXISTS.


CREATE TRIGGER IF NOT EXISTS i_lab_item_service_finished
BEFORE INSERT ON lab_items
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM services WHERE id = NEW.service_id AND is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_item_service_finished
BEFORE UPDATE ON lab_items
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM services WHERE id = OLD.service_id AND is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;

CREATE TRIGGER IF NOT EXISTS d_lab_item_service_finished
BEFORE DELETE ON lab_items
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM services WHERE id = OLD.service_id AND is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;


CREATE TRIGGER IF NOT EXISTS i_lab_action_value_service_finished
BEFORE INSERT ON lab_action_values
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM lab_items li
	JOIN services s ON s.id = li.service_id
	WHERE li.id = NEW.l_item_id AND s.is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_action_value_service_finished
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

CREATE TRIGGER IF NOT EXISTS d_lab_action_value_service_finished
BEFORE DELETE ON lab_action_values
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM lab_items li
	JOIN services s ON s.id = li.service_id
	WHERE li.id = OLD.l_item_id AND s.is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;


CREATE TRIGGER IF NOT EXISTS i_lab_property_value_service_finished
BEFORE INSERT ON lab_property_values
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM lab_items li
	JOIN services s ON s.id = li.service_id
	WHERE li.id = NEW.l_item_id AND s.is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;

CREATE TRIGGER IF NOT EXISTS u_lab_property_value_service_finished
BEFORE UPDATE ON lab_property_values
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM lab_items li
	JOIN services s ON s.id = li.service_id
	WHERE li.id = OLD.l_item_id AND s.is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;

CREATE TRIGGER IF NOT EXISTS d_lab_property_value_service_finished
BEFORE DELETE ON lab_property_values
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM lab_items li
	JOIN services s ON s.id = li.service_id
	WHERE li.id = OLD.l_item_id AND s.is_finished = 1
)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível alterar o laboratório de um serviço finalizado.');
END;



-- ------------------------------------------------------------
-- 9. notification_url_services_migration.sql
-- ------------------------------------------------------------

-- Notification links: the frontend route is /services/:id, but these
-- notification triggers stored data.url as 'service/<id>' (singular), so
-- clicking one of those notifications 404'd. Recreates every affected
-- trigger with 'services/' || id and rewrites the existing notification
-- rows to match (the frontend now only understands 'services/<id>').
--
-- service_association_finished_notification is not here: its own
-- migration (service_association_finished_notification_migration.sql)
-- already creates it with 'services/'.
--
-- Safe to run more than once: each trigger is dropped (IF EXISTS) and
-- recreated, and the UPDATE only touches rows still on 'service/'.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 <path-to-db> < notification_url_services_migration.sql


DROP TRIGGER IF EXISTS service_finished_notification;
CREATE TRIGGER service_finished_notification
AFTER UPDATE OF is_finished ON services
FOR EACH ROW
WHEN NEW.is_finished = 1 AND OLD.is_finished = 0
BEGIN
    INSERT INTO notifications (
        notification_type_id,
        title,
        message,
        data
    )
    VALUES (
        (SELECT id FROM notification_types WHERE name = 'Escritório'),
        'Folha de serviço finalizada',
        'Folha de serviço ' || NEW.id || ' - ' ||
        COALESCE(
            (
                SELECT
                    CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
                    ||
                    CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
                    ||
                    CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
                FROM cars c
                LEFT JOIN makes mk ON mk.id = c.make_id
                LEFT JOIN models m ON m.id = c.model_id
                WHERE c.id = NEW.car_id
            ),
            ''
        ) ||
        ' finalizada.',
        json_object(
            'url', 'services/' || NEW.id
        )
    );
END;

DROP TRIGGER IF EXISTS product_requested_notification;
CREATE TRIGGER product_requested_notification
AFTER INSERT ON services_products_requested
FOR EACH ROW
BEGIN
	INSERT INTO notifications (
		notification_type_id,
		title,
		message,
		data
	)
	VALUES (
		(SELECT id FROM notification_types WHERE name = 'Escritório'),
		'Produto pedido',
		'Produto ' ||
		COALESCE((SELECT name FROM products WHERE id = NEW.product_id), '') ||
		' pedido para a folha de serviço ' || NEW.service_id ||
		CASE WHEN COALESCE(
			(
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM services s
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE s.id = NEW.service_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM services s
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE s.id = NEW.service_id
			)
		ELSE ''
		END ||
		'.',
		json_object(
			'url', 'services/' || NEW.service_id
		)
	);
END;

DROP TRIGGER IF EXISTS product_delivered_notification;
CREATE TRIGGER product_delivered_notification
AFTER UPDATE OF is_delivered ON services_products_requested
FOR EACH ROW
WHEN NEW.is_delivered = 1 AND OLD.is_delivered = 0
BEGIN
	INSERT INTO notifications (
		notification_type_id,
		title,
		message,
		data
	)
	VALUES (
		(SELECT id FROM notification_types WHERE name = 'Oficina'),
		'Produto entregue',
		'Produto ' ||
		COALESCE((SELECT name FROM products WHERE id = NEW.product_id), '') ||
		' entregue para a folha de serviço ' || NEW.service_id ||
		CASE WHEN COALESCE(
			(
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM services s
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE s.id = NEW.service_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM services s
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE s.id = NEW.service_id
			)
		ELSE ''
		END ||
		'.' ||
		CASE WHEN NOT EXISTS (
			SELECT 1 FROM services_products_requested
			WHERE service_id = NEW.service_id AND is_delivered = 0
		) THEN ' Todos os produtos pedidos para esta folha de serviço foram entregues.'
		ELSE ''
		END,
		json_object(
			'url', 'services/' || NEW.service_id
		)
	);
END;

DROP TRIGGER IF EXISTS service_same_car_open_notification;
CREATE TRIGGER service_same_car_open_notification
AFTER INSERT ON services
FOR EACH ROW
WHEN NEW.car_id IS NOT NULL AND EXISTS (
	SELECT 1 FROM services
	WHERE car_id = NEW.car_id AND id != NEW.id AND is_finished = 0
)
BEGIN
	INSERT INTO notifications (
		notification_type_id,
		title,
		message,
		data
	)
	VALUES (
		(SELECT id FROM notification_types WHERE name = 'Escritório'),
		'Folha de serviço aberta para o mesmo carro',
		'A folha de serviço ' || NEW.id ||
		' foi criada, mas já existe uma folha de serviço aberta (#' ||
		(
			SELECT MIN(id) FROM services
			WHERE car_id = NEW.car_id AND id != NEW.id AND is_finished = 0
		) ||
		') para o mesmo carro' ||
		CASE WHEN COALESCE(
			(
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM cars c
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE c.id = NEW.car_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM cars c
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE c.id = NEW.car_id
			)
		ELSE ''
		END ||
		'.',
		json_object(
			'url', 'services/' || NEW.id
		)
	);
END;

DROP TRIGGER IF EXISTS service_user_time_first_notification;
CREATE TRIGGER service_user_time_first_notification
AFTER INSERT ON services_user_time
FOR EACH ROW
WHEN NOT EXISTS (
	SELECT 1 FROM services_user_time
	WHERE service_id = NEW.service_id AND id != NEW.id
)
AND NOT EXISTS (
	SELECT 1 FROM services_user_time_punches
	WHERE service_id = NEW.service_id
)
BEGIN
	INSERT INTO notifications (
		notification_type_id,
		title,
		message,
		data
	)
	VALUES (
		(SELECT id FROM notification_types WHERE name = 'Escritório'),
		'Serviço iniciado',
		'O serviço ' || NEW.service_id || ' foi iniciado por ' ||
		COALESCE((SELECT name FROM users WHERE id = NEW.user_id), '') ||
		CASE WHEN COALESCE(
			(
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM services s
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE s.id = NEW.service_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM services s
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE s.id = NEW.service_id
			)
		ELSE ''
		END ||
		'.',
		json_object(
			'url', 'services/' || NEW.service_id
		)
	);
END;

DROP TRIGGER IF EXISTS service_user_time_punch_first_notification;
CREATE TRIGGER service_user_time_punch_first_notification
AFTER INSERT ON services_user_time_punches
FOR EACH ROW
WHEN NOT EXISTS (
	SELECT 1 FROM services_user_time_punches
	WHERE service_id = NEW.service_id AND id != NEW.id
)
AND NOT EXISTS (
	SELECT 1 FROM services_user_time
	WHERE service_id = NEW.service_id
)
BEGIN
	INSERT INTO notifications (
		notification_type_id,
		title,
		message,
		data
	)
	VALUES (
		(SELECT id FROM notification_types WHERE name = 'Escritório'),
		'Serviço iniciado',
		'O serviço ' || NEW.service_id || ' foi iniciado por ' ||
		COALESCE((SELECT name FROM users WHERE id = NEW.user_id), '') ||
		CASE WHEN COALESCE(
			(
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM services s
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE s.id = NEW.service_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END
				FROM services s
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE s.id = NEW.service_id
			)
		ELSE ''
		END ||
		'.',
		json_object(
			'url', 'services/' || NEW.service_id
		)
	);
END;

-- Existing rows. json_valid() guard: a malformed data value would make
-- json_extract() abort the whole UPDATE.
UPDATE notifications
SET data = json_set(data, '$.url', 'services/' || substr(json_extract(data, '$.url'), 9))
WHERE json_valid(data)
	AND json_extract(data, '$.url') LIKE 'service/%';



-- ------------------------------------------------------------
-- 10. service_description_required_to_finish_migration.sql
-- ------------------------------------------------------------

-- A service can't be finished (is_finished = 1) while "Serviço Realizado"
-- (service_description) is empty or only whitespace — same idea as the
-- kms-required-to-finish triggers.
--
-- The UPDATE trigger only fires when the service is being finished, or when
-- the description is being changed on an already-finished one: the API's
-- PUT sets every column on each save, so without the OLD checks any save of
-- a service that was already finished without a description (there are
-- some) would be blocked too.
--
-- Safe to run more than once: every trigger is CREATE ... IF NOT EXISTS.
-- Back up the DB file before running this.
--
-- Run with: sqlite3 <path-to-db> < service_description_required_to_finish_migration.sql

CREATE TRIGGER IF NOT EXISTS i_service_description_required_to_finish
BEFORE INSERT ON services
FOR EACH ROW
WHEN NEW.is_finished = 1
	AND TRIM(COALESCE(NEW.service_description, '')) = ''
BEGIN
	SELECT RAISE(ABORT, 'Necessita preencher o serviço realizado para terminar o serviço.');
END;

CREATE TRIGGER IF NOT EXISTS u_service_description_required_to_finish
BEFORE UPDATE OF is_finished, service_description ON services
FOR EACH ROW
WHEN NEW.is_finished = 1
	AND TRIM(COALESCE(NEW.service_description, '')) = ''
	AND (OLD.is_finished != 1 OR OLD.service_description IS NOT NEW.service_description)
BEGIN
	SELECT RAISE(ABORT, 'Necessita preencher o serviço realizado para terminar o serviço.');
END;



COMMIT;
