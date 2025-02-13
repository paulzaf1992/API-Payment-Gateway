FROM php:8.1-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpq-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Enable PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy composer from official image
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files
COPY composer.json ./
RUN composer install --no-interaction --optimize-autoloader

# Copy the rest of the code
COPY . .

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
