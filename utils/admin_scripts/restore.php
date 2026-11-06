<?php
declare(strict_types=1);

// Command line only (ssh or on the server). utils/ is outside nginx's web
// root, so it can't be reached from the site anyway; this is a second lock.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

require __DIR__ . '/../../vendor/autoload.php';

use App\Services\BackupService;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

$databasePath = $_ENV['DB_PATH'];
$backupDirectory = rtrim($_ENV['BACKUP_PATH'], DIRECTORY_SEPARATOR);

if ($argc < 2) {
    throw new RuntimeException(
        "Usage:\n" .
        "  php restore.php latest\n" .
        "  php restore.php <backup_file>\n"
    );
}

$argument = $argv[1];

if ($argument === 'latest') {
    $files = glob($backupDirectory . DIRECTORY_SEPARATOR . '*.db');

    if (!$files) {
        throw new RuntimeException("No backup files found.");
    }

    usort($files, static fn($a, $b) => filemtime($b) <=> filemtime($a));

    $backupFile = $files[0];
} else {
    $backupFile = $backupDirectory . DIRECTORY_SEPARATOR . $argument;
}

if (!is_file($backupFile)) {
    throw new RuntimeException("Backup file not found: {$backupFile}");
}

// Verify the backup before restoring
$verify = new SQLite3($backupFile, SQLITE3_OPEN_READONLY);

$check = $verify->querySingle("PRAGMA integrity_check;");

if ($check !== 'ok') {
    $verify->close();
    throw new RuntimeException("Backup integrity check failed: {$check}");
}

$verify->close();

echo "Integrity check: ok" . PHP_EOL;

// Save the current database before overwriting it, so a restore of the
// wrong file can be undone. Done after picking $backupFile, so "latest"
// never picks this new copy. If the save fails, nothing is restored.
// (A missing database, e.g. a fresh server, has nothing to save.)
date_default_timezone_set('Europe/Lisbon');

if (is_file($databasePath)) {
    try {
        $preSave = (new BackupService())->create('pre_restore');
    } catch (RuntimeException $e) {
        // 409 = a copy with this name was made this same second (e.g. undoing
        // a restore right away). Wait for the next second and try once more.
        if ($e->getCode() !== 409) throw $e;
        sleep(1);
        $preSave = (new BackupService())->create('pre_restore');
    }

    if ($preSave['integrity'] !== 'ok') {
        throw new RuntimeException(
            "The current database was saved as {$preSave['name']}, but that copy failed its check " .
            "({$preSave['integrity']}). Nothing was restored."
        );
    }

    echo "Current database saved as: {$backupDirectory}" . DIRECTORY_SEPARATOR . "{$preSave['name']}" . PHP_EOL;
    echo "  (to undo this restore: php utils/admin_scripts/restore.php {$preSave['name']})" . PHP_EOL;
} else {
    echo "No current database at {$databasePath}, nothing to save first." . PHP_EOL;
}

// Restore
$backup = new SQLite3($backupFile, SQLITE3_OPEN_READONLY);
$live = new SQLite3($databasePath);

if (!$backup->backup($live)) {
    throw new RuntimeException("Restore failed.");
}

$backup->close();
$live->close();

echo "Database restored successfully." . PHP_EOL;
echo "Source backup: {$backupFile}" . PHP_EOL;
