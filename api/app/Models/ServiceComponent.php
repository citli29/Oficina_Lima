<?php 
namespace App\Models;

use App\Database\Database;
use DateTime;
use DateTimeZone;
use PDO;

class ServiceComponent
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getSUTWithFilter(array $filters): array
	{
		$sql = "
			SELECT * FROM 
				(SELECT 
					ROW_NUMBER() OVER (
						PARTITION BY service_id
						ORDER BY service_id ASC, sut.id ASC
					) AS sut_id,
					sut.service_id AS service_id,
					sut.id AS id,
					sut.minutes AS minutes,
					sut.ut_date AS date,
					sut.user_id AS user_id,
					u.name AS user_name
				FROM services_user_time sut
				LEFT JOIN users u
				ON u.id = sut.user_id
				WHERE 1 = 1)
			WHERE 1 = 1
		";

		$params = [];

		$rules = [
			'service_id' => [
				'column' => 'service_id',
				'operator' => '='
			],
			'user_name' => [
				'column' => 'user_name',
				'operator' => 'LIKE'
			],
			'user_id' => [
				'column' => 'user_id',
				'operator' => '='
			],
			'minutes' => [
				'column' => 'minutes',
				'operator' => 'LIKE'
			],
			'date' => [
				'column' => 'date',
				'operator' => 'LIKE'
			],

		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$stmt = $this->db->prepare($sql);

		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getSUTByServiceWithFilter(int $serviceId, array $filters): array
	{
		$sql = "
		SELECT 
			ROW_NUMBER() OVER (
				PARTITION BY service_id
							ORDER BY service_id ASC, sut.id ASC
			) AS sut_id,
			sut.service_id AS service_id,
			sut.id AS id,
			sut.minutes AS minutes,
			sut.ut_date AS date,
	       		sut.user_id AS user_id,
			u.name AS user_name
		FROM services_user_time sut
		LEFT JOIN users u
		ON u.id = sut.user_id
		WHERE sut.service_id = ?
		";

		$params = [];

		$rules = [
			'user_name' => [
				'column' => 'u.name',
				'operator' => 'LIKE'
			],
			'user_id' => [
				'column' => 'sut.user_id',
				'operator' => '='
			],
			'date' => [
				'column' => 'sut.ut_date',
				'operator' => 'LIKE'
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$stmt = $this->db->prepare($sql);
		array_unshift($params,$serviceId);

		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getSUTBySid_Id(int $s_id, int $id): bool|array
	{

		$stmt = $this->db->prepare("
			SELECT *
			FROM (
				SELECT 
					CAST(
						ROW_NUMBER() OVER (
							PARTITION BY sut.service_id
							ORDER BY service_id ASC, sut.id ASC
						) AS INTEGER
					) AS sut_id,
					sut.service_id,
					sut.id,
					sut.minutes,
					sut.ut_date AS date,
					sut.user_id,
					u.name AS user_name
				FROM services_user_time sut
				LEFT JOIN users u
				    ON u.id = sut.user_id
				WHERE sut.service_id = ?
			    ) ranked
		    WHERE sut_id = ?
		");

		$stmt->execute(array($s_id,$id));
		
		return $stmt->fetch();
	}

	public function getSUTById(int $id): bool|array
	{
		$stmt = $this->db->query( "
			SELECT * FROM (	
				SELECT 
					CAST(
						ROW_NUMBER() OVER (
							PARTITION BY sut.service_id
							ORDER BY service_id ASC, sut.id ASC
						) AS INTEGER
					) AS sut_id,
					sut.service_id,
					sut.id,
					sut.minutes,
					sut.ut_date AS date,
					sut.user_id,
					u.name AS user_name
				FROM services_user_time sut
				LEFT JOIN users u
				    ON u.id = sut.user_id
				WHERE sut.service_id = (
					SELECT service_id
					FROM services_user_time
					WHERE id = ?
				) 
			) WHERE id = ?
			");

		$stmt->execute([$id,$id]);

		return $stmt->fetch();
	}

	public function updateSUTBySid_Id(int $s_id,int $id, array $data): bool|array
	{
		$sut = $this->getSUTBySid_Id($s_id,$id);

		if($sut && isset($sut['id']))
		{
			$stmt = $this->db->prepare("
				UPDATE
				services_user_time
				SET
				service_id = ?,
				minutes = ?,
				ut_date = ?,
				user_id = ?
				WHERE id = ?
				");

			$stmt->execute([
				$s_id,
				!empty($data['minutes'])? $data['minutes']:0,
				!empty($data['date'])? $data['date']:null,
				!empty($data['user_id'])? $data['user_id']:null,
				$sut['id']
			]);

			$sut = $this->getSUTById($sut['id']);
		}

		return $sut;
	}

	public function createSUT(int $s_id,array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO services_user_time
			(service_id, minutes, ut_date, user_id)
			VALUES (?,?,?,?)
			");

		$stmt->execute([
			$s_id,
			!empty($data['minutes'])? $data['minutes']:0,
			!empty($data['date'])? $data['date']:null,
			!empty($data['user_id'])? $data['user_id']:null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getSUTById($newId);
	}

	public function deleteSUTBySid_Id(int $s_id, int $id): bool|array
	{
		$sut = $this->getSUTBySid_Id($s_id,$id);

		if($sut && isset($sut['id']))
		{

			$stmt = $this->db->prepare("DELETE FROM services_user_time WHERE id = ?");
			$stmt->execute([$sut['id']]);
		}

		return $sut; 
	}

	public function getSAPByServiceWithFilter(int $serviceId, array $filters): array
	{
		$sql = "
		SELECT 
			ROW_NUMBER() OVER (
				PARTITION BY service_id
				ORDER BY service_id ASC, sap.id ASC
			) AS sap_id,
			sap.service_id AS service_id,
			sap.id AS id,
	       		sap.product_id AS product_id,
			sap.quantity AS quantity,
			sap.is_applied AS is_applied,
			p.name AS product_name,
			p.reference AS product_reference,
			p.product_type_id AS product_type_id,
			pt.name AS product_type_name
		FROM services_applied_products sap
		LEFT JOIN products p
		ON p.id = sap.product_id
		LEFT JOIN product_types pt
		ON pt.id = p.product_type_id
		WHERE sap.service_id = ?
		";

		$params = [];

		$rules = [
			'user_name' => [
				'column' => 'u.name',
				'operator' => 'LIKE'
			],
			'product_name' => [
				'column' => 'p.name',
				'operator' => 'LIKE'
			],
			'product_reference' => [
				'column' => 'p.reference',
				'operator' => 'LIKE'
			],
			'product_id' => [
				'column' => 'p.id',
				'operator' => '='
			],
			'is_applied' => [
				'column' => 'is_applied',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$stmt = $this->db->prepare($sql);
		array_unshift($params,$serviceId);

		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getSAPBySid_Id(int $s_id, int $id): bool|array
	{

		$stmt = $this->db->prepare("
			SELECT *
			FROM (
				SELECT 
					CAST(
						ROW_NUMBER() OVER (
							PARTITION BY sap.service_id
							ORDER BY service_id ASC, sap.id ASC
						) AS INTEGER
					) AS sap_id,
					sap.service_id AS service_id,
					sap.id AS id,
					sap.product_id AS product_id,
					sap.quantity AS quantity,
					sap.is_applied AS is_applied,
					p.name AS product_name,
					p.reference AS product_reference,
					p.product_type_id AS product_type_id,
					pt.name AS product_type_name
				FROM services_applied_products sap
				LEFT JOIN products p
				ON p.id = sap.product_id
				LEFT JOIN product_types pt
				ON pt.id = p.product_type_id
				WHERE sap.service_id = ?
			    ) ranked
		    WHERE sap_id = ?
		");

		$stmt->execute(array($s_id,$id));
		
		return $stmt->fetch();
	}

	public function getSAPById(int $id): bool|array
	{
		$stmt = $this->db->query( "
			SELECT * FROM (	
				SELECT 
					CAST(
						ROW_NUMBER() OVER (
							PARTITION BY sap.service_id
							ORDER BY service_id ASC, sap.id ASC
						) AS INTEGER
					) AS sap_id,
					sap.service_id AS service_id,
					sap.id AS id,
					sap.product_id AS product_id,
					sap.quantity AS quantity,
					sap.is_applied AS is_applied,
					p.name AS product_name,
					p.reference AS product_reference,
					p.product_type_id AS product_type_id,
					pt.name AS product_type_name
				FROM services_applied_products sap
				LEFT JOIN products p
				ON p.id = sap.product_id
				LEFT JOIN product_types pt
				ON pt.id = p.product_type_id
				WHERE sap.service_id = (
					SELECT service_id
					FROM services_applied_products
					WHERE id = ?
				) 
			) WHERE id = ?
			");

		$stmt->execute([$id,$id]);

		return $stmt->fetch();
	}

	public function updateSAPBySid_Id(int $s_id,int $id, array $data): bool|array
	{
		$sap = $this->getSAPBySid_Id($s_id,$id);

		if($sap && isset($sap['id']))
		{
			$stmt = $this->db->prepare("
				UPDATE
				services_applied_products
				SET
				service_id = ?,
				product_id = ?,
				quantity = ?,
				is_applied = ?
				WHERE id = ?
				");

			$stmt->execute([
				$s_id,
				!empty($data['product_id'])?$data['product_id']:null,
				!empty($data['quantity'])?$data['quantity']:0,
				!empty($data['is_applied'])?$data['is_applied']:false,
				$sap['id']
			]);

			$sap = $this->getSAPById($sap['id']);
		}

		return $sap;
	}

	public function createSAP(int $s_id,array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO services_applied_products
			(service_id, product_id"
			. (isset($data['quantity']) ? ",quantity" : "") 
			. (isset($data['is_applied']) ? ",is_applied" : "") 
			. ")
			VALUES (?,?"
			. (isset($data['quantity']) ? ",?" : "")
			. (isset($data['is_applied']) ? ",?" : "")
			. ")"
		);


		$params = [
			$s_id,
			!empty($data['product_id'])?$data['product_id']:null,
		];

		if (isset($data['quantity'])) {
			$params[] =!empty($data['quantity'])?$data['quantity']:0; 
		}

		if (isset($data['is_applied'])) {
			$params[] = !empty($data['is_applied'])?$data['is_applied']:false;
		}

		$stmt->execute($params);

		$newId = (int)$this->db->lastInsertId();

		return $this->getSAPById($newId);
	}

	public function deleteSAPBySid_Id(int $s_id, int $id): bool|array
	{
		$sap = $this->getSAPBySid_Id($s_id,$id);

		if($sap && isset($sap['id']))
		{

			$stmt = $this->db->prepare("DELETE FROM services_applied_products WHERE id = ?");
			$stmt->execute([$sap['id']]);
		}

		return $sap; 
	}

	public function getSAPWithFilter(array $filters): array
	{
		$sql = "
			SELECT * FROM 
				(SELECT 
					ROW_NUMBER() OVER (
						PARTITION BY service_id
						ORDER BY service_id ASC, sap.id ASC
					) AS sap_id,
					sap.service_id AS service_id,
					sap.id AS id,
					sap.product_id AS product_id,
					sap.quantity AS quantity,
					sap.is_applied AS is_applied,
					p.name AS product_name,
					p.reference AS product_reference,
					p.product_type_id AS product_type_id,
					pt.name AS product_type_name
				FROM services_applied_products sap
				LEFT JOIN products p
				ON p.id = sap.product_id
				LEFT JOIN product_types pt
				ON pt.id = p.product_type_id
				WHERE 1 = 1)
			WHERE 1 = 1
		";

		$params = [];

		$rules = [
			'service_id' => [
				'column' => 'service_id',
				'operator' => '='
			],
			'product_name' => [
				'column' => 'product_name',
				'operator' => 'LIKE'
			],
			'product_reference' => [
				'column' => 'product_reference',
				'operator' => 'LIKE'
			],
			'product_id' => [
				'column' => 'product_id',
				'operator' => '='
			],
			'is_applied' => [
				'column' => 'is_applied',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$stmt = $this->db->prepare($sql);

		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getSUTPByServiceWithFilter(int $serviceId, array $filters): array
	{
		$sql = "
		SELECT 
			ROW_NUMBER() OVER (
				PARTITION BY service_id
							ORDER BY service_id ASC, sutp.id ASC
			) AS sutp_id,
			sutp.service_id AS service_id,
			sutp.id AS id,
			sutp.hours_s,
			sutp.minutes_s,
			sutp.hours_f,
			sutp.minutes_f,
			sutp.minutes AS minutes,
			sutp.ut_date AS date,
	       		sutp.user_id AS user_id,
			u.name AS user_name
		FROM services_user_time_punches sutp
		LEFT JOIN users u
		ON u.id = sutp.user_id
		WHERE sutp.service_id = ?
		";

		$params = [];

		$rules = [
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$stmt = $this->db->prepare($sql);
		array_unshift($params,$serviceId);

		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getSUTPById(int $id): bool|array
	{
		$stmt = $this->db->query( "
			SELECT * FROM (	
				SELECT 
					CAST(
						ROW_NUMBER() OVER (
							PARTITION BY sutp.service_id
							ORDER BY service_id ASC, sutp.id ASC
						) AS INTEGER
					) AS sutp_id,
					sutp.service_id,
					sutp.id,
					sutp.hours_s,
					sutp.minutes_s,
					sutp.hours_f,
					sutp.minutes_f,
					sutp.minutes,
					sutp.ut_date AS date,
					sutp.user_id,
					u.name AS user_name
				FROM services_user_time_punches sutp
				LEFT JOIN users u
				    ON u.id = sutp.user_id
				WHERE sutp.service_id = (
					SELECT service_id
					FROM services_user_time_punches
					WHERE id = ?
				) 
			) WHERE id = ?
			");

		$stmt->execute([$id,$id]);

		return $stmt->fetch();
	}
	
	public function createSUTP(int $s_id,array $data): array 
	{
		$stmt = $this->db->prepare("
			INSERT INTO services_user_time_punches
			(service_id, ut_date, user_id)
			VALUES (?,?,?)
			");

		$stmt->execute([
			$s_id,
			!empty($data['date'])? $data['date']:null,
			!empty($data['user_id'])? $data['user_id']:null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getSUTPById($newId);
	}

	public function getSUTPBySid_Id(int $s_id, int $id): bool|array
	{

		$stmt = $this->db->prepare("
			SELECT *
			FROM (
				SELECT 
					CAST(
						ROW_NUMBER() OVER (
							PARTITION BY sutp.service_id
							ORDER BY service_id ASC, sutp.id ASC
						) AS INTEGER
					) AS sutp_id,
					sutp.service_id,
					sutp.id,
					sutp.hours_s,
					sutp.minutes_s,
					sutp.hours_f,
					sutp.minutes_f,
					sutp.minutes,
					sutp.ut_date AS date,
					sutp.user_id,
					u.name AS user_name
				FROM services_user_time_punches sutp
				LEFT JOIN users u
				    ON u.id = sutp.user_id
				WHERE sutp.service_id = ?
			    ) ranked
		    WHERE sutp_id = ?
		");

		$stmt->execute(array($s_id,$id));
		
		return $stmt->fetch();
	}

	public function startSUTP(array $sutp): bool|array
	{
		$now = new DateTime('now', new DateTimeZone('Europe/Lisbon'));

		$hour = (int) $now->format('H');
		$minute = (int) $now->format('i');
		$id = $sutp['id'];

		$stmt = $this->db->prepare("
			UPDATE 
				services_user_time_punches 
			SET 
				hours_s= ?, 
				minutes_s= ? 
			WHERE 
				id= ?;
		");

		$stmt->execute(array($hour,$minute,$id));

		return $this->getSUTPById($id);
	}

	public function stopSUTP(array $sutp): bool|array
	{
		$now = new DateTime('now', new DateTimeZone('Europe/Lisbon'));

		$hour = (int) $now->format('H');
		$minute = (int) $now->format('i');
		$id = $sutp['id'];

		$stmt = $this->db->prepare("
			UPDATE 
				services_user_time_punches 
			SET 
				hours_f= ?, 
				minutes_f= ? 
			WHERE 
				id= ?;
		");

		$stmt->execute(array($hour,$minute,$id));

		return $this->getSUTPById($id);
	}

	public function deleteSUTPBySid_Id(int $s_id, int $id): bool|array
	{
		$sutp = $this->getSUTPBySid_Id($s_id,$id);

		if($sutp && isset($sutp['id']))
		{

			$stmt = $this->db->prepare("DELETE FROM services_user_time_punches WHERE id = ?");
			$stmt->execute([$sutp['id']]);
		}

		return $sutp; 
	}

	public function updateSUTPBySid_Id(int $s_id,int $id, array $data): bool|array
	{
		$sutp = $this->getSUTPBySid_Id($s_id,$id);

		if($sutp && isset($sutp['id']))
		{
			$stmt = $this->db->prepare("
				UPDATE
				services_user_time
				SET
				ut_date = ?,
				user_id = ?
				WHERE id = ?
				");

			$stmt->execute([
				!empty($data['date'])? $data['date']:null,
				!empty($data['user_id'])? $data['user_id']:null,
				$sutp['id']
			]);

			$sutp = $this->getSUTById($sutp['id']);
		}

		return $sutp;
	}
}
?>
