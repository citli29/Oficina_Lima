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

BEGIN TRANSACTION;

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

COMMIT;
