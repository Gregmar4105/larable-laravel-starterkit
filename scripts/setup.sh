#!/bin/bash
# ─── Larable Setup Script ─────────────────────────────────────────────
# This script initializes the entire Larable development environment.
# Prerequisites: Docker Desktop must be installed and running.

# Resolve and change to the project root directory dynamically
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR/.."

set -e

BOLD='\033[1m'
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}${BOLD}"
echo "  ╔══════════════════════════════════════════╗"
echo "  ║          🚀 LARABLE SETUP                ║"
echo "  ║    Laravel Starterkit • Decoupled Arch   ║"
echo "  ╚══════════════════════════════════════════╝"
echo -e "${NC}"

# ─── Check Docker ──────────────────────────────────────────────────────
echo -e "${YELLOW}▸ Checking Docker Desktop...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker is not installed!${NC}"
    echo ""
    echo -e "${BOLD}Please install Docker Desktop first:${NC}"
    echo "  → https://www.docker.com/products/docker-desktop/"
    echo ""
    echo "After installing Docker Desktop:"
    echo "  1. Launch Docker Desktop"
    echo "  2. Wait for it to fully start (whale icon is steady)"
    echo "  3. Run this script again"
    exit 1
fi

if ! docker info &> /dev/null; then
    echo -e "${RED}✗ Docker is installed but not running!${NC}"
    echo ""
    echo "Please start Docker Desktop and try again."
    exit 1
fi

echo -e "${GREEN}✓ Docker Desktop is running${NC}"

# ─── Copy .env if needed ──────────────────────────────────────────────
echo -e "${YELLOW}▸ Checking environment file...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env from .env.example${NC}"
else
    echo -e "${GREEN}✓ .env already exists${NC}"
fi

# ─── Update dynamic domains in .env based on current folder name ──────
FOLDER_NAME=$(basename "$PWD")
if grep -q "larable-laravel-staterkit" .env; then
    sed "s/larable-laravel-staterkit/${FOLDER_NAME}/g" .env > .env.tmp && mv .env.tmp .env
    echo -e "${GREEN}✓ Updated .env domains to use '${FOLDER_NAME}'${NC}"
fi

# ─── Configure Local Domains in Hosts File ────────────────────────────
BACKEND_DOMAIN="$FOLDER_NAME.test"
FRONTEND_DOMAIN="$FOLDER_NAME-frontend.test"
HOSTS_PATH="/etc/hosts"

if ! grep -q "$BACKEND_DOMAIN" "$HOSTS_PATH" || ! grep -q "$FRONTEND_DOMAIN" "$HOSTS_PATH"; then
    echo -e "${YELLOW}▸ Adding local domains to hosts file (requires sudo/administrator privileges)...${NC}"
    
    # Write entries header
    if [ "$EUID" -ne 0 ]; then
        echo -e "\n# Larable Local Domains" | sudo tee -a "$HOSTS_PATH" > /dev/null
        if ! grep -q "$BACKEND_DOMAIN" "$HOSTS_PATH"; then
            echo "127.0.0.1 $BACKEND_DOMAIN" | sudo tee -a "$HOSTS_PATH" > /dev/null
        fi
        if ! grep -q "$FRONTEND_DOMAIN" "$HOSTS_PATH"; then
            echo "127.0.0.1 $FRONTEND_DOMAIN" | sudo tee -a "$HOSTS_PATH" > /dev/null
        fi
    else
        echo -e "\n# Larable Local Domains" >> "$HOSTS_PATH"
        if ! grep -q "$BACKEND_DOMAIN" "$HOSTS_PATH"; then
            echo "127.0.0.1 $BACKEND_DOMAIN" >> "$HOSTS_PATH"
        fi
        if ! grep -q "$FRONTEND_DOMAIN" "$HOSTS_PATH"; then
            echo "127.0.0.1 $FRONTEND_DOMAIN" >> "$HOSTS_PATH"
        fi
    fi
    echo -e "${GREEN}✓ Local domains configured in hosts file${NC}"
else
    echo -e "${GREEN}✓ Local domains already configured in hosts file${NC}"
fi

# ─── Start Docker Services ────────────────────────────────────────────
echo -e "${YELLOW}▸ Starting Docker services...${NC}"
docker compose up -d --build

echo -e "${GREEN}✓ Docker services started${NC}"

# ─── Wait for PostgreSQL ──────────────────────────────────────────────
echo -e "${YELLOW}▸ Waiting for PostgreSQL to be ready...${NC}"
until docker compose exec pgsql pg_isready -U larable &> /dev/null; do
    sleep 1
done
echo -e "${GREEN}✓ PostgreSQL is ready${NC}"

# ─── Laravel Setup ────────────────────────────────────────────────────
echo -e "${YELLOW}▸ Running Laravel setup...${NC}"
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force 2>/dev/null || true

echo -e "${GREEN}✓ Laravel setup complete${NC}"

# ─── Frontend Setup ───────────────────────────────────────────────────
echo -e "${YELLOW}▸ Installing frontend dependencies...${NC}"
docker compose exec frontend npm install

echo -e "${GREEN}✓ Frontend dependencies installed${NC}"

# ─── Done ──────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}══════════════════════════════════════════════${NC}"
echo -e "${GREEN}${BOLD}  ✓ Larable is ready!${NC}"
echo -e "${GREEN}${BOLD}══════════════════════════════════════════════${NC}"
echo ""
echo -e "  ${CYAN}Laravel Backend + GUI:${NC}  http://$BACKEND_DOMAIN:8000"
echo -e "  ${CYAN}Larable Dashboard:${NC}     http://$BACKEND_DOMAIN:8000/larable"
echo -e "  ${CYAN}React Frontend:${NC}        http://$FRONTEND_DOMAIN:3000"
echo -e "  ${CYAN}Mailpit (Email UI):${NC}    http://localhost:8025"
echo -e "  ${CYAN}PostgreSQL:${NC}            localhost:5432"
echo ""
