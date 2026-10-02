-- Blocks all lab management writes (items, action values, property values)
-- once the owning service is finished (services.is_finished = 1).
-- lab_action_values and lab_property_values don't carry a service_id of
-- their own, so they're resolved through their lab_items.service_id.
-- Safe to run more than once: every trigger is CREATE ... IF NOT EXISTS.

BEGIN TRANSACTION;

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

COMMIT;
