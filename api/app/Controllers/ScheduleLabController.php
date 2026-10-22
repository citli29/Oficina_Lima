<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\ScheduleLab;
use App\Services\ScheduleLabService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ .'/../../../utils/util.php';

class ScheduleLabController
{
	private ScheduleLabService $service;
	public function __construct(PDO $db)
	{
		$model = new ScheduleLab($db);
		$this->service = new ScheduleLabService($model);
	}

	public function getLabItems(int $s_id): void
	{
		try{
			$lab_item_list = $this->service->listItems($s_id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_item_list'=>$lab_item_list
			]);
		}catch(RuntimeException $e){
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postLabItems(int $s_id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$lab_item = $this->service->createItem($s_id, $data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_item'=>$lab_item
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteLabItem(int $s_id, int $id): void
	{
		try {
			$lab_item = $this->service->deleteItem($s_id, $id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_item' => $lab_item
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getLabActionValues(int $s_id): void
	{
		try{
			$lab_action_value_list = $this->service->listActionValues($s_id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_action_value_list'=>$lab_action_value_list
			]);
		}catch(RuntimeException $e){
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postLabActionValues(int $s_id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$lab_action_value = $this->service->createActionValue($s_id, $data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_action_value'=>$lab_action_value
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteLabActionValue(int $s_id, int $id): void
	{
		try {
			$lab_action_value = $this->service->deleteActionValue($s_id, $id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'lab_action_value' => $lab_action_value
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
