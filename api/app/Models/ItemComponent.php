<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class ItemComponent
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getPropertiesWithFilter(array $filters, ?array $pagination = null): array
	{
		$sql = "
		SELECT pr.*, i.name AS item_name
		FROM tabled_properties pr
		LEFT JOIN tabled_items i ON pr.t_item_id = i.id
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'name' => [
				'column' => 'pr.name',
				'operator' => 'LIKE'
			],
			'item_name' => [
				'column' => 'i.name',
				'operator' => 'LIKE'
			],
			't_item_id' => [
				'column' => 'pr.t_item_id',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sql .= "ORDER BY i.name, pr.name ASC";

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

	public function getActionsWithFilter(array $filters, ?array $pagination = null): array
	{
		$sql = "
		SELECT a.*, i.name AS item_name
		FROM tabled_actions a
		LEFT JOIN tabled_items i ON a.t_item_id = i.id
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'name' => [
				'column' => 'a.name',
				'operator' => 'LIKE'
			],
			'item_name' => [
				'column' => 'i.name',
				'operator' => 'LIKE'
			],
			't_item_id' => [
				'column' => 'a.t_item_id',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sql .= "ORDER BY i.name, a.name ASC";

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

	public function getActionTabledValuesWithFilter(array $filters, ?array $pagination = null): array
	{
		$sql = "
		SELECT atv.*, a.name AS action_name
		FROM tabled_action_values atv
		LEFT JOIN tabled_actions a ON atv.t_action_id = a.id
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'value' => [
				'column' => 'atv.value',
				'operator' => 'LIKE'
			],
			'action_name' => [
				'column' => 'a.name',
				'operator' => 'LIKE'
			],
			't_action_id' => [
				'column' => 'atv.t_action_id',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sql .= "ORDER BY a.name, atv.value ASC";

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

	public function getPropertyById(int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT pr.*, i.name AS item_name
			FROM tabled_properties pr
			LEFT JOIN tabled_items i ON pr.t_item_id = i.id
			WHERE pr.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function getActionById(int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT a.*, i.name AS item_name
			FROM tabled_actions a
			LEFT JOIN tabled_items i ON a.t_item_id = i.id
			WHERE a.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function getActionTabledValueById(int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT atv.*, a.name AS action_name
			FROM tabled_action_values atv
			LEFT JOIN tabled_actions a ON atv.t_action_id = a.id
			WHERE atv.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function createProperty(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO tabled_properties
			(t_item_id, name, i_class, is_primary)
			VALUES (?, ?, ?, ?)
			");

		$stmt->execute([
			!empty($data['t_item_id']) ? $data['t_item_id']: null,
			!empty($data['name']) ? $data['name']: null,
			!empty($data['i_class']) ? $data['i_class']: null,
			!empty($data['is_primary']) ? 1 : 0,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getPropertyById($newId);
	}

	public function createAction(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO tabled_actions
			(t_item_id, name, i_class)
			VALUES (?, ?, ?)
			");

		$stmt->execute([
			!empty($data['t_item_id']) ? $data['t_item_id']: null,
			!empty($data['name']) ? $data['name']: null,
			!empty($data['i_class']) ? $data['i_class']: null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getActionById($newId);
	}

	public function createActionTabledValue(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO tabled_action_values
			(t_action_id, value, i_class)
			VALUES (?, ?, ?)
			");

		$stmt->execute([
			!empty($data['t_action_id']) ? $data['t_action_id']: null,
			!empty($data['value']) ? $data['value']: null,
			!empty($data['i_class']) ? $data['i_class']: null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getActionTabledValueById($newId);
	}

	public function updateProperty(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE tabled_properties
			SET t_item_id = ?, name = ?, i_class = ?, is_primary = ?
			WHERE id = ?
			");

		$stmt->execute([
			!empty($data['t_item_id']) ? $data['t_item_id']: null,
			!empty($data['name']) ? $data['name']: null,
			!empty($data['i_class']) ? $data['i_class']: null,
			!empty($data['is_primary']) ? 1 : 0,
			$id
		]);

		return $this->getPropertyById($id);
	}

	public function updateAction(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE tabled_actions
			SET t_item_id = ?, name = ?, i_class = ?
			WHERE id = ?
			");

		$stmt->execute([
			!empty($data['t_item_id']) ? $data['t_item_id']: null,
			!empty($data['name']) ? $data['name']: null,
			!empty($data['i_class']) ? $data['i_class']: null,
			$id
		]);

		return $this->getActionById($id);
	}

	public function updateActionTabledValue(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE tabled_action_values
			SET t_action_id = ?, value = ?, i_class = ?
			WHERE id = ?
			");

		$stmt->execute([
			!empty($data['t_action_id']) ? $data['t_action_id']: null,
			!empty($data['value']) ? $data['value']: null,
			!empty($data['i_class']) ? $data['i_class']: null,
			$id
		]);

		return $this->getActionTabledValueById($id);
	}

	public function deleteProperty(int $id): bool|array
	{
		$property = $this->getPropertyById($id);

		if($property)
		{
			$stmt = $this->db->prepare("DELETE FROM tabled_properties WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $property;
	}

	public function deleteAction(int $id): bool|array
	{
		$action = $this->getActionById($id);

		if($action)
		{
			$stmt = $this->db->prepare("DELETE FROM tabled_actions WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $action;
	}

	public function deleteActionTabledValue(int $id): bool|array
	{
		$action_tabled_value = $this->getActionTabledValueById($id);

		if($action_tabled_value)
		{
			$stmt = $this->db->prepare("DELETE FROM tabled_action_values WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $action_tabled_value;
	}
}
