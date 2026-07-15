<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Schedule;
use App\Models\Service;
use App\Services\ScheduleService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class ScheduleController
{
	private ScheduleService $service;
	public function __construct(PDO $db)
	{
		$model = new Schedule($db);
		$s_model = new Service($db);
		$this->service = new ScheduleService($model,$s_model);
	}

	public function getSchedules(): void
	{
		try{
			$filters = [
				'date' => isset($_GET['date']) ? $_GET['date'] : null,
				'car_plate' => isset($_GET['car_plate']) ? $_GET['car_plate'] : null,
				'car_model' => isset($_GET['car_model']) ? $_GET['car_model'] : null,
				'car_make' => isset($_GET['car_make']) ? $_GET['car_make'] : null,
				'client_id' => isset($_GET['client_id']) ? $_GET['client_id'] : null,
				'client_name' => isset($_GET['client_name']) ? $_GET['client_name'] : null,
			];
			$schedule_list = $this->service->listSchedules($filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'schedule_list'=>$schedule_list
			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getSchedule(int $id):void
	{
		try {
			$schedule = $this->service->showSchedule($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'schedule'=>$schedule
			]);
		} catch (RuntimeException$e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postSchedules():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$schedule = $this->service->createSchedule($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'schedule'=>$schedule
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteSchedule(int $id):void
	{
		try {

			$schedule = $this->service->deleteSchedule($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'schedule' => $schedule
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putSchedule(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$schedule = $this->service->updateSchedule($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'schedule'=>$schedule
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postCreateServiceFromSchedule(int $id){
		try{
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$service = $this->service->createServiceFromSchedule($id,$data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'service'=>$service
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
