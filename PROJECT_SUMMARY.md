# NITA Inventory Tracker — Complete Project Reference

> **Generated:** July 8, 2026  
> **Stack:** Laravel 13.17 / PHP 8.3 / SQLite / Blade / Tailwind CSS v4 / Vite 8 / Sanctum  
> **DB Driver:** SQLite (default) with configurable MySQL, MariaDB, PostgreSQL, SQL Server  
> **Cache/Queue:** Database-driven (configurable to Redis, file, Memcached, DynamoDB)

---

## 📁 PROJECT STRUCTURE

```
├── API_DOCS.md                          # Stale API documentation (placeholder)
├── PROJECT_SUMMARY.md                   # This file
├── README.md                            # Stock Laravel README (not customized)
├── artisan                              # Laravel CLI entry point
├── composer.json                        # PHP dependencies
├── package.json                         # Node.js dev dependencies (Vite, Tailwind)
├── phpunit.xml                          # PHPUnit test configuration
├── vite.config.js                       # Vite build config (Tailwind + Instrument Sans font)
├── .editorconfig                        # Editor formatting rules
├── .env.example                         # Environment template (SQLite default)
├── .gitattributes
├── .gitignore
│
├── app/
│   ├── Exceptions/
│   │   └── InsufficientStockException.php   # Custom exception (not yet thrown anywhere)
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php                   # Base controller
│   │   │   ├── AuthController.php                # API auth (owner-login, staff-login, logout, me)
│   │   │   ├── AuthOnboardingController.php      # Web registration views + API placeholder handlers
│   │   │   ├── DashboardController.php           # Live dashboard data (sales, alerts, branches)
│   │   │   ├── BranchesController.php            # Web branch listing page
│   │   │   ├── InventoryController.php           # Web stock levels page
│   │   │   ├── RecipesController.php             # Web recipes page
│   │   │   ├── AlertsController.php              # Web discrepancy alerts page
│   │   │   └── Api/
│   │   │       ├── BranchController.php          # API CRUD for branches
│   │   │       ├── StaffController.php           # API CRUD for staff/managers
│   │   │       ├── ReceiptController.php         # API receipt scanning + reconciliation
│   │   │       └── TransactionController.php     # API POS transaction with stock deduction
│   │   │
│   │   ├── Requests/
│   │   │   ├── BranchFormRequest.php             # Validation rules for branch CRUD
│   │   │   ├── StaffStoreRequest.php             # Validation for creating staff
│   │   │   └── StaffUpdateRequest.php            # Validation for updating staff
│   │   │
│   │   └── Resources/
│   │       ├── BranchResource.php                # API resource transformer (branch + staff_count)
│   │       └── StaffResource.php                 # API resource transformer (staff + branch)
│   │
│   ├── Models/
│   │   ├── User.php                 # Authenticatable with Sanctum. Roles: owner/staff/manager
│   │   ├── Branch.php               # Branch locations with location/status
│   │   ├── Product.php              # Menu items with category/price/is_active
│   │   ├── Ingredient.php           # Raw materials with name/unit (g/kg/ml/l/pcs)
│   │   ├── Recipe.php               # Product-to-ingredient mappings (quantity_required)
│   │   ├── BranchStock.php          # Per-branch inventory (current_quantity, min_threshold, stock_status)
│   │   ├── Transaction.php          # POS transactions (client_uuid for offline dedup)
│   │   ├── ShiftLog.php             # Staff shift records (open/closed)
│   │   ├── ShiftStockCount.php      # End-of-shift inventory counts with variance
│   │   ├── DiscrepancyAlert.php     # Stock mismatches (low/medium/high severity, pending/reviewed/dismissed)
│   │   ├── Notification.php         # Alert notifications per user (read_at tracking)
│   │   └── Receipt.php              # OCR-scanned receipt records with reconciliation status
│   │
│   ├── Providers/
│   │   └── AppServiceProvider.php          # Rate limiting (5/min for login + pin-login)
│   │
│   └── Services/
│       ├── OcrService.php                  # Tesseract OCR wrapper (extractText, parseTotalAmount, parseDate)
│       └── ReconciliationService.php        # Auto-match receipts to transactions (1 peso tolerance, 24hr window)
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/
│   ├── app.php          # App name, env, debug, timezone (UTC), locale (en)
│   ├── auth.php         # Auth guards/providers
│   ├── cache.php        # Default: database store. Also: file, redis, memcached, dynamodb, octane
│   ├── database.php     # Default: SQLite. Configured: MySQL, MariaDB, PostgreSQL, SQL Server, Redis
│   ├── filesystems.php  # Local, public (storage link), S3
│   ├── logging.php      # Stack channel with single/daily/slack/papertrail/syslog/errorlog
│   ├── mail.php         # SMTP config (default: log)
│   ├── queue.php        # Default: database (configurable to redis, sqs, beanstalkd)
│   ├── sanctum.php      # Stateful domains (localhost:3000, 127.0.0.1:8000), no token expiration
│   ├── services.php     # Third-party service keys
│   └── session.php      # Default: database driver, 120min lifetime, JSON serialization
│
├── database/
│   ├── factories/
│   │   └── UserFactory.php          # Faker-based user factory
│   ├── migrations/
│   │   ├── 2026_06_26_000001_create_branches_table.php
│   │   ├── 2026_06_26_000002_create_users_table.php
│   │   ├── 2026_06_26_000003_create_products_table.php
│   │   ├── 2026_06_26_000004_create_ingredients_table.php
│   │   ├── 2026_06_26_000005_create_branch_stock_table.php
│   │   ├── 2026_06_26_000006_create_recipes_table.php
│   │   ├── 2026_06_26_000007_create_transactions_table.php
│   │   ├── 2026_06_26_000008_create_shift_logs_table.php
│   │   ├── 2026_06_26_000009_create_shift_stock_counts_table.php
│   │   ├── 2026_06_26_000010_create_discrepancy_alerts_table.php
│   │   ├── 2026_06_26_000011_create_notifications_table.php
│   │   ├── 2026_06_26_100953_create_personal_access_tokens_table.php  # Sanctum
│   │   ├── 2026_07_01_100204_add_min_threshold_to_branch_stock_table.php
│   │   ├── 2026_07_01_100205_add_severity_to_discrepancy_alerts_table.php
│   │   ├── 2026_07_01_100416_make_user_id_nullable_on_transactions_table.php
│   │   ├── 2026_07_01_153130_create_receipts_table.php
│   │   └── 2026_07_06_174259_create_sessions_table.php
│   └── seeders/
│       └── DatabaseSeeder.php       # 21 users (1 owner + 10 PIN managers + 10 desktop managers)
│
├── public/
│   ├── .htaccess
│   ├── index.php                    # Laravel front controller
│   ├── api-docs.html                # Live API documentation (HTML page)
│   └── robots.txt
│
├── resources/
│   ├── css/app.css                  # Tailwind import
│   ├── js/app.js                    # Vite entry
│   └── views/
│       ├── alerts/index.blade.php       # Discrepancy alerts table with tabs + filters
│       ├── auth/
│       │   ├── login.blade.php              # Unified login (Full Name, Email, AdminID, Password)
│       │   ├── register-step-1.blade.php    # Role selection (Owner/Manager)
│       │   ├── register-step-2.blade.php    # Fallback → owner step-2
│       │   ├── register-step-3.blade.php    # Fallback → owner step-3
│       │   ├── register-owner-step-2.blade.php  # Owner: business registration docs
│       │   ├── register-owner-step-3.blade.php  # Owner: consent confirmation
│       │   ├── register-manager-step-2.blade.php # Manager: business + branch info
│       │   └── register-manager-step-3.blade.php # Manager: consent confirmation
│       ├── branches/index.blade.php      # Branch table (extends layouts.app)
│       ├── business/
│       │   ├── recipes.blade.php         # ** Standalone: recipe cards + branch selector **
│       │   └── summary.blade.php         # ** Standalone: analytics summary + branch selector **
│       ├── dashboard.blade.php           # ** Standalone: main dashboard view **
│       ├── inventory/index.blade.php     # Stock levels table (extends layouts.app)
│       ├── layouts/app.blade.php          # Orphaned sidebar layout (no views extend it anymore)
│       ├── logistics/index.blade.php      # ** Standalone: logistics metrics dashboard **
│       ├── recipes/index.blade.php        # Product recipes table (extends layouts.app)
│       └── settings/index.blade.php       # ** Standalone: slide-out settings drawer **
│
├── routes/
│   ├── api.php                   # Sanctum API routes (auth, staff, branches, transactions, receipts)
│   ├── console.php               # Artisan commands
│   └── web.php                   # Session-based routes (login, dashboard, business, logistics, settings)
│
├── storage/                      # Laravel storage (logs, cache, sessions, views)
│
└── tests/
    ├── Feature/ExampleTest.php    # GET / → 200
    ├── Unit/ExampleTest.php       # true === true
    └── TestCase.php               # Base test case
```

