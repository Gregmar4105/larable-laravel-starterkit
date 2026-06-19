# User API Endpoints

All user endpoints require authentication (Bearer token) and are prefixed with `/api/v1/`.

## GET /user
Returns the authenticated user's profile.

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2024-01-01T00:00:00.000000Z",
    "two_factor_enabled": false,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

## PUT /user/profile
Update the user's name and email.

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com"
}
```

## PUT /user/password
Change the user's password.

```json
{
  "current_password": "oldpassword",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

## Related

- [[auth]] — Auth endpoints
- [[authentication]] — Auth architecture
- [[api-design]] — API design patterns
