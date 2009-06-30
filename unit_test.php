<?php

echo "<pre>";

require_once realpath( 'functions.php' );

$dns1 = array( 'cn=joe,dc=example,dc=com', 'cn=joe,dc=example,dc=com', 'cn = bob, dc= example,dc =com' );
$dns2 = array( 'cn=joe,dc=example,dc=com', 'CN =joe,dc=Example,dc =com', 'cn= bob, dc= example,dc =com' );

for( $i=0; $i<count($dns1); $i++ ) {
	var_dump( pla_compare_dns( $dns1[$i], $dns2[$i] ) );
	echo "\n";
}

// TESTING PLA_EXPLODE_DN()
var_dump( pla_explode_dn( "cn=<stuff>,dc=example,dc=<com>" ) );
var_dump( ldap_explode_dn( "cn=<stuff>,dc=example,dc=<com>", 0 ) );
