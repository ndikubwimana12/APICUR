FROM php:8.2-apache

# Copy everything from the current folder
COPY . /var/www/html/

# Permissions
RUN chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /var/www/html

# Enable Apache mod_rewrite (optional)
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
