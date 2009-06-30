<?php 

/* 
 * tree.php
 * This script displays the LDAP tree for all the servers that you have
 * in config.php. We read the session variable 'tree' to know which
 * dns are expanded or collapsed. No query string parameters are expected,
 * however, you can use a '#' offset to scroll to a given dn. The syntax is
 * tree.php#<server_id>_<rawurlencoded dn>, so if I wanted to scroll to
 * dc=example,dc=com for server 3, the URL would be: 
 *	tree.php#3_dc%3Dexample%2Cdc%3Dcom
 */

require 'common.php';

// no expire header stuff
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// The entire visible tree is stored in the session.
session_start();

// do we not have a tree yet? Build a new one.
if( ! session_is_registered( 'tree' ) ) {
	session_register( 'tree' );
	$_SESSION['tree'] = build_initial_tree(); 
	session_register( 'tree_icons' );
	$_SESSION['tree_icons'] = build_initial_tree_icons();
}

// grab the tree out of the session variable
$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];
?>

<?php include 'header.php'; ?>

<body>

<?php 
	$bug_href = get_href( 'add_bug' );
	$open_bugs_href = get_href( 'open_bugs' );
	$feature_href = get_href( 'add_rfe' );
	$open_features_href = get_href( 'open_rfes' );
?>

<h3 class="subtitle" style="margin:0px">phpLDAPadmin - <?php echo pla_version(); ?></h3>
<table class="edit_dn_menu">
<tr>
	<td><img src="images/light.png" /></td>
	<td><nobr><a href="<?php echo $feature_href; ?>" target="new"><?php echo $lang['request_new_feature']; ?></a>
		(<a href="<?php echo $open_features_href; ?>" target="new"><?php echo $lang['see_open_requests']; ?></a>)</nobr></td>
</tr>
<tr>	
	<td><img src="images/bug.png" /></td>
	<td><nobr><a href="<?php echo $bug_href; ?>" target="new"><?php echo $lang['report_bug']; ?></a>
		(<a href="<?php echo $open_bugs_href; ?>" target="new"><?php echo $lang['see_open_bugs']; ?></a>)</nobr></td>
</tr>
</table>

<table class="tree" cellspacing="0">

<?php 

