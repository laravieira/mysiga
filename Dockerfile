FROM php:8.2.4RC1-apache

COPY src/ /var/www/html/src/
COPY src/.htaccess /var/www/html/
COPY src/constants.php /var/www/html/
COPY src/index.php /var/www/html/
COPY vendor/ /var/www/html/vendor/
COPY composer.json /var/www/html/

RUN a2enmod rewrite