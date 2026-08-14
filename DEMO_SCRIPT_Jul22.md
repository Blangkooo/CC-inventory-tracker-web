# NITA — Demo Script (July 22, 2026 · 6 PM Presentation)
### Para kina Justine (frontend) at Edgardo (backend)

---

## CHECKLIST BAGO MAG-DEMO

| Item | Value |
|------|-------|
| Server | `cd C:\Users\ADMIN\inventory-tracker` → `php artisan serve` |
| Postman collection | **Multi-Branch Inventory Tracker API** |
| Base URL | `http://127.0.0.1:8000` |
| Browser | Open `http://127.0.0.1:8000/login` |
| Admin login | `owner@inventory.test` / `123456789` |
| Manager login | `manager@inventory.test` / `123456789` (QC branch) |
| Staff PIN | `123456789` + branch_id = 1 |

> **TANDAAN:** Lahat ng password ngayon ay `123456789` — hindi na `password`.

---

# PART 1: BACKEND DEMO — SI EDGARDO (15–20 min)

---

## Step 1 — Database Schema (2 min)
> **Sasabihin:** "Bago tayo pumasok sa Postman, ipapakita ko muna ang database structure namin."

Buksan ang `database/migrations/` folder at i-highlight ang **13 tables**:

| Table | Purpose |
|-------|---------|
| `branches` | 6 operating branches (QC, Makati, BGC, Cebu, Davao, Clark) |
| `users` | 3 roles: super_admin, manager, staff |
| `products` | Menu items (Classic Milk Tea, Taro, Siomai, Pandesal) |
| `ingredients` | Raw materials (Flavor Powder, Cups, Pearls, Pork, Flour...) |
| `recipes` | Links product → ingredient with quantity per size (Regular/Large) |
| `branch_stock` | Per-branch inventory levels with min_threshold |
| `transactions` | POS sales — auto-deducts ingredients |
| `shift_logs` | Shift open/close with stock counting |
| `shift_stock_counts` | Actual counts per ingredient per shift |
| `discrepancy_alerts` | Auto-generated when actual ≠ expected |
| `notifications` | System notifications |
| `worker_profiles` | Employee details, schedule, performance |
| `stock_movements` | Audit trail of every stock change |

> **Sasabihin:** "Ang pinaka-importante dito: Branch → may stocks, users, transactions. Product → may recipe na naka-link sa ingredients. Kaya pag may benta, automatic na-dededuct ang bawat ingredient — hindi lang yung finished product."

---

## Step 2 — Postman Live Demo (12–15 min)

### 2.1 Admin Login
- Folder: **Auth** → **Admin Login**
- Method: `POST`
- URL: `{{base_url}}/api/auth/admin-login`
- Body (raw JSON):
```json
{
  "email": "owner@inventory.test",
  "password": "123456789"
}
```
- **Send** → 200 OK
- Ituro ang **token** sa response (JWT — 3 parts na pinaghihiwalay ng tuldok)

> **Sasabihin:** "Ito ang JWT token — parang digital ID card. Kailangan ito sa lahat ng susunod na request para malaman ng system kung sino ka at ano ang role mo."

✅ Auto-saved na ang token sa `{{admin_token}}` variable.

---

### 2.2 RBAC Test — Staff Blocked (403)

**Staff Login muna:**
- Folder: **Auth** → **Staff Login**
- Body:
```json
{
  "pin": "123456789",
  "branch_id": 1
}
```
- **Send** → 200 OK → makukuha ang `staff_token`

**Ngayon, Create Product gamit ang staff_token:**
- Folder: **Products** → **Create Product**
- Headers: `Authorization: Bearer {{staff_token}}`
- Body:
```json
{
  "name": "Illegal Product",
  "category": "Test",
  "price": 99
}
```
- **Send** → **403 Forbidden** 🚫

> **Sasabihin:** "Kahit valid ang token ni Staff Juan, hindi siya pwedeng mag-create ng product. Naka-block agad — role-based access control. Super admin lang ang pwede."

⚠️ **Pagkatapos:** Ibalik ang Header sa `Bearer {{admin_token}}`.

💡 **Kung 401 ang lumabas (hindi 403):** Walang laman ang `staff_token` — mag-Send muna ng Staff Login.

---

### 2.3 Manager Branch-Lock (200 vs 403)

**Manager Login:**
- Folder: **Auth** → **Admin Login** (manager account din dito)
- Body:
```json
{
  "email": "manager@inventory.test",
  "password": "123456789"
}
```
- **Send** → 200 OK → naka-save sa `{{manager_token}}`

**Show Own Branch (should 200):**
- Folder: **Branches** → **Show Branch**
- Headers: `Bearer {{manager_token}}`
- URL: `{{base_url}}/api/branches/1`
- **Send** → **200 OK** ✅ (Branch QC — sariling branch)

