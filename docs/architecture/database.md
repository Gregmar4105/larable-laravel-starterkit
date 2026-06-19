# Database Design

Larable uses **PostgreSQL 16** running in Docker.

## Connection Details

| Setting  | Value                          |
|----------|--------------------------------|
| Host     | `pgsql` (Docker) / `localhost` |
| Port     | `5432`                         |
| Database | `${APP_NAME}` (default: Larable) |
| Username | `larable`                      |
| Password | `larable`                      |

## Core Tables

### users
- `id` — Primary key (bigint, auto-increment)
- `name` — varchar(255)
- `email` — varchar(255), unique
- `email_verified_at` — timestamp, nullable
- `password` — varchar(255)
- `two_factor_secret` — text, nullable
- `two_factor_recovery_codes` — text, nullable
- `two_factor_confirmed_at` — timestamp, nullable
- `remember_token` — varchar(100), nullable
- `created_at` / `updated_at` — timestamps

### personal_access_tokens
- Sanctum token storage
- FK: `tokenable_id` → polymorphic

### passkeys
- WebAuthn credential storage
- FK: `user_id` → `users.id`

### sessions, cache, jobs
- Laravel framework tables

## Related

- [[overview]] — System architecture
- [[authentication]] — Auth flow
- [[docker]] — Docker setup
