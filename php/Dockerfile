FROM php:8.1-apache
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip curl git \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && a2enmod rewrite

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar librerías PHP para PDF y Word
WORKDIR /var/www/html
RUN composer require tecnickcom/tcpdf phpoffice/phpword --no-interaction 2>/dev/null || true

WORKDIR /var/www/html