---

## 🗄️ DATABASE SCHEMA (17 Migrations)

### `branches`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| location | string? | |
| status | enum | `active`, `inactive` (default: active) |
| created_at | timestamp | |
| updated_at | timestamp | |

### `users`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string? | unique |
| password | string? | `hashed` cast (auto-hashed) |
| pin | string? | `hashed` cast (auto-hashed) |
| role | enum | `owner`, `staff`, `manager` (default: manager) |
| branch_id | FK→branches? | nullable (owner has null) |
| email_verified_at | timestamp? | |
| remember_token | string? | |
| created_at | timestamp | |
| updated_at | timestamp | |

### `products`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| category | string? | e.g. "Milk Tea", "Goods" |
| price | decimal(10,2) | |
| is_active | boolean | default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

### `ingredients`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| unit | enum | `g`, `kg`, `ml`, `l`, `pcs` (default: pcs) |
| created_at | timestamp | |
| updated_at | timestamp | |

### `recipes`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| product_id | FK→products | cascade delete |
| ingredient_id | FK→ingredients | cascade delete |
| quantity_required | decimal(12,3) | |
| created_at | timestamp | |
| updated_at | timestamp | |
| *unique* | | (product_id, ingredient_id) |

### `branch_stock`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| branch_id | FK→branches | cascade delete |
| ingredient_id | FK→ingredients | cascade delete |
| current_quantity | decimal(12,3) | default: 0 |
| min_threshold | decimal(12,3) | default: 0 (added in later migration) |
| last_updated_at | timestamp? | |
| created_at | timestamp | |
| updated_at | timestamp | |
| *unique* | | (branch_id, ingredient_id) |

