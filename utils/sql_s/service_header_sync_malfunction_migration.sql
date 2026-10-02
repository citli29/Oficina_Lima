-- Extends u_service_sync_cluster_header_fields to also sync
-- malfunction_description ("Descrição de Avaria") across the cluster.
--
-- CREATE TRIGGER IF NOT EXISTS is a no-op against an existing trigger of
-- the same name even when its body has changed, so this drops and
-- recreates it to actually pick up the new column — still safe to run
-- more than once.

BEGIN TRANSACTION;

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

COMMIT;
