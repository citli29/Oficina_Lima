<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Event
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getEventsWithFilter(array $filters, ?array $pagination = null): array
	{
		$sql = "
		SELECT e.*, u.name AS user_name
		FROM events e
		LEFT JOIN users u ON u.id = e.user_id
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'title' => [
				'column' => 'e.title',
				'operator' => 'LIKE'
			],
			'user_id' => [
				'column' => 'e.user_id',
				'operator' => '='
			],
			'start_date' => [
				'column' => 'e.end_date',
				'operator' => '>='
			],
			'end_date' => [
				'column' => 'e.start_date',
				'operator' => '<='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sql .= "ORDER BY e.start_date ASC, e.start_time ASC";

		$total = null;

		if ($pagination !== null) {
			$total = Database::getTotalCount($this->db, $sql, $params);
			$sql = Database::applyPagination($sql, $params, $pagination['page'], $pagination['per_page']);
		}

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return [
			'rows' => $stmt->fetchAll(),
			'total' => $total,
		];
	}

	public function getEventById(int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT e.*, u.name AS user_name
			FROM events e
			LEFT JOIN users u ON u.id = e.user_id
			WHERE e.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function createEvent(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO events
			(title, description, start_date, start_time, end_date, end_time, color, user_id)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)
			");

		$stmt->execute([
			!empty($data['title']) ? $data['title'] : null,
			!empty($data['description']) ? $data['description'] : null,
			!empty($data['start_date']) ? $data['start_date'] : null,
			!empty($data['start_time']) ? $data['start_time'] : null,
			!empty($data['end_date']) ? $data['end_date'] : null,
			!empty($data['end_time']) ? $data['end_time'] : null,
			!empty($data['color']) ? $data['color'] : null,
			!empty($data['user_id']) ? $data['user_id'] : null,
		]);

		$newId = (int) $this->db->lastInsertId();

		return $this->getEventById($newId);
	}

	public function updateEvent(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE events
			SET
				title = ?,
				description = ?,
				start_date = ?,
				start_time = ?,
				end_date = ?,
				end_time = ?,
				color = ?,
				user_id = ?
			WHERE id = ?
			");

		$stmt->execute([
			!empty($data['title']) ? $data['title'] : null,
			!empty($data['description']) ? $data['description'] : null,
			!empty($data['start_date']) ? $data['start_date'] : null,
			!empty($data['start_time']) ? $data['start_time'] : null,
			!empty($data['end_date']) ? $data['end_date'] : null,
			!empty($data['end_time']) ? $data['end_time'] : null,
			!empty($data['color']) ? $data['color'] : null,
			!empty($data['user_id']) ? $data['user_id'] : null,
			$id,
		]);

		return $this->getEventById($id);
	}

	public function deleteEvent(int $id): bool|array
	{
		$event = $this->getEventById($id);

		if ($event) {
			$stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $event;
	}
}
