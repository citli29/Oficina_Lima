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

	public function showSUT(int $s_id,int $id):array
	{
		$sut  = $this->serviceComponentModel->getSUTBySid_Id($s_id,$id);
		if(!$sut)
			throw new RuntimeException("Show User Time [ID Not Found]: {$s_id} : {$id}.",404);
		return $sut;
	}

	public function showGlobalSUT(int $id):array
	{
		if(!$sut = $this->serviceComponentModel->getSUTById($id))
			throw new RuntimeException("Show User Time [ID Not Found]: {$id}.",404);
		return $sut;
	}
}
