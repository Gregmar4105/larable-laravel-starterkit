#!/bin/bash
# ─── Larable Runner Deployment Script (Bash/Linux/macOS) ──────────────
# This script handles automated deployments when triggered by GitHub Actions.
# It is designed to run inside the checked-out folder on the host machine.

set -e

BOLD='\033[1m'
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Keep track of active directory
PROJECT_ROOT=$(pwd)
echo -e "${CYAN}Deploying Larable in: ${PROJECT_ROOT}${NC}"

# ─── Check Docker ──────────────────────────────────────────────────────
echo -e "${YELLOW}▸ Checking Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker command not found on host. Make sure Docker is installed and in the system PATH.${NC}"
    exit 1
fi

if ! docker info &> /dev/null; then
    echo -e "${RED}✗ Docker is installed but not running.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker is running${NC}"

# ─── Sync environment variables ────────────────────────────────────────
echo -e "${YELLOW}▸ Verifying .env configuration...${NC}"
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ Created .env from .env.example${NC}"
    else
        echo -e "${RED}✗ No .env or .env.example found. Unable to configure application.${NC}"
        exit 1
    fi
fi

# ─── Start Docker Services and Build ───────────────────────────────────
echo -e "${YELLOW}▸ Building and starting Docker services...${NC}"
docker compose up -d --build

echo -e "${GREEN}✓ Docker containers built and started${NC}"

# ─── Wait for PostgreSQL to be ready ──────────────────────────────────
echo -e "${YELLOW}▸ Waiting for database to accept connections...${NC}"
RETRIES=0
MAX_RETRIES=30
POSTGRES_READY=0

until [ $RETRIES -ge $MAX_RETRIES ] || [ $POSTGRES_READY -eq 1 ]; do
    RETRIES=$((RETRIES+1))
    sleep 1
    # Run pg_isready inside the pgsql container without TTY (-T)
    if docker compose exec -T pgsql pg_isready -U larable &> /dev/null; then
        POSTGRES_READY=1
    fi
done

if [ $POSTGRES_READY -ne 1 ]; then
    echo -e "${RED}✗ PostgreSQL did not become ready within the timeout period.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Database is ready${NC}"

# ─── Laravel Setup & Optimization ─────────────────────────────────────
echo -e "${YELLOW}▸ Running Laravel migrations and optimization...${NC}"

# Generate app key if missing
if ! grep -q "APP_KEY=base64:" .env; then
    docker compose exec -T app php artisan key:generate --force
    echo -e "${GREEN}✓ App key generated${NC}"
fi

# Run migrations
docker compose exec -T app php artisan migrate --force
echo -e "${GREEN}✓ Migrations ran successfully${NC}"

# Clear and optimize application cache
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan optimize
echo -e "${GREEN}✓ Laravel caches cleared and re-built${NC}"

# ─── Frontend Setup ───────────────────────────────────────────────────
echo -e "${YELLOW}▸ Updating frontend dependencies...${NC}"
docker compose exec -T frontend npm install
echo -e "${GREEN}✓ Frontend dependencies updated${NC}"

echo ""
echo -e "${GREEN}${BOLD}==============================================${NC}"
echo -e "${GREEN}${BOLD}🚀 Deployment Successful!${NC}"
echo -e "${GREEN}${BOLD}==============================================${NC}"
echo ""
