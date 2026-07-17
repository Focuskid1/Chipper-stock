# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Install system dependencies and PostgreSQL driver
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    docker-php-ext-enable pdo_pgsql

# Verify that the driver is loaded (build will fail if not)
RUN php -m | grep -q pdo_pgsql && echo "✅ pdo_pgsql loaded" || (echo "❌ pdo_pgsql NOT loaded" && exit 1)

# Make Apache bind to Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Copy application files into the container
COPY . /var/www/html/

# Set correct ownership and permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expose port 80 (metadata only – Render will override with $PORT)
EXPOSE 80

# Optional: show loaded extensions on startup (useful for debugging)
RUN echo "Loaded PHP extensions:" && php -m
