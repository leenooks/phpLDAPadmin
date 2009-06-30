<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/download_binary_attr.php,v 1.6 2004/04/18 15:51:24 uugdave Exp $


require 'common.php';

$server_id = $_GET['server_id'];
$dn = rawurldecode( $_GET['dn'] );
$attr = $_GET['attr'];
// if there are multiple values in this attribute, which one do you want to see?
$value_num = isset( $_GET['value_num'] ) ? $_GET['value_num'] : 0;

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
$ds = pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
dn_exists( $server_id, $dn ) or pla_error( sprintf( $lang['no_such_entry'], pretty_print_dn( $dn ) ) );

$search = @ldap_read( $ds, $dn, "(objectClass=*)", array( $attr ), 0, 0, 0, get_view_deref_setting() );
if( ! $search )
    pla_error( $lang['error_performing_search'], ldap_error( $ds ), ldap_errno( $ds ) );
$entry =  ldap_first_entry(     $ds, $search );
$attrs =  ldap_get_attributes(  $ds, $entry );
$attr =   ldap_first_attribute( $ds, $entry, $attrs );
$values = ldap_get_values_len(  $ds, $entry, $attr );
$count = $values['count'];

// Dump the binary data to the browser
header( "Content-type: octet-stream" );
header( "Content-disposition: attachment; filename=$attr" );
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" ); 
echo $values[$value_num];

?>
