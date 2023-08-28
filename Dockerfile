FROM php:8.2.9RC1-apache

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install zip unzip -y
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY . /var/www/html/
COPY src/.htaccess /var/www/html/
COPY src/constants.php /var/www/html/
COPY src/index.php /var/www/html/

RUN composer update
RUN composer install
RUN a2enmod rewrite
