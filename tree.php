<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/tree.php,v 1.54 2004/04/24 12:59:30 uugdave Exp $


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

// This allows us to display large sub-trees without running out of time.
@set_time_limit( 0 );

// do we not have a tree and tree icons yet? Build a new ones.
initialize_session_tree();

// get the tree and tree icons.
$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

include 'header.php';
?>

<body>

<?php
	$bug_href = get_href( 'add_bug' );
	//$open_bugs_href = get_href( 'open_bugs' );
	$feature_href = get_href( 'add_rfe' );
	//$open_features_href = get_href( 'open_rfes' );
    $donate_href = get_href( 'donate' );
?>

<h3 class="subtitle" style="margin:0px">phpLDAPadmin - <?php echo pla_version(); ?></h3>
<table class="edit_dn_menu">
<tr>
	<td><img src="images/light.png" alt="<?php echo $lang['light']; ?>" /></td>
	<td><nobr><a href="<?php echo $feature_href; ?>" target="new"><?php echo $lang['request_new_feature']; ?></a>
</tr>
<tr>	
	<td><img src="images/bug.png" alt="<?php echo $lang['bug']; ?>" /></td>
	<td><nobr><a href="<?php echo $bug_href; ?>" target="new"><?php echo $lang['report_bug']; ?></a>
</tr>
<tr>	
	<td><img src="images/light.png" alt="<?php echo $lang['donate']; ?>" /></td>
	<td><nobr><a href="<?php echo $donate_href; ?>" target="new"><?php echo $lang['donate']; ?></a>
</tr>
</table>

<table class="tree" cellspacing="0">

<?php 

