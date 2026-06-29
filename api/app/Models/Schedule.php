<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Schedule
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getScheduleWithFilter(array $filters): array
	{
		//*?date* *?car_model* *?car_make* *?car_plate* *?client_name* *?client_id* 
		$sql = "
		SELECT
		    s.id,
		    s.schedule_date,
		    s.description,

		    c.plate AS car_plate,

		    mo.name AS car_model,
		    ma.name AS car_make,

		    cl.name AS client_name,
		    cl.phone AS client_phone,
		    cl.id AS client_id

		FROM schedules s

		LEFT JOIN cars c
		    ON s.car_id = c.id

		LEFT JOIN models mo
		    ON mo.id = COALESCE(c.model_id, s.model_id)

		LEFT JOIN makes ma
		    ON ma.id = c.make_id

		LEFT JOIN clients cl
		    ON cl.id = s.client_id
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'date'=>[
				'column' => 's.schedule_date',
				'operator'=> 'LIKE'
			],
			'car_plate' => [
				'column' => 'c.plate',
				'operator' => 'LIKE'
			],
			'car_model' => [
				'column' => 'mo.name',
				'operator' => 'LIKE'
			],
			'car_make' => [
				'column' => 'ma.name',
				'operator' => 'LIKE'
			],
			'client_name' => [
				'column' => 'cl.name',
				'operator' => 'LIKE'
			],
			'client_id' => [
				'column' => 'cl.name',
				'operator' => 'LIKE'
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getScheduleById(int $id): bool|array
	{
		$stmt = $this->db->query( "
		SELECT
		    s.id,
		    s.schedule_date,
		    s.description,

		    c.plate AS car_plate,

		    mo.name AS model_name,
		    ma.name AS make_name,

		    cl.name AS client_name,
		    cl.phone AS client_phone

		FROM schedules s

		LEFT JOIN cars c
		    ON s.car_id = c.id

		LEFT JOIN models mo
		    ON mo.id = COALESCE(c.model_id, s.model_id)

		LEFT JOIN makes ma
		    ON ma.id = c.make_id

		LEFT JOIN clients cl
		    ON cl.id = s.client_id
		WHERE s.id=?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}


	public function updateSchedule(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
		UPDATE schedules
		SET 
			schedule_date = ?,
			description = ?,
			car_id = ?,
			model_id = ?,
			client_id = ?
		WHERE id = ?
			");

		$stmt->execute([
			$data['schedule_date'],
			$data['description'],
			$data['car_id'] ?? null,
			$data['model_id'] ?? null,
			$data['client_id'] ?? null,
			$id
		]);

		return $this->getScheduleById($id);
	}

	public function createSchedule(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO schedules(schedule_date, description, car_id, model_id, client_id) VALUES(?,?,?,?,?)
			");

		$stmt->execute([
			$data['schedule_date'],
			$data['description'],
			$data['car_id'] ?? null,
			$data['model_id'] ?? null,
			$data['client_id'] ?? null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getScheduleById($newId);
	}


	public function deleteSchedule(int $id): bool|array
	{
		$product = $this->getScheduleById($id);

		if($product)
		{
			$stmt = $this->db->prepare("DELETE FROM schedules WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $product; 
	}

}
