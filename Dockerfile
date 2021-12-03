FROM registry.leenooks.net/leenooks/php:8.0-fpm-latest

COPY . /var/www/html/

RUN mkdir /var/www/.composer \
	&& ([ -r auth.json ] && mv auth.json /var/www/.composer/) || true \
	&& touch .composer.refresh \
	&& mv .env.example .env \
	&& FORCE_PERMS=1 /sbin/init \
	&& rm -rf /var/www/.composer/*
