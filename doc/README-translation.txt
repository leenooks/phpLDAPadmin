README-translation
==================
$Header: /cvsroot/phpldapadmin/phpldapadmin/doc/README-translation.txt,v 1.2 2004/02/29 19:59:06 i18phpldapadmin Exp $

This readme is for translators.
phpLDAPadmin support the languages

* en, of course
* de, german
* es, spanish
* fr, french
* it, italien
* nl, netherland
* pl, polish
* pt-br, portuguese (brazilian)
* ru, russian
* sv, swedish


Where are the files located?
All files are unter

  phpldapadmin/lang/


How are the files named?

Every language is named by its local representing. For example english en and 
british english by en_GB but here we use only en.


Is the location phpldapadmin/lang/ used in the application?

No, there is a Makefile in phpldapadmin/lang/ that converts the
native encoding of the language file to utf8 into the directory 
phpldapadmin/lang/recoded. For example the file
phpldapadmin/lang/de.php is converted via the programm iconv to the
the encoding utf8 to the file phpldapadmin/lang/recoded/de.php.
 

Is there a rule for the form of the translation?

* Yes, all translation is stored in an array called lang[].
* The "mother" of all translation is english (en.php).
* Use your native encoding like iso8859-1 for european
  or iso8859-2 for polish.
* Every translation is in single quote "'"
* Don't use html-code in the translation.
* If something should be highlighted we use double quote
  '"'.

Why shouldn't I use html-code?

* No problemens wich htmlspecialchars
* No JavaScript problems
* Open way for other targets like xml or other (only as a idea)
* No problem with "wellformed" output (maybe)

For example the "&gt;" is then convert to "&amp;gt;" that we don't
want, so it is better to use ">". If we have a Char like "&" that is
in the used functions convert to "&amp;" what is correct.

How could I start?
* First, the base for translation is the cvs-Version. 
  Checkout the cvs-Version and start your translation.
* Create a file that contains your translation.
  For me the easiest way was to copy the file phpldapadmin/lang/en.php
  to the phpldapadmin/lang/[new-langage].php
  That gives the way to put the "original" translation to the "end"
  as a comment. Look at the de.php and you know what I mean.
* Modify the Makefile that your langugage is also convert.

How could I see how complete the translation is?
The phpLDAPadmin contains the file phpldapadmin/check_lang_files.php
Open it in your browser and you see how complete your translation is.

* extra entry: if entry is not in the en.php, maybe the value was
               changed in en.php or you type in a wrong key.
* missing entry: the entry is missing in the translated langugage

What is zz.php and the zzz.php in the phpldapadmin/lang/?
Well that is not really a language. That is only for developers
and translators.

The zz.php replace all characters in the lang[] to Z. That helps
in finding hardcoding translation in the the source.

The ZZZ.php helps you to find the used "key". 

How could I enable the zz and zzz language?
Well, one is to hardcode it in the config.php file. That is not the
best way - but the way that always works.

Mozilla Users do like this:
 * from Menu
   Edit->Preferences
 * Option Navigator->Lanugages
    Klick the button "add" and type into "Other" the
    language "zz"
 * With Move up / Move down you can change your priority.
 * With the Button "OK" you can activate your choice. 

Do the same if you want to activate/test your translation.