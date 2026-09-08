<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class User
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getUsersWithFilter(array $filters, ?array $pagination = null): array
	{
		$sql = "
			SELECT u.id, u.name, u.email, u.user_type_id, ut.name as user_type_name
			FROM users u
			LEFT JOIN user_types ut
			ON  u.user_type_id = ut.id
			WHERE 1=1
		";
		$params = [];
		$rules = [
			'name' => [
				'column' => 'u.search_name',
				'operator' => 'LIKE'
			],
			'email' => [
				'column' => 'u.email',
				'operator' => 'LIKE'
			],
			'user_type' => [
				'column' => 'ut.search_name',
				'operator' => 'LIKE'
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);
		$sql .= "ORDER BY u.name ASC";

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

	public function getUserById(int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT u.id, u.name, u.email, u.user_type_id, ut.name as user_type_name
			FROM users u 
			LEFT JOIN user_types ut
			ON  u.user_type_id = ut.id
			WHERE u.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

}