**Show Other Branch (should 403):**
- Palitan ang URL → `{{base_url}}/api/branches/2`
- **Send** → **403 Forbidden** 🚫

> **Sasabihin:** "Valid ang ID ng Branch Makati, pero hindi branch ni Manager Juan ito — naka-lock siya sa sariling branch lang. Ito ang branch-level security namin."

⚠️ **Pagkatapos:** I-restore ang owner login — Admin Login ulit with `owner@inventory.test`.

---

### 2.4 Create Transaction — Stock Deduction ⭐

- Folder: **Transactions (POS)** → **Create Transaction**
- Headers: `Bearer {{staff_token}}` (staff login muna kung wala pa)
- Body:
```json
{
  "product_id": 1,
  "quantity": 2,
  "branch_id": 1,
  "client_uuid": "demo-sale-001"
}
```
- **Send** → **201 Created**
- I-scroll sa **`updated_stock`** sa response

> **Sasabihin:** "Isang benta ng 2 Classic Milk Tea — pero automatic na nadeduct ang TATLONG ingredients: Flavor Powder (-60g), Milk Tea Cup (-2 pcs), Cup Wrapper (-2 pcs). Hindi lang yung finished product — ingredient-level ang tracking. Ito ang core ng anti-leakage system namin."

---

### 2.5 Shift Open + Close — Discrepancy Alert ⭐

**Open Shift:**
- Folder: **Shifts** → **Open Shift**
- Headers: `Bearer {{staff_token}}`
- Body:
```json
{
  "opening_counts": [
    { "ingredient_id": 1, "opening_quantity": 1900 }
  ]
}
```
- **Send** → **201 Created** → tandaan ang `shift_log.id` (hal. `2`)

**Close Shift (with discrepancy):**
- Folder: **Shifts** → **Close Shift**
- URL: `{{base_url}}/api/shifts/close`
- Body:
```json
{
  "shift_log_id": 2,
  "closing_counts": [
    { "ingredient_id": 1, "closing_quantity_actual": 480 }
  ]
}
```
- **Send** → **200 OK** → "Shift closed successfully"

> **Sasabihin:** "Expected ang system na mga 1900g ang Flavor Powder, pero actual count ay 480g lang — malaking variance. Automatic gumawa ng discrepancy alert ang system. Kung naka-configure ang SMTP, automatic ding nag-send ng email sa owner."

---

### 2.6 View Alerts

- Folder: **Alerts** → **List Alerts**
- Headers: `Bearer {{admin_token}}`
- URL: `{{base_url}}/api/alerts?branch_id=1`
- **Send** → 200 OK
- Ituro: **severity: "high"**, **variance: -1420**, **status: "pending"**

> **Sasabihin:** "Automatic na na-flag ang leakage — hindi kailangang mag-manual check. May severity level pa: high, medium, low. Kaya alam ng owner kung alin ang unahin."

---

### 2.7 Full CRUD — Quick Demo (1 min)

**Create Ingredient:**
- Folder: **Ingredients** → **Create Ingredient**
- Headers: `Bearer {{admin_token}}`
- Body:
```json
{
  "name": "Chocolate Syrup",
  "unit": "ml"
}
```
- **Send** → **201 Created**

> **Sasabihin:** "May full Create, Read, Update, Delete kami sa lahat ng 13 tables — branches, products, ingredients, recipes, staff, lahat."

---

### 2.8 Smart Features — Sabihin Lang (1 min)

> **Sasabihin:**
> - "May **reports endpoint** kami — `/api/reports/sales` at `/api/reports/inventory` — para sa analytics."
> - "May **OCR scanning** — `/api/receipts/scan` — pwedeng mag-upload ng receipt image at auto-match sa transactions."
> - "Ang shift close variance detection may **3 severity levels** — auto-computed base sa percentage ng leakage."
> - "Sa transactions, gumagamit kami ng `lockForUpdate()` para sa **race condition protection** — hindi pwedeng mag-conflict ang dalawang sale nang sabay."

---

### 2.9 Dashboard KPIs API

- URL: `{{base_url}}/api/dashboard/kpis`
- Headers: `Bearer {{admin_token}}`
- **Send** → 200 OK

> **Sasabihin:** "Ito ang data na pini-feed sa dashboard — real-time: total sales, revenue, flagged shifts, open alerts."

---

# PART 2: FRONTEND DEMO — SI JUSTINE (15–20 min)

---

## Step 1 — Login Page (1 min)

- Buksan browser → `http://127.0.0.1:8000`
- Ituro ang design:
  - Split-screen layout — topographic pattern sa kaliwa, form card sa kanan
  - NITA logo + tagline: "Inventory Intelligence for Philippine Micro-Franchises"
  - Cream + brown + terracotta color scheme

