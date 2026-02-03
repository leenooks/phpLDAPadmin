# phpLDAPadmin
![GitHub commit activity](https://img.shields.io/github/commit-activity/m/leenooks/phpldapadmin)
![Docker Pulls](https://img.shields.io/docker/pulls/phpldapadmin/phpldapadmin)
![GitHub Release Date](https://img.shields.io/github/release-date/leenooks/phpldapadmin)
![GitHub commits since latest release](https://img.shields.io/github/commits-since/leenooks/phpldapadmin/latest)

phpLDAPadmin is a web based LDAP data management tool for system administrators. It is commonly known and referred by many as "PLA".

PLA is designed to be compliant with LDAP RFCs, enabling it to be used with any LDAP server.
If you come across an LDAP server, where PLA exhibits problems, please open an issue with full details of the problem so that we can have it fixed.

For up-to-date information on PLA, please head to the [wiki](https://github.com/leenooks/phpLDAPadmin/wiki).

### Deprecation Note for v1.2x (and earlier)
> PLA v2 is a complete rewrite of PLA.
>
> PLA v1 was written well over 10 years ago for PHP 5, and over time has been patched to work with later versions of PHP. There are logged vulnerabilities with v1 that have not been addressed.
>
> Most PLA v1 functionality has been included in v2 - see below for details of additional work required. If something is missing, open a feature request and it'll be reviewed.
>
> **The release of PHP v2 officially deprecates v1, which is no longer supported or enhanced/fixed.** It is recommended to upgrade to the latest release of v2.x.

## Demo
If you haven't seen PLA in action, you can head here to the [demo](https://demo.phpldapadmin.org) site.

## Translations
PLA v2 can be translated. See [translation](resources/lang).

If you translate PLA, please provide a pull request so that your translations can be included in the next release of PLA for other users who use your language.

Please open a bug report, if there is a text string that is missing from the translation files and code.

## Running PLA
PLA v2 is now available as a docker container. You can also download the code and install it yourself on your PHP server, or even build your own docker container.

Take a look at the [Docker Container](https://github.com/leenooks/phpLDAPadmin/wiki/Docker-Container) page for more details.

> If you come across any bugs/issues, it would be helpful if you could reproduce those issues using the docker container (or the demo website). This should help confirm that there isn't a site related issue with the issue you are having.
>
> Open an issue (details below) with enough information for me to be able to recreate the problem. An `LDIF` will be invaluable if it is not handling data correctly.

## Templates
Starting with v2.2, PLA reintroduces the template engine. Each point release going forward will improve the template 
functionality. Check [releases](https://github.com/leenooks/phpLDAPadmin/releases) for details.

Templates in v2 are in JSON format (in v1 they were XML format). If you want to create your own templates you can use 
the [example.json](templates/example.json) template as a guide. Place your custom templates in a subdirectory
under `templates`, eg: `templates/custom`, and they won't be overwritten by an update.

## Outstanding items
Compare to v1.x, there are a couple of outstanding items to address

Entry Editing:
  - [ ] JpegPhoto Create/Delete
  - [ ] Binary attribute upload
  - [ ] If removing an objectClass, remove all attributes that only that objectclass provided
  - [ ] Group membership selection (* partially implemented)
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
- [X] Apache DS
- [X] OpenBSD ldapd

If there is an LDAP server that you have that you would like to have supported, please open an issue to request it.
You might need to provide access, provide a copy or instructions to get an environment for testing. If you have enabled 
support for an LDAP server not listed above, please provide a pull request for consideration.

## Upgrading
Upgrading PLA from v1 to v2 is a manual upgrade.

When upgrading, you'll need to perform the following:
* Decide how you will deploy PLA, it's easiest to use the [docker container](https://hub.docker.com/r/phpldapadmin/phpldapadmin), but you can also use the GitHub [release](https://github.com/leenooks/phpLDAPadmin/releases)
* Re-do configuration, refer to the [wiki](https://github.com/leenooks/phpLDAPadmin/wiki/Installation-Instructions) for installation instructions.
* Recreate your templates (if you use them) and place them in `templates/custom`. Templates are now in `json` format. (*NOTE: Note all functionality is available in PLA v2*)

## Getting Help
The best place to get help with PLA (new and old) is on [Stack Overflow](https://stackoverflow.com/tags/phpldapadmin/info) or in [Github Discussions](https://github.com/leenooks/phpLDAPadmin/discussions).

## Found a bug?
If you have found a bug, and can provide detailed instructions so that it can be reproduced, please open an [issue](https://github.com/leenooks/phpLDAPadmin/issues) and provide those details.

Before opening a ticket, please check to see if it hasn't already been reported, and if it has, please provide any additional information that will help it be fixed.

*TIP*: Issues opened with:

* details enabling the problem to be reproduced,
* including (if appropriate) an LDIF with the data that exhibits the problem,
* a patch (or a git pull request) to fix the problem

will be looked at first :)

## THANK YOU
Over the years, many, many, many people have supported PLA with either their time, their coding or with financial donations.
I have tried to email acknowledge each contribution, and if you haven't seen anything personally from me, I am sorry, but please know that I do appreciate all the help I get, in whatever form it is provided.

I also appreciate the support of the following organisations supporting open source projects:

* [Docker Hub](https://www.docker.com/community/open-source/application/)
* [JetBrains](https://jb.gg/OpenSource)

Again, Thank You.

## License
[LICENSE](LICENSE)
