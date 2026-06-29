<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Client;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ClientService
{
	private Client $clientModel;

	public function __construct(Client $clientModel)
	{
		$this->clientModel = $clientModel;
	}

	public function listClients(array $filters): array
	{
		return $this->clientModel->getClientsWithFilter($filters);
	}

	public function showClient(int $id):array
	{
		if(!$client = $this->clientModel->getClientById($id))
			throw new RuntimeException("Show Client [ID Not Found]: {$id}.",404);
		return $client;
	}

	public function createClient(array $data): array
	{
		if(empty($data['name']) || empty($data['phone'])) {
			throw new InvalidArgumentException("Create Client [Arguments Required]: Name, Phone.", 400);
		}

		try
		{
			return $this->clientModel->createClient($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Client [Argument Constraints]: Name must be provided. Phone must be provided. [{$e->errorInfo[2]}]", 400);
		}
	}

	public function updateClient(int $id, array $data): array
	{
		if(empty($data['name']) || empty($data['phone'])) {
			throw new InvalidArgumentException("Update Client[Argument Required]: Name, Phone.",400);
		}
		try
		{
			$client = $this->clientModel->updateClient($id,$data);
			if(!$client) 
			throw new InvalidArgumentException("Update Client [Invalid ID]: {$id}.",400);
			return $client;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Client [Argument Required]: Name must be provided. Phone must be provided. [{$e->errorInfo[2]}]",400);
		}
	}

	public function deleteClient(int $id): array
	{
		try
		{
			if(!$client = $this->clientModel->deleteClient($id))
				throw new InvalidArgumentException("Delete Client [Invalid ID]: {$id}.", 404);
			return $client;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException("Delete Client [Error]: [{$e->errorInfo[2]}]", 409);
		}
	}
}
