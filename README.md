# Oficina_Lima

# Patch notes (backend): 25 Sep → 8 Oct 2026

Backend (`Oficina_Lima`), from `5b35393` ("added users") to `15ed662`: 3 commits, 19 files, about 1,550 lines added and 25 removed.

## New: Associated services

Services can be grouped into **associations** (clusters), for example a Mecânica and a Laboratório job on the same car.

### Database (`service_associations_migration.sql`, `service_association_sync_migration.sql`)
- **New table `service_associations`:** each row links a service to an association number (`cluster_nr`).
  - A service can be in at most one association.
  - A new association takes the current highest number + 1. Merging keeps the lower number.
- **Same car and client:** a service can only join an association if its car and client match the other members ("O veículo ou cliente deste serviço não coincide com os restantes serviços da associação.").
- **Syncing on join:** when a service joins, the shared fields are copied from the association's lowest-id member to every member. The fields are entrada, responsável (nome/telemóvel), prev. saída, kms and saída.

### Shared fields kept in sync (`service_header_sync_*.sql`)
- **Edits spread to the association:** editing any of these on one service updates every other service in the association: responsável (nome/telemóvel), entrada, prev. saída, kms, saída, marcação, viatura, cliente and descrição de avaria.
- **Kept per service:** Tipo de serviço and Validado.
- **No update loops:** only the other services whose values actually differ get updated, so the trigger never bounces back and forth.

### API (`ServiceAssociationController` / `ServiceAssociationService` / `ServiceAssociation` model)

| Method | Route | What it does |
|---|---|---|
| GET | `/api/services_associations` | List associations (with search and pagination) |
| GET | `/api/services/{id}/associations` | The other services in this service's association |
| POST | `/api/services/{id}/associations` | Link two services (creates the association or joins an existing one) |
| PUT | `/api/services/{id}/associations` | Move the service to another association |
| DELETE | `/api/services/{id}/associations` | Remove the service from its association |
| PUT | `/api/services_associations/merge` | Merge two associations into one |

Errors are clear Portuguese messages, for example:
- A service can't be linked to itself.
- Two services that are already in different associations need a merge, not a link.
- A move or merge needs the services to already be in an association.

### Services list (`Service` model / `ServiceController`)
- **`group_associations=1` (opt-in):** if a filter matches any service in an association, every service in that association is returned.
- **Status of an association:** the `status` filter and sort use the association's least-advanced status (por terminar < terminado < entregue).
- **New fields on each row:** `cluster_nr`, `cluster_size` (counts every member, not just the ones on the page) and `cluster_status_rank`.
- **New sort:** by association number, with services that aren't in an association always last.

### Notification (`service_association_finished_notification_migration.sql`)
- When the **last** unfinished service in an association is finished, the office gets one notification saying the whole association is finished.
- The existing notification for each finished service is unchanged.

## New: Eventos

Calendar entries that aren't tied to services or marcações, such as holidays, vacations or the shop being closed.

### Database (`events_migration.sql`)
- **New table `events`:** título, descrição, start and end date, optional start and end time, colour and optional user.
  - Leaving both times empty makes it an all-day event.
- **Checks:**
  - Title and both dates are required.
  - Dates and times must be valid.
  - The end date can't be before the start date.

### API (`EventController` / `EventService` / `Event` model)

| Method | Route | What it does |
|---|---|---|
| GET | `/api/events` | List events. `start_date`/`end_date` return events that overlap that window (what the calendars use). Also filters by `title` and `user_id`, with pagination |
| POST | `/api/events` | Create an event |
| GET | `/api/events/{id}` | One event |
| PUT | `/api/events/{id}` | Update an event |
| DELETE | `/api/events/{id}` | Delete an event |

## Laboratório locked once finished (`lab_finished_lock_migration.sql`)
- Once a service is **Terminado**, its lab data can't be added to, edited or deleted. That covers items, action values and property values.
- The error is "Não é possível alterar o laboratório de um serviço finalizado.".

## Fixes
- **Stable order across pages:** lists sorted by a column now also sort by id when values are equal (`Database::applySort` tie-breaker). Before, a row could show up on two pages, or on none, when several rows shared the same value. This applies to the services and marcações lists.

## Since `15ed662` (not committed yet)

