#!/usr/bin/env bash

# convert OpenLDAP schema file to LDIF file
#
# Copyright 2012 NDE Netzdesign und -entwicklung AG, Hamburg
# Written by Jens-U. Mozdzen <jmozdzen@nde.ag>
# Copyright 2014 jaseg <github@jaseg.net>
#
# Permission is granted to use, modify and redistribute this file as long as
# - this copyright notice is left unmodified and included in the final code
# - the original author is notified via email if this code is re-distributed as part of a paid-for deliverable
# - the original author is not held liable for any damage, loss of profit, efforts or inconvenience of any sorts
#   that may result from using, modifying or redistributing this software.
#
# Use at your own risk - this code may not be suitable for your needs or even cause damage when used.
# If you find any problems with this code, please let the author know so that it can be fixed or at least others
# can be warned.
#
# Usage: schema2ldif.sh <fully-qualified schema file name>
# 
# This program will try to convert the source file to an LDIF-style file, placing the resulting .ldif file
# in the current directory.

rc=0

if [ $# -gt 0 ] ; then
	slaptest=$(which slaptest 2>/dev/null ||ls /usr/sbin/slaptest||echo "")
	if [ -x "$slaptest" ] ; then
		schemaFile=$(readlink -f "$1")
		shift
		localdir=$(pwd)

		if [ -r "$schemaFile" ] ; then
			targetFile=$(basename "$schemaFile" .schema).ldif
			if [ ! -e "$localdir/$targetFile" ] ; then
				echo "$0: converting $schemaFile to LDIF $localdir/$targetFile"

				# create temp dir and config file
				tmpDir=$(mktemp -d)
				cd "$tmpDir"
				touch tmp.conf
				for dependency in "$@"
				do	echo "include $dependency" >> tmp.conf
				done
				echo "include $schemaFile" >> tmp.conf

				# convert
				"$slaptest" -f tmp.conf -F "$tmpDir"

				# 3. rename and sanitize
				cd cn\=config/cn\=schema
				filenametmp=$(echo cn\=*"$targetFile")
				sed -r -e  's/^dn: cn=\{0\}(.*)$/dn: cn=\1,cn=schema,cn=config/' \
					-e 's/cn: \{0\}(.*)$/cn: \1/' \
					-e '/^structuralObjectClass: /d' \
					-e '/^entryUUID: /d' \
					-e '/^creatorsName: /d' \
					-e '/^createTimestamp: /d' \
					-e '/^entryCSN: /d' \
					-e '/^modifiersName: /d' \
					-e '/^modifyTimestamp: /d' \
					-e '/^# AUTO-GENERATED FILE - DO NOT EDIT!! Use ldapmodify./d' \
					-e '/^# CRC32 [0-9a-f]+/d' \
					-e 's/^cn: \{[0-9]*\}(.*)$/cn: \1/' \
					-e 's/^dn: cn=\{[0-9]*\}(.*)$/dn: cn=\1,cn=schema,cn=config/' < "$filenametmp" > "$localdir/$targetFile"

				# clean up
				echo "$0: LDIF file successfully created as $localdir/$targetFile"
				rc=0
				rm -rf "$tmpDir"
			else
				echo "$0: target file $localdir/$targetFile already exists, aborting." >&2
				rc=3
			fi
		else
			echo "$0: source file $schemaFile could not be read, aborting." >&2
			rc=2
		fi
	else
		echo "$0: could not locate slaptest binary, exiting." >&2
		rc=1
	fi
else
	echo "$0: usage: $0 <schemafile>" >&2
	rc=99
fi

exit $rc
