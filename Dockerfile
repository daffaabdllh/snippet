FROM serversideup/php:8.4-fpm-nginx-alpine

# Switch to root to perform installations/permissions
USER root

# Copy application files with correct ownership
COPY --chown=www-data:www-data . .

# Run composer install during build
RUN composer install --no-dev --optimize-autoloader

# Switch back to the unprivileged web user
USER www-data

