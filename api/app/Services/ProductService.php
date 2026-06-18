<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Product;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ProductService
{
	private Product $productModel;

	public function __construct(Product $productModel)
	{
		$this->productModel = $productModel;
	}

	public function listProducts(array $filters): array
	{
		return $this->productModel->getProductsWithFilter($filters);
	}

	public function listProductTypes(array $filters): array
	{
		return $this->productModel->getProductTypesWithFilter($filters);
	}

	public function showProduct(int $id): array
	{
		if(!$product = $this->productModel->getProductById($id))
			throw new RuntimeException("Product not found: {$id}");
		return $product;
	}

	public function showProductType(int $id): array
	{
		if(!$product_type = $this->productModel->getProductTypeById($id))
			throw new RuntimeException("Product Type not found: {$id}");
		return $product_type;
	}

	public function createProduct(array $data): array
	{
		if(empty($data['designation'])) {
			throw new InvalidArgumentException("
				Mandatory arguments:\n
				\tDesignation
				");
		}
		try
{
			return $this->productModel->createProduct($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("
				Argument constraints:\n
				\tDesignation must be provided\n
				\tReference unique\n
				{$e->getMessage()}
				");
		}
	}

	public function createProductType(array $data): array
	{
		if(empty($data['designation'])) {
			throw new InvalidArgumentException("
				Mandatory arguments:\n
				\tDesignation
				");
		}
		try
{
			return $this->productModel->createProductType($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("
				Argument constraints:\n
				\tDesignation must be provided\n
				{$e->getMessage()}
				");
		}
	}
	public function deleteProductType(int $id): array
	{
		if(!$product_type = $this->productModel->deleteProductType($id))
			throw new InvalidArgumentException("{$id}: Invalid ID");
		return $product_type;
	}

	public function deleteProduct(int $id): array
	{
		if(!$product= $this->productModel->deleteProduct($id))
			throw new InvalidArgumentException("{$id}: Invalid ID");
		return $product;
	}

	public function updateProductType(int $id, array $data): array
	{
		if(empty($data['designation'])) {
			throw new InvalidArgumentException("
				Mandatory arguments:\n
				\tDesignation\n
				");
		}
		try
		{
			$product_type = $this->productModel->updateProductType($id,$data);
			if(!$product_type) throw new InvalidArgumentException("
				Invalid Product Type ID: {$id}
				"); 
			return $product_type;
		} catch (PDOException $e){
			throw new InvalidArgumentException("
				Argument constraints:\n
				\tDesignation unique\n
				{$e->getMessage()}
				");
		}
	}
	public function updateProduct(int $id, array $data): array
	{
		if(empty($data['designation'])) {
			throw new InvalidArgumentException("
				Mandatory arguments:\n
				\tDesignation\n
				");
		}
		try
		{
			$product= $this->productModel->updateProduct($id,$data);
			if(!$product) throw new InvalidArgumentException("
				Invalid Product ID: {$id}
				"); 
			return $product;
		} catch (PDOException $e){
			throw new InvalidArgumentException("
				Argument constraints:\n
				\tDesignation must be provided\n
				\tReference unique\n
				{$e->getMessage()}
				");
		}
	}
}
