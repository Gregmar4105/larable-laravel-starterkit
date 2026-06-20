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

## Automated CI/CD (GitHub Actions Self-Hosted Runner)

Larable includes a built-in CI/CD pipeline using a **GitHub Self-Hosted Runner**. This allows you to securely deploy updates to your self-hosted server on every git push without opening any inbound ports or firewalls.

### How it Works
1. A **GitHub Actions Workflow** [deploy.yml](file:///c:/Users/PC/Herd/larable-laravel-staterkit/.github/workflows/deploy.yml) is triggered when code is pushed to the `main` or `master` branches.
2. The workflow directs your `self-hosted` runner on the host machine to execute a local deployment script corresponding to your operating system.
3. The runner checks out the code, rebuilds containers, runs database migrations, clears/builds Laravel cache, and updates frontend NPM dependencies.

### Deployment Scripts
- **Windows Host**: [deploy-runner-cicd.ps1](file:///c:/Users/PC/Herd/larable-laravel-staterkit/scripts/deploy-runner-cicd.ps1)
- **Linux/macOS Host**: [deploy-runner-cicd.sh](file:///c:/Users/PC/Herd/larable-laravel-staterkit/scripts/deploy-runner-cicd.sh)

### Setting Up the Runner
1. Navigate to your GitHub Repository -> **Settings** -> **Actions** -> **Runners**.
2. Click **New self-hosted runner** and select your OS.
3. Follow the instructions to download and register the runner on your host machine. Keep the default `self-hosted` runner labels.
4. Run the listener process:
   - On Windows: `.\run.cmd`
   - On Linux/macOS: `./run.sh`

## Related

- [[docker]] — Docker configuration
- [[getting-started]] — Development setup
- [[overview]] — Architecture overview
