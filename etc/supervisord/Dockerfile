FROM php:7-alpine

RUN apk --no-cache --update add wget \
    curl \
    git \
    build-base \
    libmemcached-dev \
    libmcrypt-dev \
    libxml2-dev \
    imap-dev \
    zlib-dev \
    autoconf \
    openssl-dev \
    cyrus-sasl-dev \
    krb5-dev \
    libc-dev \
    c-client \
    libtool \
    icu-dev \
    libgsasl-dev \
    imagemagick-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    supervisor && \
    docker-php-ext-install \ 
    mysqli \
    mbstring \
    pdo \
    pdo_mysql \ 
    tokenizer \
    xml && \
    pecl channel-update pecl.php.net && \ 
    pecl install memcached && \ 
    docker-php-ext-enable memcached && \
    pecl install mcrypt-1.0.1 && \
    docker-php-ext-enable mcrypt && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap && \
    pecl install mongodb && \
    docker-php-ext-enable mongodb  && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    pecl install xdebug  && \
    docker-php-ext-enable xdebug && \
    docker-php-ext-configure gd \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
    --with-png-dir=/usr/include/ && \
    docker-php-ext-install gd && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    pecl install imagick && \
    docker-php-ext-enable imagick && \
    rm /var/cache/apk/* && \
    mkdir -p /var/www

COPY supervisord.conf /etc/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisord.conf"]