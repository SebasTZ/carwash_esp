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

# Copiar el proyecto
COPY . /var/www

# Instalar dependencias de PHP y JS
RUN composer install --no-interaction --no-dev --optimize-autoloader
RUN npm install && npm run build

# Permisos necesarios
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Exponer puerto para Artisan serve (opcional si usas Nginx en otro contenedor)
EXPOSE ${PORT:-8000}

CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
