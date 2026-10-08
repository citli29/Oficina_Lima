<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\TimeRecord;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class TimeRecordService
{
	private TimeRecord $model;

	public function __construct(TimeRecord $model)
	{
		$this->model = $model;
	}

	/* ---------- Time entries ---------- */

	public function listTimes(array $filters, ?array $pagination, ?array $sort): array
	{
		return $this->model->listTimes($filters, $pagination, $sort);
	}

	public function createTime(array $data): array
	{
		try {
			return $this->model->createTime($this->timeData($data));
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateTime(int $id, array $data): array
	{
		try {
			$time = $this->model->updateTime($id, $this->timeData($data));
			if (!$time) throw new RuntimeException("Este registo de tempo já não existe (foi apagado entretanto).", 404);
			return $time;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteTime(int $id): array
	{
		try {
			$time = $this->model->deleteTime($id);
			if (!$time) throw new RuntimeException("Este registo de tempo já não existe (foi apagado entretanto).", 404);
			return $time;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	private function timeData(array $data): array
	{
		return [
			'service_id' => $this->requiredId($data, 'service_id', 'O serviço é obrigatório.'),
			'user_id' => $this->requiredId($data, 'user_id', 'Escolha um funcionário.'),
			'minutes' => is_numeric($data['minutes'] ?? null) ? (int) $data['minutes'] : null,
			'date' => $this->requiredDate($data),
		];
	}

	/* ---------- Punches ---------- */

	public function listPunches(array $filters, ?array $pagination, ?array $sort, bool $openOnly): array
	{
		return $this->model->listPunches($filters, $pagination, $sort, $openOnly);
	}

	public function createPunch(array $data): array
	{
		[$fields, $start, $end] = $this->punchData($data);
		try {
			return $this->model->createPunch($fields, $start, $end);
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updatePunch(int $id, array $data): array
	{
		[$fields, $start, $end] = $this->punchData($data);
		try {
			$punch = $this->model->updatePunch($id, $fields, $start, $end);
			if (!$punch) throw new RuntimeException("Este ponto já não existe (foi apagado entretanto).", 404);
			return $punch;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deletePunch(int $id): array
	{
		try {
			$punch = $this->model->deletePunch($id);
			if (!$punch) throw new RuntimeException("Este ponto já não existe (foi apagado entretanto).", 404);
			return $punch;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	private function punchData(array $data): array
	{
		$fields = [
			'service_id' => $this->requiredId($data, 'service_id', 'O serviço é obrigatório.'),
			'user_id' => $this->requiredId($data, 'user_id', 'Escolha um funcionário.'),
			'date' => $this->requiredDate($data),
		];
		$start = $this->parseTime($data['start'] ?? null, 'início');
		$end = $this->parseTime($data['end'] ?? null, 'fim');

		if ($end !== null && $start === null)
			throw new InvalidArgumentException('Um ponto com hora de fim precisa da hora de início.', 400);

		return [$fields, $start, $end];
	}

	/* ---------- Helpers ---------- */

	private function requiredId(array $data, string $key, string $message): int
	{
		if (empty($data[$key]) || !is_numeric($data[$key]))
			throw new InvalidArgumentException($message, 400);
		return (int) $data[$key];
	}

	private function requiredDate(array $data): string
	{
		$date = trim((string) ($data['date'] ?? ''));
		if ($date === '') throw new InvalidArgumentException('A data é obrigatória.', 400);
		return $date;
	}

	// "HH:MM" → [hours, minutes]; empty → null.
	private function parseTime(mixed $value, string $label): ?array
	{
		$value = trim((string) ($value ?? ''));
		if ($value === '') return null;

		if (!preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m) || (int) $m[1] > 23 || (int) $m[2] > 59)
			throw new InvalidArgumentException("A hora de {$label} é inválida.", 400);

		return [(int) $m[1], (int) $m[2]];
	}
}
