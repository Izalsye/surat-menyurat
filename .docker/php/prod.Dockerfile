FROM php:8.4.7-fpm

# Install dependencies dan PHP extensions
RUN apt-get update && apt-get install -y \
    build-essential \
    zlib1g-dev \
    default-mysql-client \
    curl \
    gnupg \
    procps \
    vim \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
  && docker-php-ext-configure intl \
  && docker-php-ext-install intl zip pdo_mysql \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install gd \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20.x dan npm terbaru
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get install -y nodejs \
  && npm install -g npm@latest \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
  && php -r "unlink('composer-setup.php');"

# Copy konfigurasi PHP dan PHP-FPM
COPY ./.docker/php/php.ini /usr/local/etc/php/
COPY ./.docker/php/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY ./.docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www

COPY . .

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Setup Laravel environment & build frontend
RUN cp .env.example .env \
  && php artisan key:generate \
  && php artisan route:cache \
  && php artisan storage:link

RUN npm install \
  && npm run build

# Fix permission
RUN chown -R www-data:www-data /var/www
