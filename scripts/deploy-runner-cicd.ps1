# --- Larable Runner Deployment Script (Windows PowerShell) ------------
# This script handles automated deployments when triggered by GitHub Actions.
# It is designed to run inside the checked-out folder on the host machine.

$ErrorActionPreference = "Stop"

# Keep track of active directory
$projectRoot = Get-Location
Write-Host "Deploying Larable in: $projectRoot" -ForegroundColor Cyan

# --- Check Docker ------------------------------------------------------
Write-Host "-> Checking Docker..." -ForegroundColor Yellow
$dockerExists = Get-Command docker -ErrorAction SilentlyContinue
if (-not $dockerExists) {
    Write-Host "[!] Docker command not found on host. Make sure Docker Desktop is installed and in the system PATH." -ForegroundColor Red
    exit 1
}

docker info > $null 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "[!] Docker is installed but not running." -ForegroundColor Red
    exit 1
}
Write-Host "[+] Docker is running." -ForegroundColor Green

# --- Sync environment variables ----------------------------------------
Write-Host "-> Verifying .env configuration..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "[+] Created .env from .env.example" -ForegroundColor Green
    } else {
        Write-Host "[!] No .env or .env.example found. Unable to configure application." -ForegroundColor Red
        exit 1
    }
}

# --- Start Docker Services and Build -----------------------------------
Write-Host "-> Building and starting Docker services..." -ForegroundColor Yellow
docker compose up -d --build

Write-Host "[+] Docker containers built and started." -ForegroundColor Green

# --- Wait for PostgreSQL to be ready ----------------------------------
Write-Host "-> Waiting for database to accept connections..." -ForegroundColor Yellow
$retries = 0
$maxRetries = 30
$postgresReady = $false

do {
    $retries++
    Start-Sleep -Seconds 1
    # Run pg_isready inside the pgsql container without TTY (-T)
    docker compose exec -T pgsql pg_isready -U larable > $null 2>&1
    $postgresReady = ($LASTEXITCODE -eq 0)
} while (-not $postgresReady -and $retries -lt $maxRetries)

if (-not $postgresReady) {
    Write-Host "[!] PostgreSQL did not become ready within the timeout period." -ForegroundColor Red
    exit 1
}
Write-Host "[+] Database is ready." -ForegroundColor Green

# --- Laravel Setup & Optimization ------------------------------------
Write-Host "-> Running Laravel migrations and optimization..." -ForegroundColor Yellow

# Generate app key if missing
$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch "APP_KEY=base64:") {
    docker compose exec -T app php artisan key:generate --force
    Write-Host "[+] App key generated." -ForegroundColor Green
}

# Run migrations
docker compose exec -T app php artisan migrate --force
Write-Host "[+] Migrations ran successfully." -ForegroundColor Green

# Clear and optimize application cache
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan optimize
Write-Host "[+] Laravel caches cleared and re-built." -ForegroundColor Green

# --- Frontend Setup ---------------------------------------------------
Write-Host "-> Updating frontend dependencies..." -ForegroundColor Yellow
docker compose exec -T frontend npm install
Write-Host "[+] Frontend dependencies updated." -ForegroundColor Green

Write-Host ""
Write-Host "==============================================" -ForegroundColor Green
Write-Host "🚀 Deployment Successful!" -ForegroundColor Green
Write-Host "==============================================" -ForegroundColor Green
Write-Host ""
