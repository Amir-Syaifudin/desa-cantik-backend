FROM php:8.2-fpm-bookworm

# Install system dependencies, upgrade base packages, configure GD and install PHP extensions in one layer
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        ca-certificates \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        zip \
        unzip \
        libjpeg-dev \
        libfreetype6-dev \
        build-essential \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Pin composer to v2 to avoid breaking changes from "latest"
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Configure Git to trust the working directory
RUN git config --global --add safe.directory /var/www

# Use single COPY with ownership to avoid duplicate layers
COPY --chown=www-data:www-data . /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
