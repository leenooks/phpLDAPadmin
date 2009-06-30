<?php

require 'config.php';
require_once 'functions.php';
$container = isset( $_GET['container'] ) ? rawurldecode( $_GET['container'] ) : false;
$server_id = isset( $_GET['server_id'] ) ? $_GET['server_id'] : false;
$return_form_element = $_GET['form_element'];

include "header.php";

echo "<h3 class=\"subtitle\">Automagic Entry Chooser</h3>\n";

if( $container ) {
	echo "Server: <b>" . htmlspecialchars( $servers[ $server_id ][ 'name' ] ) .  "</b><br />\n";
	echo "Looking in: <b>" . htmlspecialchars( $container ) . "</b><br />\n"; 
}

if( $server_id !== false && $container !== false )
{
	check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
	have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. ".
							"Please check your configuration." );
	pla_ldap_connect( $server_id ) or pla_error( "Coult not connect to LDAP server." );
	$dn_list = get_container_contents( $server_id, $container );

	$base_dn = $servers[ $server_id ][ 'base' ];
	if( ! $base_dn )
		$base_dn = try_to_get_root_dn( $server_id );

	if( $container == $base_dn ) {
		$parent_container = false;
		$up_href = "entry_chooser.php?form_element=$return_form_element";
	} else {
		$parent_container = get_container( $container );
		$up_href = "entry_chooser.php?form_element=$return_form_element&amp;server_id=$server_id&amp;container=" .
				rawurlencode( $parent_container );
	}
	echo "&nbsp;<a href=\"$up_href\" style=\"text-decoration:none\">" . 
		"<img src=\"images/up.png\"> Back Up...</a><br />\n";

	if( count( $dn_list ) == 0 )
		echo "&nbsp;&nbsp;&nbsp;(no entries)<br />\n";
	else
		foreach( $dn_list as $dn ) {
			$href = "javascript:returnDN( '$dn' )";
			echo "&nbsp;&nbsp;&nbsp;<a href=\"entry_chooser.php?form_element=$return_form_element&amp;server_id=$server_id&amp;container=" .
				rawurlencode( $dn ) . "\"><img src=\"images/plus.png\" /></a> " .
				"<a href=\"$href\">" . htmlspecialchars( $dn ) . "</a><br />\n";
		}
}
else
{
	foreach( $servers as $id => $server ) {
		if( $server['host'] ) {
			echo htmlspecialchars( $server['name'] ) . "<br />\n";
			$dn = ( $server['base'] ? $server['base'] : try_to_get_root_dn( $id ) ); 
			$href = "javascript:returnDN( '$dn' )";
			echo "&nbsp;&nbsp;&nbsp;<a href=\"entry_chooser.php?form_element=$return_form_element&amp;server_id=$id&amp;container=" .
				rawurlencode( $dn ) . "\"><img src=\"images/plus.png\" /></a> " .
				"<a href=\"$href\">" . htmlspecialchars( $dn ) . "</a><br />\n";
		}
	}
}

?>

<script language="javascript">
	function returnDN( dn )
	{
		opener.document.<?php echo $return_form_element; ?>.value = dn;
		close();
	}
</script>
