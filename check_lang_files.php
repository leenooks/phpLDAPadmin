<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/check_lang_files.php,v 1.8 2004/04/02 14:44:46 uugdave Exp $
?>
<?php
// phpldapadmin/check_lang_files.php, $Revision: 1.8 $

echo "<html><head><title>phpldapadmin - check of translation</title></head><body>";

include realpath( './lang/en.php' );
$english_lang = $lang;
unset( $lang );
$lang_dir = realpath( './lang' );
$dir = opendir( $lang_dir );

// First, detect any unused strings from the english language:
echo "<h1>Checking English language file for unused strings</h1>\n";
echo "<ol>\n";
$unused_keys = false;

// special case keys that do not occur hard-coded but are dynamically generated
$ignore_keys['equals'] = 1;
$ignore_keys['starts with'] = 1;
$ignore_keys['ends with'] = 1;
$ignore_keys['sounds like'] = 1;
$ignore_keys['contains'] = 1;
foreach( $english_lang as $key => $string ) {
    if( isset( $ignore_keys[$key] ) )
        continue;
    $grep_cmd = "grep -r \"lang\[['\\\"]$key\" *.php templates/";
    $used = `$grep_cmd`;
    if( ! $used ) {
        $unused_keys = true;
        echo "<li>Unused English key: <tt>$key</tt> <br />&nbsp;&nbsp;&nbsp;&nbsp;(<small><tt>" . htmlspecialchars( $grep_cmd ) . "</tt></small>)</li>\n";
        flush();
    }
}
if( false === $unused_keys )
    echo "No unused English strings.";
echo "</ol>\n";

echo "<h1>Incomplete or Erroneous Language Files</h1>\n\n";
flush();
while( ( $file = readdir( $dir ) ) !== false ) {
    // skip the devel languages, english, and auto
    if( $file == "zz.php" || $file == "zzz.php" || $file == "auto.php" || $file == "en.php" )
        continue;
    // Sanity check. Is this really a PHP file?
	if( ! preg_match( "/\.php$/", $file ) )
		continue;
	echo "<h2>$file</h2>\n";
	echo "<ol>\n";
	unset( $lang );
	$lang = array();
	include realpath( $lang_dir.'/'.$file );
	$has_errors = false;
	foreach( $english_lang as $key => $string ) 
		if( ! isset( $lang[ $key ] ) ) {
			$has_errors = true;
			echo "<li>missing entry: <tt>$key</tt></li>\n";
		}
	foreach( $lang as $key => $string )
		if( ! isset( $english_lang[ $key ] ) ){
			$has_errors = true;
			echo "<li>extra entry: <tt>$key</tt></li>\n";
		}
	if( ! $has_errors )
		echo "(No errors)\n";
	echo "</ol>\n";
}



echo "</body></html>";

?>
