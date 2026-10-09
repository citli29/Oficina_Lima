-- ============================================================
-- Feriados (events) — Nov 2026 to Dec 2027, nacionais + municipal
-- de Marco de Canaveses.
--
-- created_at is left to its default (CURRENT_TIMESTAMP). A holiday that
-- already exists (same title and date) is skipped, so this is safe to run
-- more than once.
--
-- Run with: sqlite3 -bail <path-to-db> < holidays_2026_2027.sql
-- ============================================================

BEGIN TRANSACTION;

INSERT INTO events (title, description, start_date, end_date, color, user_id)
SELECT v.column1, v.column2, v.column3, v.column4, v.column5, NULL
FROM (
	VALUES
		-- =========================
		-- FERIADOS 2026
		-- =========================
		('Dia de Todos-os-Santos', 'Feriado nacional', '2026-11-01', '2026-11-01', '#84e82c'),
		('Restauração da Independência', 'Feriado nacional', '2026-12-01', '2026-12-01', '#84e82c'),
		('Imaculada Conceição', 'Feriado nacional', '2026-12-08', '2026-12-08', '#84e82c'),
		('Natal', 'Feriado nacional', '2026-12-25', '2026-12-25', '#84e82c'),

		-- =========================
		-- FERIADOS 2027
		-- =========================
		('Ano Novo', 'Feriado nacional', '2027-01-01', '2027-01-01', '#84e82c'),
		('Sexta-feira Santa', 'Feriado nacional', '2027-03-26', '2027-03-26', '#84e82c'),
		('Domingo de Páscoa', 'Feriado nacional', '2027-03-28', '2027-03-28', '#84e82c'),
		('Dia da Liberdade', 'Feriado nacional', '2027-04-25', '2027-04-25', '#84e82c'),
		('Dia do Trabalhador', 'Feriado nacional', '2027-05-01', '2027-05-01', '#84e82c'),
		('Corpo de Deus', 'Feriado nacional', '2027-05-27', '2027-05-27', '#84e82c'),
		('Dia de Portugal', 'Feriado nacional', '2027-06-10', '2027-06-10', '#84e82c'),
		('Assunção de Nossa Senhora', 'Feriado nacional', '2027-08-15', '2027-08-15', '#84e82c'),

		-- Feriado municipal - Marco de Canaveses
		('Feriado Municipal de Marco de Canaveses', 'Feriado municipal - Nossa Senhora da Natividade', '2027-09-08', '2027-09-08', '#84e82c'),

		('Implantação da República', 'Feriado nacional', '2027-10-05', '2027-10-05', '#84e82c'),
		('Dia de Todos-os-Santos', 'Feriado nacional', '2027-11-01', '2027-11-01', '#84e82c'),
		('Restauração da Independência', 'Feriado nacional', '2027-12-01', '2027-12-01', '#84e82c'),
		('Imaculada Conceição', 'Feriado nacional', '2027-12-08', '2027-12-08', '#84e82c'),
		('Natal', 'Feriado nacional', '2027-12-25', '2027-12-25', '#84e82c')
) AS v
WHERE NOT EXISTS (
	SELECT 1 FROM events e
	WHERE e.title = v.column1 AND e.start_date = v.column3
);

COMMIT;
