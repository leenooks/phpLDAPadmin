FROM dunglas/frankenphp:php8.3-alpine

# Base
RUN apk add --no-cache bash

# Additional extensions:
RUN install-php-extensions \
    ldap \
    memcached

RUN sed -i -e 's/^{$CADDY_EXTRA_CONFIG}$/{$CADDY_EXTRA_CONFIG} /' /etc/caddy/Caddyfile
RUN sed -i -e 's/^memory_limit = 128M/memory_limit = 1G/' /usr/local/etc/php/php.ini-production
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

RUN curl -4 https://getcomposer.org/installer|php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_HOME=/var/cache/composer

ENV SITE_USER=www-data

COPY docker/init-docker /sbin/init-docker
RUN chmod 550 /sbin/init-docker && chown ${SITE_USER}:0 /sbin/init-docker

COPY . /var/www/html/
WORKDIR /var/www/html

RUN mkdir -p ${COMPOSER_HOME} \
	&& ([ -r auth.json ] && mv auth.json ${COMPOSER_HOME}) || true \
	&& touch .composer.refresh \
	&& mv .env.example .env \
	&& FORCE_PERMS=1 BUILD=1 /sbin/init-docker \
	&& rm -rf ${COMPOSER_HOME}/* composer.lock

# Fix start up items
RUN chown ${SITE_USER} /config/caddy /data/caddy

USER ${SITE_USER}

# Control which port to open
ENV SERVER_NAME=:8080
EXPOSE 8080

ENTRYPOINT [ "/sbin/init-docker" ]
CMD [ "--config","/etc/caddy/Caddyfile","--adapter","caddyfile" ]
