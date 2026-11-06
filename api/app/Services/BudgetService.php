<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Budget;
use App\Models\Notification;
use App\Database\Database;
use InvalidArgumentException;
use PDOException;

class BudgetService
{
	private Budget $budgetModel;

	const TITLE_MAX_LENGTH = 96;

	public function __construct(Budget $budgetModel)
	{
		$this->budgetModel = $budgetModel;
	}

	// Keeps only the budget fields sent, checked and normalised: title/note
	// as trimmed-title/plain strings, done as 0/1 (a JSON false would
	// otherwise reach SQLite as '' and fail the 0/1 trigger).
	private function budgetFields(array $data): array
	{
		$fields = [];

		if (array_key_exists('title', $data)) {
			if (!is_string($data['title']))
				throw new InvalidArgumentException('O título tem de ser texto.', 400);
			$title = trim($data['title']);
			if (mb_strlen($title) > self::TITLE_MAX_LENGTH)
				throw new InvalidArgumentException('O título não pode ter mais de ' . self::TITLE_MAX_LENGTH . ' caracteres.', 400);
			$fields['title'] = $title;
		}

		if (array_key_exists('note', $data)) {
			if (!is_string($data['note']))
				throw new InvalidArgumentException('A nota tem de ser texto.', 400);
			$fields['note'] = $data['note'];
		}

		if (array_key_exists('done', $data)) {
			if (!in_array($data['done'], [true, false, 0, 1, '0', '1'], true))
				throw new InvalidArgumentException('O estado do orçamento deve ser verdadeiro ou falso.', 400);
			$fields['done'] = (int)(bool)$data['done'];
		}


		return $fields;
	}

	// "Pedir" (to_order) as 0/1: this product is to be ordered. Changed
	// with the rest of the orçamento (Editar → Guardar), so Orçamentado
	// locks it like the quantity.
	private function toOrderField(array $data): array
	{
		if (!array_key_exists('to_order', $data)) return [];

		if (!in_array($data['to_order'], [true, false, 0, 1, '0', '1'], true))
			throw new InvalidArgumentException('O campo Pedir deve ser verdadeiro ou falso.', 400);

		return ['to_order' => (int)(bool)$data['to_order']];
	}

	// quantity must be a whole number >= 1 (the trigger says so too; this
	// just turns "2" / 2.0 into 2 and rejects 1.5 before it gets there).
	private function quantityField(array $data): array
	{
		if (!array_key_exists('quantity', $data)) return [];

		$quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
		if ($quantity === false || $quantity < 1)
			throw new InvalidArgumentException('A quantidade deve ser um número inteiro maior que zero.', 400);

		return ['quantity' => $quantity];
	}

	/* ---------- budgets ---------- */

	// Every service's budgets (Orçamentos page).
	public function listAllBudgets(array $filters, ?array $pagination, ?array $sort = null): array
	{
		return $this->budgetModel->getAllBudgetsWithFilter($filters, $pagination, $sort);
	}

	public function listBudgets(int $s_id): array
	{
		return $this->budgetModel->getBudgetsByService($s_id);
	}

	public function getBudget(int $s_id, int $id): array
	{
		if (!$budget = $this->budgetModel->getBudgetBySidId($s_id, $id))
			throw new InvalidArgumentException("Get Budget [Invalid ID]: {$s_id} - {$id}.", 404);
		return $budget;
	}

	public function createBudget(int $s_id, array $data): array
	{
		try {
			return $this->budgetModel->createBudget($s_id, $this->budgetFields($data));
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateBudget(int $s_id, int $id, array $data): array
	{
		try {
			if (!$budget = $this->budgetModel->updateBudgetBySidId($s_id, $id, $this->budgetFields($data)))
				throw new InvalidArgumentException("Update Budget [Invalid ID]: {$s_id} - {$id}.", 404);
			return $budget;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	// $notify: tell the escritório it was deleted (the service page asks for
	// it; Gestão de Orçamentos, used by the escritório itself, doesn't).
	public function deleteBudget(int $s_id, int $id, bool $notify = false): array
	{
		try {
			// Delete + notification together: never one without the other.
			return Database::transaction($this->budgetModel->getDb(), function () use ($s_id, $id, $notify) {
				if (!$budget = $this->budgetModel->deleteBudgetBySidId($s_id, $id))
					throw new InvalidArgumentException("Delete Budget [Invalid ID]: {$s_id} - {$id}.", 404);
				if ($notify)
					$this->createNotification('deleted', $s_id, $budget);
				return $budget;
			});
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	/* ---------- budget products ---------- */

	public function listProducts(int $s_id, int $b_id): array
	{
		$this->getBudget($s_id, $b_id);
		return $this->budgetModel->getProductsByBudget($s_id, $b_id);
	}

	public function createProduct(int $s_id, int $b_id, array $data): array
	{
		try {
			$fields = ['product_id' => $data['product_id'] ?? null] + $this->quantityField($data) + $this->toOrderField($data);
			if (!$product = $this->budgetModel->createProduct($s_id, $b_id, $fields))
				throw new InvalidArgumentException("Create Budget Product [Invalid Budget]: {$s_id} - {$b_id}.", 404);
			return $product;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateProduct(int $s_id, int $b_id, int $id, array $data): array
	{
		try {
			if (!$product = $this->budgetModel->updateProductBySidBidId($s_id, $b_id, $id, $this->quantityField($data) + $this->toOrderField($data)))
				throw new InvalidArgumentException("Update Budget Product [Invalid ID]: {$s_id} - {$b_id} - {$id}.", 404);
			return $product;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteProduct(int $s_id, int $b_id, int $id): array
	{
		try {
			if (!$product = $this->budgetModel->deleteProductBySidBidId($s_id, $b_id, $id))
				throw new InvalidArgumentException("Delete Budget Product [Invalid ID]: {$s_id} - {$b_id} - {$id}.", 404);
			return $product;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	/* ---------- notification ---------- */

	// Tells the escritório an orçamento was created / edited. What it says
	// and where it links is all in BudgetNotification.
	public function notifyBudget(int $s_id, int $id, array $data): array
	{
		$event = $data['event'] ?? null;
		if (!is_string($event) || !isset(BudgetNotification::TITLES[$event])
			|| in_array($event, BudgetNotification::SERVER_ONLY_EVENTS, true))
			throw new InvalidArgumentException('Evento de notificação inválido.', 400);

		$budget = $this->getBudget($s_id, $id);

		try {
			return $this->createNotification($event, $s_id, $budget);
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	private function createNotification(string $event, int $s_id, array $budget): bool|array
	{
		$notificationModel = new Notification($this->budgetModel->getDb());
		return $notificationModel->createNotification(
			BudgetNotification::TYPE_NAME,
			BudgetNotification::TITLES[$event],
			BudgetNotification::message($event, $budget, $s_id, $this->budgetModel->getServiceCarLabel($s_id)),
			BudgetNotification::data($event, $s_id, (int) $budget['id'])
		);
	}
}
