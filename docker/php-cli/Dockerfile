FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev \
    && docker-php-ext-install sockets bcmath \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install pcov \
    && docker-php-ext-enable pcov

COPY conf.d /usr/local/etc/php/conf.d

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


WORKDIR /app
