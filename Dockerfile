FROM php:8.2-apache

RUN a2enmod rewrite

# Install PostgreSQL client libraries
RUN apt-get update && apt-get install -y libpq-dev

# Install pdo_pgsql
RUN docker-php-ext-install pdo pdo_pgsql

# Enable the extension using the official helper
RUN docker-php-ext-enable pdo_pgsql

# Override the default php.ini with our custom one
# This ensures the extension is loaded for both CLI and Apache
COPY php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Verify the extension is loaded (build will fail if not)
RUN php -m | grep -q pdo_pgsql || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Bind Apache to Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
