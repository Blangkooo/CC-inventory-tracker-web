# NITA — Multi-Branch Inventory Tracker

Anti-theft inventory and POS backend for food cart & micro-franchise businesses (milk tea, siomai, bakery). Laravel 13 REST API with JWT auth + 3-tier role-based access control, plus a server-rendered owner web dashboard.

**Core features:** recipe-driven automatic stock deduction on every sale, shift open/close physical counts with automatic variance detection, discrepancy alerts (in-app + email), full stock movement audit trail, per-branch access control.

---

## Quick Start

```bash
git clone https://github.com/Blangkooo/CC-inventory-tracker-web.git
cd CC-inventory-tracker-web
composer install
copy .env.example .env        # then set your DB credentials (MySQL) in .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
php artisan serve             # http://127.0.0.1:8000
```

Requirements: PHP 8.3+, Composer, MySQL. (Mail is optional — set the `MAIL_*` vars to a [Mailtrap](https://mailtrap.io) sandbox to see alert emails; leave `MAIL_MAILER=log` otherwise.)

## Test Accounts (seeded)

| Role | Login | Credentials | Access |
|---|---|---|---|
| Super Admin | `POST /api/auth/admin-login` | `owner@inventory.test` / `password123` | All branches, all endpoints, global dashboard |
| Manager | `POST /api/auth/admin-login` | `manager@inventory.test` / `password123` | Own branch only (Branch QC, id=1) |
| Staff | `POST /api/auth/staff-login` | pin `1234` + `branch_id: 1` | POS checkout, shifts, catalog reads |

The **owner web dashboard** (Blade) is at `http://127.0.0.1:8000/login` — super admin account only.

Seeded data: 2 branches (QC, Manila), 4 products (Classic Milk Tea, Taro Milk Tea, Pork Siomai, Pandesal), 10 ingredients with recipes, and starting stock for both branches.

---

## For Frontend Devs 👋

### 1. Import the Postman collection

`inventory-tracker.postman_collection.json` is in the repo root — **File → Import** in Postman. It covers all 53 requests, grouped by feature, with the required role labeled on each folder.

Run **"Admin Login"** (or "Staff Login") first — a test script auto-saves the JWT into `{{admin_token}}` / `{{staff_token}}`, so every other request works immediately. `{{base_url}}` defaults to `http://127.0.0.1:8000/api`.

> Using Postman **web**? Install the Postman Desktop Agent, or requests to `127.0.0.1` will fail with "localhost request not supported."

### 2. Auth flow

```
POST /api/auth/admin-login  { "email": "...", "password": "..." }   → { token, user }
POST /api/auth/staff-login  { "pin": "...", "branch_id": 1 }        → { token, user, branch }
```

Send the token on every request: `Authorization: Bearer <token>`. Tokens last 8 hours (`JWT_TTL=480`). `GET /api/auth/me` returns the current user + branch; `POST /api/auth/logout` invalidates the token.

### 3. Response conventions (for your Loading/Success/Error states)

| Status | Meaning | What to show |
|---|---|---|
| `200` / `201` | Success | Render data |
| `401` `{"message": "Unauthenticated."}` | No/expired token | Redirect to login |
| `403` `{"message": "Forbidden: ..."}` | Role or branch not allowed | "No access" state — **expected** for managers touching other branches or staff hitting supervisory endpoints |
| `422` | Validation / business rule | Show `errors` field messages; insufficient stock returns `{ error, needed, available }` |

List endpoints (`/transactions`, `/receipts`, `/alerts`, `/shifts`, `/notifications`, `/stock/{id}/movements`) return Laravel pagination: `{ data: [...], current_page, last_page, total, ... }`.

### 4. Role matrix (who can call what)

| Endpoint group | Staff | Manager | Super Admin |
|---|---|---|---|
| POS: `POST /transactions`, `POST /receipts/scan` | ✅ own branch | ✅ own branch | ✅ any |
| Catalog reads: `/products`, `/ingredients`, `/recipes` | ✅ | ✅ | ✅ |
| Shifts: `/shifts/open`, `/shifts/close` | ✅ | ✅ | ❌ |
| Staff CRUD, reports, alerts, shifts/transactions lists, stock & restock | ❌ | ✅ own branch | ✅ any |
| Branch CRUD, product/ingredient/recipe writes, `/dashboard` | ❌ | ❌ | ✅ |

Managers and staff get `403` on any `branch_id` that isn't theirs — the frontend should scope branch pickers accordingly.

---

## API Overview

| Area | Endpoints |
|---|---|
| Auth | `POST /api/auth/admin-login`, `POST /api/auth/staff-login`, `POST /api/auth/logout`, `GET /api/auth/me` |
| Products | `GET/POST /api/products`, `GET/PUT/DELETE /api/products/{id}` |
| Ingredients | `GET/POST /api/ingredients`, `GET/PUT/DELETE /api/ingredients/{id}` |
| Recipes (formulas) | `GET /api/recipes?product_id=`, `POST /api/recipes`, `GET/PUT/DELETE /api/recipes/{id}` |
| Branches | `GET/POST /api/branches`, `GET/PUT/DELETE /api/branches/{id}`, `GET /api/branches/{id}/stock` |
| Stock | `POST /api/stock` (initial), `POST /api/stock/restock`, `GET /api/stock/low-stock?branch_id=`, `GET /api/stock/{id}/movements` |
| POS / Transactions | `POST /api/transactions` (auto recipe deduction, race-safe), `GET /api/transactions?branch_id=`, `GET /api/transactions/{id}` |
| Shifts | `POST /api/shifts/open`, `POST /api/shifts/close` (auto variance → alert), `GET /api/shifts?branch_id=`, `GET /api/shifts/{id}` |
| Alerts | `GET /api/alerts?branch_id=`, `GET /api/alerts/{id}`, `PUT /api/alerts/{id}/review`, `PUT /api/alerts/{id}/dismiss` |
| Reports | `GET /api/reports/sales?branch_id=&period=daily|weekly|monthly`, `GET /api/reports/inventory?branch_id=` |
| Notifications | `GET /api/notifications`, `PUT /api/notifications/{id}/read` |
| Receipts (OCR) | `POST /api/receipts/scan`, `GET /api/receipts?branch_id=`, `GET /api/receipts/summary?branch_id=`, `GET /api/receipts/{id}` |
| Dashboard | `GET /api/dashboard` (super admin, global counts) |

Full request/response examples are in the Postman collection.

## How the Anti-Theft Pipeline Works

1. **Sale** → `POST /api/transactions` deducts each recipe ingredient from `branch_stock` (row-locked to prevent overselling) and logs a `sale` stock movement.
2. **Shift close** → staff submits physical counts; the system computes variance vs. expected stock, creates a `DiscrepancyAlert` on any mismatch, corrects stock to the physical count, and logs a `shift_correction` movement.
3. **Alert** → an observer instantly creates in-app notifications and sends an email to the branch manager + super admins.
4. **Audit** → `GET /api/stock/{id}/movements` shows every change (initial/restock/sale/shift_correction) with before/after quantities and who did it.
