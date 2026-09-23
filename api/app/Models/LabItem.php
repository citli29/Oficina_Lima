<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class LabItem
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getLabItemsByServiceWithFilter(int $serviceId, array $filters): array
	{
		$sql = "
		SELECT li.*, ti.name AS item_name, ti.i_class AS item_i_class
		FROM lab_items li
		LEFT JOIN tabled_items ti ON ti.id = li.t_item_id
		WHERE li.service_id = ?
		";

		$params = [];

		$rules = [
			't_item_id' => [
				'column' => 'li.t_item_id',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sql .= "ORDER BY li.id ASC";

		$stmt = $this->db->prepare($sql);
		array_unshift($params, $serviceId);

		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getLabItemBySidId(int $s_id, int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT li.*, ti.name AS item_name, ti.i_class AS item_i_class
			FROM lab_items li
			LEFT JOIN tabled_items ti ON ti.id = li.t_item_id
			WHERE li.service_id = ? AND li.id = ?
			");

		$stmt->execute([$s_id, $id]);

		return $stmt->fetch();
	}

	public function createLabItem(int $s_id, array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO lab_items
			(t_item_id, service_id)
			VALUES (?, ?)
			");

		$stmt->execute([
			!empty($data['t_item_id']) ? $data['t_item_id']: null,
			$s_id,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getLabItemBySidId($s_id, $newId);
	}

	public function deleteLabItemBySidId(int $s_id, int $id): bool|array
	{
		$labItem = $this->getLabItemBySidId($s_id, $id);

		if($labItem)
		{
			$stmt = $this->db->prepare("DELETE FROM lab_action_values WHERE l_item_id = ?");
			$stmt->execute([$labItem['id']]);

			$stmt = $this->db->prepare("DELETE FROM lab_property_values WHERE l_item_id = ?");
			$stmt->execute([$labItem['id']]);

			$stmt = $this->db->prepare("DELETE FROM lab_items WHERE id = ?");
			$stmt->execute([$labItem['id']]);
		}

		return $labItem;
	}
}
