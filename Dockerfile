
FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 10000

CMD ["bash", "-lc", "PORT=${PORT:-10000}; sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
