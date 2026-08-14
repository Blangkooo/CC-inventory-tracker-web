# NITA API Reference

Base URL: `http://127.0.0.1:8000` · All authenticated routes require a JWT bearer token (see Auth below).

> Previous versions of this document described a fictional Sanctum-style auth
> contract (`/api/auth/login` with `full_name`/`owner_id`, mock tokens). The
> app has always run on **tymon/jwt-auth**; this rewrite reflects the real
> implementation as of 2026-07-29.

---

## Auth (`config/auth.php` `api` guard, driver: `jwt`)

Two login flows, both return `{ "token": "<jwt>", "user": {...} }`:

| Endpoint | Who | Body |
|---|---|---|
| `POST /api/auth/admin-login` | super_admin, manager | `{ "email", "password" }` |
| `POST /api/auth/owner-login` | legacy alias for admin-login | same |
| `POST /api/auth/staff-login` | staff, manager | `{ "pin", "branch_id" }` |
| `POST /api/login` | unified alias for staff-login | `{ "pin", "branch_id" }` |

Send the token on every subsequent request: `Authorization: Bearer <token>`.

| Endpoint | Auth | Purpose |
|---|---|---|
| `POST /api/auth/logout` | required | Invalidate the current token |
| `GET /api/auth/me` (alias `GET /api/me`) | required | Current user |

**Errors** (standardized in `bootstrap/app.php`): expired/invalid/missing token → `401 { "message": "Token has expired." \| "Token is invalid." \| "Unauthenticated." }`. Validation failures → `422 { "message", "errors": {field: [msg]} }`. Role/branch denials → `403 { "message": "Forbidden: ..." }`.

### RBAC tiers

Three roles (`User::ROLE_SUPER_ADMIN`, `ROLE_MANAGER`, `ROLE_STAFF`). Route groups below are gated by the `role:` middleware; branch-scoped resources additionally check `AuthorizesBranchAccess` — a manager/staff `branch_id` must match the resource's branch or the request 403s, super_admin is unrestricted.

---

## Any authenticated role

| Method | Path | Notes |
|---|---|---|
| GET/PUT | `/notifications`, `/notifications/{id}/read` | |
| POST | `/transactions` | UUID-idempotent (`client_uuid`), recipe-driven stock deduction, row-locked against overselling. `unit_price` is resolved per `size` (`products.price` / `products.price_large`, falling back to `price` when no large price is set) and snapshotted on the row alongside `total_amount` — **new field** |
| POST | `/receipts/scan` | OCR receipt scan + auto-reconciliation |
| GET | `/receipts`, `/receipts/summary`, `/receipts/{id}` | |
| GET | `/products`, `/products/{id}` | |
| GET | `/ingredients`, `/ingredients/{id}` | |
| GET | `/recipes`, `/recipes/{id}` | |

## super_admin + manager (branch-scoped for manager)

