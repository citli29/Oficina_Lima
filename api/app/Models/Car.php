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

	public function update(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE cars
			SET plate = ? , model_id = ?, chassi_nr = ?, year = ?, month = ?, cc = ?, engine_code = ?, color_code = ? WHERE id = ?");

		$stmt->execute([
			$data['plate'],
			$data['model_id'],
			$data['chassi_nr'] ?? null,
			$data['year'] ?? null,
			$data['month'] ?? null,
			$data['cc'] ?? null,
			$data['engine_code'] ?? null,
			$data['color_code'] ?? null,
			$id
		]);
		$stmt = $this->db->prepare("SELECT * FROM cars WHERE id = ?");
		$stmt->execute([$id]);
		return $stmt->fetch();
	}
	public function create(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO cars
			(plate, model_id, chassi_nr, year, month, cc, engine_code, color_code)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)
			");

		$stmt->execute([
			$data['plate'],
			$data['model_id'],
			$data['chassi_nr'] ?? null,
			$data['year'] ?? null,
			$data['month'] ?? null,
			$data['cc'] ?? null,
			$data['engine_code'] ?? null,
			$data['color_code'] ?? null,
		]);

		$id = (int)$this->db->query("SELECT last_insert_rowid()")->fetchColumn();

		$stmt = $this->db->prepare("SELECT * FROM cars WHERE id = ?");
		$stmt->execute([$id]);
		return $stmt->fetch();
	}
	public function delete(int $id): bool|array
	{
		$stmt = $this->db->prepare("SELECT * FROM cars WHERE id = ?");
		$stmt->execute([$id]);
		$car = $stmt->fetch();

		if($car)
		{
			$stmt = $this->db->prepare("DELETE FROM cars WHERE id = ?");
			$stmt->execute([$id]);
		}
		return $car; 
	}
}
