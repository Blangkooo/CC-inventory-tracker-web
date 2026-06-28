# CC Inventory Tracker — Backend API

A backend REST API for a multi-branch inventory tracking system designed for food cart and micro-franchise businesses. Built to support an offline-first mobile POS app where branch staff can record sales, track ingredient stock, and log shifts — even without internet — and sync data to the server when connectivity is restored.

---

## What this does

- Authenticates branch staff using a PIN (no email/password required)
- Tracks product sales and automatically deducts ingredients from stock based on recipes
- Logs shift opening and closing stock, calculates variance, and flags suspicious discrepancies
- Supports idempotent transaction syncing via UUID — safe to re-submit offline transactions without duplicating records
- Issues Sanctum API tokens for authenticated access to all protected routes

---

## Tech stack

| Technology | Role |
|------------|------|
| **PHP 8.3** | Server-side language |
| **Laravel 13** | Backend framework — routing, ORM, migrations, validation |
| **Laravel Sanctum** | Token-based API authentication |
| **MySQL 8.4** | Relational database |
| **Eloquent ORM** | Database models and relationships |

---

## Database tables

| Table | Description |
|-------|-------------|
| `branches` | Physical locations/branches of the business |
| `users` | Staff accounts with PIN login, linked to a branch |
| `products` | Items sold at the POS (e.g. Milk Tea, Fries) |
| `recipes` | Ingredient breakdown per product (used for stock deduction) |
| `transactions` | Sales records, each with a UUID for offline-sync idempotency |
| `stock_levels` | Current ingredient stock per branch |
| `shift_logs` | Shift records with opening/closing stock, variance, and a flag if variance exceeds threshold |

---

## API endpoints

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| POST | `/api/login` | No | PIN login, returns Sanctum token |
| GET | `/api/products` | Yes | List all products |
| GET | `/api/recipes` | Yes | List recipes, filter by `?product_id=` |
| POST | `/api/transactions` | Yes | Record a sale, deduct stock, idempotent by UUID |
| POST | `/api/shift-logs` | Yes | Submit shift log, auto-calculates variance |

See `API_DOCS.md` for full request/response examples.

---

## Local setup

**Requirements:** PHP 8.3, Composer, MySQL 8.4 (Laragon recommended on Windows)

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure .env with your DB credentials, then run:
php artisan migrate

# Start the dev server
php artisan serve
```

---

## Project context

This is the backend component of a larger system that includes a React Native mobile app for offline-first POS operations across multiple food cart branches. The mobile app syncs transactions to this API when internet is available.
