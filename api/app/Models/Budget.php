<?php

namespace App\Models;

use PDO;

// Orçamentos of a service (budgets) and the products on each one
// (budget_products). Every row is addressed by its real id, always checked
// against the service in the URL so one service can't touch another's.
// Deleting a budget deletes its products too (trigger d_budget_products_cascade).
class Budget
{
	private PDO $db;

	const PRODUCT_SELECT = "
		SELECT
			bp.id AS id,
			bp.budget_id AS budget_id,
			bp.product_id AS product_id,
			bp.quantity AS quantity,
			p.name AS product_name,
			p.reference AS product_reference,
			p.product_type_id AS product_type_id,
			pt.name AS product_type_name
		FROM budget_products bp
		JOIN budgets b ON b.id = bp.budget_id
		LEFT JOIN products p ON p.id = bp.product_id
		LEFT JOIN product_types pt ON pt.id = p.product_type_id
		";

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getDb(): PDO
	{
		return $this->db;
	}

	/* ---------- budgets ---------- */

	// Each budget comes with its products under 'products'.
	public function getBudgetsByService(int $s_id): array
	{
		$stmt = $this->db->prepare("SELECT * FROM budgets WHERE service_id = ? ORDER BY id ASC");
		$stmt->execute([$s_id]);
		$budgets = $stmt->fetchAll();

		$stmt = $this->db->prepare(self::PRODUCT_SELECT . " WHERE b.service_id = ? ORDER BY bp.id ASC");
		$stmt->execute([$s_id]);

		$productsByBudget = [];
		foreach ($stmt->fetchAll() as $product)
			$productsByBudget[$product['budget_id']][] = $product;

		foreach ($budgets as &$budget)
			$budget['products'] = $productsByBudget[$budget['id']] ?? [];

		return $budgets;
	}

	public function getBudgetBySidId(int $s_id, int $id): bool|array
	{
		$stmt = $this->db->prepare("SELECT * FROM budgets WHERE service_id = ? AND id = ?");
		$stmt->execute([$s_id, $id]);
		$budget = $stmt->fetch();

		if ($budget)
			$budget['products'] = $this->getProductsByBudget($s_id, $id);

		return $budget;
	}

	public function createBudget(int $s_id, array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO budgets
			(service_id, title, note, done)
			VALUES (?, ?, ?, ?)
			");

		$stmt->execute([
			$s_id,
			$data['title'] ?? '',
			$data['note'] ?? '',
			$data['done'] ?? 0,
		]);

		return $this->getBudgetBySidId($s_id, (int)$this->db->lastInsertId());
	}

	// Only the fields present in $data change (title, note, done).
	public function updateBudgetBySidId(int $s_id, int $id, array $data): bool|array
	{
		$budget = $this->getBudgetBySidId($s_id, $id);
		if (!$budget) return false;

		$sets = [];
		$params = [];
		foreach (['title', 'note', 'done'] as $field) {
			if (array_key_exists($field, $data)) {
				$sets[] = "{$field} = ?";
				$params[] = $data[$field];
			}
		}

		if ($sets) {
			$params[] = $budget['id'];
			$stmt = $this->db->prepare("UPDATE budgets SET " . implode(', ', $sets) . " WHERE id = ?");
			$stmt->execute($params);
		}

		return $this->getBudgetBySidId($s_id, $id);
	}

	// Its products go with it (trigger d_budget_products_cascade).
	public function deleteBudgetBySidId(int $s_id, int $id): bool|array
	{
		$budget = $this->getBudgetBySidId($s_id, $id);

		if ($budget) {
			$stmt = $this->db->prepare("DELETE FROM budgets WHERE id = ?");
			$stmt->execute([$budget['id']]);
		}

		return $budget;
	}

	/* ---------- budget products ---------- */

	public function getProductsByBudget(int $s_id, int $b_id): array
	{
		$stmt = $this->db->prepare(self::PRODUCT_SELECT . " WHERE b.service_id = ? AND bp.budget_id = ? ORDER BY bp.id ASC");
		$stmt->execute([$s_id, $b_id]);

		return $stmt->fetchAll();
	}

	public function getProductBySidBidId(int $s_id, int $b_id, int $id): bool|array
	{
		$stmt = $this->db->prepare(self::PRODUCT_SELECT . " WHERE b.service_id = ? AND bp.budget_id = ? AND bp.id = ?");
		$stmt->execute([$s_id, $b_id, $id]);

		return $stmt->fetch();
	}

	// Only into a budget of this service; false when b_id isn't one.
	public function createProduct(int $s_id, int $b_id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			INSERT INTO budget_products
			(budget_id, product_id, quantity)
			SELECT b.id, ?, ?
			FROM budgets b
			WHERE b.id = ? AND b.service_id = ?
			");

		$stmt->execute([
			!empty($data['product_id']) ? $data['product_id'] : null,
			$data['quantity'] ?? 1,
			$b_id,
			$s_id,
		]);

		if ($stmt->rowCount() === 0) return false;

		return $this->getProductBySidBidId($s_id, $b_id, (int)$this->db->lastInsertId());
	}

	public function updateProductBySidBidId(int $s_id, int $b_id, int $id, array $data): bool|array
	{
		$product = $this->getProductBySidBidId($s_id, $b_id, $id);
		if (!$product) return false;

		if (array_key_exists('quantity', $data)) {
			$stmt = $this->db->prepare("UPDATE budget_products SET quantity = ? WHERE id = ?");
			$stmt->execute([$data['quantity'], $product['id']]);
		}

		return $this->getProductBySidBidId($s_id, $b_id, $id);
	}

