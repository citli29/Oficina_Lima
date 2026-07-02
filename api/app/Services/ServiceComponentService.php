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

	public function listSAPByService(int $id,array $filters): array
	{
		return $this->serviceComponentModel->getSAPByServiceWithFilter($id,$filters);
	}

	public function showSAP(int $s_id,int $id):array
	{
		$sap  = $this->serviceComponentModel->getSAPBySid_Id($s_id,$id);
		if(!$sap)
			throw new RuntimeException("Show Applied [ID Not Found]: {$s_id} : {$id}.",404);
		return $sap;
	}

	public function showGlobalSAP(int $id):array
	{
		if(!$sap = $this->serviceComponentModel->getSAPById($id))
			throw new RuntimeException("Show User Time [ID Not Found]: {$id}.",404);
		return $sap;
	}
}
