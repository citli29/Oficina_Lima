<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Database;
use InvalidArgumentException;
use PDO;
use PDOException;

/**
 * "Lixo": rows saved in deleted_rows by the trash_<table> triggers
 * (utils/admin_scripts/trash_triggers.php). Used by the admin-only Lixo page and by
 * utils/admin_scripts/restore_deleted.php, so both restore the same way.
 *
 * A restore inserts the saved row back with its original id, so anything
 * that pointed to it works again. Several rows are restored in one
 * transaction (all or nothing), parents first, so an orçamento and its
 * products can come back together. The table's normal insert triggers run,
 * so a row whose parent is still deleted is refused.
 */
class TrashService
{
	// Parents before children, so a multi-row restore inserts in a working order.
	private const RESTORE_ORDER = [
		'makes', 'models', 'product_types', 'products',
		'service_types', 'users', 'clients', 'cars',
		'tabled_items', 'tabled_properties', 'tabled_actions', 'tabled_action_values',
		'schedules', 'schedule_lab_items', 'schedule_lab_action_values',
		'services', 'service_associations',
		'budgets', 'budget_products',
		'lab_items', 'lab_action_values', 'lab_property_values',
		'services_user_time', 'services_user_time_punches',
		'services_applied_products', 'services_products_requested',
		'events',
	];

	// Never sent to the browser (still kept, so a restore is complete).
	private const HIDDEN_FIELDS = ['password'];

	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	/**
	 * Newest first. Filters: table, status ("deleted" = not restored yet,
	 * "restored", anything else = all), q (text anywhere in the saved row).
	 */
	public function list(array $filters, int $page = 1, int $perPage = 50): array
	{
		$where = [];
		$params = [];

		if (!empty($filters['table'])) {
			$where[] = 'table_name = ?';
			$params[] = (string) $filters['table'];
		}

		$status = $filters['status'] ?? 'deleted';
		if ($status === 'deleted')
			$where[] = 'restored_at IS NULL';
		elseif ($status === 'restored')
			$where[] = 'restored_at IS NOT NULL';

		if (isset($filters['q']) && trim((string) $filters['q']) !== '') {
			$where[] = 'data LIKE ?';
			$params[] = '%' . trim((string) $filters['q']) . '%';
		}

		$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

		$count = $this->db->prepare("SELECT COUNT(*) FROM deleted_rows $whereSql");
		$count->execute($params);
		$total = (int) $count->fetchColumn();

		$page = max(1, $page);
		$perPage = max(1, min(200, $perPage));
		$offset = ($page - 1) * $perPage;

		$stmt = $this->db->prepare("SELECT * FROM deleted_rows $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset");
		$stmt->execute($params);

		return [
			'rows' => array_map(fn ($r) => $this->present($r), $stmt->fetchAll(PDO::FETCH_ASSOC)),
			'total' => $total,
			'tables' => $this->tables(),
		];
	}

