# API Design

Larable's API follows a versioned, idempotent design pattern.

## Versioning

Routes are organized by version under `routes/api/`:

- `routes/api/v1.php` — Current stable version
- `routes/api/v2.php` — Future versions (add when needed)

All versioned routes are prefixed: `/api/v1/`, `/api/v2/`, etc.

The `ApiVersionMiddleware` sets `X-API-Version` response header.

## Idempotency

The `IdempotencyMiddleware` ensures safe retries for mutation requests:

1. Client sends `Idempotency-Key: <unique-key>` header
2. First request executes normally, response is cached for 24h
3. Subsequent requests with same key return cached response
4. Response includes `X-Idempotency-Replay: true` header

Only applies to: POST, PUT, PATCH methods.

## Related

- [[overview]] — System architecture
- [[auth]] — Auth endpoints
- [[users]] — User endpoints
