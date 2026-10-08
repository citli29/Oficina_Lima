<?php
declare(strict_types = 1);

namespace App\Models;

use App\Database\Database;
use PDO;

// Time entries (services_user_time) and punches (services_user_time_punches)
// across every service, for the "Registos de Tempo" page. Rows are addressed
// by their real id — not by their position inside a service (sut_id /
// sutp_id elsewhere), which shifts when another row of that service is
// deleted.
class TimeRecord
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	// Service / car / client columns shown next to each row.
	private const SERVICE_COLUMNS = "
		s.is_finished AS service_is_finished,
		s.checkin_date AS service_checkin,
		s.checkout_date AS service_checkout,
		s.service_type_id AS service_type_id,
		st.name AS service_type_name,
		c.plate AS car_plate,
		cl.name AS client_name
	";

	private const SERVICE_JOINS = "
		JOIN services s ON s.id = t.service_id
		LEFT JOIN service_types st ON st.id = s.service_type_id
		LEFT JOIN cars c ON c.id = s.car_id
		LEFT JOIN clients cl ON cl.id = s.client_id
		LEFT JOIN users u ON u.id = t.user_id
	";

	private const FILTER_RULES = [
		'user_id' => ['column' => 't.user_id', 'operator' => '='],
		'service_id' => ['column' => 't.service_id', 'operator' => '='],
		'date_from' => ['column' => 't.ut_date', 'operator' => '>='],
		'date_to' => ['column' => 't.ut_date', 'operator' => '<='],
		'car_plate' => ['column' => 'c.search_plate', 'operator' => 'LIKE'],
		'client_name' => ['column' => 'cl.search_name', 'operator' => 'LIKE'],
	];

	private const SORTS = [
		'date' => 't.ut_date',
		'service' => 't.service_id',
		'user' => 'u.name',
		'minutes' => 't.minutes',
		'car_plate' => 'c.plate',
		'client' => 'cl.name',
	];

	private function list(string $select, string $table, array $filters, ?array $pagination, ?array $sort, string $extraWhere = ''): array
	{
		$sql = "SELECT {$select}, " . self::SERVICE_COLUMNS . " FROM {$table} t " . self::SERVICE_JOINS . " WHERE 1 = 1 {$extraWhere}";

		$params = [];
		$sql = Database::applyFilters($sql, $filters, self::FILTER_RULES, $params);

		$total = null;
		if ($pagination !== null) {
			$total = Database::getTotalCount($this->db, $sql, $params);
		}

		// Newest first by default; ties (same day) by newest row.
		$sql = Database::applySort(
			$sql,
			self::SORTS,
			$sort['column'] ?? null,
			$sort['direction'] ?? 'DESC',
			't.ut_date DESC, t.id DESC',
			't.id'
		);

		if ($pagination !== null) {
			$sql = Database::applyPagination($sql, $params, $pagination['page'], $pagination['per_page']);
		}

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return ['rows' => $stmt->fetchAll(), 'total' => $total];
	}

	/* ---------- Time entries ---------- */

	private const TIME_SELECT = "
		t.id AS id,
		t.service_id AS service_id,
		t.user_id AS user_id,
		u.name AS user_name,
		t.minutes AS minutes,
		t.ut_date AS date
	";

	public function listTimes(array $filters, ?array $pagination = null, ?array $sort = null): array
	{
		return $this->list(self::TIME_SELECT, 'services_user_time', $filters, $pagination, $sort);
	}

	public function getTimeById(int $id): bool|array
	{
		$stmt = $this->db->prepare(
			"SELECT " . self::TIME_SELECT . ", " . self::SERVICE_COLUMNS .
			" FROM services_user_time t " . self::SERVICE_JOINS . " WHERE t.id = ?"
		);
		$stmt->execute([$id]);
		return $stmt->fetch();
	}

	public function createTime(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO services_user_time(service_id, user_id, minutes, ut_date)
			VALUES (?, ?, ?, ?)
		");
		$stmt->execute([$data['service_id'], $data['user_id'], $data['minutes'], $data['date']]);

		return $this->getTimeById((int) $this->db->lastInsertId());
	}

	public function updateTime(int $id, array $data): bool|array
	{
		if (!$this->getTimeById($id)) return false;

		$stmt = $this->db->prepare("
			UPDATE services_user_time
			SET service_id = ?, user_id = ?, minutes = ?, ut_date = ?
			WHERE id = ?
		");
		$stmt->execute([$data['service_id'], $data['user_id'], $data['minutes'], $data['date'], $id]);

		return $this->getTimeById($id);
	}

	public function deleteTime(int $id): bool|array
	{
		$time = $this->getTimeById($id);
		if (!$time) return false;

		$this->db->prepare("DELETE FROM services_user_time WHERE id = ?")->execute([$id]);
		return $time;
	}

	/* ---------- Punches ---------- */

	// start / end as "HH:MM" (end NULL while the punch is still running).
	private const PUNCH_SELECT = "
		t.id AS id,
		t.service_id AS service_id,
		t.user_id AS user_id,
		u.name AS user_name,
		t.ut_date AS date,
		CASE WHEN t.hours_s IS NOT NULL THEN printf('%02d:%02d', t.hours_s, t.minutes_s) END AS start,
		CASE WHEN t.hours_f IS NOT NULL THEN printf('%02d:%02d', t.hours_f, t.minutes_f) END AS end,
		t.minutes AS minutes
	";

	public function listPunches(array $filters, ?array $pagination = null, ?array $sort = null, bool $openOnly = false): array
	{
		// "Open" = started and never stopped — the ones someone forgot.
		$extra = $openOnly ? "AND t.hours_s IS NOT NULL AND t.hours_f IS NULL" : '';
		return $this->list(self::PUNCH_SELECT, 'services_user_time_punches', $filters, $pagination, $sort, $extra);
	}

	public function getPunchById(int $id): bool|array
	{
		$stmt = $this->db->prepare(
			"SELECT " . self::PUNCH_SELECT . ", " . self::SERVICE_COLUMNS .
			" FROM services_user_time_punches t " . self::SERVICE_JOINS . " WHERE t.id = ?"
		);
		$stmt->execute([$id]);
		return $stmt->fetch();
	}

	// $start / $end: [hours, minutes] or null.
	public function createPunch(array $data, ?array $start, ?array $end): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO services_user_time_punches(service_id, user_id, ut_date, hours_s, minutes_s, hours_f, minutes_f)
			VALUES (?, ?, ?, ?, ?, ?, ?)
		");
		$stmt->execute([
			$data['service_id'], $data['user_id'], $data['date'],
			$start[0] ?? null, $start[1] ?? null, $end[0] ?? null, $end[1] ?? null,
		]);

		return $this->getPunchById((int) $this->db->lastInsertId());
	}

	public function updatePunch(int $id, array $data, ?array $start, ?array $end): bool|array
	{
		if (!$this->getPunchById($id)) return false;

		// minutes: recalculated by the DB trigger when start and end are both
		// set; cleared here when they aren't (a reopened punch has no total).
		$stmt = $this->db->prepare("
			UPDATE services_user_time_punches
			SET service_id = ?, user_id = ?, ut_date = ?,
				hours_s = ?, minutes_s = ?, hours_f = ?, minutes_f = ?,
				minutes = CASE WHEN ? IS NULL OR ? IS NULL THEN NULL ELSE minutes END
			WHERE id = ?
		");
		$stmt->execute([
			$data['service_id'], $data['user_id'], $data['date'],
			$start[0] ?? null, $start[1] ?? null, $end[0] ?? null, $end[1] ?? null,
			$start[0] ?? null, $end[0] ?? null,
			$id,
		]);

		return $this->getPunchById($id);
	}

	public function deletePunch(int $id): bool|array
	{
		$punch = $this->getPunchById($id);
		if (!$punch) return false;

		$this->db->prepare("DELETE FROM services_user_time_punches WHERE id = ?")->execute([$id]);
		return $punch;
	}
}
