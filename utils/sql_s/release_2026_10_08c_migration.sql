-- ============================================================
-- Release 2026-10-08 (c). Run AFTER release_2026_10_08_migration.sql
-- and release_2026_10_08b_migration.sql.
--
--  1. Duplicate names are checked ignoring upper/lower case and spaces
--     around the name ("opel" / "Opel " count as "Opel"): marcas, modelos
--     (per marca), tipos de produto, produtos without reference.
--     Existing duplicates are left as they are; only new ones are refused.
--  2. A punch (Ponto) can't be dated outside the service's Entrada–Saída,
--     the same rule time entries already have. Checked when the date or
--     service changes, so older punches can still have their times fixed.
--  3. Notification messages lose the stray spaces ("Megane . Todos",
--     "ZZMarca  finalizada", "Folha de serviço 30 -  finalizada."). Old
--     notification rows are tidied the same way.
--  4. Deleting something still in use gives a clear Portuguese message
--     instead of "FOREIGN KEY constraint failed":
--     clientes, produtos, marcas, serviços, utilizadores, itens/ações/
--     propriedades do laboratório.
--     New rule: a viatura (or a modelo) used by a serviço / viatura can't
--     be deleted at all. Before, the delete went through and the services
--     (or cars) silently lost their viatura (modelo).
--
--  5. Plates are the same with or without dashes/spaces, for search and
--     for the duplicate check ("13 13 SR" = "13-13-SR").
--  6. A time entry can't be more than 1440 minutes (24 hours).
--
-- One transaction: if anything fails, nothing is applied.
-- Safe to run more than once. Back up the DB file before running this.
--
-- Run with: sqlite3 -bail <path-to-db> < release_2026_10_08c_migration.sql
-- ============================================================

BEGIN TRANSACTION;

-- ------------------------------------------------------------
-- 1. Duplicate names ignoring case
-- ------------------------------------------------------------

DROP TRIGGER IF EXISTS i_make_name_unique;
CREATE TRIGGER i_make_name_unique
BEFORE INSERT ON makes
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM makes WHERE lower(trim(name)) = lower(trim(NEW.name))
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe uma marca com esse nome.');
END;

