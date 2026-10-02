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

BEGIN TRANSACTION;

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

COMMIT;
