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

## Related

- [[docker]] — Docker configuration details
- [[overview]] — Architecture overview
- [[deployment]] — Production deployment
