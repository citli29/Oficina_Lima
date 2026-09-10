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

	public function listProducts(array $filters, ?array $pagination = null): array
	{
		return $this->productModel->getProductsWithFilter($filters, $pagination);
	}
	public function listProductsOr(string $str): array
	{
		return $this->productModel->getProductsWithOrFilter($str);
	}

	public function listProductTypes(array $filters, ?array $pagination = null): array
	{
		return $this->productModel->getProductTypesWithFilter($filters, $pagination);
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
		try
{
			return $this->productModel->createProduct($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function createProductType(array $data): array
	{
		try
{
			return $this->productModel->createProductType($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
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
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
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
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateProductType(int $id, array $data): array
	{
		try
		{
			$product_type = $this->productModel->updateProductType($id,$data);
			if(!$product_type) 
			throw new InvalidArgumentException("Update Product Type [Invalid ID]: {$id}.",404);
			return $product_type;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateProduct(int $id, array $data): array
	{
		try
		{
			$product= $this->productModel->updateProduct($id,$data);
			if(!$product)
			throw new InvalidArgumentException("Update Product [Invalid ID]: {$id}.",404);
			return $product;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}
}
