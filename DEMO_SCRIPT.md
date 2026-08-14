# NITA — Demo Script (Simple Version)

## ⏳ PENDING: Web Dashboard Fix

Yung Owner Dashboard home page ("Main Branch", "$50,000" atbp.) ay **placeholder pa lang** — hindi pa totoong data mula sa database. Alam na natin ito, desisyon ng user na "ok lang muna" (2026-07-10).

**Kung sakaling gusto na ayusin bago ang demo, sabihin lang: "sige simulan mo na" — ito ang gagawin:**
1. Kunin ang totoong data mula sa `DashboardController.php` (existing na ang logic doon, tama na)
2. Palitan ang mga hardcoded na branch names/numbers sa `dashboard.blade.php` ng totoong Blade variables
3. Panatilihin ang parehong itsura/layout — laman lang ang mababago, hindi disenyo
4. I-verify sa owner AT manager account (manager dapat 1 branch lang makita)

*(Note: yung "Businesses" tab naman — hindi ito apektado, totoong data na yun.)*

---

## Bago ang Demo (gawin mo ito ilang minuto bago magsimula)

1. Buksan ang terminal, patakbuhin: `php artisan serve`
2. Buksan ang Postman
3. Buksan ang browser, i-type: `127.0.0.1:8000/login`
4. Buksan din ang isa pang tab: `mailtrap.io` (mag-login na kaagad)

**3 bagay lang palaging tatandaan sa Postman, kapag na-guluhan ka:**
- **URL bar** (yung mahabang kahon sa taas, katabi ng "Send") = laging nagsisimula sa `{{base_url}}/...` — HUWAG kailanman ilagay dito ang JSON o password, address lang.
- **Headers tab** = dito naka-lagay yung "Bearer token" (parang ID mo)
- **Body tab** = dito naka-lagay yung email/password/data na ipapadala mo

---

## Bahagi 1: Intro (sabihin lang, walang gagawin sa screen)

> "NITA — Multi-Branch Inventory Tracker. Anti-theft na inventory at POS system para sa food-cart micro-franchises. Ang problema: hirap ma-track ng branch owners kung saan nawawala ang stock nila. Ang solusyon namin: awtomatikong sinusubaybayan ang bawat ingredient, hindi lang finished product, at may automatic alert kapag may hindi tugma."

---

## Bahagi 2: JWT Login

1. Sa **Postman sidebar**, i-click ang folder **`Auth`**
2. I-click ang request **`Admin Login (super_admin / manager)`**
3. I-click ang **Send** button (bughaw, kanang-taas)
4. Sa response sa ibaba, ituro ang salitang **`token`** — sabihin: *"Ito yung JWT token — makikita niyo may 3 parts siya na naka-separate ng tuldok, hindi lang random na text."*

✅ **Tapos na ang JWT part.** Wala nang ibang gagawin — naka-save na awtomatiko ang token para sa susunod na steps.

---

## Bahagi 3: RBAC — Bawal sa Staff (403 test)

1. Sa sidebar, buksan ang folder **`Auth`** (kung sarado) → i-click **`Staff Login (pin + branch)`**
2. I-click **Send**
3. Sa sidebar, buksan ang folder **`Products`** → i-click **`Create Product`**
4. I-click ang tab na **"Headers"** (nasa taas, katabi ng "Params")
5. Hanapin ang row na may salitang `Authorization` — makikita mo sa Value column: `Bearer {{admin_token}}`
6. **I-click ang text na iyon** para ma-edit, i-delete ang `admin_token`, i-type na `staff_token` sa lugar niya
   - Dapat maging ganito: `Bearer {{staff_token}}`
7. I-click **Send**
8. Dapat lumabas: **403 Forbidden**

> Sabihin: *"Kita niyo, kahit valid na staff account ito, hindi sila pwedeng gumawa ng bagong product — naka-block agad."*

**⚠️ Bago ka tumuloy**, ibalik mo muna ang Header pabalik: i-click ulit yung value, palitan pabalik ng `Bearer {{admin_token}}`.

---

## Bahagi 4: RBAC — Manager Branch-Lock (403/200 test)

1. Buksan **`Auth`** → i-click **`Admin Login (super_admin / manager)`**
2. I-click ang tab **"Body"** (nasa taas)
3. I-clear ang laman, i-type:
   ```json
   { "email": "manager@inventory.test", "password": "password123" }
   ```
4. I-click **Send**
5. Buksan ang folder **`Branches`** (⚠️ hindi "Branch Stock" — magkaiba, magkatabi lang sila sa listahan)
6. I-click **`Show Branch (manager: own, super_admin: any)`**
7. I-click **Send** (huwag muna galawin ang URL) → dapat **200 OK**, makikita "Branch QC"
8. **I-click ang URL bar mismo** (yung kahon sa taas na may nakasulat na `{{base_url}}/branches/1`)
9. I-select ALL ng laman ng URL bar (pindutin ang **Ctrl+A** habang naka-cursor doon), i-delete
10. I-type mula umpisa, dahan-dahan:
    ```
    {{base_url}}/branches/2
    ```
11. I-click **Send** → dapat **403 Forbidden**

> Sabihin: *"Kita niyo, valid na branch ID ito, pero hindi kanila si Branch 2 — naka-block pa rin sila."*

**⚠️ Bago ka tumuloy**, i-restore ang owner access:
1. Buksan **`Admin Login`** → Body tab → palitan pabalik:
   ```json
   { "email": "owner@inventory.test", "password": "password123" }
   ```
