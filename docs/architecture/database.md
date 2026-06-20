# Database Design

Larable uses **PostgreSQL 16** running in Docker.

## Connection Details

| Setting  | Value                          |
|----------|--------------------------------|
| Host     | `pgsql` (Docker) / `localhost` |
| Port     | `5432`                         |
| Database | `${APP_NAME}` (default: Larable) |
| Username | `larable`                      |
| Password | `larable`                      |

## Core Tables

### users
- `id` — Primary key (bigint, auto-increment)
- `name` — varchar(255)
- `email` — varchar(255), unique
- `email_verified_at` — timestamp, nullable
- `password` — varchar(255)
- `two_factor_secret` — text, nullable
- `two_factor_recovery_codes` — text, nullable
- `two_factor_confirmed_at` — timestamp, nullable
- `remember_token` — varchar(100), nullable
- `created_at` / `updated_at` — timestamps

### personal_access_tokens
- Sanctum token storage
- FK: `tokenable_id` → polymorphic

### passkeys
- WebAuthn credential storage
- FK: `user_id` → `users.id`

### sessions, cache, jobs
- Laravel framework tables

## Database Query Optimization

### N+1 Query Prevention

To guarantee optimal performance and prevent database request bloat, **lazy loading is disabled in non-production environments** (local, testing, development). 

In [AppServiceProvider](file:///c:/Users/PC/Herd/larable-laravel-staterkit/app/Providers/AppServiceProvider.php), strict loading is enabled:
```php
Model::preventLazyLoading(! $this->app->isProduction());
```
If an N+1 query is introduced during development, Eloquent will immediately throw an `Illuminate\Database\LazyLoadingViolationException`. Developers must resolve this by eagerly loading the relationship:

```php
// Bad (Will throw an exception in development)
$users = User::all();
foreach ($users as $user) {
    $passkeys = $user->passkeys;
}

// Good (Eager loaded)
$users = User::with('passkeys')->get();
foreach ($users as $user) {
    $passkeys = $user->passkeys;
}
```

### Index Optimization

To ensure query latency remains sub-millisecond as the dataset scales, index optimizations are applied according to the following guidelines:

1. **Primary & Unique Keys**: Handled automatically by PostgreSQL (`id`, `uuid`, unique columns like `users.email` and `passkeys.credential_id`).
2. **Foreign Keys**: Explicitly indexed to speed up relations and joins. For example, in the `passkeys` table, `user_id` is indexed:
   ```php
   $table->foreignIdFor(Passkeys::userModel(), 'user_id')->constrained()->cascadeOnDelete();
   $table->index('user_id');
   ```
3. **Queue and Session Indices**: High-throughput fields like `sessions.last_activity` and `jobs.queue` are indexed to ensure quick lookup times during lock releases and worker polling.
4. **Custom Queries**: When creating new migrations, always index columns that are frequently used in `WHERE`, `ORDER BY`, or `GROUP BY` statements.

## Related

- [[overview]] — System architecture
- [[authentication]] — Auth flow
- [[docker]] — Docker setup
