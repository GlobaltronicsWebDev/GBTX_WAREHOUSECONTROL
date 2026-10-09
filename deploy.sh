#!/bin/bash
set -Eeuo pipefail

cd "$(dirname "$0")"

echo "Starting Laravel 13 deployment..."

git pull --ff-only origin main

composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

php artisan migrate --force
php artisan optimize:clear
php artisan optimize

echo "Laravel 13 deployment completed!"
