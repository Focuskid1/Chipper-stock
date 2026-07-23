FROM php:8.2-apache-bullseye

RUN a2enmod rewrite

# Install PostgreSQL client libraries and development headers
RUN apt-get update && apt-get install -y \
    libpq-dev \
    postgresql-server-dev-all \
    && rm -rf /var/lib/apt/lists/*

# Install pdo_pgsql using docker-php-ext-install (preferred)
RUN docker-php-ext-install pdo pdo_pgsql

# Also try PECL as a fallback (just in case)
RUN pecl install pdo_pgsql && docker-php-ext-enable pdo_pgsql

# Create a dedicated ini file to ensure the extension is loaded
RUN echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/20-pdo_pgsql.ini

# Verify that the driver is loaded
RUN php -m | grep -q pdo_pgsql || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Bind Apache to Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
