<?php

namespace App\Database;

use InvalidArgumentException;
use PDO;

class Database
{
	private static ?PDO $instance = null;

	public static function getConnection(): PDO
	{
		if (self::$instance === null) {
			self::$instance= new PDO('sqlite:' . $_ENV['DB_PATH'] );
			self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$instance->exec("PRAGMA foreign_keys = on");
		}
		return self::$instance;
	}

	public static function applyFilters(string $sql, array $filters, array $rules, array &$params = []): string
	{
		foreach ($filters as $key => $value) {

			// skip empty values
			if ($value === null || $value === '') {
				continue;
			}

			// skip unknown filters (security)
			if (!isset($rules[$key])) {
				continue;
			}

			$rule = $rules[$key];

			$column = $rule['column'];
			$operator = strtoupper($rule['operator']);

			// whitelist operators
			$allowed = ['=', '!=', '>', '<', '>=', '<=', 'LIKE'];

			if (!in_array($operator, $allowed, true)) {
				throw new InvalidArgumentException("Invalid operator: $operator");
			}

			// LIKE handling
			if ($operator === 'LIKE') {
				$value = "%{$value}%";
			}

			$sql .= " AND {$column} {$operator} ?";

			$params[] = $value;
		}

		return $sql;
	}

	public static function applyPagination(string $sql, array &$params, int $page, int $perPage): string
	{
		$sql .= " LIMIT ? OFFSET ?";

		$params[] = $perPage;
		$params[] = ($page - 1) * $perPage;

		return $sql;
	}

	public static function getTotalCount(PDO $db, string $sql, array $params): int
	{
		$stmt = $db->prepare("SELECT COUNT(*) AS total FROM ({$sql}) sub");
		$stmt->execute($params);

		return (int) $stmt->fetchColumn();
	}

	public static function applySort(
		string $sql,
		array $sortableColumns,
		?string $sortKey,
		string $direction,
		string $defaultOrderBy
	): string {
		$direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

		if ($sortKey !== null && isset($sortableColumns[$sortKey])) {
			return $sql . " ORDER BY {$sortableColumns[$sortKey]} {$direction}";
		}

		return $sql . " ORDER BY {$defaultOrderBy}";
	}

}
