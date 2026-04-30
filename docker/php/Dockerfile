FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create user for Laravel
RUN groupadd -g 1000 www && \
    useradd -u 1000 -g www -s /bin/bash -m www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Change user to www
USER www

EXPOSE 9000
CMD ["php-fpm"]
