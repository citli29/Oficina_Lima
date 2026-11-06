<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Auth;
use App\Services\AuthService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ .'/../../../utils/util.php';

class AuthController
{
	private AuthService $service;

	public function __construct(PDO $db)
	{
		$this->service = new AuthService(new Auth($db));
	}

	/** Raw token from "Authorization: Bearer <token>", or '' if missing. */
	public static function bearerToken(): string
	{
		$header = $_SERVER['HTTP_AUTHORIZATION']
			?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
			?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '');

		return preg_match('/^Bearer\s+([A-Fa-f0-9]{64})$/', trim((string) $header), $m) ? $m[1] : '';
	}

	/**
	 * Gate run by public/index.php before any non-public route. Sends a 401
	 * and returns false when the request has no valid token.
	 */
	public static function requireAuth(PDO $db): bool
	{
		$service = new AuthService(new Auth($db));

		if ($service->check(self::bearerToken()) !== null)
			return true;

		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Sessão terminada. Inicie sessão novamente.']);
		return false;
	}

	/**
	 * For admin-only routes, on top of the login gate in public/index.php.
	 * Sends a 403 and returns false when the account isn't an admin.
	 */
	/** $respond = false: just answer whether it's an admin, without sending a 401/403. */
	public static function requireAdmin(PDO $db, bool $respond = true): bool
	{
		$service = new AuthService(new Auth($db));
		$account = $service->check(self::bearerToken());

		if ($account !== null && $account['is_admin'])
			return true;

		if (!$respond)
			return false;

		http_response_code($account === null ? 401 : 403);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Só um administrador pode fazer isto.']);
		return false;
	}

	/**
	 * The address the request came from. Only REMOTE_ADDR: X-Forwarded-For
	 * and similar headers are written by the client, so trusting them would
	 * let anyone dodge the lockout by sending a different fake IP each time.
	 * (nginx + PHP-FPM passes the real client address in REMOTE_ADDR.)
	 */
	public static function clientIp(): string
	{
		return mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
	}

	public function login(): void
	{
		header('Content-Type: application/json');
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if (!is_array($data))
				throw new InvalidArgumentException("JSON Body Invalid.", 400);

			$result = $this->service->login(
				(string) ($data['username'] ?? ''),
				(string) ($data['password'] ?? ''),
				$_SERVER['HTTP_USER_AGENT'] ?? null,
				self::clientIp()
			);

			http_response_code(200);
			echo json_encode(['success' => true] + $result);
		} catch (InvalidArgumentException | RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		} catch (\PDOException $e) {
			http_response_code(500);
			echo json_encode(['error' => dbErrorMessage($e)]);
		}
	}

	public function me(): void
	{
		header('Content-Type: application/json');
		$account = $this->service->check(self::bearerToken());
		http_response_code(200);
		echo json_encode([
			'success' => true,
			'username' => $account['username'] ?? null,
			'notification_type_id' => $account['notification_type_id'] ?? null,
			'is_admin' => $account['is_admin'] ?? false,
		]);
	}

	public function logout(): void
	{
		header('Content-Type: application/json');
		$this->service->logout(self::bearerToken());
		http_response_code(200);
		echo json_encode(['success' => true]);
	}

	public function getDevices(): void
	{
		$this->respond(fn () => ['devices' => $this->service->getDevices(self::bearerToken())]);
	}

	public function renameDevice(string $id): void
	{
		$this->respond(function () use ($id) {
			$data = json_decode(file_get_contents('php://input'), true);
			if (!is_array($data) || !array_key_exists('name', $data))
				throw new InvalidArgumentException("JSON Body Invalid.", 400);

			$this->service->renameDevice(self::bearerToken(), (int) $id, $data['name']);
			return [];
		});
	}

	public function deleteDevice(string $id): void
	{
		$this->respond(fn () => ['was_current' => $this->service->deleteDevice(self::bearerToken(), (int) $id)]);
	}

	private function respond(callable $fn): void
	{
		header('Content-Type: application/json');
		try {
			$result = $fn();
			http_response_code(200);
			echo json_encode(['success' => true] + $result);
		} catch (InvalidArgumentException | RuntimeException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		} catch (\PDOException $e) {
			http_response_code(500);
			echo json_encode(['error' => dbErrorMessage($e)]);
		}
	}
}