| Method | Path | Notes |
|---|---|---|
| CRUD | `/staff` | |
| GET | `/reports/sales`, `/reports/inventory` | |
| GET | `/transactions`, `/transactions/{id}` | |
| GET | `/shifts`, `/shifts/{id}` | |
| GET | `/alerts`, `/alerts/{id}` | |
| PATCH | `/alerts/{id}` | `{ status: reviewed\|dismissed }` — single-verb alternative to the two PUT endpoints below — **new** |
| PUT | `/alerts/{id}/review`, `/alerts/{id}/dismiss` | Unchanged, kept for existing callers |
| POST | `/stock`, `/stock/restock` | Both accept optional `capacity` — if `min_threshold` is omitted, it's auto-derived as `capacity × low_stock_threshold_pct` (Settings, default 25%) — **new field** |
| PUT/DELETE | `/stock/{branchStock}` | Edit/remove a branch+ingredient stock row — `min_threshold` and/or `capacity`, same auto-derive rule — **new** |
| GET | `/stock/low-stock`, `/stock/{branchStock}/movements` | |
| GET | `/branches/{id}`, `/branches/{id}/stock` | |
| GET | `/dashboard/kpis`, `/dashboard/sales-summary`, `/dashboard/top-products` | |
| GET | `/dashboard/trends?year=&branch_id=` | `{ year, monthly_revenue[12], monthly_revenue_prior_year[12], yearly_total, yearly_total_prior_year }` — **new** |
| GET | `/dashboard/leakage?from=&to=&branch_id=` | `{ total_leakage, by_branch: [{branch_id, branch_name, leakage}] }` — **new** |
| CRUD | `/suppliers`, `/suppliers/{id}/ingredients` (link), `/suppliers/{id}/purchases` | Company-wide, not branch-scoped — **new** |
| CRUD | `/payments` + `/payments/{id}/mark-paid` | Operational cost logging: category = rent\|utilities\|supplier\|salary\|maintenance\|packaging\|utensils\|gas\|wages\|other, optional receipt photo — **new** |
| POST | `/pricing/simulate` | `{ adjustments: [{ingredient_id, new_unit_cost}] }` → `{ affected_count, net_gain_loss_per_unit_sold, results: [{product_name, size, old_margin_pct, new_margin_pct, ...}] }`. Nothing persisted — **new** |

## staff + manager

| Method | Path | Notes |
|---|---|---|
| POST | `/shifts/open`, `/shifts/close` | Opening/closing counts, auto-computed variance, auto-flags a `DiscrepancyAlert` against the configurable Settings threshold (`variance_threshold_pct`/`variance_threshold_php`, default ±5%/₱100) — see Settings note below |
| POST | `/shift-logs/start`, `/shift-logs/{id}/end` | Thin start/end aliases over the same table — pure clock-in/out, no counts/variance. `end` now **rejects with 422** if the shift has `ShiftStockCount` rows pending reconciliation (i.e. it was opened via `/shifts/open`) — close those through `/shifts/close` instead, or the counts are silently never reconciled — **new guard** |

## super_admin only

| Method | Path | Notes |
|---|---|---|
| GET | `/dashboard` | Global overview, all branches |
| CRUD | `/branches`, `/products`, `/ingredients`, `/recipes` (write ops) | |

---

## Known gaps (see repo audit, 2026-07-29; updated 2026-07-30)

- Historical `transactions` rows created before 2026-07-30 have `unit_price = null` (not backfilled — backing it out of `total_amount ÷ quantity` would fabricate false precision on old size-unaware sales). New rows always populate it.
- Low-stock notifications still reuse the shared `DiscrepancyAlertMail`/`DiscrepancyAlertObserver` (no dedicated template), but as of 2026-07-30 the alert `details` and email both include a "Reorder from" block with the ingredient's primary-supplier contact when one is linked.
- No web UI exists yet to set a branch_stock row's `capacity`, or a product's `price_large` — both are API-only for now (mobile app / direct calls), same as `min_threshold` always has been.
- The web frontend (`resources/views/**`) is server-rendered against Eloquent directly and does **not** call this API — this API surface is for the mobile app / external integrations.

## Settings (owner-only, web `/settings` page)

Backed by a generic `AppSetting` key-value store, read via `AppSetting::get($key, $default)`:

| Key | Default | Used by |
|---|---|---|
| `variance_threshold_pct` | `0.05` | `/shifts/close` — alert fires when `\|variance\| ÷ expected` crosses this |
| `variance_threshold_php` | `100` | `/shifts/close` — alert fires when `\|variance\| × ingredient unit cost` crosses this (either leg, whichever first) |
| `low_stock_threshold_pct` | `0.25` | `/stock`, `/stock/{id}` — derives `min_threshold` from `capacity × pct` when `min_threshold` isn't given explicitly |

For the full request/response shape of every endpoint, import the Postman collection (`postman_collection.json` if present, or ask Edgar) — it covers ~65 of the requests above and is more current than this document was before this rewrite.
