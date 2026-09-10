<?php
declare(strict_types=1);

// One-off backfill for search_name / search_plate on rows that existed
// before those columns were added by server_migration.sql. New rows
// already get these computed by the app itself (Models/Product.php,
// Models/Car.php); this just catches up everything that predates it.
//
// Usage: php backfill_search_names.php /path/to/Database.db
// Safe to re-run: recomputes every row from its current name/plate,
// wrapped in a single transaction (all-or-nothing).

require_once __DIR__ . '/normalize.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php backfill_search_names.php /path/to/Database.db\n");
    exit(1);
}

$dbPath = $argv[1];

if (!is_file($dbPath)) {
    fwrite(STDERR, "No such file: {$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// [table, source column, target column]
$jobs = [
    ['users', 'name', 'search_name'],
    ['clients', 'name', 'search_name'],
    ['makes', 'name', 'search_name'],
    ['models', 'name', 'search_name'],
    ['product_types', 'name', 'search_name'],
    ['products', 'name', 'search_name'],
    ['cars', 'plate', 'search_plate'],
];

$pdo->beginTransaction();

try {
    foreach ($jobs as [$table, $sourceCol, $targetCol]) {
        $rows = $pdo->query("SELECT id, {$sourceCol} FROM {$table}")->fetchAll();

        $update = $pdo->prepare("UPDATE {$table} SET {$targetCol} = ? WHERE id = ?");

        $count = 0;

        foreach ($rows as $row) {
            if ($row[$sourceCol] === null) {
                continue;
            }

            $update->execute([normalize((string) $row[$sourceCol]), $row['id']]);
            $count++;
        }

        echo "{$table}.{$targetCol}: updated {$count} row(s)\n";
    }

    $pdo->commit();
    echo "Done.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Failed, rolled back everything: " . $e->getMessage() . "\n");
    exit(1);
}
