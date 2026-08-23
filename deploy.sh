#!/usr/bin/env bash

# ==============================================================================
# Autoffiliate - High-Speed Build & Verbose SSH Deployment Script
# ==============================================================================
# Usage:
#   1. Run on server directly:       ./deploy.sh
#   2. Build & deploy to SSH server: ./deploy.sh --ssh
# ==============================================================================

set -e
START_TIME=$(date +%s)

# Load .env.deploy or .env if present (Compatible with Alpine Busybox, Linux & macOS)
load_env() {
    local env_file="$1"
    if [ -f "$env_file" ]; then
        while IFS='=' read -r key val || [ -n "$key" ]; do
            key=$(echo "$key" | tr -d '\r' | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')
            val=$(echo "$val" | tr -d '\r' | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')
            if [ -n "$key" ] && [[ ! "$key" =~ ^# ]]; then
                val="${val%\"}"
                val="${val#\"}"
                val="${val%\'}"
                val="${val#\'}"
                export "$key=$val"
            fi
        done < "$env_file"
    fi
}

load_env ".env.deploy"
load_env ".env"

SSH_HOST="${DEPLOY_HOST:-${HOSTINGER_SSH_HOST:-$SSH_HOST}}"
SSH_USER="${DEPLOY_USER:-${HOSTINGER_SSH_USER:-$SSH_USER}}"
SSH_PORT="${DEPLOY_PORT:-${HOSTINGER_SSH_PORT:-${SSH_PORT:-65002}}}"
SSH_PATH="${DEPLOY_PATH:-${HOSTINGER_APP_PATH:-${SSH_PATH:-/var/www/autoffiliate}}}"
SSH_PASS="${DEPLOY_PASSWORD:-${HOSTINGER_SSH_PASSWORD:-$SSH_PASSWORD}}"

# Configure SSH & Rsync Execution Command
RSYNC_RSH="ssh -p $SSH_PORT -o StrictHostKeyChecking=accept-new -o ConnectTimeout=15"
SSH_EXEC="ssh -p $SSH_PORT -o StrictHostKeyChecking=accept-new -o ConnectTimeout=15"

if [ -n "$SSH_PASS" ] && command -v sshpass &> /dev/null; then
    RSYNC_RSH="sshpass -p '$SSH_PASS' ssh -p $SSH_PORT -o StrictHostKeyChecking=accept-new -o ConnectTimeout=15"
    SSH_EXEC="sshpass -p '$SSH_PASS' ssh -p $SSH_PORT -o StrictHostKeyChecking=accept-new -o ConnectTimeout=15"
fi

# Color Output & Helpers
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m'

log_info() {
    echo -e "${CYAN}[$(date +'%H:%M:%S')]${NC} ${BLUE}$1${NC}"
}
log_success() {
    echo -e "${CYAN}[$(date +'%H:%M:%S')]${NC} ${GREEN}$1${NC}"
}
log_warn() {
    echo -e "${CYAN}[$(date +'%H:%M:%S')]${NC} ${YELLOW}$1${NC}"
}
log_error() {
    echo -e "${CYAN}[$(date +'%H:%M:%S')]${NC} ${RED}$1${NC}"
}

echo -e "${BLUE}==================================================================${NC}"
echo -e "${BOLD}${CYAN}          🚀 Autoffiliate Build & Deployment Engine               ${NC}"
echo -e "${BLUE}==================================================================${NC}"

# ==============================================================================
# MODE 1: LOCAL BUILD ➔ SSH REMOTE DEPLOY
# ==============================================================================
if [ "$1" == "--ssh" ] || [ "$1" == "-s" ] || [ -n "$DEPLOY_REMOTE" ]; then
    log_info "Mode: ${BOLD}Local Node Build ➔ Deploy to Hostinger via SSH${NC}"
    log_info "Target Host: ${YELLOW}$SSH_USER@$SSH_HOST:$SSH_PORT${NC}"
    log_info "Destination: ${YELLOW}$SSH_PATH${NC}"

    if [ -z "$SSH_HOST" ] || [ -z "$SSH_USER" ]; then
        log_error "❌ Error: DEPLOY_HOST and DEPLOY_USER must be defined in .env.deploy"
        echo -e "\nExample .env.deploy:"
        echo -e "  DEPLOY_HOST=185.xxx.xxx.xxx"
        echo -e "  DEPLOY_USER=u123456789"
        echo -e "  DEPLOY_PORT=65002"
        echo -e "  DEPLOY_PATH=/home/u123456789/domains/yourdomain.com/app"
        echo -e "  DEPLOY_PASSWORD=YourPassword (Optional)\n"
        exit 1
    fi

    # 1. Local Vite Build
    log_info "📦 Step 1/3: Compiling production frontend assets with Vite..."
    BUILD_START=$(date +%s)
    npm run build
    BUILD_END=$(date +%s)
    log_success "✔ Frontend compiled in $((BUILD_END - BUILD_START))s"

    # 2. Fast Sync with live progress
    log_info "📤 Step 2/3: Syncing optimized payload to remote server (rsync with progress)..."
    SYNC_START=$(date +%s)

    rsync -avh \
        --info=progress2 \
        --delete \
        -e "$RSYNC_RSH" \
        --exclude '.git/' \
        --exclude 'node_modules/' \
        --exclude 'vendor/' \
        --exclude '.env' \
        --exclude '.env.deploy' \
        --exclude '.devcontainer/' \
        --exclude 'autoaff/' \
        --exclude 'tests/' \
        --exclude '.phpunit.cache/' \
        --exclude '.idea/' \
        --exclude '.vscode/' \
        --exclude 'storage/logs/*' \
        --exclude 'storage/framework/cache/*' \
        --exclude 'storage/framework/sessions/*' \
        --exclude 'storage/framework/views/*' \
        --exclude 'storage/pail/*' \
        ./ "$SSH_USER@$SSH_HOST:$SSH_PATH/"

    SYNC_END=$(date +%s)
    log_success "✔ Transferred in $((SYNC_END - SYNC_START))s"

    # 3. Remote Execution
    log_info "⚙️ Step 3/3: Executing server-side optimization & migrations..."
    
    $SSH_EXEC "$SSH_USER@$SSH_HOST" "bash -se" << REMOTE_SCRIPT
        set -e
        cd "$SSH_PATH" || exit 1

        echo "  [1/6] 🔒 Enabling maintenance mode..."
        php artisan down --render="errors::503" || true

        echo "  [2/6] 📦 Installing Composer dependencies (no-dev)..."
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-progress

        echo "  [3/6] 🗄️ Running database migrations..."
        php artisan migrate --force

        echo "  [4/6] ⚡ Generating Wayfinder route types & caching config..."
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan wayfinder:generate || true

        if command -v supervisorctl &> /dev/null; then
            echo "  [5/6] 🔄 Restarting background worker daemons (Supervisor)..."
            sudo supervisorctl restart all || true
        else
            echo "  [5/6] ℹ️ Supervisor not detected (Cron mode active)."
        fi

        echo "  [6/6] 🔓 Disabling maintenance mode..."
        php artisan up

        echo "  ✨ Remote execution complete!"
REMOTE_SCRIPT

    TOTAL_TIME=$(($(date +%s) - START_TIME))
    echo -e "\n${BLUE}==================================================================${NC}"
    log_success "🎉 Deployment to $SSH_HOST successfully completed in ${BOLD}${TOTAL_TIME}s!${NC}"
    echo -e "${BLUE}==================================================================${NC}\n"
    exit 0
fi

# ==============================================================================
# MODE 2: DIRECT SERVER EXECUTION (Executed directly on Hostinger VPS / SSH)
# ==============================================================================
log_info "Mode: ${BOLD}Direct Server Execution${NC}"

log_info "🔒 Step 1/6: Enabling maintenance mode..."
php artisan down || true

log_info "📥 Step 2/6: Pulling latest changes from Git..."
git pull origin main

log_info "📦 Step 3/6: Installing optimized Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-progress

log_info "🗄️ Step 4/6: Running database migrations..."
php artisan migrate --force

log_info "⚡ Step 5/6: Clearing & caching routes, config, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan wayfinder:generate || true

if command -v supervisorctl &> /dev/null; then
    log_info "🔄 Step 6/6: Restarting background worker daemons..."
    sudo supervisorctl restart all || true
fi

log_info "🔓 Disabling maintenance mode..."
php artisan up

TOTAL_TIME=$(($(date +%s) - START_TIME))
echo -e "\n${BLUE}==================================================================${NC}"
log_success "✅ Autoffiliate deployment completed successfully in ${BOLD}${TOTAL_TIME}s!${NC}"
echo -e "${BLUE}==================================================================${NC}\n"
