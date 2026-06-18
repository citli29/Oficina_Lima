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

	public function getMakes():void
	{
		try {
			$filters = [
				'make_name' => isset($_GET['make_name']) ? $_GET['make_name'] : null,
			];
			header('Content-Type: application/json');
			echo json_encode($this->service->listMakes($filters));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getModels():void
	{
		try {
			$filters = [
				'model_name' => isset($_GET['model_name']) ? $_GET['model_name'] : null,
				'make_name' => isset($_GET['make_name']) ? $_GET['make_name'] : null,
			];
			header('Content-Type: application/json');
			echo json_encode($this->service->listModels($filters));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getCars():void
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

	public function getMake(int $id):void
	{
		try {
			header('Content-Type: application/json');
			echo json_encode($this->service->showMake($id));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getModel(int $id):void
	{
		try {
			header('Content-Type: application/json');
			echo json_encode($this->service->showModel($id));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getCar(int $id):void
	{
		try {
			header('Content-Type: application/json');
			echo json_encode($this->service->showCar($id));
		} catch (RuntimeException$e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postMakes():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$make = $this->service->createMake($input);

			header('Content-Type: application/json');
			echo json_encode($make);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postModels():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$model = $this->service->createModel($input);

			header('Content-Type: application/json');
			echo json_encode($model);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postCars():void
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

	public function deleteMake(int $id):void
	{
		try {

			$make = $this->service->deleteMake($id);

			header('Content-Type: application/json');
			echo json_encode([
			    'success' => true,
			    'make' => $make
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteModel(int $id):void
	{
		try {

			$model = $this->service->deleteModel($id);

			header('Content-Type: application/json');
			echo json_encode([
			    'success' => true,
			    'model' => $model
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteCar(int $id):void
	{
		try {

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

	public function putMake(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			$make = $this->service->updateMake($id, $data);

			header('Content-Type: application/json');
			echo json_encode($make);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putModel(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			$model = $this->service->updateModel($id, $data);

			header('Content-Type: application/json');
			echo json_encode($model);
		} catch (InvalidArgumentException $e) {
			http_response_code(404);
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putCar(int $id):void
	{
		try {
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
