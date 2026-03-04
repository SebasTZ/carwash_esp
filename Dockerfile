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

# Permisos necesarios
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Exponer puerto para Artisan serve (opcional si usas Nginx en otro contenedor)
EXPOSE ${PORT:-8000}

# Migrar solo en el primer arranque (sin seed en producción)
CMD php artisan migrate --force && php-fpm
