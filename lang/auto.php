<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lang/auto.php,v 1.8 2004/03/19 20:13:09 i18phpldapadmin Exp $

// Language for auto-detect
// phpldapadmin/lang/auto.php in $Revision: 1.8 $
$useLang="en"; // default use english encoding, a Option in Config would be nice

// keep the beginning and ending spaces, they are used for finding the best language
$langSupport=array(" ca "=>"ca" // catalan 
		   ," ca-"=>"ca" // 
		   ," de "=>"de" // german
		   ," de-"=>"de" // for de-at, de-ch...
		   ," German "=>"de" // the browser Moz (1.5)submit German instead of de
		   ," en "=>"en" // englisch
		   ," en-"=>"en" // for en-us,en-gb,en-ca,..
		   ," es "=>"es" // spainish
		   ," es-"=>"es" // es-cr, es-co,....
		   ," fr "=>"fr" // french
		   ," fr-"=>"fr" // fr-lu,fr-ca,...
		   ," it "=>"it" // italien
		   ," it-"=>"it" // for it-ch (italien swiss)..
		   ," nl "=>"nl" // dutch 
		   ," nl-"=>"nl" // for ne-be, only one? 
		   ," pl "=>"pl" // polish 
		   ," pl-"=>"pl" // maybe exist 
		   ," pt "=>"pt-br" //  brazilian portuguese   
		   ," pt-br"=>"pt-br" // brazilian portuguese  
		   ," ru "=>"ru" // russian
		   ," ru-"=>"ru" // ru- exits?
		   ," sv "=>"sv" //swedish 
                   ," sv-"=>"sv" // swedisch to
		  );// all supported languages in this array
// test 

$aHTTP_ACCEPT_LANGUAGE=" ".$HTTP_ACCEPT_LANGUAGE." ";
$aHTTP_ACCEPT_LANGUAGE=strtr($aHTTP_ACCEPT_LANGUAGE,","," ");// replace , with " "
$aHTTP_ACCEPT_LANGUAGE=strtr($aHTTP_ACCEPT_LANGUAGE,";"," ");// replace , with " "
$acceptMaxPos=strlen($aHTTP_ACCEPT_LANGUAGE);// initial value, no fit
//echo $aHTTP_ACCEPT_LANGUAGE."\n";
foreach ($langSupport as $key=>$value) {
  $acceptAktPos=strpos($aHTTP_ACCEPT_LANGUAGE,$key);
  if ($acceptAktPos!==false // the test contained the substring 
      && ($acceptAktPos < $acceptMaxPos) // and is better than the one before
      ) { $useLang=$value ; $acceptMaxPos=$acceptAktPos;}
  // echo "$key=>$value:$acceptAktPos,$acceptMaxPos\n";
}
//echo "used:$useLang\n";
include realpath ("$useLang".".php");// this should include from recode/ position
$language=$useLang;
//echo "language:".$langugage;
?>
