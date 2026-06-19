# Auth API Endpoints

All auth endpoints are prefixed with `/api/v1/auth/`.

## Public Endpoints

### GET /csrf-cookie
Initialize CSRF protection for SPA authentication.

### POST /login
```json
{
  "email": "user@example.com",
  "password": "password",
  "remember_me": false
}
```

### POST /register
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### POST /forgot-password
```json
{
  "email": "user@example.com"
}
```

### POST /reset-password
```json
{
  "token": "reset-token",
  "email": "user@example.com",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

## Authenticated Endpoints (requires Bearer token)

### POST /logout
Revokes the current access token.

### POST /two-factor/enable
Returns QR code SVG and recovery codes.

### POST /two-factor/confirm
```json
{
  "code": "123456"
}
```

### DELETE /two-factor/disable
Disables 2FA for the account.

## Related

- [[authentication]] — Auth architecture
- [[api-design]] — API versioning
- [[users]] — User endpoints
