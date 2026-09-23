<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Item;
use App\Services\ItemService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ .'/../../../utils/util.php';

class ItemController
{
	private ItemService $service;
	public function __construct(PDO $db)
	{
		$model = new Item($db);
		$this->service = new ItemService($model);
	}

	public function getItems(): void
	{
		try{
			$pagination = parsePagination($_GET);

			$result = $this->service->listItems($pagination);

			$response = [
				'success' => true,
				'item_list' => $result['rows'],
			];

			if ($pagination !== null) {
				$response['pagination'] = [
					'page' => $pagination['page'],
					'per_page' => $pagination['per_page'],
					'total' => $result['total'],
					'total_pages' => (int) ceil($result['total'] / $pagination['per_page']),
				];
			}

			http_response_code(200);
			header('Content-Type: application/json');

			echo json_encode($response);

		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getItem(int $id):void
	{
		try {
			$item = $this->service->showItem($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'item'=>$item
			]);
		} catch (RuntimeException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postItems():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$item = $this->service->createItem($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'item'=>$item
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteItem(int $id):void
	{
		try {

			$item = $this->service->deleteItem($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'item' => $item
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putItem(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);
			$item = $this->service->updateItem($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'item'=>$item
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
