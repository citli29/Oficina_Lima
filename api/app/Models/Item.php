<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Item
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getItems(?array $pagination = null): array
	{
		$sql = "SELECT * FROM tabled_items ORDER BY name ASC";

		$params = [];

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

	public function getItemById(int $id): bool|array
	{
		$stmt = $this->db->query("
			SELECT *
			FROM tabled_items
			WHERE id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function updateItem(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE tabled_items
			SET name = ? , i_class = ?
			WHERE id = ?
			");

		$stmt->execute([
			!empty($data['name']) ? $data['name']: null,
			!empty($data['i_class']) ? $data['i_class']: null,
			$id
		]);

		return $this->getItemById($id);
	}

	public function createItem(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO tabled_items
			(name, i_class)
			VALUES (?, ?)
			");

		$stmt->execute([
			!empty($data['name']) ?$data['name']: null,
			!empty($data['i_class']) ?$data['i_class']: null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getItemById($newId);
	}

	public function deleteItem(int $id): bool|array
	{
		$item = $this->getItemById($id);

		if($item)
		{
			$stmt = $this->db->prepare("DELETE FROM tabled_properties WHERE t_item_id = ?");
			$stmt->execute([$id]);

			$stmt = $this->db->prepare("DELETE FROM tabled_items WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $item;
	}
}
