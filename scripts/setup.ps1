# --- Larable Setup Script (Windows PowerShell) ------------------------
# This script initializes the entire Larable development environment.
# Prerequisites: Docker Desktop must be installed and running.

$ErrorActionPreference = "Stop"

# Automatically resolve and change to the project root directory
if ($PSScriptRoot) {
    Set-Location (Split-Path -Parent $PSScriptRoot)
}

Write-Host ""
Write-Host "  +------------------------------------------+" -ForegroundColor Cyan
Write-Host "  |              LARABLE SETUP               |" -ForegroundColor Cyan
Write-Host "  |    Laravel Starterkit * Decoupled Arch   |" -ForegroundColor Cyan
Write-Host "  +------------------------------------------+" -ForegroundColor Cyan
Write-Host ""

# --- Check Docker ------------------------------------------------------
Write-Host "-> Checking Docker Desktop..." -ForegroundColor Yellow

$dockerExists = Get-Command docker -ErrorAction SilentlyContinue
if (-not $dockerExists) {
    Write-Host "[!] Docker is not installed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install Docker Desktop first:" -ForegroundColor White
    Write-Host "  -> https://www.docker.com/products/docker-desktop/" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "After installing Docker Desktop:"
    Write-Host "  1. Launch Docker Desktop"
    Write-Host "  2. Wait for it to fully start (whale icon is steady)"
    Write-Host "  3. Run this script again"
    exit 1
}

$oldErrorPreference = $ErrorActionPreference
$ErrorActionPreference = "SilentlyContinue"
docker info > $null 2>&1
$dockerRunning = ($LASTEXITCODE -eq 0)
$ErrorActionPreference = $oldErrorPreference

if (-not $dockerRunning) {
    Write-Host "[!] Docker is installed but not running!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please start Docker Desktop and try again."
    exit 1
}

Write-Host "[+] Docker Desktop is running" -ForegroundColor Green

# --- Copy .env if needed ----------------------------------------------
Write-Host "-> Checking environment file..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "[+] Created .env from .env.example" -ForegroundColor Green
} else {
    Write-Host "[+] .env already exists" -ForegroundColor Green
}

# --- Update dynamic domains in .env based on current folder name ------
$folderName = Split-Path -Leaf (Get-Item .).FullName
$envContent = Get-Content ".env" -Raw
if ($envContent -match "larable-laravel-staterkit") {
    $envContent = $envContent -replace "larable-laravel-staterkit", $folderName
    Set-Content -Path ".env" -Value $envContent -NoNewline
    Write-Host "[+] Updated .env domains to use '$folderName'" -ForegroundColor Green
}

# --- Configure Local Domains in Hosts File ----------------------------
$backendDomain = "$folderName.test"
$frontendDomain = "$folderName-frontend.test"
$targetFilePath = "$env:windir\System32\drivers\etc\hosts"

$backendEntry = "127.0.0.1 $backendDomain"
$frontendEntry = "127.0.0.1 $frontendDomain"

$hasBackend = Select-String -Path $targetFilePath -Pattern $backendDomain -SimpleMatch
$hasFrontend = Select-String -Path $targetFilePath -Pattern $frontendDomain -SimpleMatch

if (-not $hasBackend -or -not $hasFrontend) {
    Write-Host "-> Adding local domains to hosts file (requires Administrator privileges)..." -ForegroundColor Yellow
    $isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    
    $entriesToAdd = @()
    if (-not $hasBackend) { $entriesToAdd += $backendEntry }
    if (-not $hasFrontend) { $entriesToAdd += $frontendEntry }
    
    if ($isAdmin) {
        Add-Content -Path $targetFilePath -Value "# Larable Local Domains"
        foreach ($entry in $entriesToAdd) {
            Add-Content -Path $targetFilePath -Value $entry
        }
        Write-Host "[+] Local domains added to hosts file" -ForegroundColor Green
    } else {
        Write-Host "[!] Elevating privileges to write to hosts file..." -ForegroundColor Yellow
        # Create a temporary script to execute with elevated privileges (avoids command line quote escaping issues)
        $tempScript = Join-Path (Get-Location) "temp_hosts_setup.ps1"
        $scriptContent = @()
        $scriptContent += "Add-Content -Path '$targetFilePath' -Value '# Larable Local Domains'"
        foreach ($entry in $entriesToAdd) {
            $scriptContent += "Add-Content -Path '$targetFilePath' -Value '$entry'"
        }
        $scriptContent | Set-Content -Path $tempScript -Encoding UTF8
        Start-Process powershell -Verb RunAs -ArgumentList "-NoProfile", "-ExecutionPolicy", "Bypass", "-File", $tempScript -Wait
        Remove-Item $tempScript -Force
        Write-Host "[+] Local domains configured in hosts file" -ForegroundColor Green
    }
} else {
    Write-Host "[+] Local domains already configured in hosts file" -ForegroundColor Green
}

# --- Start Docker Services --------------------------------------------
Write-Host "-> Starting Docker services..." -ForegroundColor Yellow
docker compose up -d --build

Write-Host "[+] Docker services started" -ForegroundColor Green

# --- Wait for PostgreSQL ----------------------------------------------
Write-Host "-> Waiting for PostgreSQL to be ready..." -ForegroundColor Yellow
$retries = 0
$maxRetries = 30
$oldErrorPreference = $ErrorActionPreference
$ErrorActionPreference = "SilentlyContinue"

do {
    $retries++
    Start-Sleep -Seconds 1
    docker compose exec pgsql pg_isready -U larable > $null 2>&1
    $postgresReady = ($LASTEXITCODE -eq 0)
} while (-not $postgresReady -and $retries -lt $maxRetries)

$ErrorActionPreference = $oldErrorPreference

if (-not $postgresReady) {
    Write-Host "[!] PostgreSQL failed to start within timeout" -ForegroundColor Red
    exit 1
}
Write-Host "[+] PostgreSQL is ready" -ForegroundColor Green

# --- Laravel Setup ----------------------------------------------------
Write-Host "-> Running Laravel setup..." -ForegroundColor Yellow
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force

Write-Host "[+] Laravel setup complete" -ForegroundColor Green

# --- Frontend Setup ---------------------------------------------------
Write-Host "-> Installing frontend dependencies..." -ForegroundColor Yellow
docker compose exec frontend npm install

Write-Host "[+] Frontend dependencies installed" -ForegroundColor Green

# --- Done -------------------------------------------------------------
Write-Host ""
Write-Host "----------------------------------------------" -ForegroundColor Green
Write-Host "  [+] Larable is ready!" -ForegroundColor Green
Write-Host "----------------------------------------------" -ForegroundColor Green
Write-Host ""
Write-Host "  Laravel Backend + GUI:  " -NoNewline -ForegroundColor White
Write-Host "http://${backendDomain}:8000" -ForegroundColor Cyan
Write-Host "  Larable Dashboard:      " -NoNewline -ForegroundColor White
Write-Host "http://${backendDomain}:8000/larable" -ForegroundColor Cyan
Write-Host "  React Frontend:         " -NoNewline -ForegroundColor White
Write-Host "http://${frontendDomain}:3000" -ForegroundColor Cyan
Write-Host "  Mailpit (Email UI):     " -NoNewline -ForegroundColor White
Write-Host "http://localhost:8025" -ForegroundColor Cyan
Write-Host "  PostgreSQL:             " -NoNewline -ForegroundColor White
Write-Host "localhost:5432" -ForegroundColor Cyan
Write-Host ""
