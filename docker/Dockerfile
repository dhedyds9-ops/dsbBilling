FROM php:8.2-fpm

# Update OS and install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    snmp \
    libsnmp-dev \
    snmp-mibs-downloader \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP Extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd snmp

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]