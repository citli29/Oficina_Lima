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
			throw new RuntimeException("Show Product [ID Not Found]: {$id}.",404);
		return $product;
	}

	public function showProductType(int $id): array
	{
		if(!$product_type = $this->productModel->getProductTypeById($id))
			throw new RuntimeException("Show Product Type [ID Not Found]: {$id}.",404);
		return $product_type;
	}

	public function createProduct(array $data): array
	{
		if(empty($data['name'])) {
			throw new InvalidArgumentException("Create Product [Arguments Required]: Name.",400);
		}
		try
{
			return $this->productModel->createProduct($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Product [Argument Constraints]: Name must be provided. Reference must be unique. [{$e->errorInfo[2]}]",400);
		}
	}

	public function createProductType(array $data): array
	{
		if(empty($data['name'])) {
			throw new InvalidArgumentException("Create Product Type [Arguments Required]: Name.",400);
		}
		try
{
			return $this->productModel->createProductType($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException("Create Product Type [Argument Constraints]: Name must be provided. [{$e->errorInfo[2]}]",400);
		}
	}
	public function deleteProductType(int $id): array
	{
		try
		{
		if(!$product_type = $this->productModel->deleteProductType($id))
			throw new InvalidArgumentException("Delete Product Type [Invalid ID]: {$id}.",404);
		return $product_type;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException("Delete Product Type [Error]: [{$e->errorInfo[2]}]",400);
		}
	}

	public function deleteProduct(int $id): array
	{
		try
		{
		if(!$product= $this->productModel->deleteProduct($id))
			throw new InvalidArgumentException("Delete Product [Invalid ID]: {$id}.",404);
		return $product;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException("Delete Product Type [Error]: [{$e->errorInfo[2]}]",400);
		}
	}

	public function updateProductType(int $id, array $data): array
	{
		if(empty($data['name'])) {
			throw new InvalidArgumentException("Update Product Type [Argument required]: Name.",400);
		}
		try
		{
			$product_type = $this->productModel->updateProductType($id,$data);
			if(!$product_type) 
			throw new InvalidArgumentException("Update Product Type [Invalid ID]: {$id}.",404);
			return $product_type;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Product Type [Argument constraints]: Name must be unique. [{$e->errorInfo[2]}]",400);
		}
	}

	public function updateProduct(int $id, array $data): array
	{
		if(empty($data['name'])) {
			throw new InvalidArgumentException("Update Make [Arguments required]: name.",400);
		}

		try
		{
			$product= $this->productModel->updateProduct($id,$data);
			if(!$product)
			throw new InvalidArgumentException("Update Product [Invalid ID]: {$id}.",404);
			return $product;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Product [Argument constraints]: Name must be provided. Reference must be unique. [{$e->errorInfo[2]}]",400);
		}
	}
}
