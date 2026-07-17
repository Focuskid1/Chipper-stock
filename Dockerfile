FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install PostgreSQL driver and its dependencies
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    docker-php-ext-enable pdo_pgsql

# Verify the driver is loaded (this will show a build-time message)
RUN php -m | grep -q pdo_pgsql && echo "✅ pdo_pgsql loaded" || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Make Apache listen on Render's PORT (dynamic)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expose port 80 (metadata only)
EXPOSE 80
