<?php

namespace App\Models;

use PDO;

// Lab items and actions planned on a marcação. Only a plan: no notes, done
// check, properties or locks. "Criar Serviço" copies them into the new
// service's lab draft. Deleting the marcação (or the item type / action in
// Gestão de Laboratório) deletes them too (ON DELETE CASCADE).
class ScheduleLab
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getDb(): PDO
	{
		return $this->db;
	}

	// The description can only be left empty on a Laboratório marcação
	// (type 2, as in the service triggers) with at least one lab item and
	// every item having at least one action.
	public function isMissingDescription(int $scheduleId): bool
	{
		$stmt = $this->db->prepare("
			SELECT
				TRIM(COALESCE(s.description, '')) = ''
				AND NOT (
					COALESCE(s.service_type_id, 0) = 2
					AND EXISTS (SELECT 1 FROM schedule_lab_items sli WHERE sli.schedule_id = s.id)
					AND NOT EXISTS (
						SELECT 1 FROM schedule_lab_items sli
						WHERE sli.schedule_id = s.id
						AND NOT EXISTS (SELECT 1 FROM schedule_lab_action_values slav WHERE slav.l_item_id = sli.id)
					)
				) AS missing
			FROM schedules s
			WHERE s.id = ?
			");

		$stmt->execute([$scheduleId]);

		return (bool) $stmt->fetchColumn();
	}

	public function getLabItemsBySchedule(int $scheduleId): array
	{
		$stmt = $this->db->prepare("
			SELECT sli.*, ti.name AS item_name, ti.i_class AS item_i_class
			FROM schedule_lab_items sli
			LEFT JOIN tabled_items ti ON ti.id = sli.t_item_id
			WHERE sli.schedule_id = ?
			ORDER BY sli.id ASC
			");

		$stmt->execute([$scheduleId]);

		return $stmt->fetchAll();
	}

	public function getLabItemBySidId(int $s_id, int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT sli.*, ti.name AS item_name, ti.i_class AS item_i_class
			FROM schedule_lab_items sli
			LEFT JOIN tabled_items ti ON ti.id = sli.t_item_id
			WHERE sli.schedule_id = ? AND sli.id = ?
			");

		$stmt->execute([$s_id, $id]);

		return $stmt->fetch();
	}

	public function createLabItem(int $s_id, array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO schedule_lab_items
			(schedule_id, t_item_id)
			VALUES (?, ?)
			");

		$stmt->execute([
			$s_id,
			!empty($data['t_item_id']) ? $data['t_item_id']: null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getLabItemBySidId($s_id, $newId);
	}

	// One item with its actions in one go — so it never exists without them
	// (an item without actions doesn't count for the description rule).
	// $labItem: {t_item_id, action_ids: [...]}.
	public function createLabItemWithActions(int $s_id, array $labItem): array
	{
		$created = $this->createLabItem($s_id, $labItem);

		foreach (($labItem['action_ids'] ?? []) as $actionId) {
			$this->createLabActionValue($s_id, ['l_item_id' => $created['id'], 't_action_id' => $actionId]);
		}

		return $created;
	}

	// "Criar Serviço": the marcação's planned items/actions become the new
	// service's lab items/actions (the marcação keeps its own copy).
	public function copyToService(int $scheduleId, int $serviceId): void
	{
		$insertItem = $this->db->prepare("INSERT INTO lab_items (t_item_id, service_id) VALUES (?, ?)");
		$insertActions = $this->db->prepare("
			INSERT INTO lab_action_values (l_item_id, t_action_id, note, is_done)
			SELECT ?, slav.t_action_id, NULL, 0
			FROM schedule_lab_action_values slav
			WHERE slav.l_item_id = ?
			ORDER BY slav.id ASC
			");

		foreach ($this->getLabItemsBySchedule($scheduleId) as $plannedItem) {
			$insertItem->execute([$plannedItem['t_item_id'], $serviceId]);
			$insertActions->execute([(int)$this->db->lastInsertId(), $plannedItem['id']]);
		}
	}

	// Its actions go with it (ON DELETE CASCADE).
	public function deleteLabItemBySidId(int $s_id, int $id): bool|array
	{
		$labItem = $this->getLabItemBySidId($s_id, $id);

		if($labItem)
		{
			$stmt = $this->db->prepare("DELETE FROM schedule_lab_items WHERE id = ?");
			$stmt->execute([$labItem['id']]);
		}

		return $labItem;
	}

	public function getLabActionValuesBySchedule(int $scheduleId): array
	{
		$stmt = $this->db->prepare("
			SELECT
				slav.id AS id,
				sli.schedule_id AS schedule_id,
				slav.l_item_id AS l_item_id,
				slav.t_action_id AS t_action_id,
				a.name AS action_name,
				a.i_class AS action_i_class,
				sli.t_item_id AS t_item_id,
				i.name AS item_name
			FROM schedule_lab_action_values slav
			JOIN schedule_lab_items sli ON sli.id = slav.l_item_id
			LEFT JOIN tabled_actions a ON a.id = slav.t_action_id
			LEFT JOIN tabled_items i ON i.id = sli.t_item_id
			WHERE sli.schedule_id = ?
			ORDER BY slav.id ASC
			");

		$stmt->execute([$scheduleId]);

		return $stmt->fetchAll();
	}

	public function getLabActionValueBySidId(int $s_id, int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT
				slav.id AS id,
				sli.schedule_id AS schedule_id,
				slav.l_item_id AS l_item_id,
				slav.t_action_id AS t_action_id,
				a.name AS action_name,
				a.i_class AS action_i_class,
				sli.t_item_id AS t_item_id,
				i.name AS item_name
			FROM schedule_lab_action_values slav
			JOIN schedule_lab_items sli ON sli.id = slav.l_item_id
			LEFT JOIN tabled_actions a ON a.id = slav.t_action_id
			LEFT JOIN tabled_items i ON i.id = sli.t_item_id
			WHERE sli.schedule_id = ? AND slav.id = ?
			");

		$stmt->execute([$s_id, $id]);

		return $stmt->fetch();
	}

	// Only into an item of this marcação; false when l_item_id isn't one.
	public function createLabActionValue(int $s_id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			INSERT INTO schedule_lab_action_values
			(l_item_id, t_action_id)
			SELECT sli.id, ?
			FROM schedule_lab_items sli
			WHERE sli.id = ? AND sli.schedule_id = ?
			");

		$stmt->execute([
			!empty($data['t_action_id']) ? $data['t_action_id']: null,
			!empty($data['l_item_id']) ? $data['l_item_id']: null,
			$s_id,
		]);

		if($stmt->rowCount() === 0) return false;

		$newId = (int)$this->db->lastInsertId();

		return $this->getLabActionValueBySidId($s_id, $newId);
	}

	public function deleteLabActionValueBySidId(int $s_id, int $id): bool|array
	{
		$labActionValue = $this->getLabActionValueBySidId($s_id, $id);

		if($labActionValue)
		{
			$stmt = $this->db->prepare("DELETE FROM schedule_lab_action_values WHERE id = ?");
			$stmt->execute([$labActionValue['id']]);
		}

		return $labActionValue;
	}
}
