# Inventory Tracker API Documentation

Base URL: `http://localhost:8000/api`

All protected routes require the header:
```
Authorization: Bearer <token>
```

---

## POST /api/login

**Auth required:** No

Validates a user's PIN against their stored hash for a given branch and returns a Sanctum token.

**Request**
```json
{
  "branch_id": 1,
  "pin": "1234"
}
```

**Response 200**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Juan",
    "role": "cashier"
  }
}
```

**Response 401**
```json
{ "message": "Invalid PIN." }
```

---

## GET /api/products

**Auth required:** Yes

Returns all products.

**Response 200**
```json
[
  { "id": 1, "name": "Milk Tea", "price": "45.00", "created_at": "...", "updated_at": "..." }
]
```

---

## GET /api/recipes

**Auth required:** Yes

Returns all recipes. Filter by product with `?product_id=1`.

**Query params**
| Param | Type | Required |
|-------|------|----------|
| product_id | integer | No |

**Response 200**
```json
[
  {
    "id": 1,
    "product_id": 1,
    "ingredient_name": "Tea Powder",
    "quantity": "0.0500",
    "unit": "kg"
  }
]
```

---

## POST /api/transactions

**Auth required:** Yes

Records a sale. Deducts ingredients from `stock_levels` based on the product's recipe. Idempotent — if the same `uuid` is submitted twice, the existing record is returned without re-processing.

**Request**
```json
{
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "branch_id": 1,
  "user_id": 2,
  "product_id": 1,
  "quantity": 3
}
```

**Response 201** (new record)
```json
{
  "id": 10,
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "branch_id": 1,
  "user_id": 2,
  "product_id": 1,
  "quantity": 3,
  "synced": true,
  "created_at": "...",
  "updated_at": "..."
}
```

**Response 200** (duplicate uuid — no re-processing)
```json
{
  "id": 10,
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  ...
}
```

**Side effect:** For each recipe ingredient tied to `product_id`, subtracts `recipe.quantity × quantity` from `stock_levels` where `branch_id` and `ingredient_name` match.

---

## POST /api/shift-logs

**Auth required:** Yes

Records a shift. Calculates variance between actual closing stock and expected closing stock derived from transactions in the shift window. Flags the record if `|variance| > 5` (threshold is configurable in `ShiftLogController::VARIANCE_THRESHOLD`).

**Request**
```json
{
  "branch_id": 1,
  "user_id": 2,
  "opening_stock": 100.0,
  "closing_stock": 72.5,
  "time_in": "2026-06-28 08:00:00",
  "time_out": "2026-06-28 16:00:00"
}
```

**Response 201**
```json
{
  "id": 3,
  "branch_id": 1,
  "user_id": 2,
  "opening_stock": "100.0000",
  "closing_stock": "72.5000",
  "time_in": "2026-06-28T08:00:00.000000Z",
  "time_out": "2026-06-28T16:00:00.000000Z",
  "variance": "-2.5000",
  "flagged": false,
  "created_at": "...",
  "updated_at": "..."
}
```

**Variance logic:**
```
expected_closing = opening_stock - sum(recipe.quantity × transaction.quantity)
                   for all transactions in [time_in, time_out] for this branch
variance         = closing_stock - expected_closing
flagged          = |variance| > 5
```

---

## Error format

All validation errors return `422`:
```json
{
  "message": "The branch_id field is required.",
  "errors": {
    "branch_id": ["The branch_id field is required."]
  }
}
```

Unauthenticated requests to protected routes return `401`:
```json
{ "message": "Unauthenticated." }
```
