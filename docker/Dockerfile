FROM php:8.5-zts-alpine AS builder
ARG BUILD_ARCH

RUN apk add --no-cache \
    git curl gcc musl-dev make \
    readline-dev ncurses-dev \
    libxml2-dev openssl-dev sqlite-dev \
    curl-dev oniguruma-dev argon2-dev \
    gnu-libiconv-dev zlib-dev

ARG GO_VERSION=1.27.3
RUN echo "ARCH: [${BUILD_ARCH}]" && curl -sL https://go.dev/dl/go${GO_VERSION}.linux-${BUILD_ARCH}.tar.gz | tar -C /usr/local -xz
ENV PATH="/usr/local/go/bin:/root/go/bin:${PATH}"
ENV GOPATH="/root/go" GOFLAGS="-trimpath"

RUN go install github.com/caddyserver/xcaddy/cmd/xcaddy@latest

RUN printf '/lib\n/usr/lib\n/usr/local/lib\n' > /etc/ld-musl-s390x.path

RUN CGO_ENABLED=1 \
    CGO_CFLAGS="$(php-config --includes)" \
    CGO_LDFLAGS="$(php-config --ldflags) $(php-config --libs)" \
    XCADDY_GO_BUILD_FLAGS="-ldflags '-w -s' -trimpath -tags nobadger,nomysql,nopgx,nowatcher,nomercure,noadmin,noautocert,nolog,nostorage,notlsredirect,nometrics,noprofiling,notracing" \
    xcaddy build \
    --with github.com/dunglas/frankenphp/caddy \
    --with github.com/dunglas/caddy-cbrotli \
    --output /usr/local/bin/frankenphp

FROM php:8.5-zts-alpine

ARG BUILD_VERSION BUILD_REVISION
ENV XDG_CONFIG_HOME=/config XDG_DATA_HOME=/data PHP_INI_DIR=/usr/local/etc/php COMPOSER_HOME=/var/cache/composer SITE_USER=www-data

LABEL org.opencontainers.image.vendor="Deon George"
LABEL org.opencontainers.image.licenses=GPLv2
LABEL org.opencontainers.image.source=https://github.com/leenooks/phpldapadmin
LABEL org.opencontainers.image.title=phpLDAPadmin
LABEL org.opencontainers.image.description="An LDAP Administration Tool"
LABEL org.opencontainers.image.url=https://phpldapadmin.org
LABEL org.opencontainers.image.version=${BUILD_VERSION}
LABEL org.opencontainers.image.revision=${BUILD_REVISION}

COPY --from=builder /usr/local/bin/frankenphp /usr/local/bin/frankenphp
COPY docker/Caddyfile /etc/caddy/Caddyfile

RUN printf '/lib\n/usr/lib\n/usr/local/lib\n' > /etc/ld-musl-s390x.path \
    && mkdir -p /data/caddy /config/caddy

RUN apk update --no-cache \
	&& curl -sSLf https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o /usr/local/bin/install-php-extensions \
	&& chmod +x /usr/local/bin/install-php-extensions

# Additional extensions:
ENV PHPIZE_DEPS="autoconf dpkg-dev dpkg file g++ gcc libc-dev make pkgconf re2c"\
	PHP_CFLAGS=-"fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"\
	PHP_CPPFLAGS=-"fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"\
	PHP_LDFLAGS="-Wl,-O1 -pie"

RUN install-php-extensions igbinary memcached opcache intl ldap msgpack

# xpass required for yescrypt passwords
RUN cd /tmp \
        && apk --no-cache add build-base git perl linux-headers \
        && git clone https://github.com/remicollet/php-xpass.git \
        && export installLibXCrypt_version=4.4.36 \
        && wget https://github.com/besser82/libxcrypt/releases/download/v$installLibXCrypt_version/libxcrypt-$installLibXCrypt_version.tar.xz \
        && tar xJf libxcrypt-$installLibXCrypt_version.tar.xz \
        && cd libxcrypt-$installLibXCrypt_version \
        && ./configure --prefix /usr --disable-werror \
        && make install \
        && docker-php-ext-configure /tmp/php-xpass --with-xpass-dlopen \
        && docker-php-ext-install /tmp/php-xpass \
        && rm -r /tmp/php-xpass /tmp/libxcrypt-$installLibXCrypt_version.tar.xz \
        && apk --no-cache del build-base git perl linux-headers

# Tune PHP
RUN cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini \
	&& sed -i -e 's/^memory_limit = 128M/memory_limit = 256M/' ${PHP_INI_DIR}/php.ini \
	&& sed -i -e 's/^expose_php = On/expose_php = Off/' ${PHP_INI_DIR}/php.ini

# Support for LDAPS queries
RUN echo "TLS_REQCERT never" >> /etc/openldap/ldap.conf

# Add composer
RUN curl -4 https://getcomposer.org/installer|php -- --install-dir=/usr/local/bin --filename=composer

RUN apk add --no-cache bash

# Startup for ${SITE_USER}
COPY docker/init-docker /sbin/init-docker
RUN chmod 550 /sbin/init-docker && chown ${SITE_USER}:0 /sbin/init-docker && chown -R ${SITE_USER}:0 ${XDG_DATA_HOME} ${XDG_CONFIG_HOME}

COPY . /app
WORKDIR /app

RUN mkdir -p ${COMPOSER_HOME} \
	&& ([ -r auth.json ] && mv auth.json ${COMPOSER_HOME}) || true \
	&& touch .composer.refresh \
	&& mv .env.example .env \
	&& chgrp -R ${SITE_USER} /app \
	&& BUILD=1 APP_TIMEZONE=UTC FORCE_PERMS=1 /sbin/init-docker \
	&& chmod g+w /app/.env \
	&& rm -rf ${COMPOSER_HOME}/* composer.lock storage/logs/*.log /tmp/opcache/*

USER ${SITE_USER}

# Control which port to open
ENV SERVER_NAME=:8080
EXPOSE 8080

ENTRYPOINT [ "/sbin/init-docker" ]
CMD [ "frankenphp","run","--config","/etc/caddy/Caddyfile","--adapter","caddyfile" ]
