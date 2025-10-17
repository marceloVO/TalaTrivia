# Dockerfile (en la raíz de TalaTrivia)
FROM php:8.2-fpm-alpine

# Instala Git y dependencias de PHP necesarias
RUN apk update && apk add --no-cache \
    git \
    curl \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    && docker-php-ext-install pdo pdo_mysql opcache zip \
    # Instala Composer globalmente
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


RUN chown -R www-data:www-data /var/www/html
USER www-data

WORKDIR /var/www/html