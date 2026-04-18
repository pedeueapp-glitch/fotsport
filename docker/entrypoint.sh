#!/bin/sh

# Aguardar o banco de dados (MySQL) estar pronto
echo "Aguardando o banco de dados..."
while ! nc -z db 3306; do
  sleep 1
done
echo "Banco de dados pronto!"

# Garantir que as pastas de storage existem
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/cache
touch /var/www/html/storage/logs/laravel.log

# Ajuste de permissões
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Link de storage
echo "Criando link de storage..."
php artisan storage:link --force || true

# Rodar migrações
echo "Rodando migrações..."
php artisan migrate --force

# Iniciar Nginx
nginx

# Iniciar PHP-FPM
echo "Iniciando PHP-FPM..."
exec php-fpm
