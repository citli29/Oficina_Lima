-- Keeps the service-header fields (Nome, Telemóvel, Entrada, Prev. Saída,
-- Kms., Saída, Marcação) synced across every service in the same
-- service_associations cluster whenever one of them is edited — everything
-- the header exposes except service type and the office-validation check,
-- which stay per-service on purpose.
--
-- Safe to run more than once (idempotent).

BEGIN TRANSACTION;

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

COMMIT;
