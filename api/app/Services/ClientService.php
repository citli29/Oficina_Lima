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

	public function listClients(array $filters, ?array $pagination = null): array
	{
		return $this->clientModel->getClientsWithFilter($filters, $pagination);
	}

	public function showClient(int $id):array
	{
		if(!$client = $this->clientModel->getClientById($id))
			throw new RuntimeException("Show Client [ID Not Found]: {$id}.",404);
		return $client;
	}

	public function createClient(array $data): array
	{
		try
		{
			return $this->clientModel->createClient($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateClient(int $id, array $data): array
	{
		try
		{
			$client = $this->clientModel->updateClient($id,$data);
			if(!$client) 
			throw new InvalidArgumentException("Update Client [Invalid ID]: {$id}.",400);
			return $client;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
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
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}
}