### `transactions`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| client_uuid | uuid | unique (offline dedup) |
| branch_id | FK→branches | cascade delete |
| user_id | FK→users? | nullable (can be null for anonymous sales) |
| product_id | FK→products | cascade delete |
| quantity | unsigned int | |
| total_amount | decimal(10,2) | price × quantity |
| sync_status | enum | `pending`, `synced` (default: synced) |
| created_offline_at | timestamp? | |
| synced_at | timestamp? | |
| created_at | timestamp | |
| updated_at | timestamp | |

### `shift_logs`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| branch_id | FK→branches | cascade delete |
| user_id | FK→users | cascade delete |
| shift_start | timestamp | |
| shift_end | timestamp? | |
| status | enum | `open`, `closed` (default: open) |
| created_at | timestamp | |
| updated_at | timestamp | |

### `shift_stock_counts`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| shift_log_id | FK→shift_logs | cascade delete |
| ingredient_id | FK→ingredients | cascade delete |
| opening_quantity | decimal(12,3) | |
| closing_quantity_expected | decimal(12,3)? | |
| closing_quantity_actual | decimal(12,3)? | |
| variance | decimal(12,3)? | expected − actual |
| created_at | timestamp | |
| updated_at | timestamp | |
| *unique* | | (shift_log_id, ingredient_id) |

### `discrepancy_alerts`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| branch_id | FK→branches | cascade delete |
| type | enum | `stock_mismatch`, `shift_variance` (default: stock_mismatch) |
| severity | enum | `low`, `medium`, `high` (default: medium, added in later migration) |
| ingredient_id | FK→ingredients? | nullOnDelete |
| shift_log_id | FK→shift_logs? | nullOnDelete |
| expected_value | decimal(12,3)? | |
| actual_value | decimal(12,3)? | |
| variance | decimal(12,3)? | expected − actual |
| details | text? | |
| status | enum | `pending`, `reviewed`, `dismissed` (default: pending) |
| created_at | timestamp | |
| updated_at | timestamp | |

### `notifications`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK→users | cascade delete |
| discrepancy_alert_id | FK→discrepancy_alerts? | cascade delete |
| title | string | |
| message | text | |
| read_at | timestamp? | |
| created_at | timestamp | |
| updated_at | timestamp | |

