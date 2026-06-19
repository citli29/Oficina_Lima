<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Car;
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
			throw new RuntimeException("Show Car [ID Not Found]: {$id}.");
		return $car;
	}

	public function showModel(int $id): array
	{
		if(!$model = $this->carModel->getModelById($id)) 
			throw new RuntimeException("Show Model [ID Not Found]: {$id}.");
		return $model;
	}

	public function showMake(int $id): array
	{
		if(!$make = $this->carModel->getMakeById($id)) 
			throw new RuntimeException("Show Make [ID Not Found]: {$id}.");
		return $make;
	}

	public function createMake(array $data): array
	{
		if(empty($data['name'])) {
			throw new InvalidArgumentException("Create Model [Arguments Required]: Name.");
		}

		try
		{
			return $this->carModel->createMake($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Model [Argument Constraints]: Name must be provided. [{$e->getMessage()}]");
		}
	}

	public function createModel(array $data): array
	{
		if(empty($data['name']) || !isset($data['make_id'])) 
		{
			throw new InvalidArgumentException("Create Model [Arguments required]: Name, Make.");
		}

		try
		{
			return $this->carModel->createModel($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Model [Argument constraints]: Name must be provided. Make must be provided. [{$e->getMessage()}]");
		}
	}
	public function createCar(array $data): array
	{
		if(empty($data['plate']) || !isset($data['make_id'])) {
			throw new InvalidArgumentException("Create Car [Arguments required]: Plate, Make.");
		}
		try
		{
			return $this->carModel->createCar($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Car [Argument constraints]: Plate unique. Make must be provided. [{$e->getMessage()}]");
		}
	}

	public function deleteMake(int $id): array
	{
		if(!$make = $this->carModel->deleteMake($id))
		throw new InvalidArgumentException("Delete Make [Invalid ID]: {$id}.");
		return $make;
	}
	public function deleteModel(int $id): array
	{
		if(!$model = $this->carModel->deleteModel($id))
		throw new InvalidArgumentException("Delete Model [Invalid ID]: {$id}.");
		return $model;
	}

	public function deleteCar(int $id): array
	{
		if(!$car = $this->carModel->deleteCar($id))
		throw new InvalidArgumentException("Delete Car [Invalid ID]: {$id}.");
		return $car;
	}

	public function updateMake(int $id, array $data): array
	{
		if(empty($data['name'])) {
			throw new InvalidArgumentException("Update Model [Argument required]: Name.");
		}
		try
		{
			$make = $this->carModel->updateMake($id,$data);
			if(!$make) 
			throw new InvalidArgumentException("Update Make [Invalid ID]: {$id}.");
			return $make;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Make [Argument constraints]: Name must be provided. [{$e->getMessage()}]");
		}
	}

	public function updateModel(int $id, array $data): array
	{
		if(empty($data['name']) || !isset($data['make_id'])) {
			throw new InvalidArgumentException("Update Make [Arguments required]: Name, Make.");
		}

		try
		{
			$model = $this->carModel->updateModel($id,$data);
			if(!$model) 
			throw new InvalidArgumentException("Update Model [Invalid ID]: {$id}.");
			return $model;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Model [Argument constraints]: Name must be provided. Make must be provided. [{$e->getMessage()}]");
		}
	}

	public function updateCar(int $id, array $data): array
	{
		if(empty($data['plate']) || !isset($data['make_id'])) {
			throw new InvalidArgumentException("Update Car [Arguments required]: Plate, Make.");
		}
		try
		{
			$car = $this->carModel->updateCar($id,$data);
			if(!$car)
			throw new InvalidArgumentException("Update Car [Invalid ID]: {$id}.");
			return $car;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Car [Argument constraints]: Plate unique. Make must be provided. [{$e->getMessage()}]");
		}
	}

}
