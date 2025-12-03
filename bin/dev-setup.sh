#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

php -v || true
composer --version || true
node -v || true
npm -v || true

# Composer deps
composer install --no-interaction --prefer-dist --no-progress
composer require -W \
  laravel/jetstream:^5.0 \
  livewire/livewire:^3.5 \
  filament/filament:^3.2 \
  spatie/laravel-permission:^6.0 \
  barryvdh/laravel-dompdf:^2.0 --no-interaction --prefer-dist --no-progress || true
composer require --dev pestphp/pest:^3.0 pestphp/pest-plugin-laravel:^3.0 --no-interaction --prefer-dist --no-progress || true

# Jetstream scaffolding
php artisan jetstream:install livewire --dark || true

# Publish vendor assets/migrations
php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider" --tag=migrations --force || true
php artisan vendor:publish --provider="Barryvdh\\DomPDF\\ServiceProvider" --tag=config --force || true

# Session table
php artisan session:table || true

# Migrate
php artisan migrate --force || true

# Node deps & build
npm install --no-audit --no-fund --progress=false
npm run build

echo "Development stack ready."
