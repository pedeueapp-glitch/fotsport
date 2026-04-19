FROM php:8.2-fpm-alpine

# Instalar dependências do sistema para Alpine
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
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    postgresql-dev

# Instalar extensões do PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip bcmath intl mbstring exif pcntl

# Configurar limites de upload no PHP
RUN echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/uploads.ini

# Copiar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- OTIMIZAÇÃO DE CACHE DO COMPOSER ---
# Copiar apenas os arquivos de dependência primeiro
COPY composer.json composer.lock ./

# Instalar as dependências sem scripts e sem autoloader final (camada reciclável)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Agora copiar o código do projeto (isso mudará com frequência)
COPY . .

# Finalizar o autoloader e rodar scripts necessários do Laravel/Composer
RUN composer dump-autoload --optimize --no-dev \
    && composer run-script post-autoload-dump

# --- FIM DA OTIMIZAÇÃO ---

# Configurar permissões e Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
