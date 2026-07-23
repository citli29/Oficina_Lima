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

	public function listSUTs(array $filters): array
	{
		return $this->serviceComponentModel->getSUTWithFilter($filters);
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

	public function createSUT(int $s_id,array $data): array
	{
		if(empty($data['user_id'])|| empty($data['minutes'])||empty($data['date'])) {
			throw new InvalidArgumentException("Create Service User Time [Arguments Required]: User ID, Minutes, Date.", 400);
		}

		try
		{
			return $this->serviceComponentModel->createSUT($s_id,$data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Service User Time [Argument Constraints]: User ID must be provided. Minutes must be provided. Date must be provided. [{$e->errorInfo[2]}]", 400);
		}
	}

	public function updateSUT(int $s_id, int $id, array $data): array
	{
		if(empty($data['user_id'])|| empty($data['minutes'])||empty($data['date'])) {
			throw new InvalidArgumentException("Update Service User Time [Argument Required]: User ID, Minutes, Date.",400);
		}
		try
		{
			$sut = $this->serviceComponentModel->updateSUTBySid_Id($s_id,$id,$data);
			if(!$sut) 
			throw new InvalidArgumentException("Update Service User Time [Invalid ID]: {$s_id} - {$id}.",400);
			return $sut;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Service User Time [Argument Required]: User ID must be provided. Minutes must be provided. Date must be provided. [{$e->errorInfo[2]}]",400);
		}
	}

	public function deleteSUT(int $s_id, int $id): array
	{
		try
		{
			if(!$sut = $this->serviceComponentModel->deleteSUTBySid_Id($s_id,$id))
				throw new InvalidArgumentException("Delete User Time [Invalid ID]: {$s_id} - {$id}.", 404);
			return $sut;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException("Delete Applied Product [Error]: [{$e->errorInfo[2]}]", 409);
		}
	}

	public function listSAPs(array $filters): array
	{
		return $this->serviceComponentModel->getSAPWithFilter($filters);
	}
	public function listSAPByService(int $id,array $filters): array
	{
		return $this->serviceComponentModel->getSAPByServiceWithFilter($id,$filters);
	}

	public function showSAP(int $s_id,int $id):array
	{
		$sap  = $this->serviceComponentModel->getSAPBySid_Id($s_id,$id);
		if(!$sap)
			throw new RuntimeException("Show Applied Products [ID Not Found]: {$s_id} : {$id}.",404);
		return $sap;
	}

	public function showGlobalSAP(int $id):array
	{
		if(!$sap = $this->serviceComponentModel->getSAPById($id))
			throw new RuntimeException("Show Global ID Applied Product [ID Not Found]: {$id}.",404);
		return $sap;
	}

	public function createSAP(int $s_id,array $data): array
	{
		if(empty($data['product_id'])) {
			throw new InvalidArgumentException("Create Service Applied Products Time [Arguments Required]: Product ID.", 400);
		}

		try
		{
			return $this->serviceComponentModel->createSAP($s_id,$data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Service Applied Products [Argument Constraints]: Product ID must be provided. [{$e->errorInfo[2]}]", 400);
		}
	}

	public function updateSAP(int $s_id, int $id, array $data): array
	{
		if(empty($data['product_id'])) {
			throw new InvalidArgumentException("Update Service Applied Products [Argument Required]: Product ID.",400);
		}
		try
		{
			$sap = $this->serviceComponentModel->updateSAPBySid_Id($s_id,$id,$data);
			if(!$sap) 
			throw new InvalidArgumentException("Update Service Applied Products [Invalid ID]: {$s_id} - {$id}.",400);
			return $sap;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Service Applied Products [Argument Required]: Product ID must be provided. [{$e->errorInfo[2]}]",400);
		}
	}

	public function deleteSAP(int $s_id, int $id): array
	{
		try
		{
			if(!$sap = $this->serviceComponentModel->deleteSAPBySid_Id($s_id,$id))
				throw new InvalidArgumentException("Delete Applied Products [Invalid ID]: {$s_id} - {$id}.", 404);
			return $sap;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException("Delete Applied Product [Error]: [{$e->errorInfo[2]}]", 409);
		}
	}

	public function listSUTPsByService(int $s_id,array $filters): array
	{
		return $this->serviceComponentModel->getSUTPByServiceWithFilter($s_id,$filters);
	}

	public function createSUTP(int $s_id, array $data){
		if(empty($data['user_id'])||empty($data['date'])) {
			throw new InvalidArgumentException("Create Service User Time Punches[Arguments Required]: User ID, Date.", 400);
		}

		try
		{
			return $this->serviceComponentModel->createSUTP($s_id,$data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Service User Time Punches[Argument Constraints]: User ID must be provided. Date must be provided. [{$e->errorInfo[2]}]", 400);
		}
	}

	public function showSUTP(int $s_id,int $id):array
	{
		$sutp  = $this->serviceComponentModel->getSUTPBySid_Id($s_id,$id);
		if(!$sutp)
			throw new RuntimeException("Show User Time Punches[ID Not Found]: {$s_id} : {$id}.",404);
		return $sutp;
	}

	public function startSUTP(int $s_id,int $id):array
	{
		$sutp  = $this->serviceComponentModel->getSUTPBySid_Id($s_id,$id);
		if(!$sutp)
			throw new RuntimeException("Show User Time Punches[ID Not Found]: {$s_id} : {$id}.",404);
		if($sutp['hours_s']!== null && $sutp['minutes_s'] !==null )
			throw new RuntimeException("Show User Time Punches[Already Started]: {$s_id} : {$id}.",400);
		$sutp = $this->serviceComponentModel->startSUTP($sutp);
		return $sutp;
	}

	public function stopSUTP(int $s_id,int $id):array
	{
		$sutp  = $this->serviceComponentModel->getSUTPBySid_Id($s_id,$id);
		if(!$sutp)
			throw new RuntimeException("Show User Time Punches[ID Not Found]: {$s_id} : {$id}.",404);
		if($sutp['hours_f']!== null && $sutp['minutes_f'] !==null )
			throw new RuntimeException("Show User Time Punches[Already Ended]: {$s_id} : {$id}.",400);
		$sutp = $this->serviceComponentModel->stopSUTP($sutp);
		return $sutp;
	}

	public function deleteSUTP(int $s_id, int $id): array
	{
		try
		{
			if(!$sutp = $this->serviceComponentModel->deleteSUTPBySid_Id($s_id,$id))
				throw new InvalidArgumentException("Delete User Time Punches[Invalid ID]: {$s_id} - {$id}.", 404);
			return $sutp;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException("Delete User Time Punches [Error]: [{$e->errorInfo[2]}]", 409);
		}
	}

	public function updateSUTP(int $s_id, int $id, array $data): array
	{
		if(empty($data['user_id'])|| empty($data['date'])) {
			throw new InvalidArgumentException("Update Service User Time Punches[Argument Required]: User ID, Date.",400);
		}
		try
		{
			$sutp = $this->serviceComponentModel->updateSUTPBySid_Id($s_id,$id,$data);
			if(!$sutp) 
			throw new InvalidArgumentException("Update Service User Time Punches[Invalid ID]: {$s_id} - {$id}.",400);
			return $sutp;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Service User Time Punches[Argument Required]: User ID must be provided. Date must be provided. [{$e->errorInfo[2]}]",400);
		}
	}
}
