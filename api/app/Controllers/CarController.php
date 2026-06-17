<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Car;
use App\Services\CarService;
use RuntimeException;

class CarController
{
	private CarService $service;
	public function __construct($db)
	{
		$model = new Car($db);
		$this->service = new CarService($model);
	}

	public function cars()
	{
		try {
			$filters = [
				'plate' => $_GET['plate'] ?? null,
				'model_name' => isset($_GET['model_name']) ? $_GET['model_name'] : null,
				'make_name' => isset($_GET['make_name']) ? $_GET['make_name'] : null,
				'year' => isset($_GET['year']) ? (int) $_GET['year'] : null,
				'month' => isset($_GET['month']) ? (int) $_GET['month'] : null,
			];
			header('Content-Type: application/json');
			echo json_encode($this->service->listCars($filters));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function car(int $id)
	{
		try {
			header('Content-Type: application/json');
			echo json_encode($this->service->listCar($id));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
	public function carsSearch(string $search)
	{
		try {
			header('Content-Type: application/json');
			echo json_encode($this->service->listCar(1));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
