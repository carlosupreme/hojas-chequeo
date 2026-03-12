#!/bin/bash
set -e  # Exit immediately on any error

APP_DIR="/var/www/v2"
PHP="php"
BRANCH="${1:-v2}"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[$(date '+%H:%M:%S')] $1${NC}"; }
warn() { echo -e "${YELLOW}[$(date '+%H:%M:%S')] ⚠ $1${NC}"; }
fail() { echo -e "${RED}[$(date '+%H:%M:%S')] ✗ $1${NC}"; exit 1; }

# Load NVM so npm/node are available in non-interactive shells
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

cd "$APP_DIR" || fail "Cannot cd to $APP_DIR"

log "Starting deploy from branch: $BRANCH"

# ── Git ──────────────────────────────────────────────────────────────────────
log "Pulling latest changes..."
git pull origin "$BRANCH"

# ── PHP Dependencies ──────────────────────────────────────────────────────────
log "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet

# ── Frontend Assets ───────────────────────────────────────────────────────────
log "Building frontend assets..."
npm ci
npm run build

# ── Database ──────────────────────────────────────────────────────────────────
log "Running migrations..."
$PHP artisan migrate --force

# ── Caches ────────────────────────────────────────────────────────────────────
log "Clearing and rebuilding caches..."
php artisan config:clear 
php artisan cache:clear 
php artisan route:clear 
php artisan view:clear 
php artisan optimize:clear
php artisan optimize
sudo rm -rf /var/lib/php/opcache/*

# ── Queue Workers ──────────────────────────────────────────────────────────────
log "Restarting queue workers..."
$PHP artisan queue:restart

# ── Reverb (WebSockets) ───────────────────────────────────────────────────────
if systemctl is-active --quiet reverb 2>/dev/null; then
    log "Restarting Reverb..."
    sudo systemctl restart reverb
else
    warn "Reverb service not found as systemd unit — restart it manually if needed"
fi

# ── Web Server ────────────────────────────────────────────────────────────────
# Run in background with delay — restarting php-fpm kills the current process
# so we let the script finish first before the restart happens
log "Reiniciando PHP-FPM y Nginx en segundo plano..."
(sleep 3 && sudo systemctl restart php8.4-fpm && sudo systemctl reload nginx) &

log "Actualización completada correctamente. ✅"
