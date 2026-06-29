<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Client
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getClientsWithFilter(array $filters): array
	{
		$sql = "
		SELECT c.* 
		FROM clients c
		WHERE 1=1
		";
		$params = [];
		$rules = [
			'name' => [
				'column' => 'c.name',
				'operator' => 'LIKE'
			],
			'phone' => [
				'column' => 'c.phone',
				'operator' => 'LIKE'
			],
			'email' => [
				'column' => 'c.email',
				'operator' => 'LIKE'
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll();
	}

	public function getClientById(int $id): bool|array
	{
		$stmt = $this->db->prepare("
			SELECT c.* 
			FROM clients c
			WHERE c.id = ?
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