### `receipts`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| branch_id | FK→branches | cascade delete |
| user_id | FK→users? | nullOnDelete |
| image_path | string | stored in storage/app/public/receipts/ |
| raw_ocr_text | text? | raw Tesseract output |
| parsed_total_amount | decimal(10,2)? | extracted via OCR patterns |
| matched_transaction_id | FK→transactions? | nullOnDelete |
| reconciliation_status | enum | `pending`, `matched`, `mismatched`, `unreadable` (default: pending) |
| scanned_at | timestamp? | |
| created_at | timestamp | |
| updated_at | timestamp | |

### `sessions` (Laravel built-in)
| Column | Type |
|--------|------|
| id | string PK |
| user_id | FK→users? |
| ip_address | string(45)? |
| user_agent | text? |
| payload | longText |
| last_activity | int (indexed) |

### `personal_access_tokens` (Sanctum)
Standard Laravel Sanctum tokens table.

---

## 🔙 BACKEND — Controllers & Logic

### API Authentication (Sanctum — `routes/api.php`)

| Method | Endpoint | Controller Method | Auth | Description |
|--------|----------|-------------------|------|-------------|
| POST | `/api/auth/owner-login` | `AuthController@ownerLogin` | None | Email + password → Sanctum token. Throttle: 5/min |
| POST | `/api/auth/staff-login` | `AuthController@staffLogin` | None | PIN + branch_id → Sanctum token (staff + manager). Throttle: 5/min |
| POST | `/api/login` | `AuthController@login` | None | Unified PIN login. Throttle: 5/min |
| POST | `/api/auth/logout` | `AuthController@logout` | Bearer | Revoke current token |
| GET | `/api/auth/me` | `AuthController@me` | Bearer | Get user + branch |
| GET | `/api/me` | `AuthController@me` | Bearer | Alias for /api/auth/me |

### API Onboarding (No auth — `routes/api.php`)

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| POST | `/api/auth/login` | `AuthOnboardingController@apiLogin` | Session login (web middleware) or JSON login → Sanctum token |
| POST | `/api/auth/register/step-1` | `AuthOnboardingController@apiRegisterStep1` | Validate personal info + role |
| POST | `/api/auth/register/step-2` | `AuthOnboardingController@apiRegisterStep2` | Validate multi-location businesses |
| POST | `/api/auth/register/manager/step-2` | `AuthOnboardingController@apiRegisterManagerStep2` | Validate branch manager info |
| POST | `/api/auth/register/confirm` | `AuthOnboardingController@apiRegisterConfirm` | Final confirmation + doc trackers (placeholder) |

