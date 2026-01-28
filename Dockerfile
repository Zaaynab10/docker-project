FROM php:8.2-apache

# Enable required Apache modules
RUN a2enmod rewrite

# Install MySQL dependencies
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy Apache configuration
COPY ./apache/vhost.conf /etc/apache2/sites-available/000-default.conf



# Set directory permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
