FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    git \
    libzip-dev \
    zip \
    && docker-php-ext-install zip pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN a2enmod rewrite
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
