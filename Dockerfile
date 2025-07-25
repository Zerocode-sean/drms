FROM php:8.1-cli

# Install basic dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions that we need
RUN docker-php-ext-install pdo_mysql mysqli

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

# Copy all files
COPY . .

# Try to install dependencies, but don't fail if composer.json has issues
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs || echo "Composer install failed, continuing..."

# Expose port
EXPOSE $PORT

# Start the application
CMD php -S 0.0.0.0:$PORT index.php
