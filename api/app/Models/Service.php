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

	public function getServicesWithFilter(array $filters): array
	{
		$sql = "
		SELECT 
			s.id,
			s.kms,
			s.checkin_date as checkin,
			s.checkout_date as checkout,
			s.schedule_id,
			s.is_finished as is_finished,

			cl.name as client_name,
			cl.phone as client_phone,

			c.id as car_id,
			c.plate as car_plate,

			ma.id as car_make_id, 
			ma.name as car_make_name, 
			mo.id as car_model_id,
			mo.name as car_model_name

		FROM services s

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
				'column' => 'cl.name',
				'operator' => 'LIKE'
			],
			'checkin' => [
				'column' => 'checkin',
				'operator' => 'LIKE'
			],
			'checkout' => [
				'column' => 'checkout',
				'operator' => 'LIKE'
			],
			'car_plate' => [
				'column' => 'car_plate',
				'operator' => 'LIKE'
			],
			'car_model' => [
				'column' => 'car_model_name',
				'operator' => 'LIKE'
			],
			'car_make' => [
				'column' => 'car_make_name',
				'operator' => 'LIKE'
			],
			'schedule_id' => [
				'column' => 's.schedule_id',
				'operator' => '='
			],
			'is_finished' => [
				'column' => 's.is_finished',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);
		$sql .= "ORDER BY checkin,checkout, car_plate ASC";

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getServiceById(int $id): bool|array
	{
		$stmt = $this->db->query( "
			SELECT 
				s.id,
				s.checkin_date as checkin,
				s.checkout_date as checkout,
				s.schedule_id as schedule_id,
				s.kms as kms,
				s.is_finished as is_finished,

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
				s.service_description as service
				
			FROM services s

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
				is_finished = ?

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
			!empty($data['is_finished']) ?$data['is_finished']: false,
			$id
		]);

		return $this->getServiceById($id);
	}

	public function createService(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO
			services(client_id, kms, checkin_date, checkout_date, malfunction_description, service_description, car_id, schedule_id, is_finished)
			VALUES (?,?,?,?,?,?,?,?,?)
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
			!empty($data['is_finished']) ?$data['is_finished']: false,
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
		$client_id = !empty($data['client_id']) ? $data['client_id']: $schedule['client_id'];
		$car_id = !empty($data['car_id']) ? $data['car_id']: $schedule['car_id'];
		$checkin = !empty($data['checkin']) ? $data['checkin']: $schedule['date'];

		$stmt = $this->db->prepare("
			INSERT INTO
			services(client_id, kms, checkin_date, checkout_date, malfunction_description, service_description, car_id, schedule_id)
			VALUES (?,?,?,?,?,?,?,?)
			");

		$stmt->execute([
			$client_id ?? null,
			!empty($data['kms']) ?$data['kms']: null,
			$checkin ?? null,
			!empty($data['checkout']) ?$data['checkout']: null,
			!empty($schedule['description']) ?$schedule['description']: null,
			!empty($data['service']) ?$data['service']: null,
			$car_id ?? null,
			$id ?? null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getServiceById($newId);
	}
}
?>
