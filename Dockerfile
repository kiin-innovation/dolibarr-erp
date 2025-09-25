# Use official PHP 8.0 with Apache
FROM php:8.0-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev zip unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mysqli zip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy your modified Dolibarr project into the container
COPY . /var/www/html/

# Create custom documents directory and ensure it's writable
RUN mkdir -p /app/bucket/documents \
    && chown -R www-data:www-data /app/bucket/documents \
    && chmod -R 775 /app/bucket/documents



# Expose HTTP
EXPOSE 80

CMD ["apache2-foreground"]
