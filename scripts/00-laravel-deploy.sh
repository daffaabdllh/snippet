#!/usr/bin/env bash
echo "Running migrations..."
php artisan migrate --force
