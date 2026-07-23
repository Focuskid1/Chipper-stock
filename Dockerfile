FROM php:8.2-apache

RUN a2enmod rewrite

# Install system dependencies
RUN apt-get update && apt-get install -y libpq-dev

# Install PostgreSQL PDO driver
RUN docker-php-ext-install pdo pdo_pgsql

# Enable the extension
RUN docker-php-ext-enable pdo_pgsql

# Explicitly write an ini file (just to be safe)
RUN echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/pdo_pgsql.ini

# Print loaded modules for debugging
RUN php -m

# Fail if pdo_pgsql is not loaded
RUN php -m | grep -q pdo_pgsql || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Bind Apache to Render's PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Copy application
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
