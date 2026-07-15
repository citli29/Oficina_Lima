<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Service;
use App\Services\ServiceService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

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
				'client_name' => isset($_GET['client_name']) ? $_GET['client_name'] : null,
				'checkin' => isset($_GET['checkin']) ? $_GET['checkin'] : null,
				'checkout' => isset($_GET['checkout']) ? $_GET['checkout'] : null,
				'car_plate' => isset($_GET['car_plate']) ? $_GET['car_plate'] : null,
				'car_model' => isset($_GET['car_model']) ? $_GET['car_model'] : null,
				'car_make' => isset($_GET['car_make']) ? $_GET['car_make'] : null,
			];
			$service_list = $this->service->listServices($filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'service_list'=>$service_list
			]);
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
