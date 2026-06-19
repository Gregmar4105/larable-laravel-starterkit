# Docker Configuration

Larable's Docker setup is defined in `docker-compose.yml` at the project root.

## Services

### app (Laravel)
- PHP 8.3 CLI with `artisan serve`
- Mounts the entire project directory
- Depends on PostgreSQL and Mailpit

### frontend (React)
- Node.js 20 Alpine
- Vite dev server on port 3000
- Hot module replacement enabled

### pgsql (PostgreSQL)
- PostgreSQL 16 Alpine
- Data persisted in `pgsql_data` volume
- Health check with `pg_isready`

### mailpit (Email Testing)
- SMTP on port 1025
- Web UI on port 8025
- Catches all outgoing emails

## Network

All services share the `larable` bridge network for inter-container communication.

## Volumes

- `pgsql_data` — Persistent PostgreSQL data

## Related

- [[getting-started]] — Setup instructions
- [[deployment]] — Production config
- [[database]] — Database design
