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
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getActionTabledValues():void
	{
		try {
			$filters = [
				'value' => isset($_GET['value']) ? $_GET['value'] : null,
				'action_name' => isset($_GET['action_name']) ? normalize($_GET['action_name']) : null,
				't_action_id' => isset($_GET['t_action_id']) ? $_GET['t_action_id'] : null,
			];

			$pagination = parsePagination($_GET);

			$result = $this->service->listActionTabledValues($filters, $pagination);

			$response = [
				'success' => true,
				'action_tabled_value_list' => $result['rows'],
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
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getActionTabledValue(int $id):void
	{
		try {
			$action_tabled_value = $this->service->showActionTabledValue($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'action_tabled_value'=>$action_tabled_value
			]);
		} catch (RuntimeException $e) {
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postActionTabledValues():void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$action_tabled_value = $this->service->createActionTabledValue($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'action_tabled_value'=>$action_tabled_value
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putActionTabledValue(int $id):void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$action_tabled_value = $this->service->updateActionTabledValue($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'action_tabled_value'=>$action_tabled_value
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
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
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteActionTabledValue(int $id):void
	{
		try {
			$action_tabled_value = $this->service->deleteActionTabledValue($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'action_tabled_value' => $action_tabled_value
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