### API Protected Resources (Sanctum — `routes/api.php`)

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/api/staff` | `StaffController@index` | List paginated staff/managers |
| POST | `/api/staff` | `StaffController@store` | Create manager with PIN |
| GET | `/api/staff/{staff}` | `StaffController@show` | Get single staff |
| PUT | `/api/staff/{staff}` | `StaffController@update` | Update staff |
| DELETE | `/api/staff/{staff}` | `StaffController@destroy` | Delete staff + revoke tokens |
| GET | `/api/branches` | `BranchController@index` | List branches with staff_count |
| POST | `/api/branches` | `BranchController@store` | Create branch |
| GET | `/api/branches/{branch}` | `BranchController@show` | Get single branch |
| PUT | `/api/branches/{branch}` | `BranchController@update` | Update branch |
| POST | `/api/transactions` | `TransactionController@store` | Record sale with stock deduction + alert creation |
| POST | `/api/receipts/scan` | `ReceiptController@scan` | Scan receipt OCR + auto-reconcile |
| GET | `/api/receipts` | `ReceiptController@index` | List receipts by branch (paginated) |
| GET | `/api/receipts/summary` | `ReceiptController@summary` | Receipt counts by status |
| GET | `/api/branches/{branch}/stock` | Closure | Get branch stock with ingredient details |
| GET | `/api/dashboard` | Closure | Dashboard meta counts |
| POST | `/api/test-transaction` | `TransactionController@store` | Dev endpoint (no auth) |
| POST | `/api/test-receipt-scan` | `ReceiptController@scan` | Dev endpoint (no auth) |

### Web Routes (Session-based — `routes/web.php`)

| Method | Path | Handler | View |
|--------|------|---------|------|
| GET | `/` or `/login` or `/auth/login` | `AuthOnboardingController@showLogin` | `auth.login` |
| GET | `/auth/register/step-1` | `AuthOnboardingController@showRegisterStep1` | `auth.register-step-1` |
| GET | `/auth/register/step-2/3` | Fallback to owner steps | fallback |
| GET | `/auth/register/owner/step-2/3` | Owner step views | owner registration |
| GET | `/auth/register/manager/step-2/3` | Manager step views | manager registration |
| POST | `/login` | Closure (Auth::attempt) | Redirect to dashboard |
| GET | `/dashboard` | `DashboardController@index` | `dashboard` |
| GET | `/recipes` | `RecipesController@index` | `recipes.index` |
| GET | `/inventory` | `InventoryController@index` | `inventory.index` |
| GET | `/branches` | `BranchesController@index` | `branches.index` |
| GET | `/alerts` | `AlertsController@index` | `alerts.index` |
| GET | `/business/recipes` | Closure | `business.recipes` |
| GET | `/business/summary` | Closure | `business.summary` |
| GET | `/logistics` | Closure | `logistics.index` |
| GET | `/settings` | Closure | `settings.index` |
| POST | `/logout` | Closure (Auth::logout) | Redirect to login |

### Transaction Flow (Core Business Logic)

1. **POS Terminal** → `POST /api/transactions` with `client_uuid` (offline dedup)
2. **Stock Deduction**: Each recipe's ingredients are checked + decremented
3. **Alert Creation**: If stock ≤ min_threshold after deduction, `DiscrepancyAlert` created
4. **Receipt Scanning**: `POST /api/receipts/scan` → Tesseract OCR → amount parsing → auto-reconciliation
5. **Reconciliation**: Matches receipts to transactions (same branch, ±₱1, within 24hrs). Mismatches create alerts

---

## 🎨 FRONTEND — 18 Blade Views

### Auth Pages (7 views — standalone HTML, split-screen layout)

| View | Layout | Key Elements |
|------|--------|--------------|
| `auth/login` | 2-column split (topographic left / form right) | NITA logo (140×42), Welcome back! header, Full Name + Email + AdminID (with tooltip) + Password fields, "Done" button, "Don't have an account? Sign In" link |
| `auth/register-step-1` | 2-column split | Welcome! header, Full Name + Email + Contact Number + Role selector (Owner/Manager), JS role-based routing to step-2 |
| `auth/register-owner-step-2` | 2-column split | Owner: Business Name, Business Registration (DTI/SEC/CDA), Tax ID (BIR/TIN), Business Permit (LGU/docs), Business Address |
| `auth/register-owner-step-3` | 2-column split | Owner: Confirmation with consent checkboxes (accuracy, legal docs, terms) |
| `auth/register-manager-step-2` | 2-column split | Manager: Business Name, Branch Location, Business Owner name |
| `auth/register-manager-step-3` | 2-column split | Manager: Confirmation with consent checkboxes |

### Dashboard (1 view — standalone HTML, 72/28 two-column)

**`dashboard`** — Full standalone page with top navbar.

- **Top Navbar**: NITA logo (120×36 SVG), [Dashboard] [Businesses] [Logistics] pills (active state), Bell icon (orange), Mail pill (white), Gear icon (gray), Logout button
- **Left Column (72%)**:
  - Title banner: "DASHBOARD | Owner/Manager"
  - Flag Summary: 3×3 grid of location badges with severity legend (yellow/orange/red dots)
  - Key Metrics: Total Monthly Revenue ($300k), Overall Leakage (20%↓), Total Value Saved ($80k)
  - Rankings: Top Earner last Month (8 rows) + Least Leakage last Month (8 rows)
- **Right Column (28%)**:
  - Terracotta sidebar with topographic pattern overlay
  - June Schedule calendar (5 weeks)
  - 3 upcoming schedule items (Team Meeting, Submit requirements, Funding Handover)

**Data source**: `DashboardController` passes `$total_branches`, `$pending_alerts`, `$low_stock_count`, `$total_sales` (today), `$daily_sales` (7 days), `$branches_with_sales` (today), `$recent_alerts` (last 5), `$recent_transactions` (last 10) — but the view currently uses **hardcoded static data**.

### Business Pages (2 views — standalone HTML with branch-selector layout)

**Shared Layout (both views)**:
- Top navbar with **Businesses pill active**
- Left branch-selector sidebar (120px):
  - "Coffee Shop" header badge with coffee cup icon + "Main Branch" sublabel
  - 6 circular clickable branch badges: **QC** (active, dark brown fill), Makati, BGC, Cebu, Davao, Clark
  - Owners see all 6 branches; managers see only their assigned branch
  - Hover effect: scale up + dark fill
- Right workspace:
  - Title header: "BUSINESSES | Owner/Manager"
  - Sub-header tabs: [Summary] [Recipe] [Staff/Profile] [Verification] (active tab matches current page; Summary/Recipe link to routes; Staff/Verification link to `#`)

