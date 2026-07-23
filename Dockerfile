FROM php:8.2-apache-bullseye

RUN a2enmod rewrite

# Install PostgreSQL client libraries and development headers
RUN apt-get update && apt-get install -y \
    libpq-dev \
    postgresql-server-dev-all \
    && rm -rf /var/lib/apt/lists/*

# Install pdo_pgsql using docker-php-ext-install (official method)
RUN docker-php-ext-install pdo pdo_pgsql

# Enable the extension (explicitly)
RUN docker-php-ext-enable pdo_pgsql

# Write an ini file to ensure it's loaded
RUN echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/20-pdo_pgsql.ini

# Verify that the driver is loaded (build will fail if not)
RUN php -m | grep -q pdo_pgsql || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Print loaded modules for debugging (visible in build logs)
RUN php -m

# Bind Apache to Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
