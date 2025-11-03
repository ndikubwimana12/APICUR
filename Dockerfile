# Use the official PHP image with Apache
FROM php:8.1-apache

# Enable commonly used PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files to Apache web root
COPY . /var/www/html/

# Give proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (default for HTTP)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
