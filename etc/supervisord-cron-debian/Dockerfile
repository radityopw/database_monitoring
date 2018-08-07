FROM php:7.2-cli-stretch

# Installing apt-utils
RUN  apt-get update ;\
    apt-get install -y --no-install-recommends \
    apt-utils

# Installing https
RUN apt-get update ; \
    apt-get install -y --no-install-recommends \
    systemd \
    apt-transport-https \
    gnupg \
    vim

# Adding key for sql server extension gpg
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - ; \
    #Download appropriate package for the OS version
    #Choose only ONE of the following, corresponding to your OS version
    # #Debian 8
    # curl https://packages.microsoft.com/config/debian/8/prod.list > /etc/apt/sources.list.d/mssql-release.list
    #Debian 9
    curl https://packages.microsoft.com/config/debian/9/prod.list > /etc/apt/sources.list.d/mssql-release.list

RUN apt-get update ; \
    # Installing Requirements for php extension
    ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
    libmemcached-dev \
    libkrb5-dev \
    libc-client-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    libmagickcore-dev \
    libbz2-dev \
    libpq-dev \
    libxml2-dev \
    libargon2-0-dev \
    libcurl4-openssl-dev \
    libedit-dev \
    libsodium-dev \
    libsqlite3-dev \
    libssl-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    zlib1g-dev \
    # uuid-dev \
    # SQL Server Requirements
    msodbcsql17 \
    locales \ 
    unixodbc \
    odbcinst \
    libgss3 \
    unixodbc-dev

# PHP Extension install
RUN docker-php-ext-install \
    bcmath \
    bz2 \
    iconv \
    mbstring \
    mysqli \
    pdo \
    pgsql \
    pdo_mysql \
    pdo_pgsql \
    soap \
    zip ; \
    # PHP Extension for gd
    docker-php-ext-configure gd \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
    --with-png-dir=/usr/include/ ;\
    docker-php-ext-install gd ;\
    # PHP Extension for intl
    docker-php-ext-configure intl ;\
    docker-php-ext-install intl ;\
    # PHP Extension for imagick
    pecl install imagick ;\
    docker-php-ext-enable imagick ;\
    # PHP Extension for imap
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl ;\
    docker-php-ext-install imap ;\
    # PHP Extension for memcached
    pecl install memcached ;\
    docker-php-ext-enable memcached ;\
    # PHP Extension for mongodb
    pecl install mongodb ;\
    docker-php-ext-enable mongodb ;\
    # PHP Extension for redis
    pecl install redis ;\
    docker-php-ext-enable redis ;\
    # PHP Extension for xdebug
    pecl install xdebug ;\
    docker-php-ext-enable xdebug ;\
    # PHP Extension for sqlsrv
    pecl install sqlsrv ;\
    docker-php-ext-enable sqlsrv ;\
    # PHP Extension for pdo_sqlsrv
    pecl install pdo_sqlsrv ;\
    docker-php-ext-enable pdo_sqlsrv

# Installing recommends
RUN apt-get install -y --no-install-recommends \
    cron \
    supervisor ; \
    rm -rf /var/lib/apt/lists/*

# Copying custom cron
COPY dependency-tool.txt /config/

# Adding user crontab
RUN crontab /config/dependency-tool.txt

# Copying supervisord config
COPY supervisord.conf /etc/supervisord.conf

# Adding crontab first and start supervisord
CMD  /usr/bin/crontab /config/dependency-tool.txt ; /usr/bin/supervisord -n -c /etc/supervisord.conf
