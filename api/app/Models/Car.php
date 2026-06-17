<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Car
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getCarsAll(array $filters): array
	{
		$sql = "
		SELECT c.*, m.name AS model_name, mk.name AS make_name
		FROM cars c
		LEFT JOIN models m ON c.model_id = m.id
		LEFT JOIN makes mk ON m.make_id = mk.id
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'plate' => [
				'column' => 'c.plate',
				'operator' => 'LIKE'
			],
			'month' => [
				'column' => 'c.month',
				'operator' => '='
			],
			'year' => [
				'column' => 'c.year',
				'operator' => '='
			],
			'model_name' => [
				'column' => 'model_name',
				'operator' => 'LIKE'
			],
			'make_name' => [
				'column' => 'make_name',
				'operator' => 'LIKE'
			]
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return $stmt->fetchAll();
	}
	public function getCarById(int $id): bool|array
	{
		$stmt = $this->db->query( "
		SELECT c.*, m.name AS model_name, mk.name AS make_name
		FROM cars c
		LEFT JOIN models m ON c.model_id = m.id
		LEFT JOIN makes mk ON m.make_id = mk.id
		WHERE c.id = ?
		");
		$stmt->execute([$id]);
		return $stmt->fetch();
	}

	public function getModelById(int $id):bool|array
	{
		$stmt = $this->db->query("SELECT * FROM models WHERE id = ?");
		$stmt->execute([$id]);
		return $stmt->fetch();
	}

	public function getMakeById(int $id):bool|array
	{
		$stmt = $this->db->query("SELECT * FROM makes WHERE id = ?");
		$stmt->execute([$id]);
		return $stmt->fetch();
	}

}
