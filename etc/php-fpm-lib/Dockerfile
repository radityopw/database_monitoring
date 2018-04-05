FROM php:7.1-fpm-alpine3.4

RUN apk add --no-cache \
    bzip2-dev \
    imagemagick-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    icu-dev \
    libmemcached-dev \
    postgresql-dev \
    krb5-dev \
    libc-dev \
    c-client \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    imap-dev \
    coreutils \
    libxml2-dev \
    curl-dev \
    libtool \
    cyrus-sasl-dev \
    # && docker-php-ext-configure \
    # pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install \
    bz2 \
    iconv \
    mbstring \
    mysqli \
    pdo \
    pgsql \
    pdo_mysql \
    pdo_pgsql \
    soap \
    zip \
    && docker-php-ext-configure gd \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# COPY ./xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
