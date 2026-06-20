# Larable Starter Kit

Larable is a state-of-the-art, fully decoupled full-stack starter kit combining the power of **Laravel 13** and **React 19** with **Vite**. It is pre-configured with production-ready DevOps patterns, developer workflows, and security safeguards.

## 🚀 Tech Stack

- **Backend**: Laravel 13 (decoupled API design)
- **Frontend**: React 19, Vite, TypeScript, Vanilla CSS
- **Database**: PostgreSQL 16
- **Caching & Queues**: Redis (Alpine)
- **Email Testing**: Mailpit (local SMTP trap)
- **Local DNS**: Dnsmasq wildcard DNS
- **Local Dev**: Docker Compose, Makefile

---

## ✨ Features

- **Decoupled Auth System**: SPA-friendly cookie-based and Token-based authentication using Laravel Sanctum and Laravel Fortify.
- **Two-Factor Authentication (2FA)**: Fully implemented 2FA with QR codes and backup recovery codes.
- **Secured Database GUI**: In-browser database introspection under `/larable` with read-only query execution mode, dangerous statement blocking, and PostgreSQL statement timeouts.
- **Global Error Handling**: React `ErrorBoundary` and a custom toast notification context/provider.
- **API Versioning & Idempotency**: `X-API-Version` middleware and automatic API replay detection.
- **CI/CD Pipeline**: GitHub Actions for linting (Pint) and automated Pest/TypeScript checks.

---

## 🛠️ Quick Start

Ensure you have **Docker** and **Docker Compose** installed.

### 1. Initialize the project
Using the included `Makefile`, run:
```bash
make setup
```
This will:
- Spin up the containers (PHP-FPM, Nginx, PostgreSQL, Redis, Mailpit)
- Install Composer dependencies
- Generate application keys
- Run migrations and seed default data
- Install frontend packages and build assets

### 2. Access the Application
- **Frontend**: [http://localhost:3000](http://localhost:3000)
- **Backend API**: [http://localhost:8000](http://localhost:8000)
- **Database Management GUI**: [http://localhost:8000/larable](http://localhost:8000/larable) (Password protected)
- **Mailpit Inbox**: [http://localhost:8025](http://localhost:8025)

---

## 🔒 Credentials & Environment

### Default Demo User
- **Email**: `admin@larable.test`
- **Password**: `password`

### Larable GUI Protection
- **Dashboard Password**: Set `LARABLE_PASSWORD` in your `.env`. The default is `larable`.
- **SQL Read-Only Mode**: Controlled by `LARABLE_SQL_READONLY` (defaults to `true`).

---

## 📂 Project Architecture

For detailed information on design patterns, route versioning, and architecture details, refer to:
- [LARABLE_ARCHITECTURE.md](LARABLE_ARCHITECTURE.md)
- [CONTRIBUTING.md](CONTRIBUTING.md)
- [CHANGELOG.md](CHANGELOG.md)

---

## 🛠️ Makefile Commands Reference

- `make up` - Start containers in background
- `make down` - Stop and clean up containers
- `make restart` - Restart containers
- `make test` - Run backend test suite (Pest)
- `make fresh` - Refresh database schema and re-seed
- `make lint` - Check code style with Pint
- `make shell` - Open bash inside PHP container
- `make logs` - Watch container logs
