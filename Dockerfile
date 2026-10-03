# MindCare NG — container deploy (Railway, Render, Fly.io, any Docker host)
FROM php:8.3-apache
# libonig-dev: oniguruma, required to compile the mbstring extension on PHP 7.4+
RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*
COPY . /var/www/html/
# Container deploys configure via environment variables (see config.sample.php)
RUN cp /var/www/html/config.sample.php /var/www/html/config.php
EXPOSE 80
