FROM php:8.2-apache-bullseye

RUN a2enmod rewrite

# Install PostgreSQL driver via apt-get (most reliable)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    php-pgsql \
    && rm -rf /var/lib/apt/lists/*

# Enable the extension
RUN echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/20-pdo_pgsql.ini

# Verify driver is loaded
RUN php -m | grep -q pdo_pgsql || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Bind Apache to Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
