<?php
namespace App\Models;

use App\Database\Database;
use PDO;

class ServiceAssociation
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	private function serviceJoinSelect(): string
	{
		return "
			s.checkin_date AS checkin,
			s.checkout_date AS checkout,
			s.is_finished AS is_finished,
			s.kms AS kms,
			s.service_type_id AS service_type_id,
			st.name AS service_type_name,

			cl.name AS client_name,
			cl.phone AS client_phone,

			c.plate AS car_plate,
			ma.name AS car_make_name,
			mo.name AS car_model_name
		";
	}

	private function serviceJoinFrom(string $serviceIdColumn): string
	{
		return "
			LEFT JOIN services s
			ON s.id = {$serviceIdColumn}

			LEFT JOIN service_types st
			ON st.id = s.service_type_id

			LEFT JOIN clients cl
			ON cl.id = s.client_id

			LEFT JOIN cars c
			ON c.id = s.car_id

			LEFT JOIN models mo
			ON mo.id = c.model_id

			LEFT JOIN makes ma
			ON ma.id = COALESCE(mo.make_id, c.make_id)
		";
	}

	public function getAssociationsWithFilter(array $filters, ?array $pagination = null): array
	{
		$sql = "
			SELECT
				sa.service_id AS service_id,
				sa.cluster_nr AS cluster_nr,
				" . $this->serviceJoinSelect() . "
			FROM service_associations sa
			" . $this->serviceJoinFrom('sa.service_id') . "
			WHERE 1=1
		";

		$params = [];

		$rules = [
			'cluster_nr' => [
				'column' => 'sa.cluster_nr',
				'operator' => '='
			],
			'service_id' => [
				'column' => 'sa.service_id',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);
		$sql .= " ORDER BY sa.cluster_nr ASC, sa.service_id ASC";

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

	public function getClusterMates(int $serviceId): array
	{
		$stmt = $this->db->prepare("
			SELECT
				sa2.service_id AS service_id,
				sa2.cluster_nr AS cluster_nr,
				" . $this->serviceJoinSelect() . "
			FROM service_associations sa
			JOIN service_associations sa2
				ON sa2.cluster_nr = sa.cluster_nr
				AND sa2.service_id != sa.service_id
			" . $this->serviceJoinFrom('sa2.service_id') . "
			WHERE sa.service_id = ?
			ORDER BY sa2.service_id ASC
		");

		$stmt->execute([$serviceId]);

		return $stmt->fetchAll();
	}

	public function getDb(): PDO
	{
		return $this->db;
	}

	// A service created from the association's "+" fires the usual "same
	// car already has an open sheet" notification on insert
	// (service_same_car_open_notification), before the link exists. Once
	// linked, its text says the new sheet belongs to the association:
	// - every other open sheet for the car is in this association → the
	//   message says so instead of reading like a mistake;
	// - the car also has an open sheet outside it (a real second job) → the
	//   warning names that sheet, not one of the association's own.
	public function markSameCarNoticeAsAssociation(int $serviceId): void
	{
		$cluster = $this->db->prepare("SELECT cluster_nr FROM service_associations WHERE service_id = ?");
		$cluster->execute([$serviceId]);
		$clusterNr = $cluster->fetchColumn();
		if ($clusterNr === false) return;

		// The first open sheet for this car outside the association, if any.
		$outside = $this->db->prepare("
			SELECT MIN(s.id) FROM services s
			WHERE s.car_id = (SELECT car_id FROM services WHERE id = ?)
				AND s.id != ?
				AND s.is_finished = 0
				AND s.id NOT IN (SELECT service_id FROM service_associations WHERE cluster_nr = ?)
		");
		$outside->execute([$serviceId, $serviceId, $clusterNr]);
		$outsideId = $outside->fetchColumn();

		// Rebuilt up to " para o mesmo carro…", which (with the car text) is kept.
		$prefix = $outsideId
			? "'A folha de serviço ' || :id || ' foi criada na associação #' || :nr || ', mas já existe uma folha de serviço aberta fora da associação (#' || :other || ')'"
			: "'A folha de serviço ' || :id || ' foi criada na associação #' || :nr || ', onde já existe uma folha de serviço aberta (#' || :other || ')'";

		$stmt = $this->db->prepare("
			UPDATE notifications
			SET message = {$prefix} || substr(message, instr(message, ' para o mesmo carro'))
			WHERE title = 'Folha de serviço aberta para o mesmo carro'
				AND json_extract(data, '$.url') = 'services/' || :id
				AND instr(message, ' para o mesmo carro') > 0
		");

		if (!$outsideId) {
			// The first other open sheet in the association.
			$inside = $this->db->prepare("
				SELECT MIN(s.id) FROM services s
				JOIN service_associations sa ON sa.service_id = s.id
				WHERE sa.cluster_nr = ? AND s.id != ? AND s.is_finished = 0
					AND s.car_id = (SELECT car_id FROM services WHERE id = ?)
			");
			$inside->execute([$clusterNr, $serviceId, $serviceId]);
			$otherId = $inside->fetchColumn();
		} else {
			$otherId = $outsideId;
		}

		if (!$otherId) return;
		$stmt->execute([':id' => $serviceId, ':nr' => $clusterNr, ':other' => $otherId]);
	}

	// The header fields an association keeps in sync across its services
	// (same set the header-sync trigger copies, minus car/client, which
	// must already match for a service to join).
	private const SYNCED_COLUMNS = [
		'r_name',
		'r_phone',
		'checkin_date',
		'checkout_predict',
		'kms',
		'checkout_date',
		'schedule_id',
		'malfunction_description',
	];

	public function getSyncedFields(int $serviceId): ?array
	{
		$columns = implode(', ', self::SYNCED_COLUMNS);
		$stmt = $this->db->prepare("SELECT {$columns} FROM services WHERE id = ?");
		$stmt->execute([$serviceId]);
		$row = $stmt->fetch();
		return $row === false ? null : $row;
	}

	// Writes the given header values back onto one service; the header-sync
	// trigger then copies them to every other service in its association.
	public function applySyncedFields(int $serviceId, array $fields): void
	{
		$set = implode(', ', array_map(fn($c) => "{$c} = ?", self::SYNCED_COLUMNS));
		$values = array_map(fn($c) => $fields[$c] ?? null, self::SYNCED_COLUMNS);
		$values[] = $serviceId;

		$stmt = $this->db->prepare("UPDATE services SET {$set} WHERE id = ?");
		$stmt->execute($values);
	}

	public function serviceExists(int $serviceId): bool
	{
		$stmt = $this->db->prepare("SELECT 1 FROM services WHERE id = ?");
		$stmt->execute([$serviceId]);

		return (bool) $stmt->fetchColumn();
	}

	public function getClusterNr(int $serviceId): ?int
	{
		$stmt = $this->db->prepare("SELECT cluster_nr FROM service_associations WHERE service_id = ?");
		$stmt->execute([$serviceId]);
		$value = $stmt->fetchColumn();

		return $value === false ? null : (int) $value;
	}

	public function getNextClusterNr(): int
	{
		$stmt = $this->db->query("SELECT COALESCE(MAX(cluster_nr), 0) + 1 AS next FROM service_associations");

		return (int) $stmt->fetchColumn();
	}

	public function insertAssociation(int $serviceId, int $clusterNr): void
	{
		$stmt = $this->db->prepare("INSERT INTO service_associations (service_id, cluster_nr) VALUES (?, ?)");
		$stmt->execute([$serviceId, $clusterNr]);
	}

	public function updateClusterNr(int $serviceId, int $clusterNr): void
	{
		$stmt = $this->db->prepare("UPDATE service_associations SET cluster_nr = ? WHERE service_id = ?");
		$stmt->execute([$clusterNr, $serviceId]);
	}

	public function deleteAssociation(int $serviceId): bool|array
	{
		$stmt = $this->db->prepare("SELECT service_id, cluster_nr FROM service_associations WHERE service_id = ?");
		$stmt->execute([$serviceId]);
		$existing = $stmt->fetch();

		if ($existing) {
			$stmt = $this->db->prepare("DELETE FROM service_associations WHERE service_id = ?");
			$stmt->execute([$serviceId]);
		}

		return $existing;
	}

	public function countClusterMembers(int $clusterNr): int
	{
		$stmt = $this->db->prepare("SELECT COUNT(*) FROM service_associations WHERE cluster_nr = ?");
		$stmt->execute([$clusterNr]);

		return (int) $stmt->fetchColumn();
	}

	public function getSoleClusterMember(int $clusterNr): ?int
	{
		$stmt = $this->db->prepare("SELECT service_id FROM service_associations WHERE cluster_nr = ?");
		$stmt->execute([$clusterNr]);
		$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

		return count($rows) === 1 ? (int) $rows[0] : null;
	}

	public function reassignClusterNr(int $fromClusterNr, int $toClusterNr): void
	{
		$stmt = $this->db->prepare("UPDATE service_associations SET cluster_nr = ? WHERE cluster_nr = ?");
		$stmt->execute([$toClusterNr, $fromClusterNr]);
	}
}
