# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy all project files into the Apache root directory
COPY . /var/www/html/

# Give proper permissions
RUN chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /var/www/html

# Enable Apache rewrite module (useful for many frameworks)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
