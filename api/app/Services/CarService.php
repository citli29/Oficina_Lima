<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Car;
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

}
