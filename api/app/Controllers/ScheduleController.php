<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Schedule;
use App\Models\Service;
use App\Services\ScheduleService;
use InvalidArgumentException;
use RuntimeException;
use PDO;
require_once __DIR__ . "./../../../utils/normalize.php";
require_once __DIR__ . "./../../../utils/util.php";

class ScheduleController
{
	private ScheduleService $service;
	public function __construct(PDO $db)
	{
		$model = new Schedule($db);
		$s_model = new Service($db);
		$this->service = new ScheduleService($model,$s_model);
	}

	public function getSchedulesFree(): void
	{
		try{
			$schedule_list = $this->service->listSchedulesFree();

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
	public function getSchedules(): void
	{
		try{
			$filters = [
				'date' => isset($_GET['date']) ? $_GET['date'] : null,
				'car_plate' => isset($_GET['car_plate']) ? normalize($_GET['car_plate']) : null,
				'car_model' => isset($_GET['car_model']) ? normalize($_GET['car_model']) : null,
				'car_make' => isset($_GET['car_make']) ? normalize($_GET['car_make']): null,
				'client_id' => isset($_GET['client_id']) ? $_GET['client_id'] : null,
				'client_name' => isset($_GET['client_name']) ? normalize($_GET['client_name']): null,
				'service_type_id' => isset($_GET['service_type_id']) && $_GET['service_type_id'] !== '' ? (int)$_GET['service_type_id'] : null,
				'status' => isset($_GET['status']) ? $_GET['status'] : null,
				'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : null,
				'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : null,
			];
			$pagination = parsePagination($_GET);
			$sort = parseSort($_GET);

			$result = $this->service->listSchedules($filters, $pagination, $sort);

			$response = [
				'success' => true,
				'schedule_list' => $result['rows'],
			];

			if ($pagination !== null) {
				$response['pagination'] = [
					'page' => $pagination['page'],
					'per_page' => $pagination['per_page'],
					'total' => $result['total'],
					'total_pages' => (int) ceil($result['total'] / $pagination['per_page']),
				];
			}

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode($response);
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
