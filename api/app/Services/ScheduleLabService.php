<?php
declare(strict_types = 1);

namespace App\Services;

use App\Database\Database;
use App\Models\ScheduleLab;
use InvalidArgumentException;
use PDOException;

class ScheduleLabService
{
	private ScheduleLab $scheduleLabModel;

	const MISSING_DESCRIPTION = 'A marcação não tem descrição, por isso precisa de itens de laboratório e todos com ações.';

	public function __construct(ScheduleLab $scheduleLabModel)
	{
		$this->scheduleLabModel = $scheduleLabModel;
	}

	// Runs $write, then refuses (rolls back) if it left the marcação with
	// no description and no lab items-with-actions to stand in for it.
	private function keepingDescriptionRule(int $s_id, callable $write): mixed
	{
		return Database::transaction($this->scheduleLabModel->getDb(), function () use ($s_id, $write) {
			$result = $write();

			if ($this->scheduleLabModel->isMissingDescription($s_id))
				throw new InvalidArgumentException(self::MISSING_DESCRIPTION, 400);

			return $result;
		});
	}

	public function listItems(int $s_id): array
	{
		return $this->scheduleLabModel->getLabItemsBySchedule($s_id);
	}

	public function createItem(int $s_id, array $data): array
	{
		try
		{
			return $this->keepingDescriptionRule($s_id, fn() => $this->scheduleLabModel->createLabItemWithActions($s_id, $data));
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteItem(int $s_id, int $id): array
	{
		try
		{
			return $this->keepingDescriptionRule($s_id, function () use ($s_id, $id) {
				if(!$labItem = $this->scheduleLabModel->deleteLabItemBySidId($s_id, $id))
					throw new InvalidArgumentException("Delete Schedule Lab Item [Invalid ID]: {$s_id} - {$id}.", 404);
				return $labItem;
			});
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function listActionValues(int $s_id): array
	{
		return $this->scheduleLabModel->getLabActionValuesBySchedule($s_id);
	}

	public function createActionValue(int $s_id, array $data): array
	{
		try
		{
			if(!$labActionValue = $this->scheduleLabModel->createLabActionValue($s_id, $data))
				throw new InvalidArgumentException("Create Schedule Lab Action Value [Invalid Item]: {$s_id}.", 400);
			return $labActionValue;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteActionValue(int $s_id, int $id): array
	{
		try
		{
			return $this->keepingDescriptionRule($s_id, function () use ($s_id, $id) {
				if(!$labActionValue = $this->scheduleLabModel->deleteLabActionValueBySidId($s_id, $id))
					throw new InvalidArgumentException("Delete Schedule Lab Action Value [Invalid ID]: {$s_id} - {$id}.", 404);
				return $labActionValue;
			});
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}
}
