# Deployment Guide

## Production Docker Compose

For production, modify `docker-compose.yml`:

1. Change `APP_ENV` to `production`
2. Set `APP_DEBUG` to `false`
3. Use the `production` target in the Dockerfile
4. Configure proper domain and SSL
5. Update `SANCTUM_STATEFUL_DOMAINS` with your domain
6. Set strong `DB_PASSWORD`

## Environment Variables

Critical production settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_PASSWORD=<strong-password>
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

## Passkey Configuration

Update `config/fortify.php`:
```php
'passkeys' => [
    'relying_party_id' => 'yourdomain.com',
    'allowed_origins' => ['https://yourdomain.com'],
],
```

## Related

- [[docker]] — Docker configuration
- [[getting-started]] — Development setup
- [[overview]] — Architecture overview
