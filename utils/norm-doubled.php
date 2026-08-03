<?php
declare(strict_types=1);

function getConnection(): PDO
{
	// change this to 
	//$instance = new PDO('sqlite:' . '/home/oficina-lima/Server/Database/database.db');
	$instance = new PDO('sqlite:' . '/home/citlima/Desktop/Server/Database/Database.db');

	$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$instance->exec("PRAGMA foreign_keys = on");

	return $instance;
}

$tables = [
	'users' => [
		'name' => 'name',
		'search_name' => 'search_name'
	],
	'clients' => [
		'name' => 'name',
		'search_name' => 'search_name'
	],
	'makes' => [
		'name' => 'name',
		'search_name' => 'search_name'
	],
	'models' => [
		'name' => 'name',
		'search_name' => 'search_name'
	],
	'cars' => [
		'name' => 'plate',
		'search_name' => 'search_plate'
	],
	'product_types' => [
		'name' => 'name',
		'search_name' => 'search_name'
	],
	'products' => [
		'name' => 'name',
		'search_name' => 'search_name'
	],
];

function columnExists(PDO $db, string $table, string $column): bool
{
	$stmt = $db->query("PRAGMA table_info($table)");
	$columns = $stmt->fetchAll();

	foreach ($columns as $col) {
		if ($col['name'] === $column) {
			return true;
		}
	}

	return false;
}

function add_search_name(array $tables): void
{
	print_r($tables);

	$db = getConnection();

	foreach ($tables as $table => $def) {

		if (!columnExists($db, $table, $def['search_name'])) {
			$sql = "ALTER TABLE {$table} ADD COLUMN {$def['search_name']} VARCHAR(255) NULL";
			echo $sql . PHP_EOL;
			$db->exec($sql);
		} else {
			echo "Skipping {$table}: column {$def['search_name']} already exists." . PHP_EOL;
		}

		$sql = "SELECT id, {$def['name']} FROM {$table}";
		echo $sql . PHP_EOL;

		$stmt = $db->query($sql);
		$rows = $stmt->fetchAll();

		$update = $db->prepare(
			"UPDATE {$table} SET {$def['search_name']} = ? WHERE id = ?"
		);

		foreach ($rows as $row) {
			$update->execute([
				normalize($row[$def['name']]),
				$row['id']
			]);
		}
	}
}
function normalize(string $str): string
{
    $str = mb_strtolower($str, 'UTF-8');

    $str = strtr($str, [
        'á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','-'=>'',
    ]);

    // Keep only letters, digits and spaces
    $str = preg_replace('/[^a-z0-9 ]+/', ' ', $str);

    // Collapse multiple spaces
    $str = preg_replace('/\s+/', ' ', $str);

    return trim($str);
}

add_search_name($tables);
