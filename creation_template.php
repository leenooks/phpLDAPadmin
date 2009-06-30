<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/creation_template.php,v 1.18 2004/10/24 23:51:49 uugdave Exp $

/* file: creation_template.php
 * This file simply acts as a plugin grabber for the creator templates in
 * the directory templates/creation/
 *
 * Expected POST vars:
 *  server_id
 *  template
 */

require_once 'common.php';
require 'templates/template_config.php';

$template = http_get_value( 'template' );
$template !== false or pla_error( $lang['ctemplate_no_template'] );

if( $template == 'custom' ) {
    foreach( $templates as $id => $template ) {
        if( $template['handler'] == 'custom.php' ) {
            $template = $id;
            break;
        }
    }
}

isset( $templates[$template] ) or pla_error( sprintf( $lang['invalid_template'], htmlspecialchars( $template ) ) );
$template_id = $template;
$template = isset( $templates[$template] ) ? $templates[$template_id] : null;
$server_id = http_get_value( 'server_id' );
check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
$ds = pla_ldap_connect( $server_id );
pla_ldap_connection_is_error( $ds );
$server_name = $servers[ $server_id ][ 'name' ];

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

include './header.php';

?>

<body>
<h3 class="title"><?php echo $lang['createf_create_object']?></h3>
<h3 class="subtitle"><?php echo $lang['ctemplate_on_server']?> '<?php echo htmlspecialchars( $server_name ); ?>', <?php echo $lang['using_template']?> '<?php echo htmlspecialchars( $template['desc'] ); ?>'</h3>

<?php

$handler = 'templates/creation/' . $template['handler'];
$handler = realpath( $handler );
if( ! file_exists( $handler ) )
	pla_error( sprintf( $lang['template_does_not_exist'], htmlspecialchars( $template['handler'] ) ) );
if( ! is_readable( $handler ) )
	pla_error( sprintf( $lang['template_not_readable'], htmlspecialchars( $template['handler'] ) ) );

include $handler;

echo "</body>\n</html>";

?>
