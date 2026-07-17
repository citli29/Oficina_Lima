<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\User;
use Exception;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class UserService
{
	private User $userModel;

	public function __construct(User $userModel)
	{
		$this->userModel = $userModel;
	}

	public function listUsers(array $filters): array
	{
		return $this->userModel->getUsersWithFilter($filters);
	}

	public function showUser(int $id):array
	{
		if(!$user = $this->userModel->getUserById($id))
			throw new RuntimeException("Show User [ID Not Found]: {$id}.",404);
		return $user;
	}

	public function createUser(array $data): ?array
	{
		throw new RuntimeException("Not Implement",404);
	}

	public function updateClient(int $id, array $data): array
	{
		throw new RuntimeException("Not Implement",404);
	}

	public function deleteClient(int $id): array
	{
		throw new RuntimeException("Not Implement",404);
	}
}
