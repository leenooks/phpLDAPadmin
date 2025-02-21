FROM dunglas/frankenphp:php8.4-alpine

# Base
RUN apk add --no-cache bash

# Additional extensions:
RUN install-php-extensions \
    ldap \
    igbinary msgpack memcached

# Tune PHP
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
	&& sed -i -e 's/^memory_limit = 128M/memory_limit = 256M/' /usr/local/etc/php/php.ini \
	&& sed -i -e 's/^expose_php = On/expose_php = Off/' /usr/local/etc/php/php.ini

# Support for LDAPS queries
RUN echo "TLS_REQCERT never" >> /etc/openldap/ldap.conf

# Add composer
RUN curl -4 https://getcomposer.org/installer|php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_HOME=/var/cache/composer

ENV SITE_USER=www-data

# Startup for ${SITE_USER}
COPY docker/init-docker /sbin/init-docker
RUN chmod 550 /sbin/init-docker && chown ${SITE_USER}:0 /sbin/init-docker && chown -R ${SITE_USER}:0 ${XDG_DATA_HOME} ${XDG_CONFIG_HOME}

COPY . /app

RUN mkdir -p ${COMPOSER_HOME} \
	&& ([ -r auth.json ] && mv auth.json ${COMPOSER_HOME}) || true \
	&& touch .composer.refresh \
	&& mv .env.example .env \
	&& FORCE_PERMS=1 BUILD=1 /sbin/init-docker \
	&& rm -rf ${COMPOSER_HOME}/* composer.lock

WORKDIR /app
USER ${SITE_USER}

# Control which port to open
ENV SERVER_NAME=:8080
EXPOSE 8080

ENTRYPOINT [ "/sbin/init-docker" ]
CMD [ "--config","/etc/caddy/Caddyfile","--adapter","caddyfile" ]
