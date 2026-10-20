<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\ItemComponent;
use App\Services\ItemComponentService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ .'/../../../utils/normalize.php';
require_once __DIR__ .'/../../../utils/util.php';

class ItemComponentController
{
	private ItemComponentService $service;
	public function __construct(PDO $db)
	{
		$model = new ItemComponent($db);
		$this->service = new ItemComponentService($model);
	}

	public function getProperties():void
	{
		try {
			$filters = [
				'name' => isset($_GET['name']) ? normalize($_GET['name']) : null,
				'item_name' => isset($_GET['item_name']) ? normalize($_GET['item_name']) : null,
				't_item_id' => isset($_GET['t_item_id']) ? $_GET['t_item_id'] : null,
			];

			$pagination = parsePagination($_GET);

			$result = $this->service->listProperties($filters, $pagination);

			$response = [
				'success' => true,
				'property_list' => $result['rows'],
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
		} catch (RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getActions():void
	{
		try {
			$filters = [
				'name' => isset($_GET['name']) ? normalize($_GET['name']) : null,
				'item_name' => isset($_GET['item_name']) ? normalize($_GET['item_name']) : null,
				't_item_id' => isset($_GET['t_item_id']) ? $_GET['t_item_id'] : null,
			];

			$pagination = parsePagination($_GET);

			$result = $this->service->listActions($filters, $pagination);

			$response = [
				'success' => true,
				'action_list' => $result['rows'],
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
		} catch (RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}


	public function getProperty(int $id):void
	{
		try {
			$property = $this->service->showProperty($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'property'=>$property
			]);
		} catch (RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getAction(int $id):void
	{
		try {
			$action = $this->service->showAction($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'action'=>$action
			]);
		} catch (RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}


	public function postProperties():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$property = $this->service->createProperty($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'property'=>$property
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postActions():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$action = $this->service->createAction($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'action'=>$action
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}


	public function putProperty(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$property = $this->service->updateProperty($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'property'=>$property
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putAction(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$action = $this->service->updateAction($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'action'=>$action
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}


	public function deleteProperty(int $id):void
	{
		try {
			$property = $this->service->deleteProperty($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'property' => $property
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteAction(int $id):void
	{
		try {
			$action = $this->service->deleteAction($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'action' => $action
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

}