foreach( $servers as $server_id => $server_tree ) { 

	if( $servers[$server_id]['host'] != '' ) { 

		$server_name = $servers[$server_id]['name'];
		echo '<tr class="server">';
		echo '<td class="icon"><img src="images/server.png" alt="server"/></td>';
		echo '<td colspan="99"><a name="' . $server_id . '"></a>';
		echo '<nobr>' . htmlspecialchars( $server_name ) . '</nobr></td>';
		echo '</tr>';

		// do we have what it takes to authenticate here, or do we need to
		// present the user with a login link (for 'form' auth_types)?
		if( have_auth_info( $server_id ) )
		{
			$schema_href =  'schema.php?server_id=' . $server_id  . '" target="right_frame';
			$search_href=   'search.php?server_id=' . $server_id  . '" target="right_frame';
			$refresh_href = 'refresh.php?server_id=' . $server_id;
			$create_href =  'create_form.php?server_id=' . $server_id . '&amp;container=' .
					 rawurlencode( $servers[$server_id]['base'] );
			$logout_href = 'logout.php?server_id=' . $server_id;
			$info_href = 'server_info.php?server_id=' . $server_id;
			$import_href = 'ldif_import_form.php?server_id=' . $server_id;

			// Draw the quick-links below the server name: 
			// ( schema | search | refresh | create )
			echo '<tr><td colspan="100" class="links">';
			echo '<nobr>';
			echo '( ';
			echo '<a title="' . $lang['view_schema_for'] . ' ' . $server_name . '"'.
			     ' href="' . $schema_href . '">' . $lang['schema'] . '</a> | ';
			echo '<a title="' . $lang['search'] . ' ' . $server_name . '"' .
			     ' href="' . $search_href . '">' . $lang['search'] . '</a> | ';
			echo '<a title="' . $lang['refresh_expanded_containers'] . ' ' . $server_name . '"'.
			     ' href="' . $refresh_href . '">' . $lang['refresh'] . '</a> | ';
			echo '<a title="' . $lang['create_new_entry_on'] . ' ' . $server_name . '"'.
			     ' href="' . $create_href . '" target="right_frame">create</a> | ';
			echo '<a title="' . $lang['view_server_info'] . '" target="right_frame"'.
			     'href="' . $info_href . '">' . $lang['info'] . '</a> | ';
			echo '<a title="' . $lang['import_from_ldif'] . '" target="right_frame"' .
			     'href="' . $import_href .'">' . $lang['import'] . '</a>';
			if( $servers[ $server_id ][ 'auth_type' ] == 'form' )
				echo ' | <a title="' . $lang['logout_of_this_server'] . '" href="' . $logout_href . 
					'" target="right_frame">' . $lang['logout'] . '</a>';
			echo ' )</nobr></td></tr>';
			
			if( $servers[$server_id]['auth_type'] == 'form' && have_auth_info( $server_id ) )
				echo "<tr><td class=\"links\" colspan=\"100\"><nobr>" .
					$lang['logged_in_as'] . htmlspecialchars(get_logged_in_dn($server_id)) . 
					"</nobr></td></tr>";
			if( is_server_read_only( $server_id ) ) 
				echo "<tr><td class=\"links\" colspan=\"100\"><nobr>" .
					"(" . $lang['read_only'] . ")</nobr></td></tr>";

			// Fetch and display the base DN for this server
			//$rdn = utf8_decode( $dn );
			if( null == $servers[ $server_id ]['base'] ) {
				$base_dn = try_to_get_root_dn( $server_id );
			} else {
				$base_dn = $servers[ $server_id ]['base'];
			}

			// Did we get a base_dn for this server somehow?
			if( $base_dn ) {
				// is the root of the tree expanded already?
				if( isset( $tree[$server_id][$base_dn] ) ) {
					$expand_href =  "collapse.php?server_id=$server_id&amp;" .
					"dn=" . rawurlencode( $base_dn );
					$expand_img = "images/minus.png";
				} else {
					$expand_href =  "expand.php?server_id=$server_id&amp;" .
					"dn=" . rawurlencode( $base_dn );
					$expand_img = "images/plus.png";
				}

				$edit_href = "edit.php?server_id=$server_id&amp;dn=" . rawurlencode( $base_dn );

				$icon = get_icon( $server_id, $base_dn );
				echo "<td class=\"expander\" style=\"text-align: right\">";
				echo "<a href=\"$expand_href\"><img src=\"$expand_img\" /></td>";
				echo "<td class=\"icon\"><a href=\"$edit_href\" target=\"right_frame\">";
				echo "<img src=\"images/$icon\" /></a></td>\n";
				echo "<td class=\"rdn\" colspan=\"98\"><nobr><a href=\"$edit_href\" ";
				echo " target=\"right_frame\">$base_dn</nobr></td>\n";
				echo "</tr>\n";
			} else {
				if( "" === $base_dn || null === $base_dn ) {
					// The server refuses to give out the base dn
					echo "<tr><td class=\"spacer\"></td><td colspan=\"98\"><small><nobr>";
					echo $lang['could_not_determine_root'];
					echo '<br />';
					echo $lang['ldap_refuses_to_give_root'];
					echo '<br />';
					echo $lang['please_specify_in_config'];
					echo "</small></nobr></td></tr>";
					// Proceed to the next server. We cannot draw anything else for this server.
					continue;
				} else {
					// For some unknown reason, we couldn't determine the base dn
					echo "<tr><td class=\"spacer\"></td><td colspan=\"99\"><small><nobr>";
					echo $lang['could_not_determine_root'];
					echo '<br />';
					echo $lang['please_specify_in_config'];
					echo "</small></nobr></td></tr>";
					// Proceed to the next server. We cannot draw anything else for this server.
					continue;
				}
			}

			flush();

			// Is the root of the tree expanded already?
			if( isset( $tree[$server_id][$base_dn] ) ) {
				foreach( $tree[ $server_id ][ $base_dn ] as $child_dn )
					draw_tree_html( $child_dn, $server_id, 0 );
				if( ! is_server_read_only( $server_id ) ) {
					echo '<td class="spacer"></td>';
					echo '<td class="icon"><a href="' . $create_href .
						'" target="right_frame"><img src="images/star.png" /></a></td>';
					echo '<td class="create" colspan="100"><a href="' . $create_href . 
						'" target="right_frame" title="' . $lang['create_new_entry_in'] . ' ' . 
						$base_dn.'">' . $lang['create_new'] . '</a></td></tr>';
				}
			}
		}
		else // have_auth_info() returned false.
		{
			// We don't have enough information to login to this server
			// Draw the "login..." link
			$login_href = "login_form.php?server_id=$server_id";
			echo '<tr class="login"><td colspan="100">';
			echo '&nbsp;&nbsp;&nbsp;<a href="' . $login_href . '" target="right_frame">';
			echo '<img src="images/uid.png" align="top" alt="login"/></a> ';
			echo '<a href="' . $login_href . '" target="right_frame">login...</a>';
			echo '</td></tr>';
		}
	}
}

