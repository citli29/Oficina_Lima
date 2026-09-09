<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Service;
use App\Services\ServiceService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ .'/../../../utils/normalize.php';
require_once __DIR__ .'/../../../utils/util.php';

class ServiceController
{
	private ServiceService $service;
	public function __construct(PDO $db)
	{
		$model = new Service($db);
		$this->service = new ServiceService($model);
	}

	public function getServices(): void
	{
		try{
			$filters = [
				'client_name' => isset($_GET['client_name']) ? normalize($_GET['client_name']) : null,
				'checkin' => isset($_GET['checkin']) ? $_GET['checkin'] : null,
				'schedule_id' => isset($_GET['schedule_id']) ? $_GET['schedule_id'] : null,
				'checkout' => isset($_GET['checkout']) ? $_GET['checkout'] : null,
				'car_plate' => isset($_GET['car_plate']) ? normalize($_GET['car_plate']) : null,
				'car_model' => isset($_GET['car_model']) ? normalize($_GET['car_model']) : null,
				'car_make' => isset($_GET['car_make']) ? normalize($_GET['car_make']) : null,
				'service_type_id' => isset($_GET['service_type_id']) ? $_GET['service_type_id'] : null,
				'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : null,
				'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : null,
				'status' => isset($_GET['status']) ? $_GET['status'] : null,
				'q' => isset($_GET['q']) ? normalize($_GET['q']) : null,
			];
			$pagination = parsePagination($_GET);
			$sort = parseSort($_GET);

			$result = $this->service->listServices($filters, $pagination, $sort);

			$response = [
				'success' => true,
				'service_list' => $result['rows'],
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

	public function getServiceTypes(): void
	{
		try{
			$filters = [
				'name' => isset($_GET['name']) ? normalize($_GET['name']) : null,
			];

			$pagination = parsePagination($_GET);

			$result = $this->service->listServiceTypes($filters, $pagination);

			$response = [
				'success' => true,
				'service_type_list' => $result['rows'],
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

	public function getService(int $id):void
	{
		try {
			$service = $this->service->showService($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'service'=>$service
			]);
		} catch (RuntimeException$e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postServices():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$service = $this->service->createService($data);

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

	public function deleteService(int $id):void
	{
		try {

			$service = $this->service->deleteService($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'service' => $service
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putService(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$service = $this->service->updateService($id, $data);

			http_response_code(200);
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
