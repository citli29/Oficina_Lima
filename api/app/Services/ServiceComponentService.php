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

	public function listSUTs(array $filters, ?array $pagination = null): array
	{
		return $this->serviceComponentModel->getSUTWithFilter($filters, $pagination);
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
		try
		{
			return $this->serviceComponentModel->createSUT($s_id,$data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateSUT(int $s_id, int $id, array $data): array
	{
		try
		{
			$sut = $this->serviceComponentModel->updateSUTBySid_Id($s_id,$id,$data);
			if(!$sut) 
			throw new InvalidArgumentException("Update Service User Time [Invalid ID]: {$s_id} - {$id}.",400);
			return $sut;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
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
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function listSAPs(array $filters, ?array $pagination = null): array
	{
		return $this->serviceComponentModel->getSAPWithFilter($filters, $pagination);
	}

	public function listSPRs(array $filters, ?array $pagination = null): array
	{
		return $this->serviceComponentModel->getSPRWithFilter($filters, $pagination);
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
		try
		{
			return $this->serviceComponentModel->createSAP($s_id,$data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateSAP(int $s_id, int $id, array $data): array
	{
		try
		{
			$sap = $this->serviceComponentModel->updateSAPBySid_Id($s_id,$id,$data);
			if(!$sap) 
			throw new InvalidArgumentException("Update Service Applied Products [Invalid ID]: {$s_id} - {$id}.",400);
			return $sap;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
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
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function listSUTPsByService(int $s_id,array $filters): array
	{
		return $this->serviceComponentModel->getSUTPByServiceWithFilter($s_id,$filters);
	}

	public function listOpenSUTPs(): array
	{
		return $this->serviceComponentModel->getOpenSUTPs();
	}

	public function listMonthlyUserTimeStats(string $year): array
	{
		return $this->serviceComponentModel->getMonthlyUserTimeStats($year);
	}

	public function listWeeklyUserTimeStats(string $year, string $month): array
	{
		return $this->serviceComponentModel->getWeeklyUserTimeStats($year, $month);
	}

	public function listYearlyDailyUserTimeStats(string $year): array
	{
		return $this->serviceComponentModel->getYearlyDailyUserTimeStats($year);
	}

	public function listDailyUserTimeStats(string $year, string $month, ?int $week = null): array
	{
		return $this->serviceComponentModel->getDailyUserTimeStats($year, $month, $week);
	}

	public function createSUTP(int $s_id, array $data){
		try
		{
			return $this->serviceComponentModel->createSUTP($s_id,$data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
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
		if($sutp['hours_s']=== null && $sutp['minutes_s'] ===null )
			throw new RuntimeException("Show User Time Punches[Didn't Started]: {$s_id} : {$id}.",400);
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
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function updateSUTP(int $s_id, int $id, array $data): array
	{
		try
		{
			$sutp = $this->serviceComponentModel->updateSUTPBySid_Id($s_id,$id,$data);
			if(!$sutp) 
			throw new InvalidArgumentException("Update Service User Time Punches[Invalid ID]: {$s_id} - {$id}.",400);
			return $sutp;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}
	public function listSPRsByService(int $s_id,array $filters): array
	{
		return $this->serviceComponentModel->getSPRByServiceWithFilter($s_id,$filters);
	}

	public function createSPR(int $s_id, array $data){
		try
		{
			return $this->serviceComponentModel->createSPR($s_id,$data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function showSPR(int $s_id,int $id):array
	{
		$spr  = $this->serviceComponentModel->getSPRBySid_Id($s_id,$id);
		if(!$spr)
			throw new RuntimeException("Show Product Request[ID Not Found]: {$s_id} : {$id}.",404);
		return $spr;
	}


	public function deleteSPR(int $s_id, int $id): array
	{
		try
		{
			if(!$spr = $this->serviceComponentModel->deleteSPRBySid_Id($s_id,$id))
				throw new InvalidArgumentException("Delete Product Request[Invalid ID]: {$s_id} - {$id}.", 404);
			return $spr;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function updateSPR(int $s_id, int $id, array $data): array
	{
		try
		{
			$spr = $this->serviceComponentModel->updateSPRBySid_Id($s_id,$id,$data);
			if(!$spr) 
			throw new InvalidArgumentException("Update Service Product Request[Invalid ID]: {$s_id} - {$id}.",400);
			return $spr;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function listLabActionValuesByService(int $id, array $filters): array
	{
		return $this->serviceComponentModel->getLabActionValuesByServiceWithFilter($id, $filters);
	}

	public function listLabPropertyValuesByService(int $id, array $filters): array
	{
		return $this->serviceComponentModel->getLabPropertyValuesByServiceWithFilter($id, $filters);
	}

	public function createLav(int $s_id, array $data): array
	{
		try
		{
			return $this->serviceComponentModel->createLav($s_id, $data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function showLav(int $s_id, int $slav_id): array
	{
		if(!$lav = $this->serviceComponentModel->getLavBySid_SlavId($s_id, $slav_id))
			throw new RuntimeException("Show Lab Action Value [ID Not Found]: {$s_id} : {$slav_id}.",404);
		return $lav;
	}

	public function updateLav(int $s_id, int $slav_id, array $data): array
	{
		try
		{
			$lav = $this->serviceComponentModel->updateLavBySid_SlavId($s_id, $slav_id, $data);
			if(!$lav)
			throw new InvalidArgumentException("Update Lab Action Value [Invalid ID]: {$s_id} - {$slav_id}.",400);
			return $lav;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteLav(int $s_id, int $slav_id): array
	{
		try
		{
			if(!$lav = $this->serviceComponentModel->deleteLavBySid_SlavId($s_id, $slav_id))
				throw new InvalidArgumentException("Delete Lab Action Value [Invalid ID]: {$s_id} - {$slav_id}.", 404);
			return $lav;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function createLpv(int $s_id, array $data): array
	{
		try
		{
			return $this->serviceComponentModel->createLpv($s_id, $data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function showLpv(int $s_id, int $id): array
	{
		if(!$lpv = $this->serviceComponentModel->getLpvBySidId($s_id, $id))
			throw new RuntimeException("Show Lab Property Value [ID Not Found]: {$s_id} : {$id}.",404);
		return $lpv;
	}

	public function updateLpv(int $s_id, int $id, array $data): array
	{
		try
		{
			$lpv = $this->serviceComponentModel->updateLpvBySidId($s_id, $id, $data);
			if(!$lpv)
			throw new InvalidArgumentException("Update Lab Property Value [Invalid ID]: {$s_id} - {$id}.",400);
			return $lpv;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteLpv(int $s_id, int $id): array
	{
		try
		{
			if(!$lpv = $this->serviceComponentModel->deleteLpvBySidId($s_id, $id))
				throw new InvalidArgumentException("Delete Lab Property Value [Invalid ID]: {$s_id} - {$id}.", 404);
			return $lpv;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}
}
