FROM nginx:alpine

RUN echo "http://dl-2.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories \
    # echo "http://dl-3.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories; \
    # echo "http://dl-4.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories; \
    # echo "http://dl-5.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories \
    && apk update \
    && apk add --no-cache --virtual .build-deps \
    shadow \
    && mkdir -p /var/www/html \
    # && chown -R nginx:nginx /var/www/html \
    # && usermod -a -G root nginx \
    # && usermod -u 1000 nginx \
    # && chmod -R 2775 /var/www/html \
    && chown -R nginx:nginx /var/www/html \
    # && chown -R root:root /var/www/html \
    # && sed -ri 's/^nginx:x:100:101:/nginx:x:1000:50:/' /etc/passwd \
    && apk del .build-deps