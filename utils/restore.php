<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
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
