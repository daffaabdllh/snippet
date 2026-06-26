# Stage 1: Build front-end assets
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Final production image
FROM serversideup/php:8.4-fpm-nginx-alpine
WORKDIR /var/www/html

# Switch to root to perform installations/permissions
USER root

# Copy application files with correct ownership
COPY --chown=www-data:www-data . .

# Copy compiled assets from node-builder stage
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Run composer install during build
RUN composer install --no-dev --optimize-autoloader

# Switch back to the unprivileged web user
USER www-data


