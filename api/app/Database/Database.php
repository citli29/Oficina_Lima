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
			self::$instance->exec("PRAGMA journal_mode = WAL");
			self::$instance->exec("PRAGMA busy_timeout = 5000");
		}
		return self::$instance;
	}

	/**
	 * Runs $fn inside one transaction: everything it writes is committed
	 * together, or — if it throws — rolled back together and the exception
	 * rethrown. BEGIN IMMEDIATE takes the write lock up front, so two
	 * requests doing read-then-write (e.g. "next cluster number, then
	 * insert") can't both read the same value before either writes.
	 * Not reentrant: don't call it from inside another transaction.
	 */
	public static function transaction(PDO $db, callable $fn): mixed
	{
		$db->exec('BEGIN IMMEDIATE');

		try {
			$result = $fn();
			$db->exec('COMMIT');
			return $result;
		} catch (\Throwable $e) {
			$db->exec('ROLLBACK');
			throw $e;
		}
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
		string $defaultOrderBy,
		?string $tieBreaker = null
	): string {
		$direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

		// Rows that tie on the sort column need a fixed order too — without
		// one SQLite may return them differently per query, so with LIMIT/
		// OFFSET a row can show up on two pages or on none.
		$tie = $tieBreaker !== null ? ", {$tieBreaker} {$direction}" : '';

		if ($sortKey !== null && isset($sortableColumns[$sortKey])) {
			return $sql . " ORDER BY {$sortableColumns[$sortKey]} {$direction}{$tie}";
		}

		return $sql . " ORDER BY {$defaultOrderBy}" . ($tieBreaker !== null ? ", {$tieBreaker}" : '');
	}

}