?>

</table>
<?php 
	//echo "<pre>"; print_r( $tree ); 
?>

</body>
</html>

<?php 

exit;

/**
 * Recursively descend on the given dn and draw the tree in html
 */
function draw_tree_html( $dn, $server_id, $level=0 )
{
	global $servers, $tree, $tree_icons, $lang;
	$id = $server_id;
	
	$encoded_dn = rawurlencode( $dn );
	$expand_href = "expand.php?server_id=$id&amp;dn=$encoded_dn";
	$collapse_href = "collapse.php?server_id=$id&amp;dn=$encoded_dn";
	$edit_href = "edit.php?server_id=$id&amp;dn=$encoded_dn";

	// should never happen, but just in case
	if( ! isset( $tree_icons[ $server_id ][ $dn ] ) )
		$tree_icons[ $server_id ][ $dn ] = get_icon( $server_id, $dn );
	$img_src = 'images/' . $tree_icons[ $server_id ][ $dn ];

	$rdn = pla_explode_dn( $dn );
	$rdn = $rdn[0];

	echo '<tr>';

	for( $i=0; $i<=$level; $i++ ) {
		echo '<td class="spacer"></td>' . "\n";
	}
		
	// is this node expanded? (deciding whether to draw "+" or "-")
	if( isset( $tree[$server_id][$dn] ) ) { ?>
		<td class="expander">
			<nobr>
			<a href="<?php echo $collapse_href; ?>"><img src="images/minus.png" alt="plus" /></a>
			</nobr>
		</td>
		<?php  $object_count = ' <span class="count">(' . count( $tree[$server_id][$dn] ) . ')</span>';
	} else { ?>	
		<td class="expander">
			<nobr>
			<a href="<?php echo $expand_href; ?>"><img src="images/plus.png" alt="minus" /></a>
			</nobr>
		</td>
		<?php  $object_count = '';
	} ?>	

	<td class="icon">
		<a href="<?php echo $edit_href; ?>"
		   target="right_frame"
		   name="<?php echo $server_id; ?>_<?php echo $encoded_dn; ?>"><img src="<?php echo $img_src; ?>" /></a>
	</td>
	<td class="rdn" colspan="<?php echo (97-$level); ?>">
		<nobr>
			<a href="<?php echo $edit_href; ?>"
				target="right_frame"><?php echo htmlspecialchars( utf8_decode( $rdn ) ); ?></a>
				<?php echo $object_count; ?>
		</nobr>
	</td>
	</tr>

	<?php 

	if( isset( $tree[$server_id][$dn] ) && is_array( $tree[$server_id][$dn] ) )	{
		foreach( $tree[$server_id][$dn] as $dn ) { 
			draw_tree_html( $dn, $server_id, $level+1 );
		}

		// print the "Create New object" link.
		$create_href = "create_form.php?server_id=$server_id&amp;container=$encoded_dn";
		echo '<tr>';
		for( $i=0; $i<=$level; $i++ ) {
			echo '<td class="spacer"></td>';
		}
		echo '<td class="spacer"></td>';
		echo '<td class="icon"><a href="' . $create_href .
		     '" target="right_frame"><img src="images/star.png" /></a></td>';
		echo '<td class="create" colspan="' . (97-$level) . '"><a href="' . $create_href . 
		     '" target="right_frame" title="' . $lang['create_new_entry_in'] . ' ' . $rdn.'">' . 
		     $lang['create_new'] . '</a></td></tr>';
	}

	echo '</tr>';

}

?>
