<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class ProductController
{
	private ProductService $service;
	public function __construct(PDO $db)
	{
		$model = new Product($db);
		$this->service = new ProductService($model);
	}

	public function getProducts(): void
	{
		try{
			$filters = [
				'name' => isset($_GET['name']) ? $_GET['name'] : null,
				'reference' => isset($_GET['reference']) ? $_GET['reference'] : null,
				'p_t_name' => isset($_GET['p_t_name']) ? $_GET['p_t_name'] : null,
				'p_t_id' => isset($_GET['p_t_id']) ? $_GET['p_t_id'] : null,
			];

			$product_list = $this->service->listProducts($filters);

			http_response_code(200);
			header('Content-Type: application/json');

			echo json_encode([
				'success' => true,
				'product_list'=>$product_list
			]);

		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getProductTypes(): void
	{
		try{
			$filters = [
				'name' => isset($_GET['name']) ? $_GET['name'] : null,
			];

			$product_type_list = $this->service->listProductTypes($filters);

			http_response_code(200);
			header('Content-Type: application/json');

			echo json_encode([
				'success' => true,
				'product_type_list'=>$product_type_list
			]);

		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getProduct(int $id):void
	{
		try {
			$product = $this->service->showProduct($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product'=>$product
			]);
		} catch (RuntimeException$e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getProductType(int $id):void
	{
		try {
			$product_type = $this->service->showProductType($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product_type'=>$product_type
			]);
		} catch (RuntimeException$e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postProducts():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$product = $this->service->createProduct($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product'=>$product
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postProductTypes():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$product_type = $this->service->createProductType($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product_type'=>$product_type
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteProduct(int $id):void
	{
		try {

			$product = $this->service->deleteProduct($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product' => $product
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteProductType(int $id):void
	{
		try {

			$product_type = $this->service->deleteProductType($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product_type' => $product_type
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putProduct(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);
			$product = $this->service->updateProduct($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product'=>$product
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putProductType(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);
			$product_type = $this->service->updateProductType($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product_type'=>$product_type
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}