2. Send

---

## Bahagi 5: Web Dashboard (Browser)

1. Pumunta sa `127.0.0.1:8000/login`
2. Mag-login: `owner@inventory.test` / `password123`
3. Sabihin: *"Ito ang owner view — nakikita lahat ng branches."*
4. I-click ang **"Businesses"** sa navbar sa taas → i-click ang isang branch → ipakita ang tabs (Analytics/Recipe/Workers/Logistics)
5. I-click **"Logout"** (kanang-taas)
6. Mag-login ulit: `manager@inventory.test` / `password123`
7. Sabihin: *"Manager naman ito — Branch QC lang laging lumalabas, kahit saan sila magpunta sa site."*

*(Note: yung numbers sa Owner Dashboard home page — "Main Branch", "₱300k" atbp. — placeholder pa lang muna, sinabihan na natin ito. Yung "Businesses" section lang ang may totoong data.)*

---

## Bahagi 6: Checkout → Stock Deduction (ang pangunahing feature)

1. Sa Postman, buksan ang folder **`Auth`** → **`Staff Login (pin + branch)`** → Send (para may staff_token ulit)
2. Buksan ang folder **`Transactions (POS)`** → i-click **`Create Transaction (any role)`**
3. I-click **Send** (naka-preset na ang body — Classic Milk Tea, quantity 1)
4. Sa response, i-scroll pababa hanggang makita ang **`updated_stock`**

> Sabihin: *"Dito ang core ng system namin. Hindi lang 'nabawasan ang Milk Tea by 1' — binabreak down namin ang benta papunta sa bawat ingredient: flavor powder, cup, wrapper — lahat automatic na-babawasan sa tamang dami. Kaya kung may nawawalang ingredient na hindi galing sa totoong benta, lalabas agad ito bilang discrepancy."*

---

## Bahagi 7: SMTP Email Alert

1. Sa Postman, buksan **`Shifts`** folder → **`Open Shift (staff, manager)`** → **Send**
2. Sa response, hanapin ang `shift_log.id` (halimbawa `1`) — tandaan ito
3. I-click **`Close Shift (staff, manager)`**
4. I-click ang tab **"Body"**
5. Hanapin ang `"shift_log_id": {{shift_log_id}}` sa loob ng JSON, palitan ang `{{shift_log_id}}` ng number na nakuha sa step 2 (hal. `1`)
6. I-click **Send** → dapat **200 OK**, "Shift closed successfully"
7. Pumunta sa **Mailtrap tab** sa browser
8. I-click ang **"Sandbox"** (kung wala ka pa doon) → i-click ang inbox
9. I-click ang pinaka-bagong email — "Discrepancy Alert"

> Sabihin: *"Automatic na nag-eemail ang system papunta sa manager at super_admin tuwing may na-detect na discrepancy — hindi kailangang i-trigger ito manually."*

---

## Bahagi 8: Full CRUD (mabilis lang)

1. Sa Postman sidebar, ituro-turo lang ang mga folders: `Branches`, `Products`, `Ingredients`, `Recipes`, `Staff Management`
2. Buksan ang **`Ingredients`** folder → i-click **`Create Ingredient`** → **Send** → ituro ang **201 Created**

> Sabihin: *"May folder kami sa Postman para sa bawat isa sa 12 tables ng database namin — lahat may full Create, Read, Update, Delete."*

---

## Bahagi 9: Bagong Feature — Recipe Sizes

1. Sa browser, naka-login ka pa bilang manager o owner
2. Pumunta sa **"Businesses"** → i-click ang isang branch → i-click ang tab **"Recipe"**
3. Ituro ang Regular/Large columns at ang procedure text

> Sabihin: *"Bago namin idinagdag base sa updated Figma — Regular at Large sizes, magkaibang dami ng ingredients, at may step-by-step procedure para sa mga bagong staff."*

---

## Bahagi 10: Pagsara

> "Sa summary: JWT authentication, 3-tier role-based access control, full CRUD sa lahat ng 12 tables, automatic email alerts, at ang core feature namin — ingredient-level na anti-theft tracking. Web dashboard ito para sa branch owners at managers; hiwalay na tablet app ang gagamitin ng staff sa counter mismo. Salamat!"

---

## Kung May Magtanong

| Tanong | Sagot |
|---|---|
| Bakit JWT hindi Sanctum? | Kailangan ng stateless token na pwedeng gamitin kahit sa future mobile/tablet app, specific requirement din ito ng propesor. |
| Race condition kapag sabay-sabay mag-checkout? | May row-level locking sa database bago mag-deduct ng stock, kaya safe kahit magkasabay. |
| May mobile app ba? | Wala pa, hiwalay na bahagi ng grupo ang gagawa nito. |
| Paano na-verify ang deduction? | May audit trail — bawat pagbabago sa stock ay naka-log na may timestamp at dahilan. |

---

## Kung May Mali (troubleshooting)

- **"Cannot send request" / "ENETUNREACH" error** → mali yung URL bar, may naipasok kang JSON doon. I-clear at i-type ulit: `{{base_url}}/kung-anong-path`
- **"Port should be..." error** → parehong dahilan — JSON napunta sa URL bar imbes na sa Body tab
- **419 error sa login** → normal lang minsan, i-refresh at ulitin
- **"These credentials do not match"** → baka na-reset ang database, sabihin sa akin, ire-restore ko agad
