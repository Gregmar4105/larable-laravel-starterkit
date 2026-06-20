# Changelog

All notable changes to the Larable project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-06-20

### Added
- **Security Hardening**:
  - `LarableAuthMiddleware` password gate protecting `/larable` route dashboard using session storage.
  - Read-only SQL mode and keyword blocking safeguards inside `DatabaseController`.
  - API rate limits (60/min for API, 5/min for auth) and trusted proxy configuration.
- **Architecture Refactoring**:
  - Decoupled `AuthController` into focused sub-controllers: `PasswordResetController`, `TwoFactorController`, and `PasskeyController`.
  - Standardized request validation using Form Request classes (`LoginRequest`, `RegisterRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest`, `ConfirmTwoFactorRequest`, `UpdateProfileRequest`, `UpdatePasswordRequest`, `ExecuteQueryRequest`).
- **Tests**:
  - Pest test suites for auth, 2FA, password reset, user profile, API versioning, idempotency, and database GUI query operations.
- **Frontend Improvements**:
  - `ErrorBoundary` component for rendering fallbacks.
  - Global `ToastContext` provider and custom lightweight styled notifications.
- **DevOps**:
  - Optimized Dockerfile utilizing multi-stage cache layers.
  - `.dockerignore` excludes dependencies and secrets.
  - Production-ready `opcache.ini` configurations.
  - Docker Compose `app` container healthchecks using `php artisan about`.
  - GitHub Actions CI workflow (`ci.yml`) for Pint formatting validation and automated Pest/Node builds.
- **Developer Experience**:
  - `DatabaseSeeder` demo data including a default administrator (`admin@larable.test`).
  - Makefile wrapping docker-compose commands.
- **Documentation**:
  - Refactored `README.md`, added `CONTRIBUTING.md`, `CHANGELOG.md`, and updated `LARABLE_ARCHITECTURE.md`.
