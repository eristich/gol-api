FROM dunglas/frankenphp

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install the PHP extensions we need
RUN install-php-extensions @composer pdo_pgsql gd intl zip opcache apcu

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Copy the application code
COPY . /app
WORKDIR /app

RUN chmod +x ./wait-for-it.sh ./run-it.sh

RUN composer install && php bin/console lexik:jwt:generate-keypair -n