> **Sasabihin:** "Ito ang login page ng NITA. Minimalist ang design — cream background, terracotta accents. Responsive siya — maganda rin sa mobile."

**Login:**
- Email: `owner@inventory.test`
- Password: `123456789`
- Click **Sign In**

---

## Step 2 — Dashboard (3 min)

Pagka-login, dadating sa Dashboard. I-point out:

1. **Branch Status Grid** — 6 branches with color-coded dots
   > "Makikita agad ng owner kung anong status ng bawat branch — green means active, red means may problem."

2. **Key Metrics Cards**
   - Annual Revenue: ₱1.2M
   - Overall Leakage: 8.4%
   - Est. Value Saved: ₱92.0k
   > "Real-time data ito galing sa database — hindi placeholder."

3. **Secondary Metrics**
   - Total Branches: 6
   - Pending Alerts: may count
   - Low Stock Items: may count

4. **Rankings**
   - Top Earners — per branch
   - Least Leakage — per branch

5. **Calendar Sidebar** — today highlighted, open shifts listed

6. **Alert Breakdown** — High / Medium / Low counts

> **Sasabihin:** "Isang tingin lang, alam na ng owner ang buong estado ng negosyo — revenue, leakage, alerts, stocks, shifts. Hindi na kailangan pumunta sa bawat branch."

---

## Step 3 — Businesses: Recipe Tab (3 min)

- Click **Businesses** sa top nav
- Makikita ang **Recipe** tab (default landing)

I-point out:
1. **Branch Selector** sa kaliwa — 6 circular badges (BB, BC, BD, BM, BQ)
2. **Category Pills** — All, Milk Tea, Siomai, Bakery
3. **Recipe Cards** — bawat product may ingredient table:
   - Classic Milk Tea (₱65): Flavor Powder 30g/45g, Cup 1pc, Wrapper 1pc
   - May Regular at Large columns

> **Sasabihin:** "Dito makikita ang exact recipe ng bawat product — kung ilang grams o pieces ang kailangan per serving. Ito ang basehan ng auto-deduction sa POS."

4. Click **Edit** sa isang product → ipakita ang Edit Modal
   - Animated overlay with blur backdrop
   - Pwedeng i-edit ang name, category, price
   - Pwedeng add/remove ingredients
   - May Preparation Procedure textarea

> **Sasabihin:** "Pwedeng i-modify ng owner ang recipes — pag nagbago ang supplier o recipe, dito i-update. Real-time ang effect sa stock deduction."

---

## Step 4 — Businesses: Workers Tab (3 min)

- Click **Staff** tab (or **Workers**)
- Makikita ang employee listing

I-point out:
1. **Employee List** sa kaliwa — with role badges (Manager/Staff)
2. **Filter** — All branches, All roles
3. Click isang employee → Profile view:
   - Contact Info (phone, email, address, birthday)
   - Education & Emergency contacts
   - Skills badges
   - Notes
4. **Work Shift Schedule** — Mon-Fri with times
5. **Performance** section with rating

> **Sasabihin:** "Full employee management — profile, schedule, performance tracking. Ang manager, sariling branch lang ang nakikita. Ang owner, lahat."

6. I-point out ang **Clock In** button
   > "May attendance tracking din — clock in/out per worker."

---

## Step 5 — Businesses: Summary Tab (2 min)

- Click **Summary** tab

I-point out:
1. **Recent Transactions** — transaction list with product, date, staff, amount
2. **Annual Revenue** card — ₱1.24M
3. **Monthly Sales chart** — 2026
4. **Leakage Log** — Negative Variance per ingredient

> **Sasabihin:** "Real-time financial overview — makikita ang bawat benta at kung saan nawawala ang stock."

---

## Step 6 — Logistics Page (3 min)

- Click **Logistics** sa top nav

**Summary Tab:**
1. **Stock Reconciliation Table** — per item, per branch:
   - Item name, Branch, Estimated Amount, On-Site Amount
   - Leakage Indicator (NONE = green)
   - Inventory Indicator (STOCKED = green)
   - Remarks (Normal = green)

> **Sasabihin:** "Ito ang heart ng logistics — makikita kung ang estimated stock (base sa sales) ay tumutugma sa actual na nasa branch. Pag hindi tugma — may leakage."

2. I-point out ang color coding:
   - Green = Normal/Stocked
   - Amber = Low
   - Red = Out/Critical

**Flags Tab:**
- Click **Flags** → Active Discrepancy Flags

> **Sasabihin:** "Dito nakikita ang lahat ng naka-flag na discrepancy — with severity level at formula kung paano na-compute."

---

## Step 7 — Settings (1 min)

- Click ang **Gear icon** sa top nav
- Ipakita ang Settings page:
  - Profile card with avatar
  - Account details
  - Preferences (currency, language, timezone)

