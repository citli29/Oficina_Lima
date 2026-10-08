<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\TimeRecord;
use App\Services\TimeRecordService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ . '/../../../utils/normalize.php';
require_once __DIR__ . '/../../../utils/util.php';

// "Registos de Tempo" page: time entries and punches of every service,
// addressed by their real id.
class TimeRecordController
{
	private TimeRecordService $service;

	public function __construct(PDO $db)
	{
		$this->service = new TimeRecordService(new TimeRecord($db));
	}

	private function filters(): array
	{
		return [
			'user_id' => $_GET['user_id'] ?? null,
			'service_id' => $_GET['service_id'] ?? null,
			'date_from' => $_GET['date_from'] ?? null,
			'date_to' => $_GET['date_to'] ?? null,
			'car_plate' => isset($_GET['car_plate']) ? normalizePlate($_GET['car_plate']) : null,
			'client_name' => isset($_GET['client_name']) ? normalize($_GET['client_name']) : null,
		];
	}

	private function respond(int $status, array $body): void
	{
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($body);
	}

	private function listResponse(string $key, array $result, ?array $pagination): array
	{
		$response = ['success' => true, $key => $result['rows']];

		if ($pagination !== null) {
			$response['pagination'] = [
				'page' => $pagination['page'],
				'per_page' => $pagination['per_page'],
				'total' => $result['total'],
				'total_pages' => (int) ceil($result['total'] / $pagination['per_page']),
			];
		}

		return $response;
	}

	private function body(): array
	{
		$data = json_decode(file_get_contents('php://input'), true);
		if (is_null($data))
			throw new InvalidArgumentException("JSON Body Invalid.", 400);
		return $data;
	}

	/* ---------- Time entries ---------- */

	public function getTimes(): void
	{
		try {
			$pagination = parsePagination($_GET);
			$result = $this->service->listTimes($this->filters(), $pagination, parseSort($_GET));
			$this->respond(200, $this->listResponse('time_list', $result, $pagination));
		} catch (RuntimeException | InvalidArgumentException $e) {
			$this->respond(httpStatusFromException($e), ['error' => $e->getMessage()]);
		}
	}

	public function postTime(): void
	{
		try {
			$this->respond(201, ['success' => true, 'time' => $this->service->createTime($this->body())]);
		} catch (RuntimeException | InvalidArgumentException $e) {
			$this->respond(httpStatusFromException($e), ['error' => $e->getMessage()]);
		}
	}

	public function putTime(int $id): void
	{
		try {
			$this->respond(200, ['success' => true, 'time' => $this->service->updateTime($id, $this->body())]);
		} catch (RuntimeException | InvalidArgumentException $e) {
			$this->respond(httpStatusFromException($e), ['error' => $e->getMessage()]);
		}
	}

	public function deleteTime(int $id): void
	{
		try {
			$this->respond(200, ['success' => true, 'time' => $this->service->deleteTime($id)]);
		} catch (RuntimeException | InvalidArgumentException $e) {
			$this->respond(httpStatusFromException($e), ['error' => $e->getMessage()]);
		}
	}

	/* ---------- Punches ---------- */

	public function getPunches(): void
	{
		try {
			$pagination = parsePagination($_GET);
			$openOnly = ($_GET['open'] ?? '') === '1';
			$result = $this->service->listPunches($this->filters(), $pagination, parseSort($_GET), $openOnly);
			$this->respond(200, $this->listResponse('punch_list', $result, $pagination));
		} catch (RuntimeException | InvalidArgumentException $e) {
			$this->respond(httpStatusFromException($e), ['error' => $e->getMessage()]);
		}
	}

	public function postPunch(): void
	{
		try {
			$this->respond(201, ['success' => true, 'punch' => $this->service->createPunch($this->body())]);
		} catch (RuntimeException | InvalidArgumentException $e) {
			$this->respond(httpStatusFromException($e), ['error' => $e->getMessage()]);
		}
	}

	public function putPunch(int $id): void
	{
		try {
			$this->respond(200, ['success' => true, 'punch' => $this->service->updatePunch($id, $this->body())]);
		} catch (RuntimeException | InvalidArgumentException $e) {
			$this->respond(httpStatusFromException($e), ['error' => $e->getMessage()]);
		}
	}

	public function deletePunch(int $id): void
	{
		try {
			$this->respond(200, ['success' => true, 'punch' => $this->service->deletePunch($id)]);
		} catch (RuntimeException | InvalidArgumentException $e) {
			$this->respond(httpStatusFromException($e), ['error' => $e->getMessage()]);
		}
	}
}
