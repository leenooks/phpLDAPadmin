<?php

require 'common.php';

$server_id = $_GET['server_id'];
$dn = rawurldecode( $_GET['dn'] );
$attr = $_GET['attr'];
// if there are multiple values in this attribute, which one do you want to see?
$value_num = isset( $_GET['value_num'] ) ? $_GET['value_num'] : 0;

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
$ds = pla_ldap_connect( $server_id ) or pla_error( "Coult not connect to LDAP server." );

$search = ldap_read( $ds, $dn, "(objectClass=*)", array( $attr ), 0, 200, 0, LDAP_DEREF_ALWAYS );
$entry = ldap_first_entry( $ds, $search );
$attrs = ldap_get_attributes( $ds, $entry );
$attr = ldap_first_attribute( $ds, $entry, $attrs );
$values = ldap_get_values_len( $ds, $entry, $attr );
$count = $values['count'];
unset( $values['count'] );
Header( "Content-type: octet-stream" );
Header( "Content-disposition: attachment; filename=$attr" );
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" ); 
echo $values[$value_num];

?>
