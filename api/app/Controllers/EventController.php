<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;
use App\Services\EventService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ . '/../../../utils/normalize.php';
require_once __DIR__ . '/../../../utils/util.php';

class EventController
{
	private EventService $service;

	public function __construct(PDO $db)
	{
		$model = new Event($db);
		$this->service = new EventService($model);
	}

	public function getEvents(): void
	{
		try {
			$filters = [
				'title' => isset($_GET['title']) ? $_GET['title'] : null,
				'user_id' => isset($_GET['user_id']) ? $_GET['user_id'] : null,
				'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : null,
				'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : null,
			];

			$pagination = parsePagination($_GET);

			$result = $this->service->listEvents($filters, $pagination);

			$response = [
				'success' => true,
				'event_list' => $result['rows'],
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
			http_response_code((int) $e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getEvent(int $id): void
	{
		try {
			$event = $this->service->showEvent($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'event' => $event
			]);
		} catch (RuntimeException $e) {
			http_response_code((int) $e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postEvents(): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if (is_null($data))
				throw new InvalidArgumentException("JSON Body Invalid.", 400);

			$event = $this->service->createEvent($data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'event' => $event
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int) $e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putEvent(int $id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if (is_null($data))
				throw new InvalidArgumentException("JSON Body Invalid.", 400);

			$event = $this->service->updateEvent($id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'event' => $event
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int) $e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteEvent(int $id): void
	{
		try {
			$event = $this->service->deleteEvent($id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'event' => $event
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int) $e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