**`business/recipes`**:
- Sub-nav: Summary tab → `/business/summary`, **Recipe tab active**
- Search bar + category pills (Drinks/Goods/Sets)
- Recipe card: "Black Forest Milk Tea" with Regular/Large/Procedure ingredient table (ingredient, regular amount, large amount, procedure)
- 8 ingredients: Black Tea, Fresh Milk, Brown Sugar, Tapioca Pearls, Cream Cheese, Whipped Cream, Chocolate Syrup, Ice

**`business/summary`**:
- Sub-nav: **Summary tab active**, Recipe tab → `/business/recipes`
- 2-column analytics grid:
  - Current Activity (5-line activity log with timestamps)
  - Annual Leakages (5-line table with % leakages per month)
  - Total Profit Margin (card with 20%↑, green trend arrow)
  - Annual Performance Analytics (line chart placeholders)
  - Annual Historical Trends (area chart placeholders)

### Logistics Page (1 view — standalone HTML)

**`logistics.index`** — Top navbar with **Logistics pill active**.

- Title banner: "LOGISTICS | Owner/Manager"
- Sub-nav pills: Summary (inactive), Flags (active)
- 2-column card grid (6 cards):
  - **Variables**: Constant Float Value ($200), Expected Total Sales, Total Inventory
  - **Remarks**: Leakage + Inventory indicator thresholds (Normal → Out, 5 levels with colored dots)
  - **Float Amount Discrepancy**: Formula: Actual Till / Constant Float Value (must = 1)
  - **Total Sales Discrepancy**: Formula: Actual Cash / Expected Total Sales
  - **EOD Inventory Discrepancy**: Formula: Actual Inventory Left / Expected Inventory Left
  - **Leakage & Inventory Breakdown**: Percentage ranges per indicator level (Normal: <5% / >60%, Out: >20% / <10%)

Each card has an "Edit" button (bottom-right positioning via `.has-edit` padding).

### Standard Layout Pages (5 views — extend `layouts.app`)

**Note**: `layouts/app.blade.php` is an **orphaned sidebar layout** — no views currently extend it since all views were rebuilt as standalone HTML.

| View | Route | Content |
|------|-------|---------|
| `branches.index` | `/branches` | Branch table: name, location, status badge (active/inactive), today's sales (₱), staff count, View button |
| `inventory.index` | `/inventory` | Branch filter dropdown, OK/Low/Out summary cards, stock table: ingredient, unit, branch, on hand, min threshold, status badge, last updated. Client-side JS branch filter |
| `recipes.index` | `/recipes` | Search input, category tabs (all products count per category), product table: product name, category, ingredient count, price, updated date, Edit button. Client-side JS search + category filter |
| `alerts.index` | `/alerts` | Status tabs (All/Pending/Reviewed/Dismissed), severity filter dropdown, alert table: branch, type, severity badge, ingredient, expected/actual/variance, status badge, date, Review button. Client-side JS dual filter |
| `settings.index` | `/settings` | **Standalone slide-out drawer overlay**. Profile card (avatar + name + email), Account section (Name, Email, Role), Preferences section (Currency: USD, Language: English, Notifications: Enabled), Logout button. Dimmed background → click to dismiss |

### Branding & Design System

