<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

// Puts back rows saved in deleted_rows (see utils/trash_triggers.php).
//
//   php utils/restore_deleted.php list                 last 30 deletions
//   php utils/restore_deleted.php list <table> [n]     last n of one table
//   php utils/restore_deleted.php show <id>            the saved row
//   php utils/restore_deleted.php restore <id> [...]   put rows back
//
// A row comes back with its original id, so anything that pointed to it
// works again. Restore parents before children (e.g. the service before its
// punches): the database's normal checks run on the restored row, so a
// child whose parent is still gone is refused and nothing changes.
// Restoring several ids at once is all-or-nothing, and they're put back
// parents first whatever the order given. Same code as the Lixo page
// (App\Services\TrashService).

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
date_default_timezone_set('Europe/Lisbon');

$db = new PDO('sqlite:' . $_ENV['DB_PATH']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec('PRAGMA foreign_keys = on');

$command = $argv[1] ?? '';

function shopTime(string $utc): string
{
	return (new DateTime($utc . ' UTC'))->setTimezone(new DateTimeZone('Europe/Lisbon'))->format('d/m/Y H:i');
}

function summary(string $json): string
{
	$text = json_encode(json_decode($json, true), JSON_UNESCAPED_UNICODE);
	return mb_strlen($text) > 90 ? mb_substr($text, 0, 87) . '...' : $text;
}

switch ($command) {
	case 'list':
		$table = $argv[2] ?? null;
		$limit = max(1, (int) ($argv[3] ?? 30));
		$stmt = $table
			? $db->prepare("SELECT * FROM deleted_rows WHERE table_name = ? ORDER BY id DESC LIMIT $limit")
			: $db->prepare("SELECT * FROM deleted_rows ORDER BY id DESC LIMIT $limit");
		$stmt->execute($table ? [$table] : []);
		$rows = $stmt->fetchAll();
		if (!$rows) {
			echo "Nothing deleted" . ($table ? " in {$table}" : '') . "." . PHP_EOL;
			break;
		}
		foreach ($rows as $r) {
			printf(
				"#%-5d %s  %-28s id %-6s %s%s\n",
				$r['id'], shopTime($r['deleted_at']), $r['table_name'], $r['row_id'] ?? '-',
				summary($r['data']), $r['restored_at'] ? '  [restored]' : ''
			);
		}
		break;

	case 'show':
		$stmt = $db->prepare('SELECT * FROM deleted_rows WHERE id = ?');
		$stmt->execute([(int) ($argv[2] ?? 0)]);
		$r = $stmt->fetch();
		if (!$r) {
			fwrite(STDERR, 'Not found.' . PHP_EOL);
			exit(1);
		}
		echo "#{$r['id']} {$r['table_name']} id {$r['row_id']}, deleted " . shopTime($r['deleted_at'])
			. ($r['restored_at'] ? ', restored ' . shopTime($r['restored_at']) : '') . PHP_EOL;
		echo json_encode(json_decode($r['data'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
		break;

	case 'restore':
		$ids = array_map('intval', array_slice($argv, 2));
		if (!$ids) {
			fwrite(STDERR, 'Usage: php utils/restore_deleted.php restore <id> [<id> ...]' . PHP_EOL);
			exit(1);
		}

		// Same code as the Lixo page (parents first, all or nothing).
		try {
			$restored = (new App\Services\TrashService($db))->restore($ids);
		} catch (Throwable $e) {
			fwrite(STDERR, 'Nothing restored: ' . $e->getMessage() . PHP_EOL);
			exit(1);
		}
		foreach ($restored as $r)
			echo "Restored #{$r['id']}: {$r['table_name']} id {$r['row_id']}" . PHP_EOL;
		break;

	default:
		fwrite(STDERR,
			"Usage:\n" .
			"  php utils/restore_deleted.php list [<table> [n]]\n" .
			"  php utils/restore_deleted.php show <id>\n" .
			"  php utils/restore_deleted.php restore <id> [<id> ...]\n"
		);
		exit(1);
}
