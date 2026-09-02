<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Product
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getProductsWithOrFilter(string $str): array
	{
		$sql = "
		SELECT p.*, pt.name AS product_type_name, pt.id AS product_type_id
		FROM products p
		LEFT JOIN product_types pt ON p.product_type_id = pt.id
		WHERE UPPER(p.search_name) LIKE UPPER(?)
		OR UPPER(p.reference) LIKE UPPER(?)
		OR UPPER(pt.name) LIKE UPPER(?)
		ORDER BY p.name, p.reference, pt.name ASC
		";

		$search = '%' . $str . '%';

		$stmt = $this->db->prepare($sql);
		$stmt->execute([$search, $search, $search]);

		return $stmt->fetchAll();
	}
	public function getProductsWithFilter(array $filters): array
	{
		$sql = "
		SELECT p.*, pt.name AS product_type_name,pt.id AS product_type_id 
		FROM products p
		LEFT JOIN product_types pt ON p.product_type_id = pt.id
		WHERE 1=1
		";

		$params = [];

		$rules = [
			'name' => [
				'column' => 'p.search_name',
				'operator' => 'LIKE'
			],
			'reference' => [
				'column' => 'p.reference',
				'operator' => 'LIKE'
			],
			'p_t_name' => [
				'column' => 'pt.search_name',
				'operator' => 'LIKE'
			],
			'p_t_id' => [
				'column' => 'pt.id',
				'operator' => 'LIKE'
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);

		$sql .= "ORDER BY p.name, p.reference, pt.name ASC";

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getProductTypesWithFilter(array $filters):bool|array
	{		
		$sql = "
		SELECT * FROM product_types WHERE 1=1
		";

		$params = [];

		$rules = [
			'name' => [
				'column' => 'search_name',
				'operator' => 'LIKE'
			],
		];

		$sql = Database::applyFilters($sql, $filters, $rules, $params);
		$sql .= "ORDER BY name ASC";

		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);

		return $stmt->fetchAll();
	}

	public function getProductById(int $id): bool|array
	{
		$stmt = $this->db->query( "
			SELECT p.*, pt.name AS product_type 
			FROM products p 
			LEFT JOIN product_types pt ON p.product_type_id = pt.id
			WHERE p.id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function getProductTypeById(int $id):bool|array
	{
		$stmt = $this->db->query("
			SELECT * 
			FROM product_types 
			WHERE id = ?
			");

		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function updateProduct(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE products
			SET name = ? , reference = ?, product_type_id = ? , search_name = ?
			WHERE id = ?
			");

		$stmt->execute([
			!empty($data['name']) ? $data['name']: null,
			!empty($data['reference']) ? $data['reference']: null,
			!empty($data['product_type_id']) ? $data['product_type_id']: null,
			!empty($data['name']) ? normalize($data['name']): null,
			$id
		]);

		return $this->getProductById($id);
	}

	public function updateProductType(int $id, array $data): bool|array
	{
		$stmt = $this->db->prepare("
			UPDATE product_types
			SET name = ? , search_name = ?
			WHERE id = ?
			");

		$stmt->execute([
			!empty($data['name']) ? $data['name']: null,
			!empty($data['name']) ? normalize($data['name']): null,
			!empty($data['name']) ? normalize($data['name']): null,
			$id
		]);

		return $this->getProductTypeById($id);
	}

	public function createProduct(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO products
			(name, reference, product_type_id, search_name)
			VALUES (?, ?, ?, ?)
			");

		$stmt->execute([
			!empty($data['name']) ? $data['name']:null,
			!empty($data['reference']) ? $data['reference']:null,
			!empty($data['product_type_id']) ?$data['product_type_id']: null,
			!empty($data['name']) ? normalize($data['name']):null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getProductById($newId);
	}

	public function createProductType(array $data): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO product_types
			(name, search_name)
			VALUES (?, ?)
			");

		$stmt->execute([
			!empty($data['name']) ?$data['name']: null,
			!empty($data['name']) ?normalize($data['name']): null,
		]);

		$newId = (int)$this->db->lastInsertId();

		return $this->getProductTypeById($newId);
	}

	public function deleteProduct(int $id): bool|array
	{
		$product = $this->getProductById($id);

		if($product)
		{
			$stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $product; 
	}

	public function deleteProductType(int $id): bool|array
	{
		$product_type = $this->getProductTypeById($id);

		if($product_type)
		{
			$stmt = $this->db->prepare("DELETE FROM product_types WHERE id = ?");
			$stmt->execute([$id]);
		}

		return $product_type; 
	}
}
