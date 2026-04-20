# Lodging Management System ERD

```mermaid
erDiagram
    USERS {
        INT id PK
        VARCHAR full_name
        VARCHAR email UK
        TEXT password_hash
        DATETIME created_at
        DATETIME updated_at
    }

    ROOMS {
        INT id PK
        VARCHAR room_number UK
        VARCHAR type
        INT capacity
        DECIMAL price_per_night
        VARCHAR status
        TEXT description
        DATETIME created_at
        DATETIME updated_at
    }

    TENANTS {
        INT id PK
        VARCHAR full_name
        VARCHAR email
        VARCHAR phone
        VARCHAR id_type
        VARCHAR id_number
        VARCHAR address
        VARCHAR emergency_contact_name
        VARCHAR emergency_contact_phone
        DATETIME created_at
        DATETIME updated_at
    }

    BOOKINGS {
        INT id PK
        INT room_id FK
        INT tenant_id FK
        DATE check_in
        DATE check_out
        DECIMAL total_amount
        VARCHAR status
        TEXT notes
        DATETIME created_at
        DATETIME updated_at
    }

    ROOMS ||--o{ BOOKINGS : assigned_to
    TENANTS ||--o{ BOOKINGS : makes
```

## Relationship Notes
- One room can have many bookings over time.
- One tenant can also have many bookings over time.
- Each booking belongs to exactly one room and one tenant.
- Users are kept separate from tenant records because system users are staff accounts, while tenants are lodging guests.
