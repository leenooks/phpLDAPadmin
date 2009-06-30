<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/tree.php,v 1.88 2005/09/25 16:11:44 wurley Exp $

/**
 * This script displays the LDAP tree for all the servers that you have
 * in config.php.
 *
 * We read the session variable 'tree' to know which dns are expanded or collapsed.
 * No query string parameters are expected, however, you can use a '#' offset to
 * scroll to a given dn. The syntax is tree.php#<server_id>_<rawurlencoded dn>, so
 * if I wanted to scroll to dc=example,dc=com for server 3, the URL would be:
 *
 *	tree.php#3_dc%3Dexample%2Cdc%3Dcom
 *
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */
/**
 */

require './common.php';

// no expire header stuff
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// This allows us to display large sub-trees without running out of time.
@set_time_limit( 0 );

// do we not have a tree and tree icons yet? Build a new ones.
initialize_session_tree();

// get the tree and tree icons.
$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

// Test to see if any have timedout.
// Initialize array of recently timed out servers
$recently_timed_out_servers = array();
// Set a default META REFRESH value in sec. before determining it
$meta_refresh_variable = ( session_cache_expire()-1 )*60;

foreach( $ldapservers->GetServerList() as $server_id ) {
	$ldapserver = $ldapservers->Instance($server_id);

	# Test to see if we should log out the user due to the timeout.
	if ($ldapserver->haveAuthInfo()) {

		/* If time out value has been reached:
			- log out user
			- put $server_id in array of recently timed out servers */
		if (session_timed_out($ldapserver))
			array_push($recently_timed_out_servers, $server_id);

		/* if the timeout value is less than the previous $meta_refresh_variable value
		   set $meta_refresh_variable to $ldapserver->session_timeout */
		if (($ldapserver->session_timeout*60) < $meta_refresh_variable )
			$meta_refresh_variable = $ldapserver->session_timeout*60;
	}
}

/* Close the session for faster page loading (we're done with session data anyway).
   Unfortunately, now that we dont show a plus '+' for leafs in a tree, we need to keep
   the session open, so that if we create an entry, it'll cause the refresh of the tree view.
   Hope this doesnt affect performance...? */
// pla_session_close();

include './header.php';
?>

<body>

<?php
	$bug_href = get_href( 'add_bug' );
	$feature_href = get_href( 'add_rfe' );
	$donate_href = get_href( 'donate' );
	$help_href = get_href( 'help' );
?>

<h3 class="subtitle" style="margin:0px">phpLDAPadmin - <?php echo pla_version(); ?></h3>

<!-- Links at the top of the tree viewer -->
<table class="edit_dn_menu" width=100%>
<tr>
	<td><img src="images/home.png" alt="<?php echo $lang['home']; ?>" /></td>
	<td width=50%><nobr><a href="welcome.php" target="right_frame"><?php echo $lang['home']; ?></a></nobr></td>
	<td><img src="images/trash.png" alt="<?php echo $lang['purge_cache']; ?>" /></td>
	<td width=50%><nobr><a href="purge_cache.php" target="right_frame" title="<?php echo $lang['purge_cache_tooltip']; ?>"><?php echo $lang['purge_cache']; ?></a></nobr></td>
</tr>
<tr>

<?php if ( ! $config->GetValue('appearance','hide_configuration_management') ) { ?>
	<td><img src="images/light.png" alt="<?php echo $lang['light']; ?>" /></td>
	<td width=50%><nobr><a href="<?php echo $feature_href; ?>" target="new"><?php echo $lang['request_new_feature']; ?></a></nobr></td>
	<td><img src="images/bug.png" alt="<?php echo $lang['bug']; ?>" /></td>
	<td width=50%><nobr><a href="<?php echo $bug_href; ?>" target="new"><?php echo $lang['report_bug']; ?></a></nobr></td>
</tr>
<tr>
	<td><img src="images/smile.png" alt="<?php echo $lang['donate']; ?>" /></td>
	<td width=50%><nobr><a href="<?php echo $donate_href; ?>" target="right_frame"><?php echo $lang['donate']; ?></a></nobr></td>
<?php } ?>
	<td><img src="images/help.png" alt="<?php echo $lang['help']; ?>" /></td>
	<td><nobr><a href="help.php" target="right_frame"><?php echo $lang['help']; ?></a></nobr></td>
</tr>
</table>

<table class="tree" cellspacing="0">

<?php

# We want the std tree function as a fallback
require LIBDIR.'tree_functions.php';

# For each of the configured servers
foreach( $ldapservers->GetServerList() as $server_id ) {
	$ldapserver = $ldapservers->Instance($server_id);

	if ($ldapserver->isVisible()) {
		$filename = get_custom_file($server_id,'tree_functions.php',LIBDIR);
		require_once($filename);

		call_custom_function($server_id,'draw_server_tree');
	}
}

# Case where user not logged into any server
if ($meta_refresh_variable == 0)
	$meta_refresh_variable = (session_cache_expire()-1)*60;

?>

<META HTTP-EQUIV="REFRESH" CONTENT="<?php echo $meta_refresh_variable?>">

</table>
</body>
</html>

<?php
exit;

/**
 * Recursively descend on the given dn and draw the tree in html
 *
 * @param dn $dn Current dn.
 * @param object $LDAPServer LDAPServer object
 * @param int $level Level to start drawing (defaults to 0)
 */
