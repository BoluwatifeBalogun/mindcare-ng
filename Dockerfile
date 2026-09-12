# MindCare NG — container deploy (Railway, Render, Fly.io, any Docker host)
FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql mbstring && a2enmod rewrite
COPY . /var/www/html/
# Container deploys configure via environment variables (see config.sample.php)
RUN cp /var/www/html/config.sample.php /var/www/html/config.php
EXPOSE 80
