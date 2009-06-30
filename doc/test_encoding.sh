#!/bin/sh
# $Header: /cvsroot/phpldapadmin/phpldapadmin/doc/test_encoding.sh,v 1.1 2005/02/06 00:37:15 wurley Exp $
# $Id: test_encoding.sh,v 1.1 2005/02/06 00:37:15 wurley Exp $
# Written by: Daniel van Eeden <daniel_e@dds.nl>
# Purpose: test utf-8 encoding

for file in `find . -type f ! -name \*png ! -name \*jpg | egrep -v "^./lang/"`
do
  output=`iconv ${file} -o /dev/null 2>&1`
  err=$?
  output=`echo ${output} | cut -d: -f2`
  if [ ${err} != "0" ]; then
    echo "${file}:${output}"
  fi
done
