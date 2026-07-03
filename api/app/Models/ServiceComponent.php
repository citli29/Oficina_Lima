<?php 
namespace App\Models;

use App\Database\Database;
use PDO;

class ServiceComponent
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
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
				$data['minutes'],
				$data['date'],
				$data['user_id'],
				$sut['id']
			]);

			$sut = $this->getSUTById($id);
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
			$data['minutes'],
			$data['date'],
			$data['user_id'],
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
				ORDER BY pt.id ASC, sap.id ASC
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
			'product_name' => [
				'column' => 'p.name',
				'operator' => 'LIKE'
			],
			'product_reference' => [
				'column' => 'p.reference',
				'operator' => '='
			],
			'product_id' => [
				'column' => 'p.id',
				'operator' => 'LIKE'
			],
			'is_applied' => [
				'column' => 'sap.is_applied',
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
							ORDER BY pt.id ASC, sap.id ASC
						) AS INTEGER
					) AS sap_id,
					sap.service_id,
					sap.id,
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
			SELECT 
				ROW_NUMBER() OVER (
					PARTITION BY service_id
					ORDER BY sap.id
				) AS sap_id,
				sap.service_id,
				sap.id,
				sap.product_id AS product_id,
				sap.quantity AS quantity,
				sap.is_applied AS is_applied,
				p.name AS product_name,
				p.reference AS product_reference
			FROM services_applied_products sap
			LEFT JOIN products p 
			ON p.id = sap.product_id
			WHERE  sap.id= ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function updateSAPBySid_Id(int $s_id, int $id, array $data): bool|array
	{
		$sap = $this->getSAPBySid_Id($s_id, $id);

		if($sap && isset($sap['id']))
		{
			$stmt = $this->db->prepare("
				UPDATE
				services_applied_products
				SET
				service_id = ?,
				product_id = ?,
				quantity = ?,
				is_applied = ?,
				WHERE id = ?
				");

			$stmt->execute([
				$s_id,
				$data['product_id'],
				$data['quantity'],
				$data['is_applied'],
				$sap['id']
			]);

			$sap = $this->getSAPBySid_Id($s_id, $id);
		}

		return $sap;
	}

	public function createSAP(int $s_id,array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO services_user_time
			(service_id, product_id, quantity, is_applied)
			VALUES (?,?,?,?)
			");

		$stmt->execute([
			$s_id,	
			$data['product_id'],
			$data['quantity'],
			$data['is_applied'],
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getSAPById($newId);
	}

	public function deleteSAPBySid_Id(int $s_id,int $id): bool|array
	{
		$sap = $this->getSAPBySid_Id($s_id,$id);

		if($sap && isset($sap['id']))
		{
			$stmt = $this->db->prepare("DELETE FROM services_applied_products WHERE id = ?");
			$stmt->execute([$sap['$id']]);
		}

		return $sap; 
	}

	public function getSUTWithFilter(array $filters): array
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
		WHERE 1 = 1
		";

		$params = [];

		$rules = [
			'service_id' => [
				'column' => 'sut.service_id',
				'operator' => '='
			]
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$stmt = $this->db->prepare($sql);

		$stmt->execute($params);

		return $stmt->fetchAll();
	}
}
?>
