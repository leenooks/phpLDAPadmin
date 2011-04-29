#!/bin/bash
#
# Makefile Utility to manipulate PO and POT files
# Copyright (c) 2009 Alessandro De Zorzi - <lota@nonlontano.it>
#
# This code is part of phpLDAPadmin
# http://phpldapadmin.wiki.sourceforge.net
# Released under the same licence of phpldapadmin
#
# Current target list:
#
# all		display target
# all-mo	compile .mo files from .po translation available
# pot		create a update main POT file
# all-merge	merge last POT with each current translations (re-create POT)
# all-merge-pot	merge last POT with each current translations

LOCALEDIR = ../../locale
TEMPLATEFILES=`find ../../templates -iname *xml -exec echo -m {} \;|grep -v /test_|grep -v /custom_`
EXPORTFILE = $(shell echo $${EXPORTFILE:-/tmp/launchpad-export.tar.gz})

all:
	@echo Please, specify a target [pot, xml-pot, all-mo, all-merge, all-merge-pot, launchpad-export]

all-mo:
	@for i in `ls -1 $(LOCALEDIR)` ; \
	do \
	    if [ -f $(LOCALEDIR)/$$i/LC_MESSAGES/messages.po ]; then \
	    echo Processing: $$i ; \
	    msgfmt -v -c --statistics $(LOCALEDIR)/$$i/LC_MESSAGES/messages.po -o $(LOCALEDIR)/$$i/LC_MESSAGES/messages.mo; \
	    fi \
	done


pot:
	@po4a-gettextize -o tags="<title> <description> <display> <hint>" -o tagsonly=1 -f xml $(TEMPLATEFILES) -p messages.pot
	@find ../../ -name *.php -exec xgettext --keyword=_ -L PHP -j --omit-header -o messages.pot -s {} \;
	@find ../../ -name *.inc -exec xgettext --keyword=_ -L PHP -j --omit-header -o messages.pot -s {} \;
	@echo messages.pot created, you might like to change the header with contents of messages.header

all-merge: pot
all-merge-pot:
	@for i in `ls -1 $(LOCALEDIR)` ; \
	do \
	    if [ -f $(LOCALEDIR)/$$i/LC_MESSAGES/messages.po ]; then \
	    echo Processing: $$i ; \
	    msgmerge -v $(LOCALEDIR)/$$i/LC_MESSAGES/messages.po messages.pot -o $$i.po; \
	    mv $$i.po $(LOCALEDIR)/$$i/LC_MESSAGES/messages.po; \
	    fi \
	done

launchpad-extract:
	@[ ! -r $(EXPORTFILE) ] && echo "No export file [$(EXPORTFILE)] found?" && exit 1 || true
	@cd $(LOCALEDIR); tar xzf $(EXPORTFILE)
	@rm -f $(LOCALEDIR)/phpldapadmin/phpldapadmin-uk.po
	@rm -f $(LOCALEDIR)/phpldapadmin/phpldapadmin.pot
	@find $(LOCALEDIR) -name phpldapadmin-\*.po | while read i; do newi=$$(echo $$i| sed -r 's/phpldapadmin-(.*).po/..\/\1\*\/LC_MESSAGES\/messages.po/'); mv $$i $$newi; done
	rmdir $(LOCALEDIR)/phpldapadmin

launchpad-export: launchpad-extract all-mo

launchpad-import:
	@[ -d /tmp/pla ] && rmdir /tmp/pla
	@mkdir /tmp/pla
	@for i in $(LOCALEDIR)/*/LC_MESSAGES/messages.po; do echo $$i; x=$${i##$(LOCALEDIR)/}; x=$${x/_??\/LC_MESSAGES\/messages/}; [ ! -e /tmp/pla/$$x ] && cp $$i /tmp/pla/$$x || echo "$$x Already exists!"; done
