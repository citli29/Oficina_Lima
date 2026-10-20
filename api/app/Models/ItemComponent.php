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

}
