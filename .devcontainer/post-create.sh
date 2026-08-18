#!/bin/bash
set -e

echo "🚀 Initializing PHP Laravel Devcontainer environment..."

# 1. Check if Laravel exists or scaffold if requested
if [ ! -f "artisan" ]; then
    echo "ℹ️ No Laravel application detected in workspace."
    echo "💡 You can create one now by running: composer create-project laravel/laravel ."
else
    echo "✔ Laravel application detected."
    if [ -f "composer.json" ] && [ ! -d "vendor" ]; then
        echo "📦 Installing composer dependencies..."
        composer install --prefer-dist --no-interaction
    fi
    if [ -f "package.json" ] && [ ! -d "node_modules" ]; then
        echo "📦 Installing NPM packages..."
        npm install --no-audit --no-fund || true
    fi
    if [ ! -f ".env" ] && [ -f ".env.example" ]; then
        echo "⚙️ Creating .env from .env.example..."
        cp .env.example .env
        php artisan key:generate || true
    fi
fi

echo "✨ Environment ready! Happy coding!"
