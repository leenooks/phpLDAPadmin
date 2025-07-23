# phpLDAPadmin
![GitHub commit activity](https://img.shields.io/github/commit-activity/m/leenooks/phpldapadmin)
![Docker Pulls](https://img.shields.io/docker/pulls/phpldapadmin/phpldapadmin)
![GitHub Downloads (all assets, all releases)](https://img.shields.io/github/downloads/leenooks/phpldapadmin/total)
![GitHub Release Date](https://img.shields.io/github/release-date/leenooks/phpldapadmin)
![GitHub commits since latest release](https://img.shields.io/github/commits-since/leenooks/phpldapadmin/latest)

phpLDAPadmin is a web based LDAP data management tool for system administrators. It is commonly known and referred by many as "PLA".

PLA is designed to be compliant with LDAP RFCs, enabling it to be used with any LDAP server.
If you come across an LDAP server, where PLA exhibits problems, please open an issue with full details of the problem so that we can have it fixed.

For up to date information on PLA, please head to the [wiki](https://github.com/leenooks/phpLDAPadmin/wiki).

> **NOTE**
> PLA v2 is a complete rewrite of PLA.
>
> PLA v1.2 was written well over 10 years ago for PHP 5, and over time has been patched to work with later versions of PHP. There are logged vulnerabilities with v1.2 that have not been addressed.
>
> Not all PLA v1.2 functionality has been included in v2 (yet) - see below for details
>
> **The release of PHP v2 officially deprecates v1.2, which is no longer supported or enhanced/fixed.** It is recommended to upgrade to v2.

## Demo
If you havent seen PLA in action, you can head here to the [demo](https://demo.phpldapadmin.org) site.

## Running PLA
PLA v2 is now available as a docker container. You can also download the code and install it yourself on your PHP server, or even build your own docker container.

Take a look at the [Docker Container](https://github.com/leenooks/phpLDAPadmin/wiki/Docker-Container) page for more details.

> If you come across any bugs/issues, it would be helpful if you could reproduce those issues using the docker container (or the demo website). This should help confirm that there isnt a site related issue with the issue you are having.
>
> Open an issue (details below) with enough information for me to be able to recreate the problem. An `LDIF` will be invaluable if it is not handling data correctly.

## Templates
Starting with v2.2, PLA reintroduces the template engine. Each point release going forward will improve the template 
functionality. Check [releases](https://github.com/leenooks/phpLDAPadmin/releases) for details.

Templates in v2 are in JSON format (in v1 they were XML format). If you want to create your own templates you can use 
the [example.json](/templates/example.json) template as a guide. Place your custom templates in a subdirectory
under `templates`, eg: `templates/custom`, and they wont be overwritten by an update.

## Outstanding items
Compare to v1.x, there are a couple of outstanding items to address

Entry Editing:
  - [ ] JpegPhoto Create/Delete
  - [ ] Binary attribute upload
  - [ ] If removing an objectClass, remove all attributes that only that objectclass provided
  - [ ] Move an entry
  - [ ] Group membership selection
  - [ ] Attribute tag creation

Templates Engine
  - [ ] Enforcing attribute uniqueness

Raise a [feature request](https://github.com/leenooks/phpLDAPadmin/issues/new) if there is a capability that you would like to see added to PLA.

Other items [under consideration](https://github.com/leenooks/phpLDAPadmin/issues?q=state%3Aopen%20label%3Aenhancement)

## Support is known for these LDAP servers:
- [X] OpenLDAP
- [X] OpenDJ
- [ ] Microsoft Active Directory
- [X] 389 Directory Server

If there is an LDAP server that you have that you would like to have supported, please open an issue to request it.
You might need to provide access, provide a copy or instructions to get an environment for testing. If you have enabled 
support for an LDAP server not listed above, please provide a pull request for consideration.

## Getting Help
The best place to get help with PLA (new and old) is on [Stack Overflow](https://stackoverflow.com/tags/phpldapadmin/info).

## Found a bug?
If you have found a bug, and can provide detailed instructions so that it can be reproduced, please open an [issue](https://github.com/leenooks/phpLDAPadmin/issues) and provide those details.

Before opening a ticket, please check to see if it hasnt already been reported, and if it has, please provide any additional information that will help it be fixed.

*TIP*: Issues opened with:

* details enabling the problem to be reproduced,
* including (if appropriate) an LDIF with the data that exhibits the problem,
* a patch (or a git pull request) to fix the problem

will be looked at first :)

## THANK YOU
Over the years, many, many, many people have supported PLA with either their time, their coding or with financial donations.
I have tried to send an email to acknowledge each contribution, and if you havent seen anything personally from me, I am sorry, but please know that I do appreciate all the help I get, in whatever form it is provided.

I also appreciate the support of the following organisations supporting open source projects:

* [Docker Hub](https://www.docker.com/community/open-source/application/)
* [JetBrains](https://jb.gg/OpenSource)

Again, Thank You.

## License
[LICENSE](LICENSE)
