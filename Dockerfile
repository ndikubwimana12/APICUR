FROM php:8.2-apache

# Copy your PHP app from its subfolder
COPY ./SchoolManagementSystem/ /var/www/html/

# Permissions
RUN chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /var/www/html

RUN a2enmod rewrite
EXPOSE 80
CMD ["apache2-foreground"]