	/** Tables that have something in the trash (not restored), with counts. */
	public function tables(): array
	{
		return $this->db->query("
			SELECT table_name, COUNT(*) AS count
			FROM deleted_rows
			WHERE restored_at IS NULL
			GROUP BY table_name
			ORDER BY table_name
		")->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get(int $id): array
	{
		$stmt = $this->db->prepare('SELECT * FROM deleted_rows WHERE id = ?');
		$stmt->execute([$id]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$row)
			throw new InvalidArgumentException("Registo #{$id} não encontrado no lixo.", 404);

		return $row;
	}

	/** Restores the given deleted_rows ids, all or nothing. Returns them, restored. */
	public function restore(array $ids): array
	{
		$ids = array_values(array_unique(array_map('intval', $ids)));
		if (!$ids)
			throw new InvalidArgumentException('Indique o que repor.', 400);

		return Database::transaction($this->db, function () use ($ids) {
			$entries = array_map(fn ($id) => $this->get($id), $ids);

			usort($entries, function ($a, $b) {
				$rank = array_flip(self::RESTORE_ORDER);
				return [$rank[$a['table_name']] ?? 999, (int) $a['id']]
					<=> [$rank[$b['table_name']] ?? 999, (int) $b['id']];
			});

			foreach ($entries as $entry) {
				if ($entry['restored_at'] !== null)
					throw new InvalidArgumentException("O registo #{$entry['id']} já foi reposto.", 409);

				$this->insertBack($entry);

				$this->db->prepare('UPDATE deleted_rows SET restored_at = CURRENT_TIMESTAMP WHERE id = ?')
					->execute([$entry['id']]);
			}

			return array_map(fn ($e) => $this->present($this->get((int) $e['id'])), $entries);
		});
	}

	private function insertBack(array $entry): void
	{
		$table = $entry['table_name'];

		// Only columns that still exist (one may have been dropped since).
		$columns = $this->db->query('SELECT name FROM pragma_table_info(' . $this->db->quote($table) . ')')
			->fetchAll(PDO::FETCH_COLUMN);
		if (!$columns)
			throw new InvalidArgumentException("A tabela {$table} já não existe.", 409);

		$data = array_intersect_key(json_decode($entry['data'], true) ?: [], array_flip($columns));
		if (!$data)
			throw new InvalidArgumentException("O registo #{$entry['id']} está vazio.", 409);

		$names = implode(', ', array_map(fn ($c) => '"' . str_replace('"', '""', $c) . '"', array_keys($data)));
		$marks = implode(', ', array_fill(0, count($data), '?'));

		try {
			$this->db->prepare("INSERT INTO \"{$table}\" ({$names}) VALUES ({$marks})")->execute(array_values($data));
		} catch (PDOException $e) {
			$message = $e->errorInfo[2] ?? $e->getMessage();
			if (str_starts_with((string) $message, 'UNIQUE constraint failed'))
				$message = 'Já existe um registo igual (o mesmo id ou nome). Pode já ter sido criado de novo.';
			throw new InvalidArgumentException("Não foi possível repor #{$entry['id']}: {$message}", 409);
		}
	}

	/** A deleted_rows row as sent to the browser: data decoded, hidden fields removed, names added. */
	private function present(array $row): array
	{
		$data = json_decode($row['data'], true) ?: [];
		foreach (self::HIDDEN_FIELDS as $field)
			unset($data[$field]);

		return [
			'id' => (int) $row['id'],
			'table_name' => $row['table_name'],
			'row_id' => $row['row_id'] !== null ? (int) $row['row_id'] : null,
			'data' => $data,
			'names' => $this->names($data),
			'deleted_at' => $row['deleted_at'],
			'restored_at' => $row['restored_at'],
		];
	}

	// Names for the ids a row points to, so the page can say "Óleo 5W30"
	// instead of "product_id 1". Only for rows that still exist.
	private function names(array $data): array
	{
		$lookups = [
			'product_id' => 'SELECT name FROM products WHERE id = ?',
			'user_id' => 'SELECT name FROM users WHERE id = ?',
			'client_id' => 'SELECT name FROM clients WHERE id = ?',
			'car_id' => 'SELECT plate FROM cars WHERE id = ?',
			'make_id' => 'SELECT name FROM makes WHERE id = ?',
			'model_id' => 'SELECT name FROM models WHERE id = ?',
			't_item_id' => 'SELECT name FROM tabled_items WHERE id = ?',
			't_action_id' => 'SELECT name FROM tabled_actions WHERE id = ?',
			'property_id' => 'SELECT name FROM tabled_properties WHERE id = ?',
			'product_type_id' => 'SELECT name FROM product_types WHERE id = ?',
			'service_type_id' => 'SELECT name FROM service_types WHERE id = ?',
		];

		$names = [];
		foreach ($lookups as $field => $sql) {
			if (!isset($data[$field]))
				continue;
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$data[$field]]);
			$value = $stmt->fetchColumn();
			if ($value !== false && $value !== null)
				$names[$field] = $value;
		}
		return $names;
	}
}
