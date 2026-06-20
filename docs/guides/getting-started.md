# Getting Started

## Prerequisites

- **Docker Desktop** — [Download here](https://www.docker.com/products/docker-desktop/)
- **Git** — For cloning the repository

## Quick Start

### Windows (PowerShell)
```powershell
.\scripts\setup.ps1
```

### macOS / Linux
```bash
chmod +x scripts/setup.sh
./scripts/setup.sh
```

The setup script will:
1. Check for Docker Desktop
2. Start all Docker services
3. Run Laravel migrations
4. Install frontend dependencies

## Services

| Service  | URL                     | Purpose               |
|----------|-------------------------|-----------------------|
| Laravel  | http://[folder-name].test:8000   | Backend API           |
| Larable  | http://[folder-name].test:8000/larable | Backend GUI      |
| Frontend | http://[folder-name]-frontend.test:3000   | React SPA             |
| Mailpit  | http://localhost:8025   | Email testing UI      |
| PgSQL    | localhost:5432          | PostgreSQL database   |

## Troubleshooting

### 419 Page Expired (CSRF Mismatch) in SQL Console
If you hit a `419 Page Expired` or `Unexpected token '<', "<!DOCTYPE ... is not valid JSON"` error when running raw SQL queries in the SQL Query Console:
- **Cause**: If you use a local host manager like **Laravel Herd** or **Valet** on the host machine using HTTPS, your browser stores a `Secure` cookie (`larable_session`). When accessing the Dockerized backend over HTTP (`http://larable-laravel-staterkit.test:8000`), the browser prevents the non-secure container environment from overriding or accessing the secure cookie, leading to CSRF validation failure.
- **Resolution**:
  1. We have isolated the container cookie by setting `SESSION_COOKIE=larable_docker_session` in [.env](file:///c:/Users/PC/Herd/larable-laravel-staterkit/.env).
  2. Clear cookies for the `*.test` domain in your browser or run the GUI in a private/incognito window to clear any stale secure cookie conflicts.

## Related

- [[docker]] — Docker configuration details
- [[overview]] — Architecture overview
- [[deployment]] — Production deployment
