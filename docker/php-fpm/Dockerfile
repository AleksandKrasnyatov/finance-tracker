FROM php:8.5-fpm-alpine

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        linux-headers \
    && docker-php-ext-install -j$(nproc) sockets bcmath\
    && apk del .build-deps

WORKDIR /app

CMD ["php-fpm"]
