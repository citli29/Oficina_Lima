<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

// Cron: makes one copy of the database in BACKUP_PATH and checks it.
// Same code as the "Cópias de Segurança" page (App\Services\BackupService).
// Exits with 1 if the copy fails or the check isn't "ok", so cron / a
// healthcheck ping can tell.

use App\Services\BackupService;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();
date_default_timezone_set('Europe/Lisbon');

try {
    $backup = (new BackupService())->create();
} catch (Throwable $e) {
    fwrite(STDERR, 'Backup failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo "Integrity check: {$backup['integrity']}" . PHP_EOL;
echo "Backup created: {$backup['name']} ({$backup['size']} bytes)" . PHP_EOL;

exit($backup['integrity'] === 'ok' ? 0 : 1);
