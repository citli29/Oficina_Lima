<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Service;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ServiceService
{

	private Service $serviceModel;

	public function __construct(Service $serviceModel)
	{
		$this->serviceModel = $serviceModel;
	}

	public function listServices(array $filters, ?array $pagination = null, ?array $sort = null): array
	{
		return $this->serviceModel->getServicesWithFilter($filters, $pagination, $sort);
	}

	public function listServiceTypes(array $filters, ?array $pagination = null): array
	{
		return $this->serviceModel->getServiceTypesWithFilter($filters, $pagination);
	}

	public function showService(int $id):array
	{
		if(!$service = $this->serviceModel->getServiceById($id))
			throw new RuntimeException("Show Service [ID Not Found]: {$id}.",404);
		return $service;
	}

	public function createService(array $data): array
	{
		try
		{
			return $this->serviceModel->createService($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}


	public function updateService(int $id, array $data): array
	{
		try
		{
			$service = $this->serviceModel->updateService($id,$data);
			if(!$service) 
			throw new InvalidArgumentException("Update Service [Invalid ID]: {$id}.",400);
			return $service;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteService(int $id): array
	{
		try
		{
			if(!$service = $this->serviceModel->deleteService($id))
				throw new InvalidArgumentException("Delete Service [Invalid ID]: {$id}.", 404);
			return $service;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

}
