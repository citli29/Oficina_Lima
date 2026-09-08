<?php

namespace App\Models;

require_once __DIR__ .'/../../../utils/normalize.php';
use App\Database\Database;
use PDO;

class Notification
{
	private PDO $db;

	public function __construct(PDO $db){
		return $this->db = $db;
	}

	public function getNotificationsWithFilter(array $filters, ?array $pagination = null): array
	{
		$sql = "
		SELECT n.*
		FROM notifications n
		LEFT JOIN notification_types nt
		ON nt.id = n.notification_type_id
		WHERE 1=1
		";
		$params = [];
		$rules = [
			'n-type' => [
				'column' => 'n.notification_type_id',
				'operator' => '='
			],
			'is_checked' => [
				'column' => 'n.is_checked',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);
		$sql .= "ORDER BY n.created_at DESC";

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

	public function getNotificationById(int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT n.* 
			FROM notifications n 
			LEFT JOIN notification_types nt
			ON nt.id = n.notification_type_id
			WHERE n.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function updateNotificationCheck(int $id, int $is_checked): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE notifications
			SET is_checked = ?
			WHERE id = ? 
			");

		$stmt->execute([$is_checked, $id]);
			
		return $this->getNotificationById($id);
	}
}
