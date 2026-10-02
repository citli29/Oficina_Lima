-- Notifies the office when the LAST unfinished service in an association
-- gets finished, making the whole association finished. Separate from the
-- existing per-service service_finished_notification trigger, which fires
-- for every service regardless of association membership.
--
-- Fires on the same event (AFTER UPDATE OF is_finished, 0 -> 1) but only
-- when the service belongs to an association AND, after this update, no
-- member of that same cluster is still unfinished.

BEGIN TRANSACTION;

CREATE TRIGGER IF NOT EXISTS service_association_finished_notification
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

COMMIT;
