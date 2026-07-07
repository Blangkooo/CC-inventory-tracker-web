# InvenTrack API — Onboarding / Auth Endpoints

Base URL: `http://127.0.0.1:8000`

---

## POST /api/auth/login

Authenticate an existing owner.

**Body:**

```json
{
  "full_name": "Juan Cruz",
  "email": "juan@example.com",
  "owner_id": "OWN-001",
  "password": "secret123"
}
```

**cURL:**

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"full_name":"Juan Cruz","email":"juan@example.com","owner_id":"OWN-001","password":"secret123"}'
```

**Response (placeholder):**

```json
{
  "status": true,
  "message": "Login successful.",
  "data": {
    "token": "mock-jwt-token-...",
    "user": {
      "full_name": "Juan Cruz",
      "email": "juan@example.com",
      "owner_id": "OWN-001"
    }
  }
}
```

---

## POST /api/auth/register/step-1

Submit personal information during registration.

**Body:**

```json
{
  "full_name": "Juan Cruz",
  "email": "juan@example.com",
  "contact_number": "+63-912-345-6789",
  "business_registration_id": "BR-98765"
}
```

**cURL:**

```bash
curl -X POST http://127.0.0.1:8000/api/auth/register/step-1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"full_name":"Juan Cruz","email":"juan@example.com","contact_number":"+63-912-345-6789","business_registration_id":"BR-98765"}'
```

**Response (placeholder):**

```json
{
  "status": true,
  "message": "Step 1 personal information is valid.",
  "data": {
    "full_name": "Juan Cruz",
    "email": "juan@example.com",
    "contact_number": "+63-912-345-6789",
    "business_registration_id": "BR-98765"
  }
}
```

---

## POST /api/auth/register/step-2

Submit multi-location business profile(s).

**Body:**

```json
{
  "businesses": [
    {
      "business_name": "Juan's Mart",
      "type_of_business": "retail",
      "business_registration": "BR-001",
      "business_permit": "PER-001",
      "location": "Manila"
    }
  ]
}
```

**cURL:**

```bash
curl -X POST http://127.0.0.1:8000/api/auth/register/step-2 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"businesses":[{"business_name":"Juan Mart","type_of_business":"retail","business_registration":"BR-001","business_permit":"PER-001","location":"Manila"}]}'
```

**Response (placeholder):**

```json
{
  "status": true,
  "message": "Multi-location business payload received.",
  "data": {
    "businesses": [
      {
        "business_name": "Juan's Mart",
        "type_of_business": "retail",
        "business_registration": "BR-001",
        "business_permit": "PER-001",
        "location": "Manila"
      }
    ]
  }
}
```

---

## POST /api/auth/register/confirm

Final confirmation and onboarding document tracker initialization.

**Body:**

```json
{
  "owner_id": "OWN-001",
  "permit_validity": true,
  "terms_accepted": true,
  "legal_papers_submitted": true,
  "legal_papers_secondary_submitted": true
}
```

**cURL:**

```bash
curl -X POST http://127.0.0.1:8000/api/auth/register/confirm \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"owner_id":"OWN-001","permit_validity":true,"terms_accepted":true,"legal_papers_submitted":true,"legal_papers_secondary_submitted":true}'
```

**Response (placeholder):**

```json
{
  "status": true,
  "message": "Onboarding document trackers initialized successfully.",
  "data": {
    "trackers": {
      "owner_id": "verified",
      "permit_validity": "valid",
      "terms_of_service": "accepted",
      "legal_papers": "submitted",
      "legal_papers_secondary": "submitted"
    }
  }
}
```
