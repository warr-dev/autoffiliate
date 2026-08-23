#!/usr/bin/env bash

# ==============================================================================
# Autoffiliate - Build & Deploy via SSH Automation Script
# ==============================================================================
# Usage:
#   1. Run on server directly:       ./deploy.sh
#   2. Build & deploy to SSH server: ./deploy.sh --ssh
# ==============================================================================

set -e

# Load .env.deploy or .env if present
if [ -f ".env.deploy" ]; then
    export $(grep -v '^#' .env.deploy | xargs -d '\n')
elif [ -f ".env" ]; then
    export $(grep -E '^(DEPLOY_|HOSTINGER_)' .env | xargs -d '\n' 2>/dev/null || true)
fi

SSH_HOST="${DEPLOY_HOST:-$HOSTINGER_SSH_HOST}"
SSH_USER="${DEPLOY_USER:-$HOSTINGER_SSH_USER}"
SSH_PORT="${DEPLOY_PORT:-${HOSTINGER_SSH_PORT:-65002}}"
SSH_PATH="${DEPLOY_PATH:-${HOSTINGER_APP_PATH:-/var/www/autoffiliate}}"

# Color Output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}======================================================${NC}"
echo -e "${BLUE}        🚀 Autoffiliate Build & Deployment            ${NC}"
echo -e "${BLUE}======================================================${NC}"

# Mode Selection
if [ "$1" == "--ssh" ] || [ "$1" == "-s" ] || [ -n "$DEPLOY_REMOTE" ]; then
    echo -e "${YELLOW}Mode: Local Build ➔ Deploy to Remote Server via SSH${NC}"

    if [ -z "$SSH_HOST" ] || [ -z "$SSH_USER" ]; then
        echo -e "${RED}❌ Error: DEPLOY_HOST and DEPLOY_USER must be defined in .env.deploy or environment.${NC}"
        echo -e "Example .env.deploy file:"
        echo -e "  DEPLOY_HOST=185.xxx.xxx.xxx"
        echo -e "  DEPLOY_USER=u123456789"
        echo -e "  DEPLOY_PORT=65002"
        echo -e "  DEPLOY_PATH=/home/u123456789/domains/yourdomain.com/app"
        exit 1
    fi

    echo -e "\n${BLUE}📦 Step 1: Compiling fresh production frontend assets locally...${NC}"
    npm run build

    echo -e "\n${BLUE}📤 Step 2: Syncing codebase and public/build to $SSH_HOST:$SSH_PATH...${NC}"
    rsync -avz --delete \
        -e "ssh -p $SSH_PORT" \
        --exclude '.git' \
        --exclude 'node_modules' \
        --exclude 'vendor' \
        --exclude '.env' \
        --exclude 'storage/logs/*' \
        --exclude 'storage/framework/cache/*' \
        --exclude 'storage/framework/sessions/*' \
        --exclude 'storage/framework/views/*' \
        ./ "$SSH_USER@$SSH_HOST:$SSH_PATH/"

    echo -e "\n${BLUE}⚙️ Step 3: Executing post-deploy commands on remote server...${NC}"
    ssh -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" "bash -se" << REMOTE_SCRIPT
        cd "$SSH_PATH" || exit 1

        echo "🔒 Enabling maintenance mode..."
        php artisan down || true

        echo "📦 Installing Composer dependencies (no Node needed on server)..."
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

        echo "🗄️ Running database migrations..."
        php artisan migrate --force

        echo "⚡ Optimizing Laravel caches..."
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan wayfinder:generate || true

        if command -v supervisorctl &> /dev/null; then
            echo "🔄 Restarting background worker daemons..."
            sudo supervisorctl restart all || true
        fi

        echo "🔓 Disabling maintenance mode..."
        php artisan up

        echo "✅ Remote deployment completed successfully!"
REMOTE_SCRIPT

    echo -e "\n${GREEN}🎉 Successfully deployed to $SSH_HOST!${NC}"
    exit 0
fi

# Default: Direct Server Mode (Executed on Hostinger / VPS)
echo -e "${YELLOW}Mode: Direct Server Execution${NC}"

echo -e "\n${BLUE}🔒 Step 1: Enabling maintenance mode...${NC}"
php artisan down || true

echo -e "\n${BLUE}📥 Step 2: Pulling latest changes from Git...${NC}"
git pull origin main

echo -e "\n${BLUE}📦 Step 3: Installing optimized Composer dependencies...${NC}"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo -e "\n${BLUE}🗄️ Step 4: Running database migrations...${NC}"
php artisan migrate --force

echo -e "\n${BLUE}⚡ Step 5: Clearing & caching routes, config, and views...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan wayfinder:generate || true

if command -v supervisorctl &> /dev/null; then
    echo -e "\n${BLUE}🔄 Step 6: Restarting background worker daemons...${NC}"
    sudo supervisorctl restart all || true
fi

echo -e "\n${BLUE}🔓 Step 7: Disabling maintenance mode...${NC}"
php artisan up

echo -e "\n${GREEN}✅ Autoffiliate deployment completed successfully!${NC}"
