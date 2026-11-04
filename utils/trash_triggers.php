<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

// "Lixo" (trash) for deleted rows.
//
// Creates the deleted_rows table (if missing) and, for every table in
// TRASHED_TABLES, an AFTER DELETE trigger "trash_<table>" that saves the
// whole deleted row as JSON. Rows older than KEEP_DAYS are cleared
// automatically. Restore with utils/restore_deleted.php.
//
// The triggers list each column by name, read from the live schema.
// ⚠️ RE-RUN THIS AFTER ANY ALTER TABLE on one of these tables, otherwise
// new columns aren't saved. It's safe to run any number of times: every
// trigger is dropped and recreated, deleted_rows itself is never touched.
//
//   php utils/trash_triggers.php            (uses DB_PATH from .env)
//   php utils/trash_triggers.php --print    (only prints the SQL)

const KEEP_DAYS = 90;

// Tables where a delete loses real work. Left out on purpose: login and
// session tables (auth_tokens, login_attempts, shop_accounts),
// notifications (they already delete themselves after 30 days) and the
// fixed type lists (notification_types, user_types).
const TRASHED_TABLES = [
	'budget_products', 'budgets',
	'cars', 'clients', 'makes', 'models',
	'events',
	'lab_action_values', 'lab_items', 'lab_property_values',
	'product_types', 'products',
	'schedule_lab_action_values', 'schedule_lab_items', 'schedules',
	'service_associations', 'service_types', 'services',
	'services_applied_products', 'services_products_requested',
	'services_user_time', 'services_user_time_punches',
	'tabled_action_values', 'tabled_actions', 'tabled_items', 'tabled_properties',
	'users',
];

$printOnly = in_array('--print', $argv, true);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$db = new PDO('sqlite:' . $_ENV['DB_PATH']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = [];

$sql[] = "CREATE TABLE IF NOT EXISTS deleted_rows (
	id INTEGER PRIMARY KEY,
	table_name TEXT NOT NULL,
	row_id INTEGER,
	data TEXT NOT NULL,
	deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	restored_at DATETIME
)";
$sql[] = "CREATE INDEX IF NOT EXISTS idx_deleted_rows_table_row ON deleted_rows(table_name, row_id)";
$sql[] = "CREATE INDEX IF NOT EXISTS idx_deleted_rows_deleted_at ON deleted_rows(deleted_at)";

$sql[] = "DROP TRIGGER IF EXISTS deleted_rows_delete_older_than_" . KEEP_DAYS . "_days";
$sql[] = "CREATE TRIGGER deleted_rows_delete_older_than_" . KEEP_DAYS . "_days
AFTER INSERT ON deleted_rows
BEGIN
	DELETE FROM deleted_rows WHERE deleted_at < datetime('now', '-" . KEEP_DAYS . " days');
END";

foreach (TRASHED_TABLES as $table) {
	$columns = $db->query("SELECT name, pk FROM pragma_table_info(" . $db->quote($table) . ")")->fetchAll(PDO::FETCH_ASSOC);
	if (!$columns) {
		fwrite(STDERR, "Table not found, skipped: {$table}" . PHP_EOL);
		continue;
	}

	$pk = array_values(array_filter($columns, fn ($c) => (int) $c['pk'] > 0));
	$rowId = count($pk) === 1 ? 'OLD."' . $pk[0]['name'] . '"' : 'OLD.rowid';

	$pairs = array_map(
		fn ($c) => "'" . str_replace("'", "''", $c['name']) . "', OLD.\"" . $c['name'] . '"',
		$columns
	);

	$sql[] = "DROP TRIGGER IF EXISTS trash_{$table}";
	$sql[] = "CREATE TRIGGER trash_{$table}
AFTER DELETE ON \"{$table}\"
FOR EACH ROW
BEGIN
	INSERT INTO deleted_rows (table_name, row_id, data)
	VALUES ('{$table}', {$rowId}, json_object(" . implode(', ', $pairs) . "));
END";
}

if ($printOnly) {
	echo implode(";\n\n", $sql) . ";\n";
	exit(0);
}

$db->exec('BEGIN IMMEDIATE');
try {
	foreach ($sql as $statement)
		$db->exec($statement);
	$db->exec('COMMIT');
} catch (Throwable $e) {
	$db->exec('ROLLBACK');
	fwrite(STDERR, 'Failed, nothing changed: ' . $e->getMessage() . PHP_EOL);
	exit(1);
}

echo 'Trash triggers created for ' . count(TRASHED_TABLES) . ' tables (keep ' . KEEP_DAYS . ' days).' . PHP_EOL;
