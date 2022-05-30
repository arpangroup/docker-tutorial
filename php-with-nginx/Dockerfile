# FROM php:8.0.2-fpm
# FROM php:7.2-fpm

FROM php:7.2-fpm

RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    vim \
    unzip

WORKDIR /var/www
