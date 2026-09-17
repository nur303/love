# Dockerfile for deploying PHP + Apache on Render, Railway, or Fly.io
FROM php:8.2-apache

# Install PDO MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy project files into web server document root
COPY . /var/www/html/

# Ensure web server has write access to SQLite database directory if needed
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Configure Apache port (Render/Railway use PORT environment variable)
ENV PORT=80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
