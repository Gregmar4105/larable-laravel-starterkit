# Docker Configuration

Larable's Docker setup is defined in `docker-compose.yml` at the project root.

## Services

### app (Laravel)
- PHP 8.3 CLI with `artisan serve`
- Mounts the entire project directory
- Depends on PostgreSQL, Mailpit, and Redis

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

### redis (Cache, Session, & Queue)
- **Image**: `redis:alpine` running on port `6379`
- **Purpose**: Centralized storage for user sessions, caching, and queue queues, removing high-write/read loads from the local filesystem or DB.
- **Client**: Configured to use the PHP-native `predis` client package, avoiding compilation of native C extensions.
- **Database Indexing**:
  - **DB 0**: User Sessions (`SESSION_DRIVER=redis`)
  - **DB 1**: Cache storage (`CACHE_STORE=redis`)
  - **Queues**: Job queues (`QUEUE_CONNECTION=redis`)
- **Key Prefixing**: Prefixes keys with `larable-database-` (for DB 0/Redis level) and `larable-cache-` (for cache stores) to prevent overlaps in multi-tenant or shared setups.

## Network

All services share the `larable` bridge network for inter-container communication.

## Volumes

- `pgsql_data` — Persistent PostgreSQL data

## Related

- [[getting-started]] — Setup instructions
- [[deployment]] — Production config
- [[database]] — Database design
