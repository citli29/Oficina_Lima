<?php

namespace App\Models;

use PDO;

class Auth
{
	private PDO $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	public function getAccountByUsername(string $username): ?array
	{
		$stmt = $this->db->prepare("SELECT id, username, password_hash, notification_type_id, is_admin FROM shop_accounts WHERE username = ? COLLATE NOCASE");
		$stmt->execute([$username]);
		return $stmt->fetch() ?: null;
	}

	public function createToken(int $accountId, string $tokenHash, ?string $userAgent, ?string $ip): void
	{
		$stmt = $this->db->prepare("INSERT INTO auth_tokens (shop_account_id, token_hash, user_agent, ip) VALUES (?, ?, ?, ?)");
		$stmt->execute([$accountId, $tokenHash, $userAgent, $ip]);
	}

	public function getTokenByHash(string $tokenHash): ?array
	{
		$stmt = $this->db->prepare("
			SELECT t.id, t.last_used_at, a.id AS account_id, a.username, a.notification_type_id, a.is_admin
			FROM auth_tokens t
			JOIN shop_accounts a ON a.id = t.shop_account_id
			WHERE t.token_hash = ?
		");
		$stmt->execute([$tokenHash]);
		return $stmt->fetch() ?: null;
	}

	public function touchToken(int $tokenId): void
	{
		$stmt = $this->db->prepare("UPDATE auth_tokens SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?");
		$stmt->execute([$tokenId]);
	}

	public function deleteTokenByHash(string $tokenHash): void
	{
		$stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE token_hash = ?");
		$stmt->execute([$tokenHash]);
	}

	/* ---------- Devices (one row of auth_tokens per logged-in device) ---------- */

	/** Every logged-in device of every account (Dispositivos is admin only). */
	public function getDevices(): array
	{
		$stmt = $this->db->query("
			SELECT t.id, t.name, t.user_agent, t.ip, t.created_at, t.last_used_at,
				t.shop_account_id, a.username
			FROM auth_tokens t
			JOIN shop_accounts a ON a.id = t.shop_account_id
			ORDER BY a.username, t.last_used_at DESC, t.id DESC
		");
		return $stmt->fetchAll();
	}

	public function deviceExists(int $tokenId): bool
	{
		$stmt = $this->db->prepare("SELECT 1 FROM auth_tokens WHERE id = ?");
		$stmt->execute([$tokenId]);
		return (bool) $stmt->fetchColumn();
	}

	/** Rows changed (0 when the device doesn't exist or the name didn't change). */
	public function renameDevice(int $tokenId, ?string $name): int
	{
		$stmt = $this->db->prepare("UPDATE auth_tokens SET name = ? WHERE id = ?");
		$stmt->execute([$name, $tokenId]);
		return $stmt->rowCount();
	}

	/** Rows deleted (0 when the device doesn't exist). */
	public function deleteDevice(int $tokenId): int
	{
		$stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE id = ?");
		$stmt->execute([$tokenId]);
		return $stmt->rowCount();
	}

	/* ---------- Failed-login lockout, per IP ---------- */

	/**
	 * Counts this attempt for the IP *before* the password is checked, in one
	 * atomic statement, so many requests sent at the same time can't all slip
	 * in under the limit. The count starts over once a lock has run out, or
	 * when the last attempt is older than $windowMinutes.
	 *
	 * Returns ['failures' => attempts so far incl. this one,
	 *          'seconds_left' => > 0 while the IP is locked].
	 */
	public function registerAttempt(string $ip, int $windowMinutes): array
	{
		$stmt = $this->db->prepare("
			INSERT INTO login_attempts (ip, failures, last_attempt_at) VALUES (:ip, 1, CURRENT_TIMESTAMP)
			ON CONFLICT(ip) DO UPDATE SET
				failures = CASE
					WHEN locked_until IS NOT NULL AND locked_until <= CURRENT_TIMESTAMP THEN 1
					WHEN locked_until IS NULL AND last_attempt_at <= datetime('now', :window) THEN 1
					ELSE failures + 1
				END,
				locked_until = CASE
					WHEN locked_until IS NOT NULL AND locked_until <= CURRENT_TIMESTAMP THEN NULL
					ELSE locked_until
				END,
				last_attempt_at = CURRENT_TIMESTAMP
			RETURNING
				failures,
				CASE WHEN locked_until IS NULL THEN 0
					ELSE MAX(0, CAST(ROUND((julianday(locked_until) - julianday('now')) * 86400) AS INTEGER))
				END AS seconds_left
		");
		$stmt->execute([':ip' => $ip, ':window' => "-{$windowMinutes} minutes"]);
		$row = $stmt->fetch();

		return ['failures' => (int) $row['failures'], 'seconds_left' => (int) $row['seconds_left']];
	}

	/** Locks the IP for $minutes, unless it's already locked (doesn't extend a running lock). */
	public function lockIp(string $ip, int $minutes): void
	{
		$stmt = $this->db->prepare("
			UPDATE login_attempts SET locked_until = datetime('now', ?)
			WHERE ip = ? AND (locked_until IS NULL OR locked_until <= CURRENT_TIMESTAMP)
		");
		$stmt->execute(["+{$minutes} minutes", $ip]);
	}

	public function clearAttempts(string $ip): void
	{
		$stmt = $this->db->prepare("DELETE FROM login_attempts WHERE ip = ?");
		$stmt->execute([$ip]);
	}

	/** Keeps the table small: rows nobody has used for a day. */
	public function deleteOldAttempts(): void
	{
		$this->db->exec("
			DELETE FROM login_attempts
			WHERE last_attempt_at <= datetime('now', '-1 day')
			  AND (locked_until IS NULL OR locked_until <= CURRENT_TIMESTAMP)
		");
	}
}
