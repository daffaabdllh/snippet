FROM richarvey/nginx-php-fpm:3.1.6

# Copy application files
COPY . .

# Run composer install during build
RUN composer install --no-dev --optimize-autoloader

# Make startup scripts executable
RUN chmod +x /var/www/html/scripts/*.sh

# Image configuration
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel configuration
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Start command
CMD ["/start.sh"]
