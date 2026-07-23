FROM php:8.2-apache

RUN a2enmod rewrite

# Install PostgreSQL client libraries
RUN apt-get update && apt-get install -y libpq-dev

# Install the PDO and PostgreSQL PDO extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Enable the extension (this usually works, but we'll also copy a custom php.ini)
RUN docker-php-ext-enable pdo_pgsql

# Copy a custom php.ini that explicitly loads pdo_pgsql
COPY php.ini /usr/local/etc/php/conf.d/99-pdo_pgsql.ini

# Verify that the extension is loaded at build time
RUN php -m | grep -q pdo_pgsql || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Bind Apache to Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Copy your application code
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
