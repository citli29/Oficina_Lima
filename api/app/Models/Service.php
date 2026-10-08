<?php 
namespace App\Models;

use App\Database\Database;
use InvalidArgumentException;
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
		// Status as a rank (0 por terminar, 1 terminado, 2 entregue). A
		// service in an association takes the association's status — its
		// least advanced member, same as the list's collapsed row shows — so
		// e.g. "Por terminar" brings the whole association while any of its
		// services is still open, instead of only that one service.
		$statusRank = "CASE WHEN %s.checkout_date IS NOT NULL THEN 2 WHEN %s.is_finished = 1 THEN 1 ELSE 0 END";
		$groupStatusRank = "COALESCE(
			(SELECT MIN(" . sprintf($statusRank, 's2', 's2') . ")
				FROM service_associations sa2
				JOIN services s2 ON s2.id = sa2.service_id
				WHERE sa2.cluster_nr = sa.cluster_nr),
			" . sprintf($statusRank, 's', 's') . "
		)";

		// cluster_size / cluster_status_rank describe the whole association,
		// so the list can show the right count and status on its collapsed
		// row even when only some of the members are on the current page.
		$select = "
		SELECT s.id,
			s.kms,
			s.checkin_date as checkin,
			s.checkout_date as checkout,
			s.schedule_id as schedule_id,
			s.note as note,
			s.service_description as service,
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
			mo.name as car_model_name,

			sa.cluster_nr as cluster_nr,
			(SELECT COUNT(*) FROM service_associations sa3 WHERE sa3.cluster_nr = sa.cluster_nr) as cluster_size,
			{$groupStatusRank} as cluster_status_rank
		";

		$from = "
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

		LEFT JOIN service_associations sa
		ON sa.service_id=s.id

		";

		// Filters are collected here and turned into the final query below.
		$where = " WHERE 1=1";

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
			'car_id' => [
				'column' => 's.car_id',
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
			$statusRanks = ['unfinished' => 0, 'finished' => 1, 'delivered' => 2];

			if (isset($statusRanks[$filters['status']])) {
				$where .= " AND {$groupStatusRank} = " . $statusRanks[$filters['status']];
			}
		}

		if (!empty($filters['q'])) {
			$q = '%' . $filters['q'] . '%';
			// Plates are stored without spaces/dashes ("1313sr"), so the
			// plate side ignores the spaces typed ("13 13 sr").
			$qPlate = '%' . str_replace(' ', '', $filters['q']) . '%';
			$where .= " AND (UPPER(cl.search_name) LIKE UPPER(?) OR UPPER(c.search_plate) LIKE UPPER(?) OR cl.phone LIKE ?)";
			$params[] = $q;
			$params[] = $qPlate;
			$params[] = $q;
		}

		if (!empty($filters['product_id'])) {
			$where .= " AND EXISTS (SELECT 1 FROM services_applied_products sap WHERE sap.service_id = s.id AND sap.product_id = ?)";
			$params[] = $filters['product_id'];
		}

		$where = Database::applyFilters($where, $filters, $rules, $params);

		if (!empty($filters['group_associations'])) {
			// The services list asks for whole associations: when a filter
			// matches any service of an association, every service of that
			// association comes back too, so it never shows half of one.
			$sql = "WITH matched AS (SELECT s.id {$from} {$where})
				{$select} {$from}
				WHERE s.id IN (SELECT id FROM matched)
					OR sa.cluster_nr IN (
						SELECT sa_m.cluster_nr
						FROM service_associations sa_m
						JOIN matched m ON m.id = sa_m.service_id
					)";
		} else {
			$sql = $select . $from . $where;
		}

		// Text columns sort on their search_* twins (lowercased, accents
		// stripped) so "asdf" and "ZZ" don't end up split apart by case —
		// falling back to the plain value for rows inserted without one.
		$sortableColumns = [
			'id' => 's.id',
			// Services without an association always go last, whichever direction.
			'cluster_nr' => 'sa.cluster_nr IS NULL, sa.cluster_nr',
			'checkin' => 'checkin',
			'checkout' => 'checkout',
			'client_name' => 'COALESCE(cl.search_name, LOWER(cl.name))',
			'client_phone' => 'cl.phone',
			'car_plate' => 'COALESCE(c.search_plate, LOWER(c.plate))',
			'car_make_name' => 'COALESCE(ma.search_name, LOWER(ma.name))',
			'car_model_name' => 'COALESCE(mo.search_name, LOWER(mo.name))',
			'kms' => 's.kms',
			'service_type_name' => 'st.name COLLATE NOCASE',
			// Same association-aware rank as the status filter above.
			'status' => $groupStatusRank,
			'schedule_id' => 's.schedule_id',
		];

		$sql = Database::applySort(
			$sql,
			$sortableColumns,
			$sort['column'] ?? null,
			$sort['direction'] ?? 'ASC',
			'checkin, checkout, car_plate ASC',
			's.id'
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
				s.version as version,
				s.checkin_date as checkin,
				s.checkout_date as checkout,
				s.schedule_id as schedule_id,
				s.kms as kms,
				s.note as note,
				s.is_finished as is_finished,
				s.office_check as office_check,
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
				office_check = ?,
				service_type_id = ?,
				r_name = ?,
				r_phone = ?,
				checkout_predict = ?,
				signed_service = ?

			WHERE id = ? AND version = ?
			");

		$stmt->execute([
			!empty($data['client_id']) ?$data['client_id']: null,
			self::kmsValue($data['kms'] ?? null),
			!empty($data['checkin']) ?$data['checkin']: null,
			!empty($data['checkout']) ?$data['checkout']: null,
			!empty($data['malfunction']) ?$data['malfunction']: null,
			!empty($data['service']) ?$data['service']: null,
			!empty($data['car_id']) ?$data['car_id']: null,
			!empty($data['schedule_id']) ?$data['schedule_id']: null,
			!empty($data['note']) ?$data['note']: null,
			!empty($data['is_finished']) ?$data['is_finished']: 0,
			!empty($data['office_check']) ?$data['office_check']: 0,
			!empty($data['service_type_id']) ?$data['service_type_id']: 1,
			!empty($data['r_name']) ?$data['r_name']: null,
			!empty($data['r_phone']) ?$data['r_phone']: null,
			!empty($data['checkout_predict']) ?$data['checkout_predict']: null,
			!empty($data['signed_service']) ?$data['signed_service']: null,
			$id,
			$data['version'] ?? null,
		]);

		if ($stmt->rowCount() === 0) {
			if (!$this->getServiceById($id)) {
				return false;
			}

			throw new InvalidArgumentException(
				"Este serviço foi alterado por outro utilizador entretanto. Recarregue a página para ver as alterações mais recentes.",
				409
			);
		}

		return $this->getServiceById($id);
	}

	// API field name => column, for the per-field update (patchService).
	// Same set (and same empty-value handling, see normalizePatchValue)
	// as updateService.
	private const PATCHABLE_FIELDS = [
		'client_id' => 'client_id',
		'kms' => 'kms',
		'checkin' => 'checkin_date',
		'checkout' => 'checkout_date',
		'malfunction' => 'malfunction_description',
		'service' => 'service_description',
		'car_id' => 'car_id',
		'schedule_id' => 'schedule_id',
		'note' => 'note',
		'is_finished' => 'is_finished',
		'office_check' => 'office_check',
		'service_type_id' => 'service_type_id',
		'r_name' => 'r_name',
		'r_phone' => 'r_phone',
		'checkout_predict' => 'checkout_predict',
		'signed_service' => 'signed_service',
	];

	public static function patchableFields(): array
	{
		return array_keys(self::PATCHABLE_FIELDS);
	}

	// Empty values become NULL (flags: 0/1, type: 1) — exactly what
	// updateService stores, so an "original" value sent by the client
	// compares equal to what's actually in the row.
	// Kms as stored: empty (and 0) is NULL, as before; a negative number
	// is refused instead of being saved.
	private static function kmsValue(mixed $value): mixed
	{
		if (is_numeric($value) && $value < 0)
			throw new \InvalidArgumentException('Os kms não podem ser negativos.', 400);
		return !empty($value) ? $value : null;
	}

	private static function normalizePatchValue(string $field, mixed $value): mixed
	{
		if ($field === 'kms')
			return self::kmsValue($value);
		if ($field === 'is_finished' || $field === 'office_check')
			return !empty($value) ? 1 : 0;
		if ($field === 'service_type_id')
			return !empty($value) ? $value : 1;
		return !empty($value) ? $value : null;
	}

	/**
	 * Per-field update: sets only the fields in $changes, and only if each
	 * of them still holds the value the client last saw ($original) — all
	 * in one statement, so nothing can change in between. Fields nobody
	 * touched are never written, so a save can't overwrite someone else's
	 * change to a field this client didn't edit.
	 *
	 * Returns the updated service, false if the service doesn't exist, or
	 * ['conflict' => [field, ...], 'service' => current row] when one of
	 * the changed fields was changed by someone else in the meantime.
	 */
	public function patchService(int $id, array $changes, array $original): array|false
	{
		$set = [];
		$where = [];
		$setValues = [];
		$whereValues = [];

		foreach ($changes as $field => $value) {
			$column = self::PATCHABLE_FIELDS[$field];
			$set[] = "{$column} = ?";
			$setValues[] = self::normalizePatchValue($field, $value);
			$where[] = "{$column} IS ?";
			$whereValues[] = self::normalizePatchValue($field, $original[$field] ?? null);
		}

		if (!$set) {
			return $this->getServiceById($id);
		}

		$sql = "UPDATE services SET " . implode(', ', $set) . " WHERE id = ? AND " . implode(' AND ', $where);
		$stmt = $this->db->prepare($sql);
		$stmt->execute([...$setValues, $id, ...$whereValues]);

		$current = $this->getServiceById($id);
		if (!$current) {
			return false;
		}

		if ($stmt->rowCount() === 0) {
			// Name the fields that no longer hold what the client saw.
			$conflicts = [];
			foreach ($changes as $field => $value) {
				$now = self::normalizePatchValue($field, $current[$field] ?? null);
				$was = self::normalizePatchValue($field, $original[$field] ?? null);
				if ((string) $now !== (string) $was) {
					$conflicts[] = $field;
				}
			}

			return ['conflict' => $conflicts, 'service' => $current];
		}

		return $current;
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
			self::kmsValue($data['kms'] ?? null),
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
			self::kmsValue($data['kms'] ?? null),
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