function draw_tree_html($dn,$ldapserver,$level=0) {
	global $config, $tree, $tree_icons, $lang;

	$encoded_dn = rawurlencode( $dn );
	$expand_href = sprintf("expand.php?server_id=%s&amp;dn=%s",$ldapserver->server_id,$encoded_dn);
	$collapse_href = sprintf("collapse.php?server_id=%s&amp;dn=%s",$ldapserver->server_id,$encoded_dn);
	$edit_href = sprintf("edit.php?server_id=%s&amp;dn=%s",$ldapserver->server_id,$encoded_dn);

	// should never happen, but just in case
	if( ! isset( $tree_icons[ $ldapserver->server_id ][ $dn ] ) )
		$tree_icons[ $ldapserver->server_id ][ $dn ] = get_icon( $ldapserver, $dn );
	$img_src = 'images/' . $tree_icons[ $ldapserver->server_id ][ $dn ];

	$rdn = get_rdn( $dn );

	echo '<tr>';

	for( $i=0; $i<=$level; $i++ ) {
		echo '<td class="spacer"></td>' . "\n";
	}

	// Shall we draw the "mass-delete" checkbox?
	if( $ldapserver->isMassDeleteEnabled() ) {
		printf('<td><input type="checkbox" name="mass_delete[%s]" /></td>',htmlspecialchars($dn));
	}

	// is this node expanded? (deciding whether to draw "+" or "-")
	if( isset( $tree[$ldapserver->server_id][$dn] ) ) { ?>
		<?php $child_count = number_format( count( $tree[$ldapserver->server_id][$dn] ) );
		if ((! $child_count) && (! $ldapserver->isShowCreateEnabled())) { ?>
		<td class="expander">
			<nobr>
			<img src="images/minus.png" alt="-" />
			</nobr>
		</td>

		<?php } else { ?>
		<td class="expander">
			<nobr>
			<a href="<?php echo $collapse_href; ?>"><img src="images/minus.png" alt="-" /></a>
			</nobr>
		</td>
		<?php }

	} else {
		$size_limit = $config->GetValue('search','size_limit');

		if( $ldapserver->isLowBandwidth() ) {
	                $child_count = null;
		} else {
	                $child_count = count( get_container_contents( $ldapserver, $dn, $size_limit+1,
				'(objectClass=*)', $config->GetValue('deref','tree')));

			if( $child_count > $size_limit )
				$child_count = $size_limit . '+';
		}

		if (($child_count === 0) && (! $ldapserver->isShowCreateEnabled())) {
			// Since we know the tree is empty, we'll create a $tree object anyway, just incase we
			// create something later (otherwise it doesnt cause the tree to get refreshed).

			$_SESSION['tree'][$ldapserver->server_id][$dn] = array();
			$_SESSION['tree_icons'][$ldapserver->server_id][$dn] = get_icon( $ldapserver, $dn ); ?>

		<td class="expander">
			<nobr>
			<img src="images/minus.png" alt="-" />
			</nobr>
		</td>

		<?php } else { ?>
		<td class="expander">
			<nobr>
			<a href="<?php echo $expand_href; ?>"><img src="images/plus.png" alt="+" /></a>
			</nobr>
		</td>
		<?php }
	} ?>

	<td class="icon">
		<a href="<?php echo $edit_href; ?>"
		   target="right_frame"
		   name="<?php echo $ldapserver->server_id; ?>_<?php echo $encoded_dn; ?>"><img src="<?php echo $img_src; ?>" alt="img" /></a>
	</td>
	<td class="rdn" colspan="<?php echo (97-$level); ?>">
		<nobr>
			<a href="<?php echo $edit_href; ?>"
				target="right_frame"><?php echo ( draw_formatted_dn( $ldapserver, $dn ) ); /*pretty_print_dn( $rdn ) );*/ ?></a>
				<?php if( $child_count ) { ?>
					<span class="count">(<?php echo $child_count; ?>)</span>
				<?php } ?>
		</nobr>
	</td>
	</tr>

	<?php

	if( isset( $tree[$ldapserver->server_id][$dn] ) && is_array( $tree[$ldapserver->server_id][$dn] ) ) {
		// Draw the "create new" link at the top of the tree list if there are more than 10
		// entries in the listing for this node.

		if(( count( $tree[$ldapserver->server_id][$dn] ) > 10 ) && ( $ldapserver->isShowCreateEnabled() ))
			draw_create_link( $ldapserver->server_id, $rdn, $level, $encoded_dn );

		foreach( $tree[$ldapserver->server_id][$dn] as $dn )
			draw_tree_html( $dn, $ldapserver, $level+1 );

		// Always draw the "create new" link at the bottom of the listing
		if( $ldapserver->isShowCreateEnabled() )
			draw_create_link( $ldapserver->server_id, $rdn, $level, $encoded_dn );
	}
}

/**
 * Print the HTML to show the "create new entry here".
 *
 * @param int $server_id
 * @param dn $rdn
 * @param int $level
 * @param dn $encoded_dn
 */
function draw_create_link( $server_id, $rdn, $level, $encoded_dn )
{
	global $lang;

	// print the "Create New object" link.
	$create_href = sprintf("create_form.php?server_id=%s&amp;container=%s",$server_id,$encoded_dn);

	$create_html = '<tr>';
	for( $i=0; $i<=$level; $i++ ) {
		$create_html .= '<td class="spacer"></td>';
	}

	$create_html .= '<td class="spacer"></td>';
	$create_html .= '<td class="icon"><a href="' . $create_href .
		'" target="right_frame"><img src="images/star.png" alt="' . $lang['new'] . '" /></a></td>';
	$create_html .= '<td class="create" colspan="' . (97-$level) . '"><a href="' . $create_href .
		'" target="right_frame" title="' . $lang['create_new_entry_in'] . ' ' . $rdn.'">' .
		$lang['create_new'] . '</a></td>';
	$create_html .= '</tr>';
	echo $create_html;
}

?>
