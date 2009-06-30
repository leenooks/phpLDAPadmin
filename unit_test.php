<?php


require_once realpath( 'functions.php' );

$dns1 = array( 'cn=joe,dc=example,dc=com', 'cn=joe,dc=example,dc=com', 'cn = bob, dc= example,dc =com' );
$dns2 = array( 'cn=joe,dc=example,dc=com', 'CN =joe,dc=Example,dc =com', 'cn= bob, dc= example,dc =com' );

echo "<pre>";
for( $i=0; $i<count($dns1); $i++ ) {
	var_dump( pla_compare_dns( $dns1[$i], $dns2[$i] ) );
	echo "\n";
}