// For each of the configured servers
foreach( $servers as $server_id => $server_tree ) { 

	if( isset( $servers[$server_id] ) && trim( $servers[$server_id]['host'] ) != '' ) { 

		// Does this server want mass deletion availble?
		if( mass_delete_enabled( $server_id ) ) {
			echo "<form action=\"mass_delete.php\" method=\"post\" target=\"right_frame\">\n";
			echo "<input type=\"hidden\" name=\"server_id\" value=\"$server_id\" />\n";
		}

		$server_name = $servers[$server_id]['name'];
		echo '<tr class="server">';
		echo '<td class="icon"><img src="images/server.png" alt="' . $lang['server'] . '" /></td>';
		echo '<td colspan="99"><a name="' . $server_id . '"></a>';
		echo '<nobr>' . htmlspecialchars( $server_name ) . '</nobr></td>';
		echo '</tr>';

		// do we have what it takes to authenticate here, or do we need to
		// present the user with a login link (for 'cookie' and 'session' auth_types)?
		if( have_auth_info( $server_id ) ) {

			if( pla_ldap_connect( $server_id ) ) {
				$schema_href =  'schema.php?server_id=' . $server_id  . '" target="right_frame';
				$search_href=   'search.php?server_id=' . $server_id  . '" target="right_frame';
				$refresh_href = 'refresh.php?server_id=' . $server_id;
				$create_href =  'create_form.php?server_id=' . $server_id . '&amp;container=' .
						rawurlencode( $servers[$server_id]['base'] );
				$logout_href =  'logout.php?server_id=' . $server_id;
				$info_href =    'server_info.php?server_id=' . $server_id;
				$import_href =  'ldif_import_form.php?server_id=' . $server_id;
	
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
				if (show_create_enabled($server_id))
					echo '<a title="' . $lang['create_new_entry_on'] . ' ' . $server_name . '"'.
					     ' href="' . $create_href . '" target="right_frame">' . $lang['create'] . '</a> | ';
				echo '<a title="' . $lang['view_server_info'] . '" target="right_frame"'.
				     'href="' . $info_href . '">' . $lang['info'] . '</a> | ';
				echo '<a title="' . $lang['import_from_ldif'] . '" target="right_frame"' .
				     'href="' . $import_href .'">' . $lang['import'] . '</a>';
				if( $servers[ $server_id ][ 'auth_type' ] != 'config'  )
					echo ' | <a title="' . $lang['logout_of_this_server'] . '" href="' . $logout_href . 
						'" target="right_frame">' . $lang['logout'] . '</a>';
				echo ' )</nobr></td></tr>';
				
				if( $servers[$server_id]['auth_type'] != 'config' && have_auth_info( $server_id ) ) {
					$logged_in_dn = get_logged_in_dn( $server_id );
					echo "<tr><td class=\"links\" colspan=\"100\"><nobr>" . $lang['logged_in_as'];
					if( strcasecmp( "anonymous", $logged_in_dn ) )
						echo "<a class=\"logged_in_dn\" href=\"edit.php?server_id=$server_id&amp;dn=" . 
							rawurlencode(get_logged_in_dn($server_id)) . "\" target=\"right_frame\">" . 
							pretty_print_dn( $logged_in_dn ) . "</a>";
					else
						echo "Anonymous";
					echo "</nobr></td></tr>";
				}
				if( is_server_read_only( $server_id ) ) 
					echo "<tr><td class=\"links\" colspan=\"100\"><nobr>" .
						"(" . $lang['read_only'] . ")</nobr></td></tr>";
	
				// Fetch and display the base DN for this server
				if( null == $servers[ $server_id ]['base'] ) {
					$base_dn = try_to_get_root_dn( $server_id );
				} else {
					$base_dn = $servers[ $server_id ]['base'];
				}
	
				// Did we get a base_dn for this server somehow?
				if( $base_dn ) {

					echo "\n\n<!-- base DN row -->\n<tr>\n";
				
					// is the root of the tree expanded already?
					if( isset( $tree[$server_id][$base_dn] ) ) {
						$expand_href =  "collapse.php?server_id=$server_id&amp;" .
						"dn=" . rawurlencode( $base_dn );
						$expand_img = "images/minus.png";
                        $expand_alt = "-";
						$child_count = number_format( count( $tree[$server_id][$base_dn] ) );
					} else {
						$expand_href =  "expand.php?server_id=$server_id&amp;" .
						"dn=" . rawurlencode( $base_dn );
						$expand_img = "images/plus.png";
                        $expand_alt = "+";
						$limit = isset( $search_result_size_limit ) ? $search_result_size_limit : 50;
                        if( is_server_low_bandwidth( $server_id ) ) {
                            $child_count = null;
                        } else {
                            $child_count = count( get_container_contents( $server_id, $base_dn, $limit+1, 
                                                        '(objectClass=*)', get_tree_deref_setting() ) );
                            if( $child_count > $limit )
                                $child_count = $limit . '+';
                        }
					}
	
					$edit_href = "edit.php?server_id=$server_id&amp;dn=" . rawurlencode( $base_dn );
	
					$icon = isset( $tree_icons[ $server_id ][ $base_dn ] ) ?
							$tree_icons[ $server_id ][ $base_dn ] :
							get_icon( $server_id, $base_dn );
	
					// Shall we draw the "mass-delete" checkbox?
					if( mass_delete_enabled( $server_id ) ) {
						echo "<td><input type=\"checkbox\" name=\"mass_delete[" . htmlspecialchars($base_dn) . "]\" /></td>\n";
					}
					
					echo "<td class=\"expander\">";
					echo "<a href=\"$expand_href\"><img src=\"$expand_img\" alt=\"$expand_alt\" /></a></td>";
					echo "<td class=\"icon\"><a href=\"$edit_href\" target=\"right_frame\">";
					echo "<img src=\"images/$icon\" alt=\"img\" /></a></td>\n";
					echo "<td class=\"rdn\" colspan=\"98\"><nobr><a href=\"$edit_href\" ";
					echo " target=\"right_frame\">" . pretty_print_dn( $base_dn ) . '</a>';
                    if( $child_count )
                        echo " <span class=\"count\">($child_count)</span>";
					echo "</nobr></td>\n";
					echo "</tr>\n<!-- end of base DN row -->";

				} else { // end if( $base_dn )

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
				if( isset( $tree[$server_id][$base_dn] ) && is_array( $tree[$server_id][$base_dn] ) ) {
					foreach( $tree[ $server_id ][ $base_dn ] as $child_dn )
						draw_tree_html( $child_dn, $server_id, 0 );
						if( ! is_server_read_only( $server_id ) ) {
						echo '<td class="spacer"></td>';
						if( show_create_enabled( $server_id ) ) {
							echo '<td class="icon"><a href="' . $create_href .
								'" target="right_frame"><img src="images/star.png" alt="' . $lang['new'] . '" /></a></td>';
							echo '<td class="create" colspan="100"><a href="' . $create_href . 
								'" target="right_frame" title="' . $lang['create_new_entry_in'] . ' ' . 
								$base_dn.'">' . $lang['create_new'] . '</a></td></tr>';
						}
					}
				}

			} else { // end if( pla_ldap_connect( $ds ) )
				// could not connect to LDAP server
				echo "<tr>\n";
				echo "<td class=\"spacer\"></td>\n";
				echo "<td><img src=\"images/warning_small.png\" alt=\"" . $lang['warning'] . "\" /></td>\n";
				echo "<td colspan=\"99\"><small><span style=\"color:red\">" . $lang['could_not_connect'] . "</span></small></td>\n";
				echo "</tr>\n";

				if( $servers[ $server_id ][ 'auth_type' ] != 'config' ) {
					$logout_href =  'logout.php?server_id=' . $server_id;
					echo "<tr>\n";
					echo "<td class=\"spacer\"></td>\n";
					echo "<td class=\"spacer\"></td>\n";
					echo "<td colspan=\"99\"><small>";
					echo "<a target=\"right_frame\" href=\"$logout_href\">" . $lang['logout'] . "</a></small></td>\n";
					echo "</tr>\n";
				}
				

				// Proceed to the next server in the list. We cannot do anything mroe here.
				continue;
			}

		} else { // end if have_auth_info( $server_id )

			// We don't have enough information to login to this server
			// Draw the "login..." link
			$login_href = "login_form.php?server_id=$server_id";
			echo '<tr class="login">';
			echo '<td class="spacer"></td>';
			echo '<td><a href="' . $login_href . '" target="right_frame">';
			echo '<img src="images/uid.png" align="top" alt="' . $lang['login'] . '" /></a></td>';
			echo '<td colspan="99"><a href="' . $login_href . '" target="right_frame">' . $lang['login_link'] . '</a>';
			echo '</td></tr>';
		}
		
		if( mass_delete_enabled( $server_id ) ) {
			echo "<tr><td colspan=\"99\"><input type=\"submit\" value=\"Delete Checked Entries\" \></td></tr>\n";
			echo "<!-- The end of the mass deletion form -->\n";
			echo "</form>\n";
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
	global $servers, $tree, $tree_icons, $lang, $search_result_size_limit;
	$id = $server_id;
	
	$encoded_dn = rawurlencode( $dn );
	$expand_href = "expand.php?server_id=$id&amp;dn=$encoded_dn";
	$collapse_href = "collapse.php?server_id=$id&amp;dn=$encoded_dn";
	$edit_href = "edit.php?server_id=$id&amp;dn=$encoded_dn";

	// should never happen, but just in case
	if( ! isset( $tree_icons[ $server_id ][ $dn ] ) )
		$tree_icons[ $server_id ][ $dn ] = get_icon( $server_id, $dn );
	$img_src = 'images/' . $tree_icons[ $server_id ][ $dn ];

	$rdn = get_rdn( $dn );

	echo '<tr>';

	for( $i=0; $i<=$level; $i++ ) {
		echo '<td class="spacer"></td>' . "\n";
	}

	// Shall we draw the "mass-delete" checkbox?
	if( mass_delete_enabled( $server_id ) ) {
		echo "<td>
			<input 
			type=\"checkbox\" 
			name=\"mass_delete[" . htmlspecialchars($dn) . "]\" />
			</td>\n";
	}

	// is this node expanded? (deciding whether to draw "+" or "-")
	if( isset( $tree[$server_id][$dn] ) ) { ?>
		<td class="expander">
			<nobr>
			<a href="<?php echo $collapse_href; ?>"><img src="images/minus.png" alt="-" /></a>
			</nobr>
		</td>
		<?php  $child_count = number_format( count( $tree[$server_id][$dn] ) );
	} else { ?>	
		<td class="expander">
			<nobr>
			<a href="<?php echo $expand_href; ?>"><img src="images/plus.png" alt="+" /></a>
			</nobr>
		</td>
		<?php  	$limit = isset( $search_result_size_limit ) ? $search_result_size_limit : 50;
            if( is_server_low_bandwidth( $server_id ) ) {
                $child_count = null;
            } else {
                $child_count = count( get_container_contents( $server_id, $dn, $limit+1 ) );
                if( $child_count > $limit )
                    $child_count = $limit . '+';
            }
	} ?>	

	<td class="icon">
		<a href="<?php echo $edit_href; ?>"
		   target="right_frame"
		   name="<?php echo $server_id; ?>_<?php echo $encoded_dn; ?>"><img src="<?php echo $img_src; ?>" alt="img" /></a>
	</td>
	<td class="rdn" colspan="<?php echo (97-$level); ?>">
		<nobr>
			<a href="<?php echo $edit_href; ?>"
				target="right_frame"><?php echo ( pretty_print_dn( $rdn ) ); ?></a>
				<?php if( $child_count ) { ?>
					<span class="count">(<?php echo $child_count; ?>)</span>
				<?php } ?>
		</nobr>
	</td>
	</tr>

	<?php 

	if( isset( $tree[$server_id][$dn] ) && is_array( $tree[$server_id][$dn] ) )	{
		foreach( $tree[$server_id][$dn] as $dn ) { 
			draw_tree_html( $dn, $server_id, $level+1 );
		}
		if( show_create_enabled( $server_id ) ) {

			// print the "Create New object" link.
			$create_href = "create_form.php?server_id=$server_id&amp;container=$encoded_dn";
			echo '<tr>';
			for( $i=0; $i<=$level; $i++ ) {
				echo '<td class="spacer"></td>';
			}
			echo '<td class="spacer"></td>';
			echo '<td class="icon"><a href="' . $create_href .
				'" target="right_frame"><img src="images/star.png" alt=\"' . $lang['new'] . '\" /></a></td>';
			echo '<td class="create" colspan="' . (97-$level) . '"><a href="' . $create_href . 
				'" target="right_frame" title="' . $lang['create_new_entry_in'] . ' ' . $rdn.'">' . 
				$lang['create_new'] . '</a></td></tr>';
			echo '</tr>';
		}
	}
}

?>
