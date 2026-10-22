<?php
declare(strict_types = 1);

namespace App\Services;

use App\Database\Database;
use App\Models\Schedule;
use App\Models\ScheduleLab;
use App\Models\Service;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ScheduleService
{
	private Schedule $scheduleModel;
	private Service $serviceModel;
	private ScheduleLab $scheduleLabModel;

	const MISSING_DESCRIPTION = 'A descrição da marcação é obrigatória, a não ser que seja de Laboratório com itens e todos tenham ações.';

	public function __construct(Schedule $scheduleModel, Service $serviceModel, ScheduleLab $scheduleLabModel)
	{
		$this->scheduleModel = $scheduleModel;
		$this->serviceModel = $serviceModel;
		$this->scheduleLabModel = $scheduleLabModel;
	}

	public function listSchedulesFree(): array
	{
		return $this->scheduleModel->getSchedulesFree();
	}
	public function listSchedules(array $filters, ?array $pagination = null, ?array $sort = null): array
	{
		return $this->scheduleModel->getScheduleWithFilter($filters, $pagination, $sort);
	}

	public function showSchedule(int $id):array
	{
		if(!$schedule = $this->scheduleModel->getScheduleById($id))
			throw new RuntimeException("Show Schedule [ID Not Found]: {$id}.",404);
		return $schedule;
	}

	// `lab_items` (optional): [{t_item_id, action_ids: [...]}, ...] — the
	// planned lab items, created with the marcação so they count for the
	// description rule (see ScheduleLab::isMissingDescription).
	public function createSchedule(array $data): array
	{
		try
		{
			return Database::transaction($this->scheduleLabModel->getDb(), function () use ($data) {
				$schedule = $this->scheduleModel->createSchedule($data);

				foreach (($data['lab_items'] ?? []) as $labItem) {
					$this->scheduleLabModel->createLabItemWithActions($schedule['id'], $labItem);
				}

				if ($this->scheduleLabModel->isMissingDescription($schedule['id']))
					throw new InvalidArgumentException(self::MISSING_DESCRIPTION, 400);

				return $schedule;
			});
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	// One transaction: a Laboratório service (type 2, as in the service
	// triggers) is created together with the marcação's planned lab
	// items/actions — never half-copied, so a retry can't leave a broken
	// first service behind.
	public function createServiceFromSchedule(int $id, array $data): array
	{
		try
		{
			return Database::transaction($this->scheduleLabModel->getDb(), function () use ($id, $data) {
				$schedule = $this->scheduleModel->getScheduleById($id);
				if(!$schedule)
				throw new InvalidArgumentException("Create Service From Schedule [Invalid ID]: {$id}.",400);
				$service = $this->serviceModel->createServiceFromSchedule($id,$data,$schedule);
				if(!$service) 
				throw new InvalidArgumentException("Update Service [Invalid ID]: {$id}.",400);

				if ((int)$service['service_type_id'] === 2)
					$this->scheduleLabModel->copyToService($id, (int)$service['id']);

				return $service;
			});
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}

	}

	public function updateSchedule(int $id, array $data): array
	{
		try
		{
			return Database::transaction($this->scheduleLabModel->getDb(), function () use ($id, $data) {
				$schedule = $this->scheduleModel->updateSchedule($id,$data);
				if(!$schedule) 
				throw new InvalidArgumentException("Update Schedule [Invalid ID]: {$id}.",400);

				if ($this->scheduleLabModel->isMissingDescription($id))
					throw new InvalidArgumentException(self::MISSING_DESCRIPTION, 400);

				return $schedule;
			});
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteSchedule(int $id): array
	{
		try
		{
			if(!$schedule = $this->scheduleModel->deleteSchedule($id))
				throw new InvalidArgumentException("Delete Schedule [Invalid ID]: {$id}.", 404);
			return $schedule;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

}
