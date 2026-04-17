#!/bin/sh

# Garantir que a pasta de logs existe e tem permissão
mkdir -p /var/www/html/storage/logs
touch /var/www/html/storage/logs/laravel.log

# Ajuste de permissões total nas pastas de escrita
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Rodar migrações
echo "Rodando migrações..."
php artisan migrate --force

# Link de storage (Remover antigo e criar novo)
rm -rf /var/www/html/public/storage
php artisan storage:link

# Iniciar Nginx em background
nginx

# Iniciar PHP-FPM
exec "$@"
