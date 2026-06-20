# System Architecture Overview

Larable uses a **decoupled architecture** separating the backend API from the frontend SPA.

## Architecture Diagram

```
┌─────────────────────┐     ┌──────────────────────┐     ┌──────────────────────┐
│   React Frontend    │────▶│   Laravel API (v1)   │◀───▶│   Redis (Cache/Sess) │
│   [folder-name]-    │     │   [folder-name].     │     │                      │
│   frontend.test     │     │   test:8000          │     │   Docker Container   │
│   :3000             │     │   Sanctum/Fortify    │     └──────────────────────┘
│   TypeScript/Vite   │     └──────────┬───────────┘
└─────────────────────┘                │
                            ┌──────────▼───────────┐
                            │   PostgreSQL 16      │
                            │   Docker Container   │
                            └──────────────────────┘
```

## Key Principles

1. **API-First** — All business logic exposed via versioned REST API
2. **Stateless Auth** — Sanctum tokens for API, SPA cookies for frontend
3. **Idempotent Mutations** — POST/PUT/PATCH support Idempotency-Key header
4. **Docker-Native** — Full Docker Compose for dev and production
5. **High-Performance Caching** — Redis manages developer sessions, system caches, and queue pipelines

## Related

- [[api-design]] — API versioning details
- [[authentication]] — Auth architecture
- [[docker]] — Docker setup
- [[database]] — Database design
