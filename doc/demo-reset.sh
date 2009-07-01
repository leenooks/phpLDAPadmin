#!/bin/sh

for i in $(grep directory phpldapadmin-demo.conf|awk '{print $2}'); do
	rm -f $i/*.dbb $i/*.bdb $i/__db.??? $i/alock $i/log.*
done

slapadd -b "dc=example.com" -l ldif-example.com
slapadd -b "dc=example,dc=com" -l ldif-example-com
slapadd -b "o=Simpsons" -l ldif-Simpsons

for i in $(grep directory phpldapadmin-demo.conf|awk '{print $2}'); do
	chown -R ldap:ldap $i
done
