-- Custom calendar entries (holidays, vacations, shop closures, etc.),
-- unrelated to services/schedules. A null start_time/end_time pair means
-- the event is an all-day entry rather than a specific time window.

BEGIN TRANSACTION;

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

COMMIT;
