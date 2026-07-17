FROM php:8.2-apache

# Enable mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy all files to the web root
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Install PostgreSQL driver if you're using PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql

# Expose port 80
EXPOSE 80
