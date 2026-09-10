<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Schedule;
use App\Models\Service;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ScheduleService
{
	private Schedule $scheduleModel;
	private Service $serviceModel;

	public function __construct(Schedule $scheduleModel, Service $serviceModel)
	{
		$this->scheduleModel = $scheduleModel;
		$this->serviceModel = $serviceModel;
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

	public function createSchedule(array $data): array
	{
		try
		{
			return $this->scheduleModel->createSchedule($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function createServiceFromSchedule(int $id, array $data): array
	{
		try
		{
			$schedule = $this->scheduleModel->getScheduleById($id);
			if(!$schedule)
			throw new InvalidArgumentException("Create Service From Schedule [Invalid ID]: {$id}.",400);
			$service = $this->serviceModel->createServiceFromSchedule($id,$data,$schedule);
			if(!$service) 
			throw new InvalidArgumentException("Update Service [Invalid ID]: {$id}.",400);
			return $service;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}

	}

	public function updateSchedule(int $id, array $data): array
	{
		try
		{
			$schedule = $this->scheduleModel->updateSchedule($id,$data);
			if(!$schedule) 
			throw new InvalidArgumentException("Update Schedule [Invalid ID]: {$id}.",400);
			return $schedule;
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
