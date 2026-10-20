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

	// Every instance of the items across all services (Gestão de Laboratório's
	// search), with the service it belongs to and all its property values.
	// $propertyFilters: [property_id => text] — each must match (LIKE) that
	// property's value; $filters['value'] matches any property's value.
	public function getLabItemsWithFilter(array $filters, array $propertyFilters, ?array $pagination = null): array
	{
		$sql = "
		SELECT
			li.id AS id,
			li.t_item_id AS t_item_id,
			ti.name AS item_name,
			ti.i_class AS item_i_class,
			li.service_id AS service_id,
			s.checkin_date AS checkin,
			s.checkout_date AS checkout,
			s.is_finished AS is_finished,
			cl.name AS client_name,
			c.plate AS car_plate,
			(
				SELECT json_group_array(json_object(
					'property_id', pv.property_id,
					'property_name', tp.name,
					'is_primary', tp.is_primary,
					'value', pv.value
				))
				FROM lab_property_values pv
				JOIN tabled_properties tp ON tp.id = pv.property_id
				WHERE pv.l_item_id = li.id AND pv.value IS NOT NULL AND pv.value != ''
			) AS properties_json
		FROM lab_items li
		JOIN services s ON s.id = li.service_id
		LEFT JOIN tabled_items ti ON ti.id = li.t_item_id
		LEFT JOIN clients cl ON cl.id = s.client_id
		LEFT JOIN cars c ON c.id = s.car_id
		WHERE 1 = 1
		";

		$params = [];

		$rules = [
			't_item_id' => [
				'column' => 'li.t_item_id',
				'operator' => '='
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		if (isset($filters['value']) && $filters['value'] !== '') {
			$sql .= " AND EXISTS (SELECT 1 FROM lab_property_values pv WHERE pv.l_item_id = li.id AND pv.value LIKE ?)";
			$params[] = "%{$filters['value']}%";
		}

		foreach ($propertyFilters as $propertyId => $value) {
			$sql .= " AND EXISTS (SELECT 1 FROM lab_property_values pv WHERE pv.l_item_id = li.id AND pv.property_id = ? AND pv.value LIKE ?)";
			$params[] = $propertyId;
			$params[] = "%{$value}%";
		}

		$total = null;

		if ($pagination !== null) {
			$total = Database::getTotalCount($this->db, $sql, $params);
		}

		// Newest services first.
		$sql .= " ORDER BY s.checkin_date DESC, li.service_id DESC, li.id ASC";

		if ($pagination !== null) {
			$sql = Database::applyPagination($sql, $params, $pagination['page'], $pagination['per_page']);
		}

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		$rows = array_map(function ($row) {
			$row['properties'] = json_decode($row['properties_json'] ?? '[]', true) ?: [];
			unset($row['properties_json']);
			return $row;
		}, $stmt->fetchAll());

		return [
			'rows' => $rows,
			'total' => $total,
		];
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
