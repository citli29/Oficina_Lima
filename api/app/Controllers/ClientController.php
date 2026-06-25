<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Client;
use App\Services\ClientService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class ClientController
{
	private ClientService $service;
	public function __construct(PDO $db)
	{
		$model = new Client($db);
		$this->service = new ClientService($model);
	}

	public function getClients(): void
	{
		try{
			$filters = [
				'name' => isset($_GET['name']) ? $_GET['name'] : null,
				'client' => isset($_GET['client']) ? $_GET['client'] : null,
				'email' => isset($_GET['email']) ? $_GET['email'] : null,
				'tax_nr' => isset($_GET['tax_nr']) ? $_GET['tax_nr'] : null,
			];
			$client_list = $this->service->listClients($filters);

			http_response_code(empty($client_list)?204:200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'client_list'=>$client_list
			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getClient(int $id):void
	{
		try {
			$client = $this->service->showClient($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'client'=>$client
			]);
		} catch (RuntimeException$e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postClients():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$client = $this->service->createClient($input);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'client'=>$client
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteClient(int $id):void
	{
		try {

			$client = $this->service->deleteClient($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'client' => $client
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putClient(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			$client = $this->service->updateClient($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'client'=>$client
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

}
