# Authentication Architecture

Larable uses **Laravel Fortify** for auth logic and **Sanctum** for token management.

## Auth Methods

### Email/Password Login
- `POST /api/v1/auth/login` with `email`, `password`, `remember_me`
- Returns Bearer token (24h or 30d with remember_me)

### Registration
- `POST /api/v1/auth/register` with `name`, `email`, `password`, `password_confirmation`
- Auto-creates token after registration

### Password Reset
1. `POST /api/v1/auth/forgot-password` — sends email via [[getting-started|Mailpit]]
2. User clicks link in email → frontend reset page
3. `POST /api/v1/auth/reset-password` with token

### Two-Factor Authentication (2FA)
1. `POST /api/v1/auth/two-factor/enable` — returns QR code SVG
2. User scans QR with authenticator app
3. `POST /api/v1/auth/two-factor/confirm` — verifies TOTP code
4. Login now requires 2FA code after password

### Passkeys (WebAuthn)
- `POST /api/v1/auth/passkeys/register-options` — get challenge
- `POST /api/v1/auth/passkeys/register` — store passkey
- `POST /api/v1/auth/passkeys/authenticate` — passwordless login

## Related

- [[overview]] — System architecture
- [[api-design]] — API versioning
- [[auth]] — Auth endpoint reference
