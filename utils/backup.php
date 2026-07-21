<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/..");
$dotenv->load();

$databasePath = $_ENV['DB_PATH'];
$backupDirectory = $_ENV['BACKUP_PATH'];

if (!is_dir($backupDirectory)) {
    if (!mkdir($backupDirectory, 0750, true) && !is_dir($backupDirectory)) {
        throw new RuntimeException('Could not create backup directory.');
    }
}

if (!is_file($databasePath)) {
    throw new RuntimeException('Database file not found.');
}

date_default_timezone_set('Europe/Lisbon');
$timestamp = date('Y_m_d_H_i_s');
$backupPath = $backupDirectory . "/{$timestamp}.db";

$source = new SQLite3($databasePath, SQLITE3_OPEN_READONLY);
$backup = new SQLite3($backupPath);

if (!$source->backup($backup)) {
    throw new RuntimeException('SQLite backup failed.');
}

$source->close();
$backup->close();

$verify = new SQLite3($backupPath, SQLITE3_OPEN_READONLY);

$check = $verify->querySingle("PRAGMA integrity_check;");

echo "Integrity check: {$check}" . PHP_EOL;

$verify->close();

echo "Backup created: {$backupPath}" . PHP_EOL;
