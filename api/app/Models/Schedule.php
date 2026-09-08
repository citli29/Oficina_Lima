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

	public function getSchedulesFree(): array
	{
		$sql = "
		SELECT *
		FROM schedules
		WHERE id NOT IN (
		SELECT schedule_id
		FROM services
		WHERE schedule_id IS NOT NULL
		)
		";
		$stmt = $this->db->prepare($sql);
		$stmt->execute();

		return $stmt->fetchAll();
	}
	public function getScheduleWithFilter(array $filters, ?array $pagination = null, ?array $sort = null): array
	{
		//*?date* *?car_model* *?car_make* *?car_plate* *?client_name* *?client_id* *?service_type_id* *?status*

		$sql = "
		SELECT
		s.id,
		s.date,
		s.description,
		s.car_id,

		c.plate AS car_plate,

		COALESCE(car_model.id, sched_model.id) AS car_model_id,
		COALESCE(car_model.name, sched_model.name) AS car_model,
		ma.name AS car_make,

		cl.name AS client_name,
		cl.phone AS client_phone,
		cl.id AS client_id,

		ss.id AS service_id,
		ss.is_finished AS service_is_finished,
		ss.checkout_date AS service_checkout,
		ss.service_type_id AS service_type_id,
		st.name AS service_type_name

		FROM schedules s

		LEFT JOIN cars c
		ON c.id = s.car_id

		LEFT JOIN models car_model
		ON car_model.id = c.model_id

		LEFT JOIN models sched_model
		ON sched_model.id = s.model_id

		LEFT JOIN makes ma
		ON ma.id = COALESCE(
		car_model.make_id,
		sched_model.make_id,
		c.make_id
		)

		LEFT JOIN services ss
		ON s.id = ss.schedule_id

		LEFT JOIN service_types st
		ON st.id = ss.service_type_id

		LEFT JOIN clients cl
		ON cl.id = s.client_id

		WHERE 1=1
		";
		$params = [];

		$rules = [
			'date'=>[
				'column' => 's.date',
				'operator'=> 'LIKE'
			],
			'car_plate' => [
				'column' => 'c.search_plate',
				'operator' => 'LIKE'
			],
			'car_model' => [
				'column' => 'car_model.search_name',
				'operator' => 'LIKE'
			],
			'car_make' => [
				'column' => 'ma.search_name',
				'operator' => 'LIKE'
			],
			'client_name' => [
				'column' => 'cl.search_name',
				'operator' => 'LIKE'
			],
			'client_id' => [
				'column' => 'cl.id',
				'operator' => '='
			],
			'service_type_id' => [
				'column' => 'ss.service_type_id',
				'operator' => '='
			],
			'start_date' => [
				'column' => 's.date',
				'operator' => '>='
			],
			'end_date' => [
				'column' => 's.date',
				'operator' => '<='
			],
		];

		if (!empty($filters['status'])) {
			switch ($filters['status']) {
				case 'without_service':
					$sql .= " AND ss.id IS NULL";
					break;
				case 'with_service':
					$sql .= " AND ss.id IS NOT NULL AND ss.checkout_date IS NULL AND (ss.is_finished IS NULL OR ss.is_finished != 1)";
					break;
				case 'finished':
					$sql .= " AND ss.checkout_date IS NULL AND ss.is_finished = 1";
					break;
				case 'delivered':
					$sql .= " AND ss.checkout_date IS NOT NULL";
					break;
			}
		}

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sortableColumns = [
			'date' => 's.date',
			'client_name' => 'client_name',
			'car_plate' => 'car_plate',
		];

		$sql = Database::applySort(
			$sql,
			$sortableColumns,
			$sort['column'] ?? null,
			$sort['direction'] ?? 'ASC',
			's.date ASC'
		);

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

	public function getScheduleById(int $id): bool|array
	{
		$stmt = $this->db->query( "
			SELECT
			s.id,
			s.date,
			s.description,
			s.car_id,

			c.plate AS car_plate,

			COALESCE(car_model.id, sched_model.id) AS car_model_id,
			COALESCE(car_model.name, sched_model.name) AS car_model,
			ma.name AS car_make,
			ma.id AS car_make_id,

			cl.name AS client_name,
			cl.phone AS client_phone,
			cl.id AS client_id

			FROM schedules s

			LEFT JOIN cars c
			ON c.id = s.car_id

			LEFT JOIN models car_model
			ON car_model.id = c.model_id

			LEFT JOIN models sched_model
			ON sched_model.id = s.model_id

			LEFT JOIN makes ma
			ON ma.id = COALESCE(
			car_model.make_id,
			sched_model.make_id,
			c.make_id
			)

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
			date = ?,
			description = ?,
			car_id = ?,
			model_id = ?,
			client_id = ?
		WHERE id = ?
			");

		$stmt->execute([
			!empty($data['date']) ?$data['date']: null,
			!empty($data['description']) ?$data['description']: null,
			!empty($data['car_id']) ?$data['car_id']: null,
			!empty($data['model_id']) ?$data['model_id']: null,
			!empty($data['client_id']) ?$data['client_id']: null,
			$id
		]);

		return $this->getScheduleById($id);
	}

	public function createSchedule(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO schedules(date, description, car_id, model_id, client_id) VALUES(?,?,?,?,?)
			");

		$stmt->execute([
			!empty($data['date']) ?$data['date']: null,
			!empty($data['description']) ?$data['description']: null,
			!empty($data['car_id']) ?$data['car_id']: null,
			!empty($data['model_id']) ?$data['model_id']: null,
			!empty($data['client_id']) ?$data['client_id']: null,
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
