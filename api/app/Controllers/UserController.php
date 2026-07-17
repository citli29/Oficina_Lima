<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\UserService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class UserController
{
	private UserService $service;
	public function __construct(PDO $db)
	{
		$model = new User($db);
		$this->service = new UserService($model);
	}

	public function getUsers():void
	{
		try {
			$filters = [
				'name' => isset($_GET['name']) ? $_GET['name'] : null,
				'email' => isset($_GET['email']) ? $_GET['email'] : null,
				'user_type' => isset($_GET['user_type']) ? $_GET['user_type'] : null,
			];

			$user_list = $this->service->listUsers($filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'user_list'=>$user_list
			]);
		} catch (RuntimeException$e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getUser(int $id):void
	{
		try {
			$user = $this->service->showUser($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'user'=>$user
			]);
		} catch (RuntimeException$e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postUsers():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);
			$user = $this->service->createUser($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'user'=>$user
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteUser(int $id):void
	{
		try {

			$user = $this->service->deleteUser($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'user' => $user
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putUser(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);
			$user = $this->service->updateUser($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'user'=>$user
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}

