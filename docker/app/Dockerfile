# Builder
FROM php:8.3-fpm-alpine as builder

# Install system dependencies
RUN apk add --no-cache \
    curl \
    postgresql-dev \
    libpq \
    && docker-php-ext-install pdo pdo_pgsql

WORKDIR /app

COPY . .

# Runtime
FROM php:8.3-fpm-alpine

WORKDIR /app

# Install curl
RUN apk add --no-cache \
    curl \
    libpq

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --from=builder /app /app

COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

COPY ["docker/app/docker-entrypoint.sh", "/usr/local/bin/docker-entrypoint.sh"]
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]