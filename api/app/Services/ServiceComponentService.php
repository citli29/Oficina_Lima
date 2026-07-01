<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\ServiceComponent;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ServiceComponentService
{
	private ServiceComponent $serviceComponentModel;

	public function __construct(ServiceComponent $serviceComponent)
	{
		$this->serviceComponentModel = $serviceComponent;
	}

	public function listSUTByService(int $id,array $filters): array
	{
		return $this->serviceComponentModel->getSUTByServiceWithFilter($id,$filters);
	}

	public function showSUT(int $id):array
	{
		if(!$sut = $this->serviceComponentModel->getSUTById($id))
			throw new RuntimeException("Show User Time [ID Not Found]: {$id}.",404);
		return $sut;
	}
}
