<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Car;
use InvalidArgumentException;
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
		return $this->carModel->getCarsAll($filters);
	}
	public function listCar(int $id): array
	{
		if(!$car = $this->carModel->getCarById($id)) 
		throw new RuntimeException("{$id}: Invalid ID");
		return $car;
	}
	public function createCar(array $data): array
	{
		if (empty($data['plate']) || empty($data['model_id'])) {
			throw new InvalidArgumentException("Plate and Model_id required");
		}

		return $this->carModel->create($data);
	}

	public function deleteCar(int $id): array
	{
		if(!$car = $this->carModel->delete($id))
			throw new InvalidArgumentException("{$id}: Invalid ID");
		return $car;
	}
	public function updateCar(int $id, array $data): array
	{
		if (empty($data['plate']) || empty($data['model_id'])) {
			throw new InvalidArgumentException("Plate and Model_id required");
		}

		if(!$car =$this->carModel->update($id, $data) )
			throw new InvalidArgumentException("{$id}: Invalid ID");
			
		return $car;
	}

}
