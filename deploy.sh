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
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan cache:clear
$PHP artisan filament:optimize

# ── Queue Workers ──────────────────────────────────────────────────────────────
log "Restarting queue workers..."
$PHP artisan queue:restart

# ── Reverb (WebSockets) ───────────────────────────────────────────────────────
if systemctl is-active --quiet laravel-reverb 2>/dev/null; then
    log "Restarting Reverb..."
    sudo systemctl restart laravel-reverb
else
    warn "Reverb service not found as systemd unit — restart it manually if needed"
fi

# ── Web Server ────────────────────────────────────────────────────────────────
log "Restarting PHP-FPM and reloading Nginx..."
sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx

log "Actualización completada correctamente. ✅"
