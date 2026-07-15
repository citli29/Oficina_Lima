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

	public function listSchedules(array $filters): array
	{
		return $this->scheduleModel->getScheduleWithFilter($filters);
	}

	public function showSchedule(int $id):array
	{
		if(!$schedule = $this->scheduleModel->getScheduleById($id))
			throw new RuntimeException("Show Schedule [ID Not Found]: {$id}.",404);
		return $schedule;
	}

	public function createSchedule(array $data): array
	{
		if(empty($data['date']) || empty($data['description'])) {
			throw new InvalidArgumentException("Create Schedule [Arguments Required]: Date [YYYY-MM-DD], Description.", 400);
		}

		try
		{
			return $this->scheduleModel->createSchedule($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Schedule [Argument Constraints]: Date [YYYY-MM-DD] must be provided. Description must be provided. [{$e->errorInfo[2]}]", 400);
		}
	}

	public function createServiceFromSchedule(int $id, array $data): array
	{
		try
		{
			$schedule = $this->scheduleModel->getScheduleById($id);
			if(!$schedule) 
			throw new InvalidArgumentException("Create Service From Schedule [Invalid ID]: {$id}.",400);
			if(empty($data['client_id']) && empty($schedule['client_id'])) {
				throw new InvalidArgumentException("Create Service From Schedule [Argument Required]: Client ID.",400);
			}
			$service = $this->serviceModel->createServiceFromSchedule($id,$data,$schedule);
			if(!$service) 
			throw new InvalidArgumentException("Update Service [Invalid ID]: {$id}.",400);
			return $service;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Service [Argument Required]: Client ID must be provided. [{$e->errorInfo[2]}]",400);
		}

	}

	public function updateSchedule(int $id, array $data): array
	{
		if(empty($data['date']) || empty($data['description'])) {
			throw new InvalidArgumentException("Update Schedule[Argument Required]: Date [YYYY-MM-DD], Description.",400);
		}
		try
		{
			$schedule = $this->scheduleModel->updateSchedule($id,$data);
			if(!$schedule) 
			throw new InvalidArgumentException("Update Schedule [Invalid ID]: {$id}.",400);
			return $schedule;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Schedule [Argument Required]: Date [YYYY-MM-DD] must be provided. Description must be provided. [{$e->errorInfo[2]}]",400);
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
			throw new InvalidArgumentException("Delete Schedule [Error]: [{$e->errorInfo[2]}]", 409);
		}
	}

}