> **Sasabihin:** "Standard settings page — profile management at preferences."

---

## Step 8 — Design Showcase (1 min)

> **Sasabihin sa closing:**
> "Sa design, consistent ang cream-brown-terracotta palette sa buong app. May glassmorphism sa navigation bar — yung frosted glass effect. Responsive ang layout — gumagana sa desktop at tablet. Lahat ng cards may soft shadow at smooth hover transitions."

---

## Step 9 — Pagsara (1 min)

> **Sasabihin:**
> "Sa summary: ang NITA ay multi-branch inventory tracker na may 5 major features:
> 1. **JWT Authentication** with 3-tier role-based access control
> 2. **Ingredient-level stock deduction** — automatic sa bawat benta
> 3. **Shift-based stock auditing** — may discrepancy detection
> 4. **Automated email alerts** para sa leakage
> 5. **Full employee management** — profile, attendance, performance
>
> Ang web app para sa owners at managers. Ang mobile app (in progress ni Ali) para sa staff sa branches. Salamat!"

---

## KUNG MAY MAGTANONG

| Tanong | Sagot |
|--------|-------|
| Bakit JWT hindi Sanctum? | "Mas portable ang JWT para sa mobile — stateless, walang session dependency. Isang token lang, pwede na sa web at mobile." |
| Paano kung sabay-sabay ang sale? | "May `lockForUpdate()` kami sa stock deduction — pessimistic locking para walang race condition." |
| Bakit ingredient-level hindi product-level? | "Para accurate ang tracking — kung Milk Tea ang binenta, alam natin exactly kung ilang grams ng powder, ilang cups, ilang wrappers ang nawala." |
| May mobile ba? | "In progress — si Ali ang gumagawa. Same API ang gagamitin — REST endpoints na na-demo namin sa Postman." |
| Paano ang images? | "Ang plan ay Firebase Storage — libre hanggang 10GB, tapos URL lang ang i-save sa database. Mas mura kaysa AWS." |
| Ano ang SMTP? | "Mailtrap ang gamit namin for testing. Kapag may discrepancy alert, automatic nag-se-send ng email sa owner." |

---

## TROUBLESHOOTING

| Problem | Fix |
|---------|-----|
| **"ENETUNREACH"** | May JSON/number sa URL bar — i-clear at itype ulit: `{{base_url}}/api/...` |
| **401 Unauthenticated** (dapat 403) | Walang token — mag-Login muna bago mag-test |
| **422 Unprocessable** | Mali ang Body — check kung tama ang JSON fields |
| **"Credentials do not match"** | Password ay `123456789` — i-re-seed kung kailangan: `php artisan migrate:fresh --seed` |
| **Server not running** | `cd C:\Users\ADMIN\inventory-tracker` → `php artisan serve` |

---

## CREDENTIALS QUICK REFERENCE

**Admin/Owner:**
| Email | Password | Role |
|-------|----------|------|
| `admin@inventory.ph` | `123456789` | super_admin |
| `owner@inventory.test` | `123456789` | super_admin |

**Managers:**
| # | Email | Password | Branch | Role |
|---|-------|----------|--------|------|
| 1 | `manager@inventory.test` | `123456789` | QC | Manager |
| 2 | `juan.cruz@nita.com` | `123456789` | QC | Manager |
| 3 | `maria.santos.mgr@nita.com` | `123456789` | QC | Manager |
| 4 | `pedro.reyes@nita.com` | `123456789` | Makati | Manager |
| 5 | `ana.gonzales@nita.com` | `123456789` | Makati | Manager |
| 6 | `jose.mercado@nita.com` | `123456789` | BGC | Manager |
| 7 | `luisa.fernandez@nita.com` | `123456789` | BGC | Manager |
| 8 | `carlos.ramos@nita.com` | `123456789` | Cebu | Manager |
| 9 | `elena.torres@nita.com` | `123456789` | Davao | Manager |
| 10 | `miguel.villanueva@nita.com` | `123456789` | Davao | Manager |
| 11 | `sofia.lim@nita.com` | `123456789` | Clark | Manager |

**Staff (PIN login):**
| PIN | Branch ID | Name |
|-----|-----------|------|
| `123456789` | 1 | Staff Juan (QC) |
| `123456789` | 1 | Maria Santos (QC) |

---

## QUICK NOTES

- Lahat ng password: `123456789` (dati `password` / `password123` / `1234`)
- Super Admin = full access lahat ng branch
- Manager = locked sa sariling branch lang
- Staff = PIN login + branch_id, limited permissions (hindi pwede mag-create ng product)
- Transaction needs: `branch_id` + `client_uuid`
- Open Shift needs: `opening_counts` array
- Alerts needs: `?branch_id=1` query param

---

*Gawa Jul 22, 2026. Updated credentials at endpoints base sa latest code ni Eduardo.*
