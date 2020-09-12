# phpLDAPadmin
phpLDAPadmin is a web based LDAP data management tool for system administrators. It is commonly known and referred by many as "PLA".

A primary goal of PLA is to be as intuitive as possible - so it is certainly possible for end users to use it as well, for example, to manage their data in an LDAP server.

PLA is designed to be compliant with LDAP RFCs, enabling it to be used with any LDAP server.
If you come across an LDAP server, where PLA exhibits problems, please open an issue with full details of the problem so that we can have it fixed.

## History
Initially created in 2002 by David Smith, it was taken over by Deon George (aka leenooks) in 2005.

Since 2003 many things have changed - initial development was done in CVS and the project was hosted on Sourceforge.
In 2009, CVS was swapped out for GIT, and in around 2011 the project was moved to Github.

The PLA v1.2.x stream was created in July 2009.

Work on PLA v2 has started and some information on that is below. Soon `master` will be updated and `BRANCH-2.0` will be visible in git. Until then, a sneak peak of v2 is available [here](https://phpldapadmin.servio.leenooks.net)

## THANK YOU
Over the years, many, many, many people have supported PLA with either their time, their coding or with financial donations.
I have tried to send an email to acknowledge each contribution, and if you havent seen anything personally from me, I am sorry, but please know that I do appreciate all the help I get, in whatever form it is provided.

Again, Thank You.

## Future
Web development, tools, approaches and technology has come along way since 2009 and some talented folks have created some fantastic tools.
With that PLA is going under a major revamp in preparation for v2 and will aim to use those existing creations to help speed up the revamp effort.

Some of the creations planned to be used in v2 include:
* Laravel (https://laravel.com)
* adldap2/adldap2 (https://github.com/Adldap2/Adldap2)
* JQuery (https://jquery.com)
* FancyTree (https://github.com/mar10/fancytree)
* ArchitectUI (https://architectui.com)

PLA v1.2.x will be archived into [BRANCH-1.2](https://github.com/leenooks/phpLDAPadmin/tree/BRANCH-1.2), and `master` will be changed to reflect the new v2 work and effort.

If you plan to use PLA, and cannot use an installation from your OS package, please use [BRANCH-1.2](https://github.com/leenooks/phpLDAPadmin/tree/BRANCH-1.2) while progress is made in master for v2.

If you like the cutting edge, feel free to try out `master`, but expect problems, bugs and missing functionality.
If you have extended v2 and would like to contribute your extension, or if you find a way to fix something that is broken or missing please submit a pull request.

Alternatively, you can get take a peek at the work so far by using our docker container, which is built automatically after testing passes.
The [demo](http://demo.phpldapadmin.org) site, will also be running the same docker container. (See below for details.)

In summary, for the time being, expect `master` to be buggy and broken, and I'll update this readme as enhancements progress.

## Installation
The following instructions will be for PLA v2 when its commited to GIT. Checkback regularly, as it will be pushed when its is semi functional.

### Installation on your server

#### Prerequisites
* A HTTP server (eg: Apache, Nginx)
* PHP (minimum version 7.2) https://www.php.net
* Composer https://getcomposer.org
* GIT

#### Installation
1. Checkout the code from github
   ```bash
   git checkout https://github.com/leenooks/phpLDAPadmin.git
   ```

1. Install composer dependencies.
   ```bash
   composer install
   ```

1. Edit your `.env` file as appropriate

   copy `.env.example` to `.env` as a start.

1. Configure your webserver to have PLA's root in the `public` directory

### Using Docker
Instructions to come.

## Getting Help
The best place to get help with PLA (new and old) is on Stack Overflow (https://stackoverflow.com/tags/phpldapadmin/info)

## Found a bug?
If you have found a bug, and can provide detailed instructions so that it can be reproduced, please open an [issue](https://github.com/leenooks/phpLDAPadmin/issues) and provide those details.

Before opening a ticket, please check to see if it hasnt already been reported, and if it has, please provide any additional information that will help it be fixed.

*TIP*: Issues opened with reproducible details accompanied with a patch (or a pull request) to fix the problem will be looked at first.

## License
[LICENSE](LICENSE)
