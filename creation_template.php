<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/creation_template.php,v 1.11 2004/03/19 20:13:08 i18phpldapadmin Exp $



/* file: creation_template.php
 * This file simply acts as a plugin grabber for the creator templates in
 * the directory templates/creation/
 *
 * Expected POST vars:
 *  server_id
 *  template
 */

require 'common.php';
require 'templates/template_config.php';

isset( $_POST['template'] ) or pla_error( $lang['must_choose_template'] );
$template = $_POST['template'];
isset( $templates[$template] ) or pla_error( sprintf( $lang['invalid_template'], htmlspecialchars( $template ) ) );
$template = isset( $templates[$template] ) ? $templates[$template] : null;
$server_id = $_POST['server_id'];
check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
$server_name = $servers[ $server_id ][ 'name' ];

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

include 'header.php';

?>

<body>
<h3 class="title"><?php echo $lang['createf_create_object']?></h3>
<h3 class="subtitle"><?php echo $lang['ctemplate_on_server']?> '<?php echo htmlspecialchars( $server_name ); ?>', <?php echo $lang['using_template']?> '<?php echo htmlspecialchars( $template['desc'] ); ?>'</h3>

<?php

if( ! isset( $_POST['template'] ) )
	pla_error( $lang['ctemplate_no_template'] );

$handler = 'templates/creation/' . $template['handler'];
$handler = realpath( $handler );
if( file_exists( $handler ) )
	include $handler;
else
	pla_error( $lang['ctemplate_config_handler'] . " <b>" . htmlspecialchars( $template['handler'] ) .
		"</b> " . $lang['ctemplate_handler_does_not_exist']);


