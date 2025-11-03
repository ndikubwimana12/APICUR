# Use the official PHP + Apache image
FROM php:8.2-apache

# Copy only the public web files into Apache's document root
COPY ./public/ /var/www/html/

# Copy configuration and app files that your PHP code depends on
COPY ./config/ /var/www/html/config/
COPY ./admin/ /var/www/html/admin/
COPY ./teacher/ /var/www/html/teacher/
COPY ./dos/ /var/www/html/dos/
COPY ./head_teacher/ /var/www/html/head_teacher/
COPY ./secretary/ /var/www/html/secretary/
COPY ./discipline/ /var/www/html/discipline/
COPY ./accountant/ /var/www/html/accountant/
COPY ./includes/ /var/www/html/includes/
COPY ./database/ /var/www/html/database/
COPY ./uploads/ /var/www/html/uploads/
COPY ./auth/ /var/www/html/auth/
COPY ./test_connection.php /var/www/html/
COPY ./verify_setup.php /var/www/html/

# Set correct file permissions
RUN chmod -R 755 /var/www/html && \
    chown -R www-data:www-data /var/www/html

# Enable mod_rewrite if needed (for friendly URLs)
RUN a2enmod rewrite

# Expose port 80 for Render
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
