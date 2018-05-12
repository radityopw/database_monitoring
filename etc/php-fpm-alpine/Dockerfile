FROM php:7.1-fpm-alpine3.4

# # Environment variables
# ENV WKHTMLTOX_VERSION=0.12.4

# # Copy patches
# RUN mkdir -p /tmp/patches
# COPY conf/* /tmp/patches/

# # Install needed packages
# RUN apk add --no-cache \
#     libstdc++ \
#     libx11 \
#     libxrender \
#     libxext \
#     libssl1.0 \
#     ca-certificates \
#     fontconfig \
#     freetype \
#     ttf-dejavu \
#     ttf-droid \
#     ttf-freefont \
#     ttf-liberation \
#     ttf-ubuntu-font-family \
#     && apk add --no-cache --virtual .build-deps \
#     g++ \
#     git \
#     gtk+ \
#     gtk+-dev \
#     make \
#     mesa-dev \
#     openssl-dev \
#     patch 

# # Download source files
# RUN git clone --recursive https://github.com/wkhtmltopdf/wkhtmltopdf.git /tmp/wkhtmltopdf \
#     && cd /tmp/wkhtmltopdf \
#     && git checkout tags/$WKHTMLTOX_VERSION 

# # Apply patches
# RUN cd /tmp/wkhtmltopdf/qt \
#     && patch -p1 -i /tmp/patches/qt-musl.patch \
#     && patch -p1 -i /tmp/patches/qt-musl-iconv-no-bom.patch \
#     && patch -p1 -i /tmp/patches/qt-recursive-global-mutex.patch \
#     && patch -p1 -i /tmp/patches/qt-font-pixel-size.patch \
#     && patch -p1 -i /tmp/patches/qt-gcc6.patch \
#     \
#     # Modify qmake config
#     && sed -i "s|-O2|$CXXFLAGS|" mkspecs/common/g++.conf \
#     && sed -i "/^QMAKE_RPATH/s| -Wl,-rpath,||g" mkspecs/common/g++.conf \
#     && sed -i "/^QMAKE_LFLAGS\s/s|+=|+= $LDFLAGS|g" mkspecs/common/g++.conf \
#     \
#     # Prepare optimal build settings
#     && NB_CORES=$(grep -c '^processor' /proc/cpuinfo) \
#     \
#     # Install qt
#     && ./configure -confirm-license -opensource \
#     -prefix /usr \
#     -datadir /usr/share/qt \
#     -sysconfdir /etc \
#     -plugindir /usr/lib/qt/plugins \
#     -importdir /usr/lib/qt/imports \
#     -silent \
#     -release \
#     -static \
#     -webkit \
#     -script \
#     -svg \
#     -exceptions \
#     -xmlpatterns \
#     -openssl-linked \
#     -no-fast \
#     -no-largefile \
#     -no-accessibility \
#     -no-stl \
#     -no-sql-ibase \
#     -no-sql-mysql \
#     -no-sql-odbc \
#     -no-sql-psql \
#     -no-sql-sqlite \
#     -no-sql-sqlite2 \
#     -no-qt3support \
#     -no-opengl \
#     -no-openvg \
#     -no-system-proxies \
#     -no-multimedia \
#     -no-audio-backend \
#     -no-phonon \
#     -no-phonon-backend \
#     -no-javascript-jit \
#     -no-scripttools \
#     -no-declarative \
#     -no-declarative-debug \
#     -no-mmx \
#     -no-3dnow \
#     -no-sse \
#     -no-sse2 \
#     -no-sse3 \
#     -no-ssse3 \
#     -no-sse4.1 \
#     -no-sse4.2 \
#     -no-avx \
#     -no-neon \
#     -no-rpath \
#     -no-nis \
#     -no-cups \
#     -no-pch \
#     -no-dbus \
#     -no-separate-debug-info \
#     -no-gtkstyle \
#     -no-nas-sound \
#     -no-opengl \
#     -no-openvg \
#     -no-sm \
#     -no-xshape \
#     -no-xvideo \
#     -no-xsync \
#     -no-xinerama \
#     -no-xcursor \
#     -no-xfixes \
#     -no-xrandr \
#     -no-mitshm \
#     -no-xinput \
#     -no-xkb \
#     -no-glib \
#     -no-icu \
#     -nomake demos \
#     -nomake docs \
#     -nomake examples \
#     -nomake tools \
#     -nomake tests \
#     -nomake translations \
#     -graphicssystem raster \
#     -qt-zlib \
#     -qt-libpng \
#     -qt-libmng \
#     -qt-libtiff \
#     -qt-libjpeg \
#     -optimized-qmake \
#     -iconv \
#     -xrender \
#     -fontconfig \
#     -D ENABLE_VIDEO=0 \
#     && make --jobs $(($NB_CORES*2)) --silent \
#     && make install 

# # Install wkhtmltopdf
# RUN cd /tmp/wkhtmltopdf \
#     && qmake \
#     && make --jobs $(($NB_CORES*2)) --silent \
#     && make install \
#     && make clean \
#     && make distclean 

# # Uninstall qt
# RUN cd /tmp/wkhtmltopdf/qt \
#     && make uninstall \
#     && make clean \
#     && make distclean \
#     \
#     # Clean up when done
#     && rm -rf /tmp/* \
#     && apk del .build-deps

# on alpine static compiled patched qt headless wkhtmltopdf (47.2 MB)
# compilation takes 4 hours on EC2 m1.large in 2016 thats why binary
COPY wkhtmltopdf /usr/bin

RUN ln -sf /usr/bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf ; \
    apk add --update --no-cache \
    libgcc libstdc++ libx11 glib libxrender libxext libintl \
    libcrypto1.0 libssl1.0 \
    fontconfig freetype \
    ttf-dejavu ttf-droid ttf-freefont ttf-liberation ttf-ubuntu-font-family


RUN echo http://dl-2.alpinelinux.org/alpine/edge/community/ >> /etc/apk/repositories \
    # && echo http://dl-3.alpinelinux.org/alpine/edge/testing/ >> /etc/apk/repositories \
    && apk update \
    \
    # Main Package
    && apk add --no-cache \
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
    c-client 

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    gtk+ \
    gtk+-dev \
    shadow \
    imap-dev \
    coreutils \
    libxml2-dev \
    curl-dev \
    libtool \
    cyrus-sasl-dev 
# && docker-php-ext-configure \
# pgsql -with-pgsql=/usr/local/pgsql \

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
    zip 

RUN docker-php-ext-configure gd \
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
    # && usermod -a -G root www-data \
    && usermod -u 1000 www-data \
    # && chmod -R 2775 /var/www/html \
    # && chown -R www-data:root /var/www/html \
    # && chown -R root:root /var/www/html \
    # && sed -ri 's/^www-data:x:82:82:/www-data:x:1000:50:/' /etc/passwd \
    && apk del .build-deps 
# && sed -ri 's/^root:x:0:0:/root:x:1000:50:/' /etc/passwd

# COPY ./xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
