<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/creation_template.php,v 1.22 2005/03/12 00:57:17 wurley Exp $

/**
 * This file simply acts as a plugin grabber for the creator templates in
 * the directory templates/creation/
 *
 * Expected POST vars:
 *  server_id
 *  template
 *
 * @package phpLDAPadmin
 */
/**
 */

require_once 'common.php';
require 'templates/template_config.php';

$server_id = (isset($_REQUEST['server_id']) ? $_REQUEST['server_id'] : '');
$ldapserver = new LDAPServer($server_id);

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$template = (isset($_REQUEST['template']) ? $_REQUEST['template'] : null);
! is_null($template) or pla_error( $lang['ctemplate_no_template'] );

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

if ( ! array_key_exists('no_header', $template ) ) {
	include './header.php';
?>

<body>
<h3 class="title"><?php echo $lang['createf_create_object']?></h3>
<h3 class="subtitle"><?php echo $lang['ctemplate_on_server']?> '<?php echo htmlspecialchars( $ldapserver->name ); ?>', <?php echo $lang['using_template']?> '<?php echo htmlspecialchars( $template['desc'] ); ?>'</h3>

<?php }

$handler = 'templates/creation/' . $template['handler'];
$handler = realpath( $handler );

if( ! file_exists( $handler ) )
	pla_error( sprintf( $lang['template_does_not_exist'], htmlspecialchars( $template['handler'] ) ) );

if( ! is_readable( $handler ) )
	pla_error( sprintf( $lang['template_not_readable'], htmlspecialchars( $template['handler'] ) ) );

include $handler;

if ( ! array_key_exists('no_header', $template ) ) {
	echo "</body>\n</html>";
}
?>
