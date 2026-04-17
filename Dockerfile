# Build Stage for Assets
FROM node:18-alpine AS assets-builder
WORKDIR /app
COPY . .
RUN npm install && npm run build

# Final Stage
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git \
    curl \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip bcmath

# Copy composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .
COPY --from=assets-builder /app/public/build ./public/build

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Nginx configuration
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Permissions
RUN chown -R mb-www-data:mb-www-data /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# Entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
