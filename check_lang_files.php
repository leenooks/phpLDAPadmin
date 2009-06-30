
<?php
// phpldapadmin/check_lang_files.php, $Revision: 1.4 $

echo "<html><head><title>phpldapadmin - check of translation</title></head><body>";
echo "<h1>Incomplete or Erroneous Language Files</h1>\n\n";

include realpath( 'lang/en.php' );
$english_lang = $lang;
unset( $lang );
$lang_dir = realpath( 'lang' );
$dir = opendir( $lang_dir );

while( ( $file = readdir( $dir ) ) !== false ) {
	if( ! preg_match( "/\.php$/", $file ) )
		continue;
	if( $file == 'en.php'  // is the mother of all language-files
	    || $file == 'auto.php' // and ignore auto.php
	    ) 
		continue;
	echo "<h2>$file</h2>";
	echo "<ol>";
	unset( $lang );
	$lang = array();
	include realpath( $lang_dir.'/'.$file );
	$has_errors = false;
	foreach( $english_lang as $key => $string ) 
		if( ! isset( $lang[ $key ] ) ) {
			$has_errors = true;
			echo "<li>missing entry: <tt>$key</tt></li>";
		}
	foreach( $lang as $key => $string )
		if( ! isset( $english_lang[ $key ] ) ){
			$has_errors = true;
			echo "<li>extra entry: <tt>$key</tt></li>";
		}
	if( ! $has_errors )
		echo "(No errors)";
	echo "</ol>";
}



echo "</body></html>";

?>
