<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/zzz.php,v 1.3 2004/03/19 20:13:09 i18phpldapadmin Exp $

// Language zzz only for testing/finding hardcode language
// usefull for i18n for finding the corresponding key-entry
// $Version$
include "en.php";
while (list($key, $value) = each ($lang)) {

     $lang[$key]="~".$key."~";

}
?>
