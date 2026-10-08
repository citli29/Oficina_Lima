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

	/**
	 * Per-field update — see Service::patchService. $data is
	 * { changes: {field: new}, original: {field: value the client saw} }.
	 * Throws 409 (with the conflicting fields and the current service in
	 * ServiceConflictException) if someone else changed one of those fields.
	 */
	public function patchService(int $id, array $data): array
	{
		$changes = $data['changes'] ?? null;
		$original = $data['original'] ?? [];

		if (!is_array($changes) || !is_array($original))
			throw new InvalidArgumentException("JSON Body Invalid.", 400);

		$allowed = Service::patchableFields();
		foreach (array_keys($changes) as $field) {
			if (!in_array($field, $allowed, true))
				throw new InvalidArgumentException("Campo desconhecido: {$field}.", 400);
			if (!array_key_exists($field, $original))
				throw new InvalidArgumentException("Falta o valor original do campo {$field}.", 400);
		}

		try
		{
			$result = $this->serviceModel->patchService($id, $changes, $original);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}

		if ($result === false)
			throw new InvalidArgumentException("Update Service [Invalid ID]: {$id}.", 404);

		if (isset($result['conflict']))
			throw new ServiceConflictException($result['conflict'], $result['service']);

		return $result;
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
