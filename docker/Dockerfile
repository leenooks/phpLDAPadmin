FROM registry.dege.au/leenooks/php:8.1-fpm-alpine-ldap

COPY . /var/www/html/

RUN mkdir -p ${COMPOSER_HOME} && \
	([ -r auth.json ] && mv auth.json ${COMPOSER_HOME}) || true && \
	touch .composer.refresh && \
	mv .env.example .env && \
	FORCE_PERMS=1 NGINX_START=FALSE /sbin/init && \
	rm -rf ${COMPOSER_HOME}/*
