<?php
// Sets the shop login (creates the account the first time, otherwise
// changes its password). Changing the password logs out every device.
//
//   php utils/set_shop_password.php
//
// It asks for the username and the password. Nothing is accepted on the
// command line: arguments are saved in the shell history (~/.bash_history)
// and show up in `ps` while the script runs.

require __DIR__ . '/../vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
	exit("Run from the command line.\n");
}

if ($argc > 1) {
	fwrite(STDERR, "This script takes no arguments: it asks for the username and password.\n");
	fwrite(STDERR, "Typing them on the command line would save them in the shell history.\n");
	fwrite(STDERR, "Usage: php utils/set_shop_password.php\n");
	exit(1);
}

if (!stream_isatty(STDIN)) {
	fwrite(STDERR, "Run it in a terminal: it has to ask for the username and password.\n");
	exit(1);
}

// Typing echo back on, even when the script stops halfway (Ctrl+C included).
function restoreEcho(): void
{
	system('stty echo');
}
register_shutdown_function('restoreEcho');
if (function_exists('pcntl_async_signals')) {
	pcntl_async_signals(true);
	pcntl_signal(SIGINT, function () { echo "\n"; exit(130); });
}

function ask(string $label): string
{
	echo $label;
	$line = fgets(STDIN);
	if ($line === false) {
		echo "\n";
		exit(1);
	}
	return rtrim($line, "\r\n");
}

function askHidden(string $label): string
{
	system('stty -echo');
	$value = ask($label);
	system('stty echo');
	echo "\n";
	return $value;
}

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
$db = App\Database\Database::getConnection();

$username = trim(ask("Username: "));
if ($username === '') {
	fwrite(STDERR, "The username can't be empty.\n");
	exit(1);
}
if (mb_strlen($username) > 60) {
	fwrite(STDERR, "The username can have at most 60 characters.\n");
	exit(1);
}

$password = askHidden("Password: ");
$repeat   = askHidden("Repeat:   ");
if ($password !== $repeat) {
	fwrite(STDERR, "Passwords don't match.\n");
	exit(1);
}

if (mb_strlen($password) < 12) {
	fwrite(STDERR, "Password must have at least 12 characters.\n");
	exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

App\Database\Database::transaction($db, function () use ($db, $username, $hash) {
	$stmt = $db->prepare("SELECT id FROM shop_accounts WHERE username = ? COLLATE NOCASE");
	$stmt->execute([$username]);
	$id = $stmt->fetchColumn();

	if ($id === false) {
		$db->prepare("INSERT INTO shop_accounts (username, password_hash) VALUES (?, ?)")->execute([$username, $hash]);
		echo "Created shop login \"{$username}\".\n";
	} else {
		$db->prepare("UPDATE shop_accounts SET password_hash = ? WHERE id = ?")->execute([$hash, $id]);
		$db->prepare("DELETE FROM auth_tokens WHERE shop_account_id = ?")->execute([$id]);
		echo "Password changed for \"{$username}\". All devices were logged out.\n";
	}
});
