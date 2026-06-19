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
			$make_list = $this->service->listMakes($filters);

			http_response_code(empty($make_list)?204:200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'make_list'=>$make_list
			]);
		} catch (RuntimeException $e) {
			http_response_code($e->getCode());
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

			$model_list = $this->service->listModels($filters);

			http_response_code(empty($model_list)?204:200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'model_list'=>$model_list
			]);
		} catch (RuntimeException$e) {
			http_response_code($e->getCode());
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

			$car_list = $this->service->listCars($filters);

			http_response_code(empty($car_list)?204:200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'car_list'=>$car_list
			]);
		} catch (RuntimeException$e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getMake(int $id):void
	{
		try {
			$make = $this->service->showMake($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'make'=>$make
			]);
		} catch (RuntimeException$e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getModel(int $id):void
	{
		try {
			$model = $this->service->showModel($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'model'=>$model
			]);
		} catch (RuntimeException$e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getCar(int $id):void
	{
		try {
			$car = $this->service->showCar($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'car'=>$car
			]);
		} catch (RuntimeException$e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postMakes():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$make = $this->service->createMake($input);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'make'=>$make
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postModels():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$model = $this->service->createModel($input);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'model'=>$model
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postCars():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$car = $this->service->createCar($input);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'car'=>$car
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteMake(int $id):void
	{
		try {

			$make = $this->service->deleteMake($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'make' => $make
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteModel(int $id):void
	{
		try {

			$model = $this->service->deleteModel($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'model' => $model
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteCar(int $id):void
	{
		try {

			$car = $this->service->deleteCar($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'car' => $car
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putMake(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			$make = $this->service->updateMake($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'make'=>$make
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putModel(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			$model = $this->service->updateModel($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'model'=>$model
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putCar(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			$car = $this->service->updateCar($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'car'=>	$car
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
