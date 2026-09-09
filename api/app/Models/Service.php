<?php 
namespace App\Models;

use App\Database\Database;
use PDO;

class Service
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getServiceTypesWithFilter(array $filters, ?array $pagination = null): array
	{
		$sql = "
		SELECT id, name FROM service_types
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'name' => [
				'column' => 'name',
				'operator' => 'LIKE'
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sql .= "ORDER BY name ASC";

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

	public function getServicesWithFilter(array $filters, ?array $pagination = null, ?array $sort = null): array
	{
		$sql = "
		SELECT s.id,
			s.kms,
			s.checkin_date as checkin,
			s.checkout_date as checkout,
			s.schedule_id as schedule_id,
			s.note as note,
			s.is_finished as is_finished,
			s.service_type_id as service_type_id,
			st.name as service_type_name,

			cl.name as client_name,
			cl.phone as client_phone,

			c.id as car_id,
			c.plate as car_plate,

			ma.id as car_make_id, 
			ma.name as car_make_name, 
			mo.id as car_model_id,
			mo.name as car_model_name

		FROM services s

		LEFT JOIN service_types st
		ON st.id=s.service_type_id

		LEFT JOIN clients cl
		ON cl.id=s.client_id
		
		LEFT JOIN cars c
		ON c.id=s.car_id

		LEFT JOIN models mo
		ON mo.id=c.model_id

		LEFT JOIN makes ma
		ON ma.id=COALESCE(mo.make_id,c.make_id)

		WHERE 1=1
		";

		$params = [];

		$rules = [
			'client_name' => [
				'column' => 'cl.search_name',
				'operator' => 'LIKE'
			],
			'checkin' => [
				'column' => 's.checkin_date',
				'operator' => 'LIKE'
			],
			'checkout' => [
				'column' => 's.checkout_date',
				'operator' => 'LIKE'
			],
			'car_plate' => [
				'column' => 'c.search_plate',
				'operator' => 'LIKE'
			],
			'car_model' => [
				'column' => 'mo.search_name',
				'operator' => 'LIKE'
			],
			'car_make' => [
				'column' => 'ma.search_name',
				'operator' => 'LIKE'
			],
			'schedule_id' => [
				'column' => 's.schedule_id',
				'operator' => '='
			],
			'service_type_id' => [
				'column' => 's.service_type_id',
				'operator' => '='
			],
			'start_date' => [
				'column' => 's.checkin_date',
				'operator' => '>='
			],
			'end_date' => [
				'column' => 's.checkin_date',
				'operator' => '<='
			],
		];

		if (!empty($filters['status'])) {
			switch ($filters['status']) {
				case 'unfinished':
					$sql .= " AND s.checkout_date IS NULL AND (s.is_finished IS NULL OR s.is_finished != 1)";
					break;
				case 'finished':
					$sql .= " AND s.checkout_date IS NULL AND s.is_finished = 1";
					break;
				case 'delivered':
					$sql .= " AND s.checkout_date IS NOT NULL";
					break;
			}
		}

		if (!empty($filters['q'])) {
			$q = '%' . $filters['q'] . '%';
			$sql .= " AND (UPPER(cl.search_name) LIKE UPPER(?) OR UPPER(c.search_plate) LIKE UPPER(?) OR cl.phone LIKE ?)";
			$params[] = $q;
			$params[] = $q;
			$params[] = $q;
		}

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sortableColumns = [
			'checkin' => 'checkin',
			'checkout' => 'checkout',
			'client_name' => 'client_name',
			'car_plate' => 'car_plate',
			'kms' => 's.kms',
		];

		$sql = Database::applySort(
			$sql,
			$sortableColumns,
			$sort['column'] ?? null,
			$sort['direction'] ?? 'ASC',
			'checkin, checkout, car_plate ASC'
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

	public function getServiceById(int $id): bool|array
	{
		$stmt = $this->db->prepare( "
			SELECT 
				s.id,
				s.checkin_date as checkin,
				s.checkout_date as checkout,
				s.schedule_id as schedule_id,
				s.kms as kms,
				s.note as note,
				s.is_finished as is_finished,
				s.service_type_id as service_type_id,
				st.name as service_type_name,

				s.client_id as client_id,
				cl.name as client_name,
				cl.phone as client_phone,
				cl.address as client_address,
				cl.email as client_email,
				cl.zip_code as client_zip_code,
				cl.tax_nr as client_tax_nr,

				s.car_id as car_id,
				ca.chassi_nr as car_chassi_nr,
				ca.cc as car_cc,
				ma.id as car_make_id, 
				ma.name as car_make_name, 
				mo.id as car_model_id,
				mo.name as car_model_name,
				ca.month as car_month,
				ca.year as car_year,
				ca.engine_code as car_engine_code,
				ca.color_code as car_color_code,
				ca.plate as car_plate, 
				
				s.malfunction_description as malfunction,
				s.service_description as service,

				s.r_name as r_name,
				s.r_phone as r_phone,

				s.checkout_predict as checkout_predict,
				s.signed_service as signed_service

			FROM services s

			LEFT JOIN service_types st
			ON st.id = s.service_type_id

			LEFT JOIN clients cl
			ON cl.id = s.client_id

			LEFT JOIN cars ca
			ON ca.id = s.car_id

			LEFT JOIN models mo
			ON mo.id = ca.model_id

			LEFT JOIN makes ma
			ON ma.id = COALESCE(mo.make_id, ca.make_id)

			WHERE s.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function updateService(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE services
			SET
				client_id = ?,
				kms = ?,
				checkin_date = ?,
				checkout_date = ?,
				malfunction_description = ?,
				service_description = ?,
				car_id = ?,
				schedule_id = ?,
				note = ?,
				is_finished = ?,
				service_type_id = ?,
				r_name = ?,
				r_phone = ?,
				checkout_predict = ?,
				signed_service = ?

			WHERE id = ?
			");

		$stmt->execute([
			!empty($data['client_id']) ?$data['client_id']: null,
			!empty($data['kms']) ?$data['kms']: null,
			!empty($data['checkin']) ?$data['checkin']: null,
			!empty($data['checkout']) ?$data['checkout']: null,
			!empty($data['malfunction']) ?$data['malfunction']: null,
			!empty($data['service']) ?$data['service']: null,
			!empty($data['car_id']) ?$data['car_id']: null,
			!empty($data['schedule_id']) ?$data['schedule_id']: null,
			!empty($data['note']) ?$data['note']: null,
			!empty($data['is_finished']) ?$data['is_finished']: 0,
			!empty($data['service_type_id']) ?$data['service_type_id']: 1,
			!empty($data['r_name']) ?$data['r_name']: null,
			!empty($data['r_phone']) ?$data['r_phone']: null,
			!empty($data['checkout_predict']) ?$data['checkout_predict']: null,
			!empty($data['signed_service']) ?$data['signed_service']: null,
			$id
		]);

		return $this->getServiceById($id);
	}

	public function createService(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO
			services(client_id, kms, checkin_date, checkout_date, malfunction_description, service_description, car_id, schedule_id, note, is_finished, service_type_id, r_name, r_phone, checkout_predict, signed_service)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
			");

		$stmt->execute([
			!empty($data['client_id']) ?$data['client_id']: null,
			!empty($data['kms']) ?$data['kms']: null,
			!empty($data['checkin']) ?$data['checkin']: null,
			!empty($data['checkout']) ?$data['checkout']: null,
			!empty($data['malfunction']) ?$data['malfunction']: null,
			!empty($data['service']) ?$data['service']: null,
			!empty($data['car_id']) ?$data['car_id']: null,
			!empty($data['schedule_id']) ?$data['schedule_id']: null,
			!empty($data['note']) ?$data['note']: null,
			0,
			!empty($data['service_type_id']) ?$data['service_type_id']: 1,
			!empty($data['r_name']) ?$data['r_name']: null,
			!empty($data['r_phone']) ?$data['r_phone']: null,
			!empty($data['checkout_predict']) ?$data['checkout_predict']: null,
			!empty($data['signed_service']) ?$data['signed_service']: null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getServiceById($newId);
	}

	public function deleteService(int $id): bool|array
	{
		$service = $this->getServiceById($id);

		if($service)
		{
			$stmt = $this->db->prepare("DELETE FROM services WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $service; 
	}
	public function createServiceFromSchedule(int $id,array $data,array $schedule):array
	{
		$client_id =  $schedule['client_id'];
		$car_id =  $schedule['car_id'];

		$stmt = $this->db->prepare("
			INSERT INTO
			services(client_id, kms, checkin_date, checkout_date, malfunction_description, car_id, schedule_id, is_finished, service_type_id, r_name, r_phone)
			VALUES (?,?,?,?,?,?,?,?,?,?,?)
			");

		$stmt->execute([
			$client_id ?? null,
			!empty($data['kms']) ?$data['kms']: null,
			!empty($data['checkin']) ?$data['checkin']: null,
			!empty($data['checkout']) ?$data['checkout']: null,
			!empty($schedule['description']) ?$schedule['description']: null,
			$car_id ?? null,
			$id ?? null,
			0,
			!empty($data['service_type_id']) ?$data['service_type_id']: 1,
			!empty($data['r_name']) ?$data['r_name']: null,
			!empty($data['r_phone']) ?$data['r_phone']: null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getServiceById($newId);
	}
}
?>
