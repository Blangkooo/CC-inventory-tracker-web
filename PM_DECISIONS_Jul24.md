# NITA — Product/Technical Decisions (Jesse, Project Manager)
### Recorded July 24, 2026

---

## Q1 — Variance Alert Threshold

**Q:** What is the exact variance threshold that should trigger an alert?

**A:** Make the threshold configurable in the Settings module, but set the default to **±5% variance** (or a currency discrepancy of **₱100+**).

> **Note:** Bulk ingredients naturally have slightly higher wastage than high-value items, so a configurable setting gives owners better flexibility while keeping default alerts active.

---

## Q2 — Recipe Edits Mid-Shift

**Q:** If a recipe is edited while a shift is ongoing, should existing transactions continue using the old recipe, or should they follow the updated one?

**A:** Use **recipe snapshotting**:
- **Past/completed transactions** — remain immutable, tied strictly to the recipe version active at the time of purchase.
- **New transactions after the edit** — use the updated recipe immediately.

> **Note:** Never retroactively recalculate completed orders — this corrupts past audit logs and stock history.

---

## Q3 — Negative Stock Handling

**Q:** How should negative stock be handled — should the system block the transaction, allow it with a warning, or flag it?

**A:** **Allow the transaction with a warning, and flag it.**

> **Note:** Do not block sales at the POS level. Refusing a paying customer due to a system inventory mismatch causes store bottlenecks. Record the negative balance and surface a flag on the manager/owner dashboard for reconciliation.

---

## Q4 — Offline Mode Duration

**Q:** For offline mode, what is the expected duration for which the tablet should be able to operate without an internet connection?

**A:** Design local storage (e.g., **SQLite / WatermelonDB**) to hold **24–48 hours** (or ~**1,000 transactions**) of local data.

> **Note:** Queue transactions locally during network drops and run an automatic background sync once connectivity is restored.

---

## Q5 — Push Notifications (FCM) for MVP

**Q:** For the MVP, should we include FCM push notifications, or can this be deferred?

**A:** **Defer FCM push notifications for Phase 2.**

> **Note:** For the MVP, rely on in-app notification badges and dashboard alert banners for stock variance flags. This keeps the MVP scope tight and avoids unnecessary FCM token management overhead.

---

## Q6 — Dashboard Access (Owner vs. Branch Manager)

**Q:** For the dashboard, should access be limited to the Owner only, or should Branch Managers also have access?

**A:** Implement **Role-Based Access Control (RBAC) with scoped views**:
- **Owner** — full visibility across all branches, aggregate financials, total margin analytics, and global settings.
- **Branch Manager** — access scoped strictly to their assigned branch (daily branch revenue, local staff schedules, local inventory/variance logs).

---

*Answered by Jesse, Project Manager. Recorded for backend + mobile implementation reference.*
