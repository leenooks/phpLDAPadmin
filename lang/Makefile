# 
# This Makefile recodes source lang files into HTML Unicode.
# You should add your encodings to this file.  You need to have GNU Recode
# installed.
#
# It is important to fix first and last lines of the file <?php and ?>
# using 'awk'
#
DESTDIR=./recoded
TARGETS=${DESTDIR}/ct.php \
		${DESTDIR}/de.php \
		${DESTDIR}/en.php \
		${DESTDIR}/es.php \
		${DESTDIR}/fr.php \
		${DESTDIR}/it.php \
		${DESTDIR}/nl.php \
		${DESTDIR}/ru.php

all:	prepare ${TARGETS} syntax
	@echo "Setting permissions..."
	@chmod 644 ${TARGETS}
	@echo "Done!"

syntax:
	@for i in ${TARGETS}; do php -l $$i >/dev/null 2>&1 || ( echo "Syntax errors found in $$i!" && exit 1 ); done

prepare:
	@which recode >/dev/null 2>&1 || ( echo "You must have GNU 'recode' installed to use this Makefile, but I \
											could not find it in your path!" && exit 1 )
	@mkdir -p ${DESTDIR}
	@chmod 755 ${DESTDIR}

clean: 
	@echo "Blowing away recoded lang files..."
	@rm -vf ${TARGETS}

${DESTDIR}/ct.php:	 ct.php
	@echo "Recoding ct.php..."
	@cat ct.php | recode iso-8859-1..html | \
		awk '{ gsub("&gt;",">"); gsub("&lt;","<"); print $0}' \
		> ${DESTDIR}/ct.php

${DESTDIR}/de.php: 	de.php
	@echo "Recoding de.php..."
	@cat de.php | recode html..html | \
		awk '{ gsub("&gt;",">"); gsub("&lt;","<"); print $0}' \
		> ${DESTDIR}/de.php

${DESTDIR}/en.php:	en.php
	@echo "Recoding en.php..."
	@cat en.php | recode html..html | \
		awk '{ gsub("&gt;",">"); gsub("&lt;","<"); print $0}' \
		> ${DESTDIR}/en.php

${DESTDIR}/es.php:	es.php
	@echo "Recoding es.php..."
	@cat es.php | recode iso-8859-1..html | \
		awk '{ gsub("&gt;",">"); gsub("&lt;","<"); print $0}' \
		> ${DESTDIR}/es.php

${DESTDIR}/fr.php:	fr.php
	@echo "Recoding fr.php..."
	@cat fr.php | recode html..html | \
		awk '{ gsub("&gt;",">"); gsub("&lt;","<"); print $0}' \
		> ${DESTDIR}/fr.php

${DESTDIR}/it.php:	it.php
	@echo "Recoding it.php..."
	@cat it.php | recode latin2..html | \
		awk '{ gsub("&gt;",">"); gsub("&lt;","<"); print $0}' \
		> ${DESTDIR}/it.php

${DESTDIR}/nl.php:	nl.php
	@echo "Recoding nl.php..."
	@cat nl.php | recode latin1..html | \
		awk '{ gsub("&gt;",">"); gsub("&lt;","<"); print $0}' \
		> ${DESTDIR}/nl.php

${DESTDIR}/ru.php:	ru.php
	@echo "Recoding ru.php..."
	@cat ru.php | recode utf-8..html | \
		awk '{ gsub("&gt;",">"); gsub("&lt;","<"); print $0}' \
		> ${DESTDIR}/ru.php

