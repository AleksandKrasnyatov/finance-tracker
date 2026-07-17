FROM php:8.5-cli-alpine

ENV XDEBUG_VERSION=3.5.3

RUN apk add --no-cache libpq-dev sqlite-dev bash coreutils git linux-headers \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && git clone --branch $XDEBUG_VERSION --depth 1 https://github.com/xdebug/xdebug.git /usr/src/php/ext/xdebug \
    && docker-php-ext-configure xdebug --enable-xdebug-dev \
    && docker-php-ext-install pdo_pgsql pdo_sqlite xdebug \
    && apk del git linux-headers

COPY conf.d /usr/local/etc/php/conf.d

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
