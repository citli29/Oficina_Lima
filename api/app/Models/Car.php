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

	public function getCarsWithFilter(array $filters): array
	{
		$sql = "
		SELECT c.*, m.name AS model_name, mk.name AS make_name
		FROM cars c
		LEFT JOIN makes mk ON c.make_id = mk.id
		LEFT JOIN models m ON c.model_id = m.id
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

	public function getModelsWithFilter(array $filters): array
	{
		$sql = "
		SELECT m.*, mk.name AS make_name
		FROM models m 
		LEFT JOIN makes mk ON m.make_id = mk.id
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'model_name' => [
				'column' => 'm.name',
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

	public function getMakesWithFilter(array $filters): array
	{
		$sql = "
		SELECT * FROM makes 
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'name' => [
				'column' => 'name',
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
		$stmt = $this->db->prepare( "
		SELECT c.*, m.name AS model_name, mk.name AS make_name
		FROM cars c
		LEFT JOIN makes mk ON c.make_id = mk.id
		LEFT JOIN models m ON c.model_id = m.id
		WHERE c.id = ?
		");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function getModelById(int $id):bool|array
	{
		$stmt = $this->db->prepare("
			SELECT m.*, mk.name AS make_name
			FROM models m 
			LEFT JOIN makes mk ON m.make_id = mk.id
			WHERE m.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function getMakeById(int $id):bool|array
	{
		$stmt = $this->db->prepare("
			SELECT * FROM makes WHERE id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function updateCar(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE cars
			SET plate = ?, make_id = ?,model_id = ?, chassi_nr = ?,
			year = ?, month = ?, cc = ?, 
			engine_code = ?, color_code = ? 
			WHERE id = ?
			");

		$stmt->execute([
			$data['plate'],
			$data['make_id'],
			$data['model_id'] ?? null,
			$data['chassi_nr'] ?? null,
			$data['year'] ?? null,
			$data['month'] ?? null,
			$data['cc'] ?? null,
			$data['engine_code'] ?? null,
			$data['color_code'] ?? null,
			$id
		]);

		return $this->getCarById($id);
	}

	public function updateModel(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE models
			SET name = ? , make_id = ? 
			WHERE id = ?
			");

		$stmt->execute([
			$data['name'],
			$data['make_id'],
			$id
		]);

		return $this->getModelById($id);
	}

	public function updateMake(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE makes
			SET name = ? , logo = ? 
			WHERE id = ?
			");

		$stmt->execute([
			$data['name'],
			$data['logo'] ?? null,
			$id
		]);

		return $this->getMakeById($id);
	}

	public function createCar(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO cars
			(plate, make_id, model_id, chassi_nr,
		       	year, month, cc, engine_code, color_code)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
			");

		$stmt->execute([
			$data['plate'],
			$data['make_id'],
			$data['model_id'] ?? null,
			$data['chassi_nr'] ?? null,
			$data['year'] ?? null,
			$data['month'] ?? null,
			$data['cc'] ?? null,
			$data['engine_code'] ?? null,
			$data['color_code'] ?? null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getCarById($newId);
	}

	public function createModel(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO models
			(name, make_id)
			VALUES (?, ?)
			");

		$stmt->execute([
			$data['name'],
			$data['make_id']
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getModelById($newId);
	}

	public function createMake(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO makes
			(name, logo)
			VALUES (?, ?)
			");

		$stmt->execute([
			$data['name'],
			$data['logo'] ?? null
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getMakeById($newId);
	}

	public function deleteCar(int $id): bool|array
	{

		$car = $this->getCarById($id);

		if($car)
		{
			$stmt = $this->db->prepare("
				DELETE FROM cars WHERE id = ?
				");
			$stmt->execute([$id]);
		}

		return $car; 
	}

	public function deleteModel(int $id): bool|array
	{
		$model = $this->getModelById($id);

		if($model)
		{
			$stmt = $this->db->prepare("
				DELETE FROM models WHERE id = ?
				");
			$stmt->execute([$id]);
		}

		return $model; 
	}

	public function deleteMake(int $id): bool|array
	{
		$make = $this->getMakeById($id);

		if($make)
		{
			$stmt = $this->db->prepare("
				DELETE FROM makes WHERE id = ?
				");
			$stmt->execute([$id]);
		}

		return $make; 
	}
}
