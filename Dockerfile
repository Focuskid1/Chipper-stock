FROM php:8.2-apache

RUN a2enmod rewrite

# Install PostgreSQL client libraries
RUN apt-get update && apt-get install -y libpq-dev

# Install pdo_pgsql using the official PHP extension installer
RUN docker-php-ext-install pdo pdo_pgsql

# Enable the extension (this is usually automatic, but we'll be explicit)
RUN docker-php-ext-enable pdo_pgsql

# Write an ini file to ensure it's loaded
RUN echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/pdo_pgsql.ini

# Verify the driver is loaded
RUN php -m | grep -q pdo_pgsql || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Bind Apache to Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
