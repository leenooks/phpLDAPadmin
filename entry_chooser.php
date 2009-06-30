<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/entry_chooser.php,v 1.23 2005/03/25 16:30:21 wurley Exp $

/**
 * Display a selection (popup window) to pick a DN.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$server_id = isset( $_GET['server_id'] ) ? $_GET['server_id'] : false;
$container = isset( $_GET['container'] ) ? rawurldecode( $_GET['container'] ) : false;
$return_form_element = isset( $_GET['form_element'] ) ? htmlspecialchars( $_GET['form_element'] ) : null;
$rdn = isset( $_GET['rdn'] ) ? htmlspecialchars( $_GET['rdn'] ) : null;

include "./header.php";

echo "<h3 class=\"subtitle\">" . $lang['entry_chooser_title'] . "</h3>\n";
flush(); ?>

<script language="javascript">
	function returnDN( dn ) {
		opener.document.<?php echo $return_form_element; ?>.value = dn;
		close();
	}
</script>

<?php if( $container ) {
	echo $lang['server_colon_pare'] . "<b>" . htmlspecialchars( $servers[ $server_id ][ 'name' ] ) . "</b><br />\n";
	echo $lang['look_in'] . "<b>" . htmlspecialchars( $container ) . "</b><br />\n";
}

/* Has the use already begun to descend into a specific server tree? */
if( $server_id !== false && $container !== false ) {
	$ldapserver = new LDAPServer($server_id);

	if( ! $ldapserver->haveAuthInfo())
		pla_error( $lang['not_enough_login_info'] );

	$dn_list = get_container_contents( $ldapserver, $container, 0, '(objectClass=*)', get_tree_deref_setting() );
	sort( $dn_list );

	foreach ($ldapserver->getBaseDN() as $base_dn) {
		debug_log(sprintf('%s: Comparing BaseDN [%s] with container [%s]','entry_chooser.php',$base_dn,$container),9);
		if( 0 == pla_compare_dns( $container, $base_dn ) ) {
			$parent_container = false;
			$up_href = sprintf('entry_chooser.php?form_element=%s&rdn=%s',$return_form_element,$rdn);
			break;

		} else {
			$parent_container = get_container( $container );
			$up_href = sprintf('entry_chooser.php?form_element=%s&rdn=%s&amp;server_id=%s&amp;container=%s',
				$return_form_element,$rdn,$server_id,rawurlencode( $parent_container ));
		}
	}

	echo "&nbsp;<a href=\"$up_href\" style=\"text-decoration:none\">" .
		"<img src=\"images/up.png\"> ". $lang['back_up_p'] ."</a><br />\n";

	if( count( $dn_list ) == 0 )
		echo "&nbsp;&nbsp;&nbsp;(". $lang['no_entries'] .")<br />\n";

	else
		foreach( $dn_list as $dn ) {
			$href = sprintf("javascript:returnDN( '%s%s' )",($rdn ? "$rdn," : ''),$dn);
			echo "&nbsp;&nbsp;&nbsp;<a href=\"entry_chooser.php?form_element=$return_form_element&rdn=$rdn".
				"&amp;server_id=$server_id&amp;container=" .
				rawurlencode( $dn ) . "\"><img src=\"images/plus.png\" /></a> " .
				"<a href=\"$href\">" . htmlspecialchars( $dn ) . "</a><br />\n";
		}

/* draw the root of the selection tree (ie, list all the servers) */
} else {
	foreach( $servers as $id => $server ) {

		$ldapserver = new LDAPServer($id);

		if( $ldapserver->isVisible() ) {

			if( ! $ldapserver->haveAuthInfo() )
				continue;

			else {
				echo "<b>" . htmlspecialchars( $ldapserver->name ) . "</b><br />\n";
				foreach ($ldapserver->getBaseDN() as $dn) {
					if( ! $dn ) {
						echo "<small>&nbsp;&nbsp;&nbsp;(". $lang['could_not_det_base_dn'] .")</small><br />";
					} else {
						$href = sprintf("javascript:returnDN( '%s%s' )",($rdn ? "$rdn," : ''),$dn);
						echo "&nbsp;&nbsp;&nbsp;<a href=\"entry_chooser.php?form_element=" .
							"$return_form_element&rdn=$rdn&amp;server_id=$id&amp;container=" .
							rawurlencode( $dn ) . "\"><img src=\"images/plus.png\" /></a> " .
							"<a href=\"$href\">" . htmlspecialchars( $dn ) . "</a><br />\n";
					}
				}
			}
		}
	}
}

// added by PD. 14082003,
// adding the element access allows it to work with javascript arrays

// the name of the form extracted from the first part of the URL variable.
$formpart=substr($return_form_element,0,strpos($return_form_element,"."));

// the name of the element extracted from the last part of the URL variable (after the dot)
$elmpart =substr($return_form_element,strpos($return_form_element,".")+1);

// rebuilt return value
$return_form_element = $formpart . ".elements[\"" . $elmpart . "\"]";
?>
