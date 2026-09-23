<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\LabItem;
use App\Services\LabItemService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ .'/../../../utils/util.php';

class LabItemController
{
	private LabItemService $service;
	public function __construct(PDO $db)
	{
		$model = new LabItem($db);
		$this->service = new LabItemService($model);
	}

	public function getLabItems(int $s_id): void
	{
		try{
			$filters = [
				't_item_id' => isset($_GET['t_item_id']) ? $_GET['t_item_id'] : null,
			];
			$lab_item_list = $this->service->listByService($s_id, $filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_item_list'=>$lab_item_list
			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getLabItem(int $s_id, int $id): void
	{
		try {
			$lab_item = $this->service->show($s_id, $id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_item'=>$lab_item
			]);
		} catch (RuntimeException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postLabItems(int $s_id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$lab_item = $this->service->create($s_id, $data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_item'=>$lab_item
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteLabItem(int $s_id, int $id): void
	{
		try {
			$lab_item = $this->service->delete($s_id, $id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_item' => $lab_item
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
