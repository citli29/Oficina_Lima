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
				'designation' => isset($_GET['designation']) ? $_GET['designation'] : null,
				'ref' => isset($_GET['ref']) ? $_GET['ref'] : null,
				'p_type' => isset($_GET['p_type']) ? $_GET['p_type'] : null,
			];

			$product_list = $this->service->listProducts($filters);

			http_response_code(empty($client_list)?204:200);
			header('Content-Type: application/json');

			echo json_encode([
				'success' => true,
				'product_list'=>$product_list
			]);

		}catch(RuntimeException $e){
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getProductTypes(): void
	{
		try{
			$filters = [
				'designation' => isset($_GET['designation']) ? $_GET['designation'] : null,
			];

			$product_type_list = $this->service->listProductTypes($filters);

			http_response_code(empty($client_list)?204:200);
			header('Content-Type: application/json');

			echo json_encode([
				'success' => true,
				'product_type_list'=>$product_type_list
			]);

		}catch(RuntimeException $e){
			http_response_code($e->getCode());
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
			http_response_code($e->getCode());
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
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postProducts():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$product = $this->service->createProduct($input);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product'=>$product
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postProductTypes():void
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			$product_type = $this->service->createProductType($input);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product_type'=>$product_type
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
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
			http_response_code($e->getCode());
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
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putProduct(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			$product = $this->service->updateProduct($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product'=>$product
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putProductType(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			$product_type = $this->service->updateProductType($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'product_type'=>$product_type
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code($e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}