DROP TRIGGER IF EXISTS u_make_name_unique;
CREATE TRIGGER u_make_name_unique
BEFORE UPDATE OF name ON makes
FOR EACH ROW
WHEN NEW.name IS NOT OLD.name AND EXISTS (
	SELECT 1 FROM makes WHERE lower(trim(name)) = lower(trim(NEW.name)) AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe uma marca com esse nome.');
END;

DROP TRIGGER IF EXISTS i_model_name_unique_per_make;
CREATE TRIGGER i_model_name_unique_per_make
BEFORE INSERT ON models
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM models WHERE lower(trim(name)) = lower(trim(NEW.name)) AND make_id = NEW.make_id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um modelo com esse nome para essa marca.');
END;

DROP TRIGGER IF EXISTS u_model_name_unique_per_make;
CREATE TRIGGER u_model_name_unique_per_make
BEFORE UPDATE OF name, make_id ON models
FOR EACH ROW
WHEN (NEW.name IS NOT OLD.name OR NEW.make_id IS NOT OLD.make_id) AND EXISTS (
	SELECT 1 FROM models WHERE lower(trim(name)) = lower(trim(NEW.name)) AND make_id = NEW.make_id AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um modelo com esse nome para essa marca.');
END;

DROP TRIGGER IF EXISTS i_product_type_name_unique;
CREATE TRIGGER i_product_type_name_unique
BEFORE INSERT ON product_types
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM product_types WHERE lower(trim(name)) = lower(trim(NEW.name))
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um tipo de produto com esse nome.');
END;

DROP TRIGGER IF EXISTS u_product_type_name_unique;
CREATE TRIGGER u_product_type_name_unique
BEFORE UPDATE OF name ON product_types
FOR EACH ROW
WHEN NEW.name IS NOT OLD.name AND EXISTS (
	SELECT 1 FROM product_types WHERE lower(trim(name)) = lower(trim(NEW.name)) AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um tipo de produto com esse nome.');
END;

DROP TRIGGER IF EXISTS i_product_name_unique_when_no_reference;
CREATE TRIGGER i_product_name_unique_when_no_reference
BEFORE INSERT ON products
FOR EACH ROW
WHEN NEW.reference IS NULL AND EXISTS (
	SELECT 1 FROM products WHERE lower(trim(name)) = lower(trim(NEW.name)) AND reference IS NULL
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um produto com esse nome sem referência associada.');
END;

DROP TRIGGER IF EXISTS u_product_name_unique_when_no_reference;
CREATE TRIGGER u_product_name_unique_when_no_reference
BEFORE UPDATE OF name, reference ON products
FOR EACH ROW
WHEN (NEW.name IS NOT OLD.name OR NEW.reference IS NOT OLD.reference) AND NEW.reference IS NULL AND EXISTS (
	SELECT 1 FROM products WHERE lower(trim(name)) = lower(trim(NEW.name)) AND reference IS NULL AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe um produto com esse nome sem referência associada.');
END;


-- ------------------------------------------------------------
-- 2. Punch dates inside the service dates
-- ------------------------------------------------------------

DROP TRIGGER IF EXISTS i_service_user_time_punch_date_range;
CREATE TRIGGER i_service_user_time_punch_date_range
BEFORE INSERT ON services_user_time_punches
FOR EACH ROW
WHEN NEW.ut_date IS NOT NULL
	AND date(NEW.ut_date) IS NOT NULL
	AND EXISTS (
		SELECT 1 FROM services s
		WHERE s.id = NEW.service_id
			AND NOT (
				NEW.ut_date >= s.checkin_date
				AND NEW.ut_date <= s.checkout_date
			)
	)
BEGIN
	SELECT RAISE(ABORT, 'Data do Ponto fora da data do Serviço.');
END;

DROP TRIGGER IF EXISTS u_service_user_time_punch_date_range;
CREATE TRIGGER u_service_user_time_punch_date_range
BEFORE UPDATE OF ut_date, service_id ON services_user_time_punches
FOR EACH ROW
WHEN (NEW.ut_date IS NOT OLD.ut_date OR NEW.service_id IS NOT OLD.service_id)
	AND NEW.ut_date IS NOT NULL
	AND date(NEW.ut_date) IS NOT NULL
	AND EXISTS (
		SELECT 1 FROM services s
		WHERE s.id = NEW.service_id
			AND NOT (
				NEW.ut_date >= s.checkin_date
				AND NEW.ut_date <= s.checkout_date
			)
	)
BEGIN
	SELECT RAISE(ABORT, 'Data do Ponto fora da data do Serviço.');
END;


-- ------------------------------------------------------------
-- 3. Notification messages without stray spaces
-- ------------------------------------------------------------
-- The car text ("plate make model ") ended in a space; it is now trimmed.


DROP TRIGGER IF EXISTS schedule_same_car_same_day_notification;
CREATE TRIGGER schedule_same_car_same_day_notification
AFTER INSERT ON schedules
FOR EACH ROW
WHEN NEW.car_id IS NOT NULL AND EXISTS (
	SELECT 1 FROM schedules
	WHERE car_id = NEW.car_id AND id != NEW.id AND date = NEW.date
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
		'Marcação duplicada para o mesmo carro',
		'A marcação ' || NEW.id ||
		' foi criada, mas já existe outra marcação (#' ||
		(
			SELECT MIN(id) FROM schedules
			WHERE car_id = NEW.car_id AND id != NEW.id AND date = NEW.date
		) ||
		') no mesmo dia (' || NEW.date || ') para o mesmo carro' ||
		CASE WHEN COALESCE(
			(
				SELECT
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
				FROM cars c
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE c.id = NEW.car_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
				FROM cars c
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE c.id = NEW.car_id
			)
		ELSE ''
		END ||
		'.',
		json_object(
			'url', 'schedules/' || NEW.id
		)
	);
END;

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
        'Folha de serviço ' || NEW.id ||
        COALESCE(
            ' - ' || (
                SELECT
                    RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
                    ||
                    CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
                    ||
                    CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
				FROM cars c
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE c.id = NEW.car_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
				FROM cars c
				LEFT JOIN makes mk ON mk.id = c.make_id
				LEFT JOIN models m ON m.id = c.model_id
				WHERE c.id = NEW.car_id
			),
			''
		) != '' THEN
			' - ' || (
				SELECT
					RTRIM(CASE WHEN c.plate IS NOT NULL THEN c.plate || ' ' ELSE '' END
					||
					CASE WHEN mk.name IS NOT NULL THEN mk.name || ' ' ELSE '' END
					||
					CASE WHEN m.name IS NOT NULL THEN m.name || ' ' ELSE '' END)
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


-- Old rows: double spaces → one, and no space before the final period.
UPDATE notifications
SET message = REPLACE(REPLACE(REPLACE(message, '  ', ' '), '  ', ' '), ' .', '.')
WHERE message LIKE '%  %' OR message LIKE '% .%';

-- ------------------------------------------------------------
-- 4. Deleting something in use
-- ------------------------------------------------------------

DROP TRIGGER IF EXISTS d_client_in_use;
CREATE TRIGGER d_client_in_use
BEFORE DELETE ON clients
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM services WHERE client_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o cliente porque está associado a serviços.');
END;

DROP TRIGGER IF EXISTS d_car_in_use;
CREATE TRIGGER d_car_in_use
BEFORE DELETE ON cars
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM services WHERE car_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar a viatura porque está associada a serviços.');
END;

DROP TRIGGER IF EXISTS d_model_in_use;
CREATE TRIGGER d_model_in_use
BEFORE DELETE ON models
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM cars WHERE model_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o modelo porque está associado a viaturas.');
END;

DROP TRIGGER IF EXISTS d_make_in_use;
CREATE TRIGGER d_make_in_use
BEFORE DELETE ON makes
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM cars WHERE make_id = OLD.id) OR EXISTS (SELECT 1 FROM models WHERE make_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar a marca porque está associada a viaturas ou modelos.');
END;

DROP TRIGGER IF EXISTS d_product_in_use;
CREATE TRIGGER d_product_in_use
BEFORE DELETE ON products
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM services_products_requested WHERE product_id = OLD.id) OR EXISTS (SELECT 1 FROM services_applied_products WHERE product_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o produto porque está associado a serviços.');
END;

DROP TRIGGER IF EXISTS d_service_in_use;
CREATE TRIGGER d_service_in_use
BEFORE DELETE ON services
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM services_products_requested WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM services_applied_products WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM services_user_time WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM services_user_time_punches WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM lab_items WHERE service_id = OLD.id)
	OR EXISTS (SELECT 1 FROM service_associations WHERE service_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o serviço porque tem produtos, tempos, laboratório ou está numa associação.');
END;

DROP TRIGGER IF EXISTS d_user_in_use;
CREATE TRIGGER d_user_in_use
BEFORE DELETE ON users
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM services_user_time WHERE user_id = OLD.id) OR EXISTS (SELECT 1 FROM services_user_time_punches WHERE user_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o utilizador porque tem tempos registados.');
END;

DROP TRIGGER IF EXISTS d_tabled_item_in_use;
CREATE TRIGGER d_tabled_item_in_use
BEFORE DELETE ON tabled_items
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM lab_items WHERE t_item_id = OLD.id) OR EXISTS (SELECT 1 FROM tabled_actions WHERE t_item_id = OLD.id) OR EXISTS (SELECT 1 FROM tabled_properties WHERE t_item_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar o item porque tem ações/propriedades ou está a ser usado em serviços.');
END;

DROP TRIGGER IF EXISTS d_tabled_action_in_use;
CREATE TRIGGER d_tabled_action_in_use
BEFORE DELETE ON tabled_actions
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM lab_action_values WHERE t_action_id = OLD.id) OR EXISTS (SELECT 1 FROM tabled_action_values WHERE t_action_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar a ação porque tem valores ou está a ser usada em serviços.');
END;

DROP TRIGGER IF EXISTS d_tabled_property_in_use;
CREATE TRIGGER d_tabled_property_in_use
BEFORE DELETE ON tabled_properties
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM lab_property_values WHERE property_id = OLD.id)
BEGIN
	SELECT RAISE(ABORT, 'Não é possível apagar a propriedade porque está a ser usada em serviços.');
END;

-- ------------------------------------------------------------
-- 5. Plates: "13 13 SR", "13-13-SR" and "1313SR" are the same plate
-- ------------------------------------------------------------
-- search_plate is the plate without dashes/spaces, lowercase (the app now
-- stores it that way); old rows lose any spaces. The duplicate check
-- compares it too, not only the exact text. An update is only checked when
-- the plate changes, so near-duplicates that already exist stay editable.
UPDATE cars SET search_plate = REPLACE(search_plate, ' ', '') WHERE search_plate LIKE '% %';

DROP TRIGGER IF EXISTS i_car_plate_unique;
CREATE TRIGGER i_car_plate_unique
BEFORE INSERT ON cars
FOR EACH ROW
WHEN EXISTS (
	SELECT 1 FROM cars
	WHERE plate = NEW.plate
		OR (NEW.search_plate IS NOT NULL AND NEW.search_plate != '' AND search_plate = NEW.search_plate)
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe uma viatura com essa matrícula.');
END;

DROP TRIGGER IF EXISTS u_car_plate_unique;
CREATE TRIGGER u_car_plate_unique
BEFORE UPDATE OF plate ON cars
FOR EACH ROW
WHEN NEW.plate IS NOT OLD.plate AND EXISTS (
	SELECT 1 FROM cars
	WHERE (plate = NEW.plate
		OR (NEW.search_plate IS NOT NULL AND NEW.search_plate != '' AND search_plate = NEW.search_plate))
		AND id != OLD.id
)
BEGIN
	SELECT RAISE(ABORT, 'Já existe uma viatura com essa matrícula.');
END;

-- ------------------------------------------------------------
-- 6. A time entry can't be more than 1440 minutes (24 hours)
-- ------------------------------------------------------------
-- Only checked when the minutes are set/changed: old entries above the
-- limit can still be edited (and fixed).
DROP TRIGGER IF EXISTS i_service_user_time_minutes_max;
CREATE TRIGGER i_service_user_time_minutes_max
BEFORE INSERT ON services_user_time
FOR EACH ROW
WHEN NEW.minutes > 1440
BEGIN
	SELECT RAISE(ABORT, 'Um registo de tempo não pode ter mais de 1440 minutos (24 horas).');
END;

DROP TRIGGER IF EXISTS u_service_user_time_minutes_max;
CREATE TRIGGER u_service_user_time_minutes_max
BEFORE UPDATE OF minutes ON services_user_time
FOR EACH ROW
WHEN NEW.minutes > 1440 AND NEW.minutes IS NOT OLD.minutes
BEGIN
	SELECT RAISE(ABORT, 'Um registo de tempo não pode ter mais de 1440 minutos (24 horas).');
END;

COMMIT;
