# Admin scripts

Scripts run **by hand**, over SSH or on the server itself, from the repo root
(`Oficina_Lima/`). They are not reachable from the website: `utils/` is outside
nginx's web root (`api/public`).

They read `DB_PATH` / `BACKUP_PATH` from the repo's `.env`. On prod, run them with
`/usr/bin/php8.3`.

| Script | What it does | Command |
|---|---|---|
| `restore.php` | Restores a **whole backup** over the live database. First saves the current one as `BACKUP_PATH/<date>_pre_restore.db` and prints the command to undo. Nothing is restored if that save fails. Command line only. | `php utils/admin_scripts/restore.php latest`<br>`php utils/admin_scripts/restore.php <file.db>` |
| `restore_deleted.php` | Lists and puts back **single deleted rows** from the trash (`deleted_rows`), with their original ids. Same code as the **Lixo** page, so it works even if the site is down. | `php utils/admin_scripts/restore_deleted.php list [<table> [n]]`<br>`... show <id>`<br>`... restore <id> [<id> ...]` |
| `trash_triggers.php` | Creates `deleted_rows` and the `trash_<table>` triggers that fill it. **Re-run after any `ALTER TABLE`**, or new columns aren't saved in the trash. Safe to run any number of times. | `php utils/admin_scripts/trash_triggers.php`<br>`... --print` (only shows the SQL) |
| `set_shop_password.php` | Creates a login, or changes its password (at least 12 characters). It asks for the username and password, so nothing ends up in the shell history. Changing a password logs that account out on every device. `is_admin` / `notification_type_id` are set by hand afterwards. | `php utils/admin_scripts/set_shop_password.php` |
| `backfill_search_names.php` | Recomputes `search_name` / `search_plate` for every row, which is what search uses. Only needed if search stops finding old rows. Already done on dev and prod. Safe to re-run. | `php utils/admin_scripts/backfill_search_names.php /path/to/Database.db` |

Not here on purpose:
- `utils/backup.php`: run daily by cron (prod: 22:00). The crontab points at that path. After a good copy it also deletes old automatic copies: it keeps every copy from the last 30 days, then the first copy of each month. Named copies (`_pre_restore`, ...) and copies from before 2026-11-07 are never deleted.
- `utils/util.php` and `utils/normalize.php`: used by the API.
