<?php
declare(strict_types=1);

namespace App\Services;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;
use SQLite3;

/**
 * Copies of the database, kept as BACKUP_PATH/YYYY_MM_DD_HH_MM_SS.db.
 * Used by the "Cópias de Segurança" page (admin only) and by
 * utils/backup.php (cron), so both make the same kind of copy.
 *
 * The copy uses SQLite's online backup API, which is safe while the app is
 * running (unlike copying the file, which can miss WAL contents).
 * Restoring is deliberately not offered here: it overwrites everyone's data,
 * so it stays a command-line step (utils/restore.php).
 */
class BackupService
{
	// Only plain file names inside BACKUP_PATH; no paths, no other types.
	private const NAME_PATTERN = '/^[A-Za-z0-9_.-]+\.db$/';

	private string $databasePath;
	private string $backupDirectory;

	public function __construct(?string $databasePath = null, ?string $backupDirectory = null)
	{
		$this->databasePath = $databasePath ?? (string) ($_ENV['DB_PATH'] ?? '');
		$this->backupDirectory = rtrim($backupDirectory ?? (string) ($_ENV['BACKUP_PATH'] ?? ''), DIRECTORY_SEPARATOR);

		if ($this->databasePath === '' || $this->backupDirectory === '')
			throw new RuntimeException('DB_PATH / BACKUP_PATH em falta no .env.', 500);
	}

	/** Makes a new copy now and checks it. Returns its listing entry + integrity result. */
	public function create(): array
	{
		if (!is_dir($this->backupDirectory)
			&& !mkdir($this->backupDirectory, 0750, true)
			&& !is_dir($this->backupDirectory))
			throw new RuntimeException('Não foi possível criar a pasta das cópias.', 500);

		if (!is_file($this->databasePath))
			throw new RuntimeException('Base de dados não encontrada.', 500);

		$now = new DateTime('now', new DateTimeZone('Europe/Lisbon'));
		$name = $now->format('Y_m_d_H_i_s') . '.db';
		$path = $this->backupDirectory . DIRECTORY_SEPARATOR . $name;

		// Two copies in the same second would share a name.
		if (file_exists($path))
			throw new RuntimeException('Já foi feita uma cópia neste segundo. Tente outra vez.', 409);

		$source = new SQLite3($this->databasePath, SQLITE3_OPEN_READONLY);
		$source->busyTimeout(5000);
		$target = new SQLite3($path);

		$ok = $source->backup($target);

		// The live DB is in WAL mode and the copy inherits it, which would
		// leave -wal / -shm files next to every copy. A copy is never written
		// to again, so make it a plain single-file database.
		if ($ok)
			$target->exec('PRAGMA journal_mode=DELETE;');

		$source->close();
		$target->close();

		if (!$ok) {
			@unlink($path);
			throw new RuntimeException('A cópia falhou.', 500);
		}

		$integrity = $this->integrityCheck($path);

		return $this->describe($path) + ['integrity' => $integrity];
	}

	/** Every copy, newest first. */
	public function list(): array
	{
		$files = glob($this->backupDirectory . DIRECTORY_SEPARATOR . '*.db') ?: [];

		$rows = [];
		foreach ($files as $path) {
			if (is_file($path) && preg_match(self::NAME_PATTERN, basename($path)))
				$rows[] = $this->describe($path);
		}

		usort($rows, static fn ($a, $b) => $b['modified_ts'] <=> $a['modified_ts']);

		return $rows;
	}

	/**
	 * "ok", or the first problem SQLite reports. Runs on a temporary copy:
	 * opening an older WAL-mode copy directly (even read-only) would leave
	 * -wal / -shm files next to it in BACKUP_PATH.
	 */
	public function integrityCheck(string $path): string
	{
		$tmp = tempnam(sys_get_temp_dir(), 'ol_check_');
		if ($tmp === false || !copy($path, $tmp))
			throw new RuntimeException('Não foi possível verificar a cópia.', 500);

		try {
			$db = new SQLite3($tmp, SQLITE3_OPEN_READWRITE);
			$result = (string) $db->querySingle('PRAGMA integrity_check;');
			$db->close();
			return $result;
		} finally {
			foreach ([$tmp, "$tmp-wal", "$tmp-shm"] as $file)
				if (file_exists($file)) @unlink($file);
		}
	}

	/** Full path of a copy, or an exception if the name isn't a copy in BACKUP_PATH. */
	public function pathFor(string $name): string
	{
		if (!preg_match(self::NAME_PATTERN, $name))
			throw new InvalidArgumentException('Nome de cópia inválido.', 400);

		$path = $this->backupDirectory . DIRECTORY_SEPARATOR . $name;
		$real = realpath($path);
		$dir = realpath($this->backupDirectory);

		if ($real === false || $dir === false || dirname($real) !== $dir || !is_file($real))
			throw new InvalidArgumentException('Cópia não encontrada.', 404);

		return $real;
	}

	private function describe(string $path): array
	{
		clearstatcache(true, $path);
		$modified = (int) filemtime($path);

		return [
			'name' => basename($path),
			'size' => (int) filesize($path),
			// ISO 8601 with offset, so the browser shows it in local time.
			'modified_at' => date(DATE_ATOM, $modified),
			'modified_ts' => $modified,
		];
	}
}
