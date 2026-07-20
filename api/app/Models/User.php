<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class User
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getUsersWithFilter(array $filters): array
	{
		$sql = "
			SELECT u.id, u.name, u.email, u.user_type_id, ut.name as user_type_name
			FROM users u 
			LEFT JOIN user_types ut
			ON  u.user_type_id = ut.id
			WHERE 1=1
		";
		$params = [];
		$rules = [
			'name' => [
				'column' => 'u.name',
				'operator' => 'LIKE'
			],
			'email' => [
				'column' => 'u.email',
				'operator' => 'LIKE'
			],
			'user_type' => [
				'column' => 'ut.name',
				'operator' => 'LIKE'
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll();
	}

	public function getUserById(int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT u.id, u.name, u.email, u.user_type_id, ut.name as user_type_name
			FROM users u 
			LEFT JOIN user_types ut
			ON  u.user_type_id = ut.id
			WHERE u.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function deleteClient(int $id): bool|array
	{
		$client = $this->getClientById($id);

		if($client)
		{
		$stmt = $this->db->prepare("
			DELETE 
			FROM clients
			WHERE id = ?
			");
		$stmt->execute([$id]);
		}

		return $client;
	}

	public function updateClient(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE clients
			SET name = ?, phone = ?, address = ?, email = ?,
			zip_code = ?, tax_nr = ?
			WHERE id = ?
			");

		$stmt->execute([
			$data['name'],
			$data['phone'],
			$data['address'] ?? null,
			$data['email'] ?? null,
			$data['zip_code'] ?? null,
			$data['tax_nr'] ?? null,
			$id
		]);

		return $this->getClientById($id);
	}

	public function createClient(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO clients
			(name, phone, address, email,
		       	zip_code, tax_nr)
			VALUES (?, ?, ?, ?, ?, ?)
			");

		$stmt->execute([
			$data['name'],
			$data['phone'],
			$data['address'] ?? null,
			$data['email'] ?? null,
			$data['zip_code'] ?? null,
			$data['tax_nr'] ?? null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getClientById($newId);
	}

}

