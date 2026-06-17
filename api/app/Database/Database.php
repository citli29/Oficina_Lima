<?php

namespace App\Database;

use InvalidArgumentException;
use PDO;

class Database
{
	private static ?PDO $instance = null;

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

	public static function getConnection(): PDO
	{
		if (self::$instance === null) {
			self::$instance= new PDO('sqlite:' . __DIR__ . '/../../../database/database.db');
			self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		}
		return self::$instance;
	}
}
