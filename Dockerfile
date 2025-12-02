FROM php:8.1-fpm-alpine

# Install Nginx
RUN apk add --no-cache nginx

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy PHP-FPM configuration
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html/public

# Copy project files into the public directory
COPY . /var/www/html/public

# Expose port 80 (Nginx default)
EXPOSE 80

# Start PHP-FPM and Nginx
CMD php-fpm && nginx -g "daemon off;"
