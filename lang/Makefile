# 
# This Makefile (lang/Makefile) converts the source lang files to UTF8
# coding. You need iconv installed to use it.
# 
# ToDo: detect the encoding in the "source"
#
# posible detect of the language
#  echo `cat de.php | grep "logged_in_as" | sed s/".*="//g | file - | sed s/".*: "//g | sed s/" .*"//g`
#
# or 
#
# maybe like this
#  cat LANG.php | sed s/"^\/"//g | sed s/".*= "//g  | sed s/";.*"//g | grep -v "<"| file - | sed s/".*: "//g | sed s/" .*"//g      
#
DESTDIR=./recoded
ICONV=iconv
# php is not on all systems in /usr/bin/php
PHP=php

TARGETS=${DESTDIR}/auto.php \
		${DESTDIR}/ca.php \
		${DESTDIR}/de.php \
		${DESTDIR}/en.php \
		${DESTDIR}/es.php \
		${DESTDIR}/fr.php \
		${DESTDIR}/it.php \
		${DESTDIR}/nl.php \
		${DESTDIR}/ru.php 

default: 
	@echo "usage:"
	@echo " make iconvlang - to generate the lang coding"
	@echo " make iconvclean - to remove the utf8-converted file in  ${DESTDIR}"

iconvlang:	prepare ${TARGETS} syntax
	@echo "Setting permissions..."
	@chmod 644 ${TARGETS}
	@echo "Done!"

syntax: 
	@echo "Starting syntax"
	@which $(PHP) >/dev/null 2>&1 || ( echo "You must have '$(PHP)' installed to use this Makefile, but I could not find it in your path!" && exit 1 )
	@for i in ${TARGETS}; do ${PHP} -l $$i >/dev/null 2>&1 || ( echo "Syntax errors found in $$i!" && exit 1 ); done
	@echo "Ending syntax"

prepare:
	@echo "Starting prepare"
	@which $(ICONV) >/dev/null 2>&1 || ( echo "You must have GNU '$(ICONV)' installed to use this Makefile, but I could not find it in your path!" && exit 1 )
	@mkdir -p ${DESTDIR}
	@chmod 755 ${DESTDIR}

iconvclean:
	@echo "deleting files "${TARGETS}
	@rm -vf ${TARGETS} 
	@echo "now use 'make iconvlang' to encode, otherwise you could not use phpldapadmin"

${DESTDIR}/auto.php: 	auto.php
	@echo "Fixing encoding auto.php to UTF8 "${DESTDIR}/auto.php
	@iconv -f iso8859-1 -t utf8 auto.php > ${DESTDIR}/auto.php


${DESTDIR}/ca.php: 	ca.php
	@echo "Fixing encoding ca.php to UTF8 "${DESTDIR}/ca.php
	@iconv -f iso8859-1 -t utf8 ca.php > ${DESTDIR}/ca.php

${DESTDIR}/de.php: 	de.php
	@echo "Fixing encoding de.php to UTF8 "${DESTDIR}/de.php
	@iconv -f iso8859-1 -t utf8 de.php > ${DESTDIR}/de.php

${DESTDIR}/en.php: 	en.php
	@echo "Fixing encoding en.php to UTF8 "${DESTDIR}/en.php
	@iconv -f iso8859-1 -t utf8 en.php > ${DESTDIR}/en.php

${DESTDIR}/es.php: 	es.php
	@echo "Fixing encoding es.php to UTF8 "${DESTDIR}/es.php
	@iconv -f iso8859-1 -t utf8 es.php > ${DESTDIR}/es.php

${DESTDIR}/fr.php: 	fr.php
	@echo "Fixing encoding fr.php to UTF8 "${DESTDIR}/fr.php
	@iconv -f iso8859-1 -t utf8 fr.php > ${DESTDIR}/fr.php

${DESTDIR}/it.php: 	it.php
	@echo "Fixing encoding it.php to UTF8 "${DESTDIR}/it.php
	@iconv -f iso8859-1 -t utf8 it.php > ${DESTDIR}/it.php

${DESTDIR}/nl.php: 	nl.php
	@echo "Fixing encoding nl.php to UTF8 "${DESTDIR}/nl.php
	@iconv -f iso8859-1 -t utf8 nl.php > ${DESTDIR}/nl.php

${DESTDIR}/ru.php: 	ru.php
	@echo "Fixing encoding ru.php to UTF8 "${DESTDIR}/ru.php
	@iconv -f utf8 -t utf8 ru.php > ${DESTDIR}/ru.php