<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Car;
use App\Services\CarService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class CarController
{
	private CarService $service;
	public function __construct(PDO $db)
	{
		$model = new Car($db);
		$this->service = new CarService($model);
	}

	public function cars():void
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

	public function car(int $id):void
	{
		try {
			header('Content-Type: application/json');
			echo json_encode($this->service->showCar($id));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function create():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$car = $this->service->createCar($input);

			header('Content-Type: application/json');
			echo json_encode($car);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
	public function delete(int $id):void
	{
		try {
			if($id < 0) throw new InvalidArgumentException("Invalid ID");
			$car = $this->service->deleteCar($id);

			header('Content-Type: application/json');
			echo json_encode([
			    'success' => true,
			    'car' => $car
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}

	}
	public function update(int $id):void
	{
		try {
			if($id < 0) throw new InvalidArgumentException("Invalid ID");
			$data = json_decode(file_get_contents('php://input'), true);

			$car = $this->service->updateCar($id, $data);

			header('Content-Type: application/json');
			echo json_encode($car);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
