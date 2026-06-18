<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Car;
use ArgumentCountError;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class CarService
{
	private Car $carModel;

	public function __construct(Car $carModel)
	{
		$this->carModel = $carModel;
	}

	public function listCars(array $filters): array
	{
		return $this->carModel->getCarsWithFilter($filters);
	}

	public function listModels(array $filters): array
	{
		return $this->carModel->getModelsWithFilter($filters);
	}

	public function listMakes(array $filters): array
	{
		return $this->carModel->getMakesWithFilter($filters);
	}

	public function showCar(int $id): array
	{
		if(!$car = $this->carModel->getCarById($id)) 
			throw new RuntimeException("Car not found: {$id}");
		return $car;
	}

	public function showModel(int $id): array
	{
		if(!$model = $this->carModel->getModelById($id)) 
			throw new RuntimeException("Model not found: {$id}");
		return $model;
	}

	public function showMake(int $id): array
	{
		if(!$make = $this->carModel->getMakeById($id)) 
			throw new RuntimeException("Make not found: {$id}");
		return $make;
	}

	public function createCar(array $data): array
	{
		if(empty($data['plate']) || !isset($data['make_id'])) {
			throw new InvalidArgumentException("
				Mandatory arguments:\n
				\tPlate\n
				\tMake
				");
		}
		try
{
			return $this->carModel->createCar($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("
				Argument constraints:\n
				\tPlate unique\n
				\tMake must be provided\n
				{$e->getMessage()}
				");
		}
	}

	public function deleteCar(int $id): array
	{
		if(!$car = $this->carModel->deleteCar($id))
			throw new InvalidArgumentException("{$id}: Invalid ID");
		return $car;
	}
	public function updateCar(int $id, array $data): array
	{
		if (empty($data['plate']) || empty($data['model_id'])) {
			throw new InvalidArgumentException("Plate and Model_id required");
		}

		if(!$car =$this->carModel->updateCar($id, $data) )
			throw new InvalidArgumentException("{$id}: Invalid ID");
			
		return $car;
	}

}
