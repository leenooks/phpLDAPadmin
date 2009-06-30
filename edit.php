<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/edit.php,v 1.46 2004/03/19 20:13:08 i18phpldapadmin Exp $
 

/*
 * edit.php
 * Displays the specified dn from the specified server for editing
 * in its template as determined by get_template(). This is a simple
 * shell for displaying entries. The real work is done by the templates
 * found in tempaltes/modification/
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - use_default_template (optional) If set, use the default template no matter what
 *  - Other vars may be set and used by the modification templates
 */

require realpath( 'common.php' );
require realpath( 'templates/template_config.php' );

$dn = isset( $_GET['dn'] ) ? $_GET['dn'] : false;
$dn !== false or pla_error( $lang['missing_dn_in_query_string'] );
$decoded_dn = rawurldecode( $dn );
$encoded_dn = rawurlencode( $decoded_dn );

$server_id = isset( $_GET['server_id'] ) ? $_GET['server_id'] : false;
$server_id !== false or pla_error( $lang['missing_server_id_in_query_string'] );

// Template authors may wish to present the user with a link back to the default, generic 
// template for editing. They may use this as the target of the href to do so.
$default_href = "edit.php?server_id=$server_id&amp;dn=$encoded_dn&amp;use_default_template=true";

$use_default_template = isset( $_GET['use_default_template'] ) ? true : false;

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );

if( $use_default_template ) {
	require realpath( 'templates/modification/default.php' );
} else {

	$template = get_template( $server_id, $dn );
	$template_file = "templates/modification/$template.php";
	if( file_exists( realpath( $template_file ) ) )
		require realpath( $template_file );
	else {
		echo "\n\n";
		echo $lang['missing_template_file']; 
		echo " <b>$template_file</b>. ";
		echo $lang['using_default'];
		echo "<br />\n\n";
		require realpath( 'templates/modification/default.php' );
	}
}

?>
