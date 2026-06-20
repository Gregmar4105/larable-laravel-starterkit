# LARABLE ARCHITECTURE

> **Larable** — A production-ready Laravel starterkit with decoupled architecture.

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Technology Stack](#2-technology-stack)
3. [Directory Structure](#3-directory-structure)
4. [Docker Setup](#4-docker-setup)
5. [API Architecture](#5-api-architecture)
6. [Authentication Flow](#6-authentication-flow)
7. [Frontend Architecture](#7-frontend-architecture)
8. [Backend GUI](#8-backend-gui)
9. [Database Design](#9-database-design)
10. [Email Testing](#10-email-testing)
11. [Documentation System](#11-documentation-system)
12. [Debugging Guide](#12-debugging-guide)
13. [Deployment Guide](#13-deployment-guide)

---

## 1. System Overview

Larable uses a **decoupled architecture** where the frontend and backend are fully independent applications communicating exclusively through a versioned REST API.

```
┌──────────────────────────┐
│   React TypeScript SPA   │
│   larable-laravel-       │
│   staterkit-             │
│   frontend.test:3000     │
│   Vite + React Router    │
│   Axios + Lucide Icons   │
└────────────┬─────────────┘
             │ REST API (JSON)
             ▼
┌──────────────────────────┐     ┌───────────────────────┐
│   Laravel Backend        │────▶│   PostgreSQL 16       │
│   larable-laravel-       │     │   Docker Container    │
│   staterkit.test:8000    │     │   larable/larable     │
│   Fortify + Sanctum      │     └───────────────────────┘
│   Versioned API (v1)     │     ┌───────────────────────┐
│   Idempotency Support    │
└────────────┬─────────────┘     ┌───────────────────────┐
             │ SMTP              │   Mailpit             │
             └──────────────────▶│   SMTP: 1025          │
                                 │   Web UI: 8025        │
                                 └───────────────────────┘
```

### Key Design Principles

- **API-First**: All business logic is exposed via versioned REST endpoints
- **Stateless Auth**: Sanctum Bearer tokens for API, SPA cookie auth for frontend
- **Idempotent Mutations**: POST/PUT/PATCH support `Idempotency-Key` header
- **Docker-Native**: Full Docker Compose for development and production
- **Observable**: Built-in API playground, database explorer, and email tester

---

## 2. Technology Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| Backend Framework | Laravel | 13.x |
| Authentication | Fortify + Sanctum | Latest |
| Frontend Framework | React | 19.x |
| Frontend Language | TypeScript | 5.7+ |
| Build Tool | Vite | 6.x |
| HTTP Client | Axios | 1.7+ |
| Routing | React Router DOM | 7.x |
| Icons | Lucide React | Latest |
| Styling | Vanilla CSS (shadcn UI style) | N/A |
| Database | PostgreSQL | 16 (Alpine) |
| Containerization | Docker + Docker Compose | Latest |
| Email Testing | Mailpit | Latest |
| PHP | PHP | 8.3+ |
| Node.js | Node.js | 20.x |

---

## 3. Directory Structure

```
larable-laravel-staterkit/
├── app/
│   ├── Actions/Fortify/          # Fortify auth actions
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/           # Versioned API controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   └── UserController.php
│   │   │   └── Larable/          # Backend GUI controllers
│   │   │       ├── DashboardController.php
│   │   │       ├── ApiPlaygroundController.php
│   │   │       ├── DatabaseController.php
│   │   │       ├── EmailTestController.php
│   │   │       └── GraphController.php
│   │   ├── Middleware/
│   │   │   ├── IdempotencyMiddleware.php
│   │   │   └── ApiVersionMiddleware.php
│   │   └── Resources/
│   │       └── UserResource.php
│   ├── Models/
│   └── Providers/
│
├── frontend/                     # Decoupled React SPA
│   ├── src/
│   │   ├── components/           # Reusable components
│   │   │   ├── Layout.tsx
│   │   │   └── ProtectedRoute.tsx
│   │   ├── contexts/
│   │   │   └── AuthContext.tsx    # Auth state management
│   │   ├── lib/
│   │   │   └── axios.ts          # API client config
│   │   ├── pages/                # Route pages
│   │   │   ├── Home.tsx
│   │   │   ├── Login.tsx
│   │   │   ├── Register.tsx
│   │   │   ├── ForgotPassword.tsx
│   │   │   ├── ResetPassword.tsx
│   │   │   ├── Dashboard.tsx
│   │   │   └── Settings.tsx
│   │   └── styles/
│   │       └── index.css         # Design system (shadcn UI Zinc style)
│   ├── routes.tsx                # Route definitions (like api.php)
│   ├── package.json
│   ├── vite.config.ts
│   ├── tsconfig.json
│   ├── index.html
│   └── Dockerfile
│
├── routes/
│   ├── api.php                   # API route loader (versioned)
│   ├── api/
│   │   └── v1.php                # V1 API routes
│   ├── web.php                   # Web + Larable GUI routes
│   └── console.php
│
├── resources/views/larable/      # Backend GUI Blade views
│   ├── layout.blade.php
│   └── dashboard.blade.php
│
├── docs/                         # Obsidian-style documentation
│   ├── index.md                  # Root (wiki-links)
│   ├── architecture/
│   │   ├── overview.md
│   │   ├── api-design.md
│   │   ├── authentication.md
│   │   ├── database.md
│   │   └── ui-design.md          # UI design documentation
│   ├── guides/
│   │   ├── getting-started.md
│   │   ├── docker.md
│   │   └── deployment.md
│   └── api/v1/
│       ├── auth.md
│       └── users.md
│
├── debugging/                    # Debugging & API test scripts
│   ├── README.md
│   ├── api-test.http
│   ├── api-test.sh
│   ├── db-check.php
│   ├── mail-test.php
│   └── third-party/
│       ├── README.md
│       └── example-webhook.php
│
├── docker/
│   ├── nginx/default.conf
│   └── php/php.ini
│
├── scripts/
│   ├── setup.sh                  # Unix setup script
│   └── setup.ps1                 # Windows setup script
│
├── docker-compose.yml
├── Dockerfile
├── LARABLE_ARCHITECTURE.md       # This file
└── .env
```

---

## 4. Docker Setup

### Services

| Service | Image | Port(s) | Purpose |
|---------|-------|---------|---------|
| `app` | PHP 8.3 FPM | 9000 (Internal) | PHP-FPM Application Server |
| `web` | Nginx Alpine | 8000 (Mapped to 80) | Nginx Web Server (Proxy to app:9000) |
| `frontend` | Node 20 Alpine | 3000 | React Vite dev server |
| `pgsql` | PostgreSQL 16 | 5432 | PostgreSQL database |
| `mailpit` | Mailpit | 1025 (SMTP), 8025 (UI) | Email testing |
| `dnsmasq` | dnsmasq | 53 (UDP/TCP) | Wildcard DNS (.test mapping to 127.0.0.1) |

### Default Credentials

| Setting | Value |
|---------|-------|
| DB Name | `Larable` (from APP_NAME) |
| DB User | `larable` |
| DB Pass | `larable` |
| DB Port | `5432` |

### Quick Start

**Windows:**
```powershell
.\scripts\setup.ps1
```

**macOS/Linux:**
```bash
chmod +x scripts/setup.sh && ./scripts/setup.sh
```

The setup script:
1. ✅ Checks for Docker Desktop (halts with install link if missing)
2. ✅ Starts all Docker services
3. ✅ Waits for PostgreSQL to be ready
4. ✅ Generates APP_KEY
5. ✅ Runs migrations
6. ✅ Installs frontend dependencies

---

## 5. API Architecture

### Versioning

All API routes are versioned with URL prefixes:

```
/api/v1/health          → V1 health check
/api/v1/auth/login      → V1 login
/api/v1/user            → V1 user profile
```

Version files are located at `routes/api/v1.php`, `routes/api/v2.php`, etc.

The `ApiVersionMiddleware` adds an `X-API-Version` response header.

### Idempotency

The `IdempotencyMiddleware` ensures safe retries:

```
Client sends:  Idempotency-Key: abc-123
First request: Executes normally, response cached for 24h
Retry request: Returns cached response with X-Idempotency-Replay: true
```

- Applies to: POST, PUT, PATCH
- Cache key: `idempotency:{user_id}:{key}`
- TTL: 24 hours

### Endpoints

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| GET | `/api/v1/health` | No | Health check |
| GET | `/api/v1/auth/csrf-cookie` | No | CSRF cookie for SPA |
| POST | `/api/v1/auth/login` | No | Login with email/password |
| POST | `/api/v1/auth/register` | No | Create account |
| POST | `/api/v1/auth/logout` | Yes | Revoke token |
| POST | `/api/v1/auth/forgot-password` | No | Send reset email |
| POST | `/api/v1/auth/reset-password` | No | Reset with token |
| POST | `/api/v1/auth/two-factor/enable` | Yes | Enable 2FA |
| POST | `/api/v1/auth/two-factor/confirm` | Yes | Confirm 2FA |
| DELETE | `/api/v1/auth/two-factor/disable` | Yes | Disable 2FA |
| GET | `/api/v1/auth/two-factor/qr-code` | Yes | Get QR code |
| GET | `/api/v1/auth/two-factor/recovery-codes` | Yes | Get codes |
| POST | `/api/v1/auth/two-factor/recovery-codes` | Yes | Regenerate codes |
| POST | `/api/v1/auth/passkeys/register-options` | Yes | Passkey challenge |
| POST | `/api/v1/auth/passkeys/register` | Yes | Store passkey |
| POST | `/api/v1/auth/passkeys/authenticate-options` | No | Auth challenge |
| POST | `/api/v1/auth/passkeys/authenticate` | No | Verify passkey |
| GET | `/api/v1/user` | Yes | Get profile |
| PUT | `/api/v1/user/profile` | Yes | Update profile |
| PUT | `/api/v1/user/password` | Yes | Change password |

---

## 6. Authentication Flow

### Login Flow
```
Frontend                    API                         Database
   │                         │                             │
   ├─ POST /auth/login ─────▶│                             │
   │  {email, password,       │── verify credentials ─────▶│
   │   remember_me}           │◀── user found ─────────────│
   │                          │── create Sanctum token ────▶│
   │◀─ {user, token} ────────│                             │
   │                          │                             │
   ├─ Store token in          │                             │
   │  localStorage            │                             │
   │                          │                             │
   ├─ GET /user ─────────────▶│ (with Bearer token)        │
   │◀─ {user} ───────────────│                             │
```

### 2FA Flow
```
1. POST /auth/two-factor/enable → QR code SVG + recovery codes
2. User scans QR with authenticator app (Google Auth, Authy, etc.)
3. POST /auth/two-factor/confirm {code: "123456"} → confirmed
4. On next login: API returns {two_factor: true}
5. Frontend shows TOTP code input
```

### Passkey Flow
```
1. POST /auth/passkeys/register-options → WebAuthn challenge
2. Browser calls navigator.credentials.create()
3. POST /auth/passkeys/register {credential} → stored
4. Login: POST /auth/passkeys/authenticate-options → challenge
5. Browser calls navigator.credentials.get()
6. POST /auth/passkeys/authenticate {credential} → token
```

### Passkey Production Configuration

Update `config/fortify.php` for your production domain:
```php
'passkeys' => [
    'relying_party_id' => 'yourdomain.com',         // Must match domain
    'allowed_origins' => ['https://yourdomain.com'], // HTTPS required
    'timeout' => 60000,
],
```

---

## 7. Frontend Architecture

### Route Pattern

The `routes.tsx` file mirrors Laravel's `api.php` pattern — a single source of truth for all frontend routes:

```tsx
export const routes: RouteObject[] = [
  {
    path: '/',
    element: <Layout />,
    children: [
      { index: true, element: <Home /> },
      { path: 'login', element: <Login /> },
      { path: 'register', element: <Register /> },
      {
        element: <ProtectedRoute />,  // Auth guard
        children: [
          { path: 'dashboard', element: <Dashboard /> },
          { path: 'settings', element: <Settings /> },
        ],
      },
    ],
  },
];
```

### Axios Configuration

The `src/lib/axios.ts` module provides:
- Base URL with API versioning (`/api/v1`)
- Automatic Bearer token injection from localStorage
- CSRF cookie handling for Sanctum SPA auth
- 401 interceptor: auto-clears token and redirects to login

### State Management

Authentication state is managed via React Context (`AuthContext.tsx`):
- `user` — Current user object or null
- `token` — Sanctum Bearer token
- `isAuthenticated` — Derived boolean
- `login()`, `register()`, `logout()` — Auth actions
- `forgotPassword()`, `resetPassword()` — Password reset
- `refreshUser()` — Re-fetch user from API

### Styling System

The application layout, typography, components, and inputs follow the **shadcn UI (Zinc theme)** design system. This system is implemented purely using custom CSS variables (in [index.css](file:///c:/Users/PC/Herd/larable-laravel-staterkit/frontend/src/styles/index.css)) and dark/light modes. See [[ui-design]] for the design system guide.

---

## 8. Backend GUI

The Larable Backend GUI is accessible at `http://[folder-name].test:8000/larable` (e.g., `http://larable-laravel-staterkit.test:8000/larable`).

### Three-Panel Layout

```
┌──────────┬──────────────────────────────┬──────────┐
│  Sidebar │       Center Panel           │  Right   │
│          │                              │  Panel   │
│ • API    │  API Playground / DB View /  │          │
│ • DB     │  Email Composer / Graph /    │  Docs &  │
│ • Mail   │  Doc Viewer                  │  Info    │
│ • Graph  │                              │          │
└──────────┴──────────────────────────────┴──────────┘
```

### Features

#### API Playground (Sidebar: API tab)
- Lists all API endpoints from `Route::getRoutes()` auto-discovery
- Color-coded HTTP method badges (GET=green, POST=blue, PUT=amber, DELETE=red, PATCH=purple)
- Click an endpoint → center panel shows sandbox with pre-filled body fields
- JSON keys are pre-populated; only values are dynamic
- Execute requests and see response with status, timing, and syntax-highlighted JSON
- Bearer token input for authenticated endpoints
- Auto-generated Idempotency-Key for mutations

#### Database Management (Sidebar: DB tab)
- **Structure View**: Lists all tables with columns, types, and row counts
- **Table Data**: Click a table → paginated live data with FK navigation
- **ER Diagram**: Canvas-rendered relational schema showing:
  - Tables as cards with column listings
  - Foreign key edges with directional arrows
  - Hover on FK constraints to see where data connects
  - Column types and primary key indicators

#### Email Testing (Sidebar: Mail tab)
- **Compose**: Send test emails through Mailpit SMTP
- **Inbox**: Browse received emails from Mailpit API
- **Preview**: View individual email content
- **Clear**: Delete all test emails

#### Obsidian Graph (Sidebar: Graph tab)
- Force-directed D3.js-style graph visualization
- Each node = a markdown file from `docs/`
- Each edge = a `[[wiki-link]]` reference
- Node size scales by connection count
- Click a node to view the markdown content
- File list in sidebar with connection counts

---

## 9. Database Design

### PostgreSQL Configuration

| Setting | Value |
|---------|-------|
| Connection | `pgsql` |
| Host | `pgsql` (Docker) / `127.0.0.1` (local) |
| Port | `5432` |
| Database | `Larable` |
| Username | `larable` |
| Password | `larable` |

### Migrations

```
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
2026_06_19_..._create_personal_access_tokens_table.php
2026_06_19_..._add_two_factor_columns_to_users_table.php
2026_06_19_..._create_passkeys_table.php
```

### Key Relationships

```
users ─────────┬──────── personal_access_tokens
               │         (user_id → users.id)
               │
               └──────── passkeys
                         (user_id → users.id)
```

---

## 10. Email Testing

Larable uses **Mailpit** for local email testing. All emails sent by Laravel are intercepted by Mailpit.

| Feature | URL |
|---------|-----|
| Mailpit Web UI | http://localhost:8025 |
| SMTP Server | mailpit:1025 (Docker) |
| Backend GUI | http://[folder-name].test:8000/larable (Mail tab) |

### .env Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@larable.test"
```

### Testing from Backend GUI

1. Open `http://[folder-name].test:8000/larable`
2. Click the **Mail** tab in the sidebar
3. Click **+ Compose** to send a test email
4. Check the **Inbox** to see received emails
5. Click an email to view its full content

---

## 11. Documentation System

Larable includes an **Obsidian-style documentation system** using markdown files with `[[wiki-links]]`.

### Structure

All documentation lives in the `docs/` folder:

```
docs/
├── index.md                 ← Root node (LARABLE_ARCHITECTURE.md links here)
├── architecture/
│   ├── overview.md          ← [[overview]]
│   ├── api-design.md        ← [[api-design]]
│   ├── authentication.md    ← [[authentication]]
│   └── database.md          ← [[database]]
├── guides/
│   ├── getting-started.md   ← [[getting-started]]
│   ├── docker.md            ← [[docker]]
│   └── deployment.md        ← [[deployment]]
└── api/v1/
    ├── auth.md              ← [[auth]]
    └── users.md             ← [[users]]
```

### Graph Visualization

The Backend GUI (Graph tab) renders a force-directed graph:
- **Nodes** = markdown files, sized by connection count
- **Edges** = `[[wiki-link]]` references between files
- Click a node to view the document content

### Purpose

This documentation system reduces AI token usage by providing:
- Contextual, linked documentation that AI can traverse
- Modular files instead of one massive document
- Graph visualization showing knowledge structure

---

## 12. Debugging Guide

The `debugging/` folder contains scripts for testing and debugging:

| File | Purpose | Command |
|------|---------|---------|
| `api-test.http` | HTTP file for REST Client | Open in VS Code |
| `api-test.sh` | Bash curl test script | `./debugging/api-test.sh` |
| `db-check.php` | Database connection check | `php debugging/db-check.php` |
| `mail-test.php` | Send test email | `php debugging/mail-test.php` |
| `third-party/example-webhook.php` | Webhook receiver | `php debugging/third-party/example-webhook.php` |

### Using with Docker

```bash
docker compose exec app php debugging/db-check.php
docker compose exec app php debugging/mail-test.php
```

---

## 13. Deployment Guide

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set strong `DB_PASSWORD`
- [ ] Set `APP_URL` to your domain
- [ ] Update `SANCTUM_STATEFUL_DOMAINS`
- [ ] Configure `SESSION_DOMAIN` for your domain
- [ ] Set up SSL/TLS certificates
- [ ] Update Fortify passkey `relying_party_id` to your domain
- [ ] Use the `production` target in Dockerfile
- [ ] Set up proper log rotation
- [ ] Configure Redis for sessions/cache (optional)

### Production Docker Compose Override

Create `docker-compose.prod.yml`:

```yaml
services:
  app:
    build:
      target: production
    environment:
      APP_ENV: production
      APP_DEBUG: "false"
    ports:
      - "80:80"

  frontend:
    command: ["npm", "run", "preview"]

  pgsql:
    environment:
      POSTGRES_PASSWORD: <strong-password>
```

Run with:
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

---

> **Larable** — Built with ❤️ for developers who want to ship fast.
