<?php declare(strict_types=1);

namespace App\Controllers;

use App\Services\TrashService;
use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

require_once __DIR__ .'/../../../utils/util.php';

// "Lixo" page: deleted rows saved by the trash_<table> triggers. Admin only.
class TrashController
{
	private PDO $db;
	private TrashService $service;

	public function __construct(PDO $db)
	{
		$this->db = $db;
		$this->service = new TrashService($db);
	}

	// ?table=&status=deleted|restored|all&q=&p=&u=
	public function getTrash(): void
	{
		$this->respond(function () {
			$page = max(1, (int) ($_GET['p'] ?? 1));
			$perPage = max(1, min(200, (int) ($_GET['u'] ?? 50)));
			$result = $this->service->list([
				'table' => $_GET['table'] ?? null,
				'status' => $_GET['status'] ?? 'deleted',
				'q' => $_GET['q'] ?? null,
			], $page, $perPage);

			return [
				'trash_list' => $result['rows'],
				'tables' => $result['tables'],
				'total' => $result['total'],
				'page' => $page,
				'pages' => max(1, (int) ceil($result['total'] / $perPage)),
			];
		});
	}

	// Body: {"ids": [1, 2, 3]} — restored together, all or nothing.
	public function postRestore(): void
	{
		$this->respond(function () {
			$data = json_decode(file_get_contents('php://input'), true);
			if (!is_array($data) || !isset($data['ids']) || !is_array($data['ids']))
				throw new InvalidArgumentException('JSON Body Invalid.', 400);

			return ['restored' => $this->service->restore($data['ids'])];
		});
	}

	private function respond(callable $fn): void
	{
		if (!AuthController::requireAdmin($this->db))
			return;

		header('Content-Type: application/json');
		try {
			$result = $fn();
			http_response_code(200);
			echo json_encode(['success' => true] + $result);
		} catch (InvalidArgumentException | RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		} catch (PDOException $e) {
			http_response_code(500);
			echo json_encode(['error' => dbErrorMessage($e)]);
		}
	}
}
