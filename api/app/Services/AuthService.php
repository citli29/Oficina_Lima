<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Auth;
use InvalidArgumentException;
use RuntimeException;

/**
 * Shop-wide login. One account for the whole shop; each device that logs in
 * gets its own random token, kept by the browser until someone presses
 * "Terminar sessão", removes the device on the Dispositivos page, or the
 * password is changed with utils/admin_scripts/set_shop_password.php (which revokes every
 * token). Tokens don't expire, so a shop PC/tablet stays logged in.
 *
 * Only a SHA-256 of the token is stored, so a copy of the DB alone can't be
 * used to log in.
 *
 * Password guessing: after MAX_FAILURES wrong tries in a row from one IP,
 * that IP can't try again for LOCK_MINUTES (see login_attempts).
 */
class AuthService
{
	public const MAX_FAILURES = 5;
	public const LOCK_MINUTES = 15;

	private const DEVICE_NAME_MAX = 60;

	private Auth $model;

	public function __construct(Auth $model)
	{
		$this->model = $model;
	}

	public static function hashToken(string $token): string
	{
		return hash('sha256', $token);
	}

	public function login(string $username, string $password, ?string $userAgent, string $ip): array
	{
		$username = trim($username);
		if ($username === '' || $password === '')
			throw new InvalidArgumentException("Indique o utilizador e a password.", 400);

		$this->model->deleteOldAttempts();

		// Counted before checking the password, see Auth::registerAttempt.
		$attempt = $this->model->registerAttempt($ip, self::LOCK_MINUTES);

		if ($attempt['seconds_left'] > 0)
			throw self::lockedError($attempt['seconds_left']);

		if ($attempt['failures'] > self::MAX_FAILURES) {
			$this->model->lockIp($ip, self::LOCK_MINUTES);
			throw self::lockedError(self::LOCK_MINUTES * 60);
		}

		$account = $this->model->getAccountByUsername($username);

		if (!$account || !password_verify($password, $account['password_hash'])) {
			// Slows down password guessing from the LAN.
			usleep(700000);

			if ($attempt['failures'] >= self::MAX_FAILURES) {
				$this->model->lockIp($ip, self::LOCK_MINUTES);
				throw self::lockedError(self::LOCK_MINUTES * 60);
			}

			$left = self::MAX_FAILURES - $attempt['failures'];
			$message = "Utilizador ou password incorretos.";
			if ($left <= 2)
				$message .= $left === 1 ? " Resta 1 tentativa." : " Restam {$left} tentativas.";

			throw new RuntimeException($message, 401);
		}

		$this->model->clearAttempts($ip);

		$token = bin2hex(random_bytes(32));
		$this->model->createToken(
			(int) $account['id'],
			self::hashToken($token),
			$userAgent !== null ? mb_substr($userAgent, 0, 255) : null,
			$ip !== '' ? $ip : null
		);

		return [
			'token' => $token,
			'username' => $account['username'],
			'notification_type_id' => isset($account['notification_type_id']) ? (int) $account['notification_type_id'] : null,
			'is_admin' => !empty($account['is_admin']),
		];
	}

	private static function lockedError(int $seconds): RuntimeException
	{
		$minutes = max(1, (int) ceil($seconds / 60));
		$when = $minutes === 1 ? "1 minuto" : "{$minutes} minutos";

		return new RuntimeException(
			"Demasiadas tentativas falhadas. Tente novamente daqui a {$when}.",
			429
		);
	}

	/**
	 * The logged-in account for a raw token, or null if the token isn't valid.
	 * 'token_id' is this device's row in auth_tokens.
	 */
	public function check(string $token): ?array
	{
		if ($token === '')
			return null;

		$row = $this->model->getTokenByHash(self::hashToken($token));
		if (!$row)
			return null;

		// Every poll would otherwise write to the DB; once an hour is enough
		// to tell which devices are still in use.
		if (strtotime($row['last_used_at'] . ' UTC') < time() - 3600)
			$this->model->touchToken((int) $row['id']);

		return [
			'id' => (int) $row['account_id'],
			'username' => $row['username'],
			// Notification view this account is locked to (Oficina /
			// Escritório); null = the account picks it (admin).
			'notification_type_id' => isset($row['notification_type_id']) ? (int) $row['notification_type_id'] : null,
			// Admin-only pages (Cópias de Segurança). Checked on the server
			// by AuthController::requireAdmin, not only hidden in the menu.
			'is_admin' => !empty($row['is_admin']),
			'token_id' => (int) $row['id'],
		];
	}

	public function logout(string $token): void
	{
		if ($token !== '')
			$this->model->deleteTokenByHash(self::hashToken($token));
	}

	/* ---------- Devices (admin only: sees and manages every account's) ---------- */

	public function getDevices(string $token): array
	{
		$account = $this->requireAdmin($token);

		return array_map(fn (array $d) => [
			'id' => (int) $d['id'],
			'name' => $d['name'],
			'user_agent' => $d['user_agent'],
			'ip' => $d['ip'],
			'created_at' => $d['created_at'],
			'last_used_at' => $d['last_used_at'],
			'account_id' => (int) $d['shop_account_id'],
			'username' => $d['username'],
			'is_current' => (int) $d['id'] === $account['token_id'],
		], $this->model->getDevices());
	}

	public function renameDevice(string $token, int $deviceId, mixed $name): void
	{
		$this->requireAdmin($token);

		if ($name !== null && !is_string($name))
			throw new InvalidArgumentException("Nome inválido.", 400);

		$name = $name === null ? null : trim($name);
		if ($name === '')
			$name = null;

		if ($name !== null && mb_strlen($name) > self::DEVICE_NAME_MAX)
			throw new InvalidArgumentException("O nome pode ter no máximo " . self::DEVICE_NAME_MAX . " caracteres.", 400);

		// rowCount is 0 also when the name didn't change, so check existence apart.
		if ($this->model->renameDevice($deviceId, $name) === 0 && !$this->model->deviceExists($deviceId))
			throw new RuntimeException("Dispositivo não encontrado.", 404);
	}

	/** Returns true when the removed device was the one making the request. */
	public function deleteDevice(string $token, int $deviceId): bool
	{
		$account = $this->requireAdmin($token);

		if ($this->model->deleteDevice($deviceId) === 0)
			throw new RuntimeException("Dispositivo não encontrado.", 404);

		return $deviceId === $account['token_id'];
	}

	private function requireAdmin(string $token): array
	{
		$account = $this->requireAccount($token);
		if (!$account['is_admin'])
			throw new RuntimeException("Só um administrador pode fazer isto.", 403);
		return $account;
	}

	private function requireAccount(string $token): array
	{
		$account = $this->check($token);
		if ($account === null)
			throw new RuntimeException("Sessão terminada. Inicie sessão novamente.", 401);
		return $account;
	}
}
