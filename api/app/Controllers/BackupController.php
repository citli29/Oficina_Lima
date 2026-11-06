<?php declare(strict_types=1);

namespace App\Controllers;

use App\Services\BackupService;
use InvalidArgumentException;
use PDO;
use RuntimeException;

require_once __DIR__ .'/../../../utils/util.php';

// "Cópias de Segurança" page. Every route is admin only.
class BackupController
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getBackups(): void
	{
		$this->respond(function (BackupService $backups) {
			$list = $backups->list();
			$latest = $list[0] ?? null;

			return [
				'backups' => $list,
				// Checked on every page load: the newest copy is the one a
				// restore would use, so it's the one that has to be good.
				'latest_integrity' => $latest
					? $backups->integrityCheck($backups->pathFor($latest['name']))
					: null,
			];
		});
	}

	// Navbar warning: is the newest copy recent and not broken? The only
	// backup route open to every logged-in account (the login gate in
	// public/index.php already rejects requests without a token); non-admins
	// get just ok + problem, not the file details.
	public function getBackupStatus(): void
	{
		header('Content-Type: application/json');
		try {
			$status = (new BackupService())->status();
			$isAdmin = AuthController::requireAdmin($this->db, false);
			if (!$isAdmin)
				unset($status['latest']);
			echo json_encode(['success' => true] + $status);
		} catch (InvalidArgumentException | RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postBackup(): void
	{
		$this->respond(fn (BackupService $backups) => ['backup' => $backups->create()], 201);
	}

	public function downloadBackup(string $name): void
	{
		if (!AuthController::requireAdmin($this->db))
			return;

		try {
			$path = (new BackupService())->pathFor($name);
		} catch (InvalidArgumentException | RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			header('Content-Type: application/json');
			echo json_encode(['error' => $e->getMessage()]);
			return;
		}

		header('Content-Type: application/vnd.sqlite3');
		header('Content-Disposition: attachment; filename="oficina_lima_' . basename($path) . '"');
		header('Content-Length: ' . filesize($path));
		header('Cache-Control: no-store');
		readfile($path);
	}

	private function respond(callable $fn, int $status = 200): void
	{
		if (!AuthController::requireAdmin($this->db))
			return;

		header('Content-Type: application/json');
		try {
			$result = $fn(new BackupService());
			http_response_code($status);
			echo json_encode(['success' => true] + $result);
		} catch (InvalidArgumentException | RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
