<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/recoded/zz.php,v 1.3 2004/03/19 20:13:09 i18phpldapadmin Exp $

// Language zz only for testing/finding hardcode language
// don't use it as default-language you see only ZZZZZ
// $Version$
include "en.php";
while (list($key, $value) = each ($lang)) {

     $lang[$key]=ereg_replace("[[:alpha:]]","Z",$value);

}
?>
