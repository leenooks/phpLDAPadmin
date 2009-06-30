<?php


/* file: creation_template.php
 * This file simply acts as a plugin grabber for the creator templates in
 * the directory templates/creation/
 *
 * Expected POST vars:
 *  server_id
 *  template
 */

require 'common.php';

$template = $_POST['template'];
$template = $templates[$template];
$server_id = $_POST['server_id'];
check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
$server_name = $servers[ $server_id ][ 'name' ];

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

include 'header.php';

?>

<body>
<h3 class="title">Create Object</h3>
<h3 class="subtitle">On server '<?php echo htmlspecialchars( $server_name ); ?>',
	using template '<?php echo htmlspecialchars( $template['desc'] ); ?>'</h3>

<?php

if( ! isset( $_POST['template'] ) )
	pla_error( "No template specified in POST variables.\n" );

$handler = 'templates/creation/' . $template['handler'];
$handler = realpath( $handler );
if( file_exists( $handler ) )
	include $handler;
else
	pla_error( "Your config specifies a handler of <b>" . htmlspecialchars( $template['handler'] ) .
		"</b> for this template. But, this handler does not exist in the 'templates/creation' directory." );


