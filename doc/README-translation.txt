README-translation
==================
$Header: /cvsroot/phpldapadmin/phpldapadmin/doc/README-translation.txt,v 1.3 2004/06/03 12:45:21 uugdave Exp $

This readme is for translators.
phpLDAPadmin currently supports the following languages:

   - en, of course
   - de, german
   - es, spanish
   - fr, french
   - it, italien
   - nl, netherland
   - pl, polish
   - pt-br, portuguese (brazilian)
   - ru, russian
   - sv, swedish

* Where are the files located?

All files are in the directory:

  phpldapadmin/lang/

* How are the files named?

Every language is named by its local representation. For example English is "en" and 
British English is "en_GB" (though phpLDAPadmin does not have an "en_GB" translation).

* Is the location phpldapadmin/lang/ used in the application?

No, there is a Makefile in phpldapadmin/lang/ that converts the
native encoding of the language file to utf8 into the directory 
phpldapadmin/lang/recoded. For example the file
phpldapadmin/lang/de.php is converted via the programm iconv to the
the encoding utf8 to the file phpldapadmin/lang/recoded/de.php.

* Is there a rule for the form of the translation?

Yes, all translation is stored in an array called lang[].
The "mother" of all translation is english (en.php).
Use your native encoding like iso8859-1 for european
or iso8859-2 for polish.
Every translated string is in single quotes "'"
Don't use html-code in the translation.
If you need to enclose text in quotes, use a double quote '"' (no escaping required).

* Why shouldn't I use html-code?

To avoid problemens wich htmlspecialchars (which coverts "<" to "&lt;", for example).
To avoid JavaScript problems.
To keep the way open for other targets like xml.
To keep the output well formed.

* How could I start?

First, the base for translation is the CVS version. 
Checkout the CVS version and start your translation.
Create a file that contains your translation.
For me the easiest way was to copy the file phpldapadmin/lang/en.php
to the phpldapadmin/lang/[new-langage].php
That gives the way to put the original translation at the end
as a comment. Look at the de.php and you can see what I mean.
Add a target to Makefile so that your langugage is also converted.

* How could I verify that my translation is complete?

phpLDAPadmin contains the file phpldapadmin/check_lang_files.php
Open it in your browser and it will tell you if your lang file has any 
omissions or extraneous strings.

  - extra entries: if entry is not in the en.php, maybe the value was
                   changed in en.php or you type in a wrong key.
  - missing entries: the entry is present in en.php but is missing in 
                     the translated langugage.

* What is zz.php and the zzz.php in the phpldapadmin/lang/ directory?

Well that is not really a language. That is only for developers
and translators to make sure that all strings are translated in the
application.

The zz.php replace all characters in the lang[] to Z. That helps
in finding hardcoding translation in the the source.

The ZZZ.php helps you to find the used "key". 

* How could I enable the zz and zzz language?

Well, one is to set $language to 'zz' or 'zzz' in the config.php file. That is not the
best way - but the way that always works.

Mozilla users do this:
 * Click Edit->Preferences
 * Option Navigator->Lanugages
    Klick the button "add" and type into "Other" the
    language "zz"
 * With Move up / Move down you can change your priority.
 * With the Button "OK" you can activate your choice. 

Do the same if you want to activate/test your translation.