| Token | Value | Usage |
|-------|-------|-------|
| Surface bg | `#FDF5D6` | Light cream, all page backgrounds |
| Text / borders | `#5C2D1B` | Dark cocoa brown, headers, borders |
| Accent | `#BC614B` | Terracotta, active pills, sidebar cards |
| Danger | `#ef4444` / `#dc2626` | Red badges, logout, high severity |
| Warning | `#eab308` / `#f97316` | Yellow/orange, low/medium severity |
| Success | `#16a34a` | Green badges, OK status |
| NITA Logo | Custom SVG | Store stall icon + "NITA" text + "INVENTORY TRACKER" subtitle |
| Bell Icon | Custom SVG | Orange bell (#FFAA2C) + brown clapper (#5C2D1B) |

---

## 👤 SEED DATA (21 Users)

### Branches (6)

| Name | Location | Status |
|------|----------|--------|
| Branch QC | Quezon City, Metro Manila | active |
| Branch Makati | Makati City, Metro Manila | active |
| Branch BGC | Bonifacio Global City, Taguig | active |
| Branch Cebu | Cebu City, Cebu | active |
| Branch Davao | Davao City, Davao del Sur | active |
| Branch Clark | Clark Freeport Zone, Pampanga | active |

### Owner (Web Login — email + password)

| Name | Email | Password | Role |
|------|-------|----------|------|
| Admin Owner | `admin@inventory.ph` | `password` | Owner |

### Desktop Managers (Web Login — email + password `password123`)

| Name | Email | Branch |
|------|-------|--------|
| Juan Cruz | juan.cruz@nita.com | QC |
| Maria Santos | maria.santos@nita.com | QC |
| Pedro Reyes | pedro.reyes@nita.com | Makati |
| Ana Gonzales | ana.gonzales@nita.com | Makati |
| Jose Mercado | jose.mercado@nita.com | BGC |
| Luisa Fernandez | luisa.fernandez@nita.com | BGC |
| Carlos Ramos | carlos.ramos@nita.com | Cebu |
| Elena Torres | elena.torres@nita.com | Davao |
| Miguel Villanueva | miguel.villanueva@nita.com | Davao |
| Sofia Lim | sofia.lim@nita.com | Clark |

### PIN Managers (Tablet Login — PIN only)

| Name | PIN | Branch |
|------|-----|--------|
| Juan Cruz | 1234 | QC |
| Maria Santos | 2345 | QC |
| Pedro Reyes | 3456 | Makati |
| Ana Gonzales | 4567 | Makati |
| Jose Mercado | 5678 | BGC |
| Luisa Fernandez | 6789 | BGC |
| Carlos Ramos | 7890 | Cebu |
| Elena Torres | 8901 | Davao |
| Miguel Villanueva | 9012 | Davao |
| Sofia Lim | 0123 | Clark |

---

## ⚙️ INFRASTRUCTURE & CONFIG

### Environment (`.env.example` defaults)
- **Database**: SQLite (`database/database.sqlite`)
- **Session**: Database driver (`sessions` table)
- **Cache**: Database store (`cache` table)
- **Queue**: Database connection
- **Mail**: Log driver
- **Filesystem**: Local (public disk at `storage/app/public`)
- **Logging**: Stack → single file (`storage/logs/laravel.log`)

### Composer Dependencies
| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^13.8 | Core framework |
| `laravel/sanctum` | ^4.3 | API token auth |
| `laravel/tinker` | ^3.0 | Artisan REPL |
| `thiagoalessio/tesseract_ocr` | ^2.13 | OCR receipt scanning |
| `fakerphp/faker` | ^1.23 (dev) | Fake data generation |
| `laravel/pint` | ^1.27 (dev) | PHP code style fixer |
| `phpunit/phpunit` | ^12.5 (dev) | Testing framework |

### Node Dependencies (Dev)
| Package | Version | Purpose |
|---------|---------|---------|
| `@tailwindcss/vite` | ^4.0 | Tailwind CSS v4 Vite plugin |
| `tailwindcss` | ^4.0 | Utility-first CSS |
| `laravel-vite-plugin` | ^3.1 | Laravel Vite integration |
| `vite` | ^8.0 | Build tool |
| `concurrently` | ^9.0 | Run multiple dev commands |

### Vite Build
- Entry: `resources/css/app.css` + `resources/js/app.js`
- Font: Instrument Sans (weights 400, 500, 600 via Bunny CDN)
- Refresh on change (excludes compiled Blade views)

---

## 🔍 KNOWN ISSUES & UNFINISHED PIECES

### Structural
- **`layouts/app.blade.php` is orphaned** — all views rebuilt as standalone HTML; no view extends it
- **4 child views still extend the orphaned layout**: `branches/index`, `inventory/index`, `recipes/index`, `alerts/index` — they render with the old sidebar
- **Settings drawer** is standalone HTML; not integrated with the unified top navbar

### Missing Data
- Database has **only branches + users** after seeding — no products, ingredients, recipes, stock, transactions, shift logs, or alerts
- Dashboard and logistics pages show **hardcoded static placeholder data** instead of live DB queries
- Branch selector in business pages is **hardcoded** (6 static branches with QC active)

### Missing Routes & Views
- Staff/Profile sub-tab → links to `#` (no route or view)
- Verification sub-tab → links to `#` (no route or view)

### Bugs / Cleanup
- Login form collects `full_name` and `admin_id` fields but only `email` + `password` are validated
- "Don't have an account? Sign In" link on login page points to registration but says "Sign In" (misleading)
- `register-step-1` uses GET form + JS redirect instead of POST
- `InsufficientStockException` exists but is never thrown
- `layouts/app.blade.php` logout uses `{{ url('/api/logout') }}` instead of `{{ route('logout') }}`
- `API_DOCS.md` references mock JWT tokens and stale field names — not synced with actual implementation

### Services
- OcrService requires **Tesseract OCR binary** installed on the server (not included in Composer)
- ReconciliationService is implemented but untested without real receipt data

---

## 🏗️ DESIGN ARCHITECTURE

```
┌──────────────────────────────────────────────────────────────────────────┐
│  TOP NAVBAR (all standalone pages)                                        │
│  [NITA Logo 120x36]  [Dashboard] [Businesses] [Logistics]  🔔 ✉ ⚙️ Logout│
├──────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌────────────────────────────────────────────────────────────────────┐  │
│  │  DASHBOARD (72/28 two-col)                                         │  │
│  │  ┌─ Title Banner ──────────────────────────────────────────┐       │  │
│  │  │  DASHBOARD | Owner/Manager                               │       │  │
│  │  ├─ Flag Summary (3x3 grid) ───────────────────────────────┤       │  │
│  │  ├─ Key Metrics (3 cards) ─────────────────────────────────┤       │  │
│  │  ├─ Rankings (2 columns) ──────────────────────────────────┤       │  │
│  │  └─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ┘       │  │
│  │                                       ┌──────────────────────┐    │  │
│  │                                       │ JUNE SCHEDULE        │    │  │
│  │                                       │ Calendar + Events    │    │  │
│  │                                       │ (terracotta card)    │    │  │
│  │                                       └──────────────────────┘    │  │
│  └────────────────────────────────────────────────────────────────────┘  │
│                                                                           │
│  ┌──────────┬──────────────────────────────────────────────────────────┐ │
│  │ BRANCH   │  BUSINESSES                                              │ │
│  │ SELECTOR │  ┌─────────────────────────────────────────────────────┐ │ │
│  │ (120px)  │  │  BUSINESSES | Owner/Manager                         │ │ │
│  │          │  │  [Summary] [Recipe] [Staff] [Verification]           │ │ │
│  │ Coffee   │  ├─────────────────────────────────────────────────────┤ │ │
│  │ Shop     │  │ Content cards / tables / charts                      │ │ │
│  │  MAIN    │  │                                                     │ │ │
│  │          │  │                                                     │ │ │
│  │  QC ●    │  └─────────────────────────────────────────────────────┘ │ │
│  │  MAK ○   │                                                        │ │
│  │  BGC ○   └──────────────────────────────────────────────────────────┘ │
│  │  CEB ○                                                               │
│  │  DVO ○                                                               │
│  │  CLK ○                                                               │
│  └──────────┘                                                           │
│                                                                           │
│  LOGISTICS                                                               │
│  ┌────────────────────────────────────────────────────────────────────┐  │
│  │  LOGISTICS | Owner/Manager  [Summary] [Flags]                     │  │
│  ├────────────────────────────────────────────────────────────────────┤  │
│  │  ┌──────────────────┐  ┌──────────────────┐                        │  │
│  │  │ VARIABLES        │  │ REMARKS          │                        │  │
│  │  ├──────────────────┤  ├──────────────────┤                        │  │
│  │  │ $200, Sales,...  │  │ Leakage/Inv ind  │                        │  │
│  └──┴──────────────────┴──┴──────────────────┴────────────────────────┘  │
│                                                                           │
│  AUTH PAGES (split-screen)                                               │
│  ┌──────────────────────┬──────────────────────────────────────────────┐ │
│  │  Topographic         │  Form Card                                    │ │
│  │  Pattern             │  [NITA Logo 140x42]                          │ │
│  │  (12x12 grid)        │  Welcome! / Welcome back!                    │ │
│  │                      │  Fields + Done/Next button                   │ │
│  └──────────────────────┴──────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────────┘
```
