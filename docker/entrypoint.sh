#!/bin/sh

# Rodar migrações
php artisan migrate --force

# Link de storage
php artisan storage:link

# Iniciar Nginx em background
nginx

# Iniciar PHP-FPM
exec "$@"
