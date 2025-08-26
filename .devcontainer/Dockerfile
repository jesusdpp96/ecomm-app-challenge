FROM mcr.microsoft.com/devcontainers/php:1-8.2-bullseye

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libgd-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for CodeIgniter 4
RUN docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        intl \
        mysqli \
        pdo_mysql \
        pdo_sqlite \
        zip \
        gd \
        mbstring \
        xml \
        curl \
        opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /workspaces/challege-ecomm-app

# Configure PHP for development
RUN echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/docker-php-memory.ini \
    && echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/docker-php-upload.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/docker-php-upload.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/docker-php-timeout.ini

# Set proper permissions for development
RUN chmod -R 777 /workspaces/challege-ecomm-app

# Keep root user for full permissions in development

# Expose port for development server
EXPOSE 8080

# Default command
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/workspaces/challege-ecomm-app"]
