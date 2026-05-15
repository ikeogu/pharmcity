FROM php:8.3-cli

# GD requires freetype + jpeg libs; intl requires icu; pgsql requires libpq
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl zip unzip \
    libpng-dev libzip-dev libxml2-dev \
    libpq-dev libonig-dev libicu-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure GD with freetype + jpeg support before installing
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql pgsql \
        mbstring xml zip gd bcmath intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

ENV COMPOSER_MEMORY_LIMIT=-1

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

RUN mkdir -p storage/framework/views \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/app/public \
        storage/logs \
        bootstrap/cache \
    && npm run build \
    && chmod -R 775 storage bootstrap/cache

COPY start.sh /start.sh
RUN chmod +x /start.sh

# PHP built-in server: allow 4 concurrent workers
ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 8000

CMD ["/start.sh"]
