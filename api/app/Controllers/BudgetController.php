<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Budget;
use App\Services\BudgetService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ .'/../../../utils/util.php';

// Orçamentos of a service: /api/services/{s_id}/budgets[/{id}]
// and their products: /api/services/{s_id}/budgets/{b_id}/products[/{id}]
class BudgetController
{
	private BudgetService $service;
	public function __construct(PDO $db)
	{
		$model = new Budget($db);
		$this->service = new BudgetService($model);
	}

	private function readJson(): array
	{
		$data = json_decode(file_get_contents('php://input'), true);
		if (!is_array($data))
			throw new InvalidArgumentException("JSON Body Invalid.", 400);
		return $data;
	}

	// Runs $fn and answers {success, $key: result} with $status, or
	// {error} with the exception's status.
	private function respond(int $status, string $key, callable $fn): void
	{
		try {
			$result = $fn();

			http_response_code($status);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				$key => $result
			]);
		} catch (InvalidArgumentException | RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			header('Content-Type: application/json');
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	/* ---------- all services (Orçamentos page) ---------- */

	// GET /api/budgets?done=true|false&service_finished=true|false&from=&q=&id=&p=&u=&sort=&dir=
	// from: UTC 'YYYY-MM-DD HH:MM:SS' (created_at is stored in UTC).
	public function getAllBudgets(): void
	{
		try {
			$filters = [
				'done' => match ($_GET['done'] ?? null) {
					'true' => true,
					'false' => false,
					default => null,
				},
				// Raw text (normalize() would drop the plate's dashes and accents
				// the stored values still have); the model compares plates
				// without dashes/spaces itself.
				'q' => isset($_GET['q']) && trim($_GET['q']) !== '' ? trim($_GET['q']) : null,
				'id' => $_GET['id'] ?? null,
				'from' => isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $_GET['from']) ? $_GET['from'] : null,
				'service_finished' => match ($_GET['service_finished'] ?? null) {
					'true' => true,
					'false' => false,
					default => null,
				},
			];
			$pagination = parsePagination($_GET);
			$result = $this->service->listAllBudgets($filters, $pagination, parseSort($_GET));

			$response = ['success' => true, 'budget_list' => $result['rows']];
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
		} catch (InvalidArgumentException | RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			header('Content-Type: application/json');
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	/* ---------- budgets ---------- */

	public function getBudgets(int $s_id): void
	{
		$this->respond(200, 'budget_list', fn() => $this->service->listBudgets($s_id));
	}

	public function getBudget(int $s_id, int $id): void
	{
		$this->respond(200, 'budget', fn() => $this->service->getBudget($s_id, $id));
	}

	public function postBudget(int $s_id): void
	{
		$this->respond(201, 'budget', fn() => $this->service->createBudget($s_id, $this->readJson()));
	}

	// Partial: only title / note / done sent are changed.
	public function patchBudget(int $s_id, int $id): void
	{
		$this->respond(200, 'budget', fn() => $this->service->updateBudget($s_id, $id, $this->readJson()));
	}

	// ?notify=1 also tells the escritório (BudgetNotification 'deleted').
	public function deleteBudget(int $s_id, int $id): void
	{
		$notify = ($_GET['notify'] ?? '') === '1';
		$this->respond(200, 'budget', fn() => $this->service->deleteBudget($s_id, $id, $notify));
	}

	// {"event": "created" | "edited"} -> notification to the escritório.
	public function postBudgetNotify(int $s_id, int $id): void
	{
		$this->respond(201, 'notification', fn() => $this->service->notifyBudget($s_id, $id, $this->readJson()));
	}

	/* ---------- budget products ---------- */

	public function getBudgetProducts(int $s_id, int $b_id): void
	{
		$this->respond(200, 'budget_product_list', fn() => $this->service->listProducts($s_id, $b_id));
	}

	public function postBudgetProduct(int $s_id, int $b_id): void
	{
		$this->respond(201, 'budget_product', fn() => $this->service->createProduct($s_id, $b_id, $this->readJson()));
	}

	public function patchBudgetProduct(int $s_id, int $b_id, int $id): void
	{
		$this->respond(200, 'budget_product', fn() => $this->service->updateProduct($s_id, $b_id, $id, $this->readJson()));
	}

	public function deleteBudgetProduct(int $s_id, int $b_id, int $id): void
	{
		$this->respond(200, 'budget_product', fn() => $this->service->deleteProduct($s_id, $b_id, $id));
	}
}
