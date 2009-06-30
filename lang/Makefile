# 
# This Makefile (lang/Makefile) converts the source lang files to UTF8
# coding. You need iconv installed to use it.
# $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/Makefile,v 1.19 2004/05/04 19:10:29 i18phpldapadmin Exp $
# ToDo: detect the encoding in the "source"
#
# posible detect of the language
#  echo `cat de.php | grep "logged_in_as" | sed s/".*="//g | file - | sed s/".*: "//g | sed s/" .*"//g`
#
# or 
# 
# maybe like this
#  cat LANG.php | sed s/"^\/"//g | sed s/".*= "//g  | sed s/";.*"//g | grep -v "<"| file - | sed s/".*: "//g | sed s/" .*"//g      

# Where to place the recoded language files
DESTDIR=./recoded
# Path to the iconv binary
ICONV=iconv
# Path to the PHP binary
PHP=php

TARGETS=${DESTDIR}/auto.php \
		${DESTDIR}/ca.php \
		${DESTDIR}/cz.php \
		${DESTDIR}/de.php \
		${DESTDIR}/en.php \
		${DESTDIR}/es.php \
		${DESTDIR}/fr.php \
		${DESTDIR}/it.php \
		${DESTDIR}/nl.php \
		${DESTDIR}/pl.php \
		${DESTDIR}/pt-br.php \
		${DESTDIR}/ru.php \
		${DESTDIR}/sv.php \
	        ${DESTDIR}/zh-tw.php \
		${DESTDIR}/zz.php \
		${DESTDIR}/zzz.php

default: 
	@echo "usage:"
	@echo " make iconvlang - to generate the lang coding"
	@echo " make iconvclean - to remove the utf8-converted file in  ${DESTDIR}"

iconvlang:	prepare ${TARGETS} syntax
	@echo "Setting permissions..."
	@chmod 644 ${TARGETS}
	@echo "Done!"

syntax: 
	@echo "Starting syntax checking..."
	@which $(PHP) >/dev/null 2>&1 || ( echo "You must have '$(PHP)' installed to use this Makefile, but I could not find it in your path!" && exit 1 )
	@for i in ${TARGETS}; do ${PHP} -l $$i >/dev/null 2>&1 || ( echo "Syntax errors found in $$i!" && exit 1 ); done
	@echo "Done"

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

${DESTDIR}/cz.php: 	cz.php
	@echo "Fixing encoding cz.php to UTF8 "${DESTDIR}/cz.php
	@iconv -f windows-1250 -t utf8 cz.php > ${DESTDIR}/cz.php


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

${DESTDIR}/pl.php: 	pl.php
	@echo "Fixing encoding pl.php to UTF8 "${DESTDIR}/pl.php
	@iconv -f iso8859-2 -t utf8 pl.php > ${DESTDIR}/pl.php

${DESTDIR}/pt-br.php: 	pt-br.php
	@echo "Fixing encoding pt-br.php to UTF8 "${DESTDIR}/pt-br.php
	@iconv -f iso8859-1 -t utf8 pt-br.php > ${DESTDIR}/pt-br.php

${DESTDIR}/sv.php: 	sv.php
	@echo "Fixing encoding sv.php to UTF8 "${DESTDIR}/sv.php
	@iconv -f iso8859-1 -t utf8 sv.php > ${DESTDIR}/sv.php

${DESTDIR}/ru.php: 	ru.php
	@echo "Fixing encoding ru.php to UTF8 "${DESTDIR}/ru.php
	@iconv -f utf8 -t utf8 ru.php > ${DESTDIR}/ru.php

${DESTDIR}/zh-tw.php:	zh-tw.php
	@echo "Copying only the zz.php"
        # 	@iconv -f utf8 -t utf8 zh-tw.php ${DESTDIR}/zh-tw.php
        # INTERNAL BUG COULDN CONVERT IT, SO WE COPY IT
	cp zh-tw.php ${DESTDIR}/zh-tw.php
${DESTDIR}/zz.php:	zz.php
	@echo "Copying only the zz.php"
	@cp zz.php ${DESTDIR}/zz.php

${DESTDIR}/zzz.php:	zzz.php
	@echo "Copying only the zzz.php"
	@cp zzz.php ${DESTDIR}/zzz.php