	public function deleteProductBySidBidId(int $s_id, int $b_id, int $id): bool|array
	{
		$product = $this->getProductBySidBidId($s_id, $b_id, $id);

		if ($product) {
			$stmt = $this->db->prepare("DELETE FROM budget_products WHERE id = ?");
			$stmt->execute([$product['id']]);
		}

		return $product;
	}

	// "AA-00-BB Opel Corsa" for the service's car ('' if none), same as the
	// service notification triggers write.
	public function getServiceCarLabel(int $s_id): string
	{
		$stmt = $this->db->prepare("
			SELECT TRIM(COALESCE(c.plate, '') || ' ' || COALESCE(mk.name, '') || ' ' || COALESCE(m.name, ''))
			FROM services s
			LEFT JOIN cars c ON c.id = s.car_id
			LEFT JOIN makes mk ON mk.id = c.make_id
			LEFT JOIN models m ON m.id = c.model_id
			WHERE s.id = ?
			");
		$stmt->execute([$s_id]);

		return preg_replace('/\s+/', ' ', (string) $stmt->fetchColumn());
	}

	/* ---------- all services (Orçamentos page) ---------- */

	// Budgets of every service with the service's car/client, newest first
	// unless $sort says otherwise, each with its products. Filters: done
	// (true/false), service_finished (true/false), from (created_at >=, UTC
	// 'YYYY-MM-DD HH:MM:SS'), q (title, plate, make, model, client, service
	// id), id. $sort = parseSort() result.
	const ALL_BUDGETS_SORTABLE = [
		'id' => 'id',
		'title' => 'title COLLATE NOCASE',
		'service_id' => 'service_id',
		'car_plate' => 'car_plate COLLATE NOCASE',
		'client_name' => 'client_name COLLATE NOCASE',
		'product_count' => 'product_count',
		'done' => 'done',
		'created_at' => 'created_at',
	];

	public function getAllBudgetsWithFilter(array $filters, ?array $pagination = null, ?array $sort = null): array
	{
		$sql = "
			SELECT * FROM (
				SELECT
					b.id AS id,
					b.title AS title,
					b.note AS note,
					b.done AS done,
					b.service_id AS service_id,
					b.created_at AS created_at,
					s.is_finished AS service_is_finished,
					c.plate AS car_plate,
					ma.name AS car_make,
					mo.name AS car_model,
					cl.name AS client_name,
					(SELECT COUNT(*) FROM budget_products bp2 WHERE bp2.budget_id = b.id) AS product_count
				FROM budgets b
				LEFT JOIN services s ON s.id = b.service_id
				LEFT JOIN cars c ON c.id = s.car_id
				LEFT JOIN models mo ON mo.id = c.model_id
				LEFT JOIN makes ma ON ma.id = COALESCE(mo.make_id, c.make_id)
				LEFT JOIN clients cl ON cl.id = s.client_id
			)
			WHERE 1 = 1
		";
		$params = [];

		if (isset($filters['done'])) {
			$sql .= " AND done = ?";
			$params[] = $filters['done'] ? 1 : 0;
		}

		if (isset($filters['service_finished'])) {
			$sql .= $filters['service_finished']
				? " AND service_is_finished = 1"
				: " AND (service_is_finished = 0 OR service_is_finished IS NULL)";
		}

		if (!empty($filters['from'])) {
			$sql .= " AND created_at >= ?";
			$params[] = $filters['from'];
		}

		if (!empty($filters['id'])) {
			$sql .= " AND id = ?";
			$params[] = (int) $filters['id'];
		}

		if (!empty($filters['q'])) {
			// Plates match with or without dashes/spaces ("31GT98" finds "31-GT-98").
			$sql .= " AND (title LIKE ? OR car_make LIKE ? OR car_model LIKE ? OR client_name LIKE ?
				OR REPLACE(REPLACE(UPPER(car_plate), '-', ''), ' ', '') LIKE ?
				OR CAST(service_id AS TEXT) = ?)";
			$q = (string) $filters['q'];
			$search = '%' . $q . '%';
			$plate = '%' . str_replace(['-', ' '], '', mb_strtoupper($q, 'UTF-8')) . '%';
			array_push($params, $search, $search, $search, $search, $plate, $q);
		}

		$sql = \App\Database\Database::applySort(
			$sql, self::ALL_BUDGETS_SORTABLE, $sort['column'] ?? null, $sort['direction'] ?? 'ASC', 'created_at DESC, id DESC', 'id'
		);

		$total = null;
		if ($pagination !== null) {
			$total = \App\Database\Database::getTotalCount($this->db, $sql, $params);
			$sql = \App\Database\Database::applyPagination($sql, $params, $pagination['page'], $pagination['per_page']);
		}

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		$budgets = $stmt->fetchAll();

		// Products of just this page's budgets, in one query.
		if ($budgets) {
			$ids = array_column($budgets, 'id');
			$placeholders = implode(',', array_fill(0, count($ids), '?'));
			$stmt = $this->db->prepare(self::PRODUCT_SELECT . " WHERE bp.budget_id IN ({$placeholders}) ORDER BY bp.id ASC");
			$stmt->execute($ids);

			$productsByBudget = [];
			foreach ($stmt->fetchAll() as $product)
				$productsByBudget[$product['budget_id']][] = $product;

			foreach ($budgets as &$budget)
				$budget['products'] = $productsByBudget[$budget['id']] ?? [];
			unset($budget);
		}

		return ['rows' => $budgets, 'total' => $total];
	}
}
