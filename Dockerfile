FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libzip-dev \
    libicu-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar dependencias primero para aprovechar el caché de capas de Docker
COPY composer.json composer.lock /var/www/
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

COPY package.json package-lock.json /var/www/
RUN npm install

# Copiar el resto del proyecto
COPY . /var/www

# Compilar assets y ejecutar scripts de Composer
RUN npm run build && composer run-script post-autoload-dump

# Optimizar para producción (rutas, config, vistas en caché)
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Permisos necesarios
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE ${PORT:-8000}

# IMPORTANTE: Las migraciones NO van aquí.
# En Laravel Cloud configurar como "Deploy Command":
#   php artisan migrate --force
#
# El CMD solo arranca el servidor — se ejecuta en cada reinicio del contenedor.
CMD ["php-fpm"]
