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
				ORDER BY sut.id
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
							ORDER BY sut.id
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
			SELECT 
				ROW_NUMBER() OVER (
					PARTITION BY service_id
					ORDER BY sut.id
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
			WHERE  sut.id= ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function updateSUT(int $id, array $data): bool|array
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
			$data['service_id'],
			$data['minutes'],
			$data['ut_date'],
			$data['user_id'],
			$id
		]);

		return $this->getSUTById($id);
	}

	public function createSUT(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO services_user_time
			(service_id, minutes, ut_date, user_id)
			VALUES (?,?,?,?)
			");

		$stmt->execute([
			$data['service_id'],
			$data['minutes'],
			$data['ut_date'],
			$data['user_id'],
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getSUTById($newId);
	}

	public function deleteSUT(int $id): bool|array
	{
		$sut = $this->getSUTById($id);

		if($sut)
		{
			$stmt = $this->db->prepare("DELETE FROM services_user_time WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $sut; 
	}
}
?>
