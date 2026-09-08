# Invoicelane

Invoice management module: paginated list with status filtering, invoice detail view, and editing of amounts and payment terms.

**Stack:** Laravel 13 · PHP 8.4 · PostgreSQL 18 · Nuxt 4 · Vue 3.5 · Pinia · TailwindCSS 4 · Docker Compose

---

## Getting started

Docker is the only requirement:

```bash
docker compose up -d
```

The first start takes a few minutes — images are pulled, dependencies installed, migrations and seeders run.

| | |
|---|---|
| Frontend | http://localhost:3000 |
| API | http://localhost:8000/api/invoices |
| Health check | http://localhost:8000/up |

The seeder creates fifteen invoices: three with fixed numbers `INV-2026-0001…0003` (one per status) and twelve random ones. Running it again changes nothing — the seeder returns early if the table is not empty.

```bash
docker compose exec api php artisan migrate:fresh --seed   # reset the data
docker compose exec db psql -U invoicelane -d invoicelane  # database console
```

---

## API

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/invoices` | list; accepts `page`, `per_page` (default 20, capped at 100), `status` |
| `GET` | `/api/invoices/{id}` | single invoice |
| `POST` | `/api/invoices` | create |
| `PUT` | `/api/invoices/{id}` | update |

Identifiers are UUIDs. Statuses: `pending`, `approved`, `rejected`.

Single invoice response:

```json
{
  "id": "…",
  "number": "INV-2026-0001",
  "supplier_name": "…",
  "supplier_tax_id": "…",
  "net_amount": "1000.00",
  "vat_amount": "200.00",
  "gross_amount": "1200.00",
  "currency": "UAH",
  "status": "pending",
  "is_editable": true,
  "issue_date": "2026-08-01",
  "due_date": "2026-08-31",
  "created_at": "…",
  "updated_at": "…"
}
```

### Rules

- **Only invoices in `pending` status can be edited.** Attempting to modify an `approved` or `rejected` one returns `409 Conflict` with the same body shape as a validation error, so the frontend handles both through one code path.
- **`gross_amount` is always computed server-side** — `bcadd(net, vat, 2)`. The client never sends it, so the two cannot drift apart.
- **`due_date` cannot precede `issue_date`.** Updates do not carry the issue date, so the rule is checked against the value already stored in the database.
- Amounts are strings with at most two decimal places: `net_amount > 0`, `vat_amount >= 0`.

The same invariants are mirrored as PostgreSQL `CHECK` constraints (`invoices_net_positive`, `invoices_vat_non_negative`, `invoices_gross_consistent`, `invoices_dates_ordered`) — application validation can be bypassed, database constraints cannot.

---

## Layout

```
apps/api    Laravel — REST API
apps/web    Nuxt — user interface
```

### Backend

The controller stays thin: it parses the request and returns a resource. Business logic lives in `InvoiceService`, data access sits behind the `InvoiceRepository` interface (implemented by `EloquentInvoiceRepository`, bound in `AppServiceProvider`). DTOs — `CreateInvoiceData` / `UpdateInvoiceData` — carry data between the layers.

The edit restriction is expressed as `InvoiceNotEditableException` with its own `render()`, so the rule lives in one place instead of spreading across controllers.

### Frontend

- `stores/invoices.ts` — Pinia store; holds the list, a `byId` map, and separate request states for list and detail so a detail spinner never blocks the list
- `schemas/invoice.ts` — zod form schema; built from the specific invoice's `issue_date`, which makes the `due_date` bound visible before any request reaches the server
- `server/api/[...path].ts` — Nuxt proxy to the API; the browser talks to its own origin, so no CORS is needed
- `composables/useRefreshOnFocus.ts` — reloads the list when the tab regains focus, at most once every 15 seconds

The page number lives in a URL query parameter, so list state survives a reload and can be shared as a link.

---

## Commands

```bash
docker compose exec api php artisan test        # tests
docker compose exec api ./vendor/bin/pint       # PHP formatting
docker compose exec web pnpm typecheck          # type checking
docker compose logs -f api web                  # logs
```