### New: Registos de Tempo API
Time entries and punches of every service, addressed by their **real id**. Elsewhere they're addressed by their position inside a service, which shifts when a row is deleted.

| Method | Route | What it does |
|---|---|---|
| GET | `/api/user_times` | List time entries. Filters: `user_id`, `service_id`, `date_from`, `date_to`, `car_plate`, `client_name`. Sort: `date`, `service`, `user`, `minutes`, `car_plate`, `client`. With pagination |
| POST / PUT / DELETE | `/api/user_times`, `/api/user_times/{id}` | Create, edit (service, employee, minutes, date), delete |
| GET | `/api/user_time_punches` | List punches, same filters and sorts. `open=1` returns only punches started and never stopped |
| POST / PUT / DELETE | `/api/user_time_punches`, `/api/user_time_punches/{id}` | Create, edit (service, employee, date, `start`/`end` as "HH:MM"; an empty end reopens the punch), delete |

New files: `TimeRecordController`, `TimeRecordService`, `TimeRecord` model.

### Saving a service: `PATCH /api/services/{id}`
- **Per-field updates:** the body is `{changes, original}`. Only the changed fields are written, and only if each still holds the value the client last saw.
- **Conflicts:** if one doesn't, the answer is **409** with `conflict_fields` and the current service, and nothing is written.

### Associations
- **Transactions:** `link` and `unlink` run in a transaction (`BEGIN IMMEDIATE`), so a refused second insert can't leave a one-member association, and two links at once can't take the same number.
- **POST `/api/services/{id}/associations` does more in one request:**
  - `{service_id, source_service_id}` links and keeps the chosen side's values;
  - `{new_service: {...}}` creates the service and links it.
- **The "mesmo carro" notification for a service created through the association's "+":** its text says so instead:
  - "…foi criada na associação #N, onde já existe uma folha de serviço aberta (#X)…";
  - or, when the car also has an open sheet elsewhere, "…mas já existe uma folha de serviço aberta fora da associação (#Y)…".

### Errors
- **Database errors are real errors now:** they come back as **500** (or their real status) instead of "200 OK" with no data. This went through `httpStatusFromException()` in every controller.
- **Portuguese fallback:** a delete or edit blocked by a reference says "Não é possível concluir: este registo está associado a outros dados." instead of "FOREIGN KEY constraint failed".
- **Kms:** negative kms are refused ("Os kms não podem ser negativos."). 0 is still stored as empty.
- **Marcação description:** a description of only spaces counts as missing.

### Plates
"13 13 SR", "13-13-SR" and "1313sr" are the same plate in every plate search (cars, services, marcações, the association search). New plates are stored that way too (`normalizePlate()`).

### Database: migration files
Everything that was "dev database only" is now in `utils/sql_s/`.

**Simplest:** run `release_2026_10_08_full_migration.sql` alone. It holds all three below in one transaction. Otherwise, run the three in this order. Either way, back up first and use `sqlite3 -bail`:

1. **`release_2026_10_08_migration.sql`:**
   - associations: the table, sync, header sync and the association-finished notification;
   - events;
   - the lab locked once finished;
   - notification links `services/<id>`;
   - **Serviço Realizado** required to finish.
2. **`release_2026_10_08b_migration.sql`:**
   - 21 indexes on the columns the app and triggers look rows up by;
   - notifications older than 30 days are deleted, once and then automatically.
3. **`release_2026_10_08c_migration.sql`:**
   - **Duplicates ignore upper/lower case and spaces:** marcas, modelos (per marca), tipos de produto, produtos without reference, and **plates** ("1313 SR" = 13-13-SR).
   - **Punches must be dated inside the service's Entrada–Saída.** It's checked when the date or service changes.
   - **Time entries are capped at 1440 minutes** (24h).
   - **Notification texts lose the stray spaces** ("Megane . Todos", "ZZMarca  finalizada", "30 -  finalizada"). Old rows are tidied too.
   - **Deleting something in use gives a Portuguese message:** clientes, produtos, marcas, serviços, utilizadores, and lab itens/ações/propriedades.
   - **New rule:** a **viatura** used by services, or a **modelo** used by cars, can't be deleted. Before, the delete went through and they silently lost it.

All three are one transaction each and safe to run twice. They were tested in order on a copy with the production schema.
