FROM alpine:3.8

RUN apk add --update curl \
    php7 \
    php7-cli \
    php7-curl \
    php7-openssl \
    php7-json \
    php7-fpm \
    php7-pdo \
    php7-mysqli \
    php7-mbstring \
    php7-gd \
    php7-dom \
    php7-xml \
    php7-posix \
    php7-intl \
    php7-apcu \
    php7-phar \
    php7-zlib \
    php7-fileinfo \
    php7-simplexml \
    php7-tokenizer \
    php7-xmlwriter \
    php7-bz2 \
    php7-ctype \
    php7-session \
    php7-pdo_mysql \
    php7-iconv \
    acl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

EXPOSE 9000

CMD ["/usr/sbin/php-fpm7", "--nodaemonize"]