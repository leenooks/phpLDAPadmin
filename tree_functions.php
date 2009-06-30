<?php

/**
 * tree_functions.php
 * This function displays the LDAP tree for all the servers that you have
 * in config.php. We read the session variable 'tree' to know which
 * dns are expanded or collapsed. No query string parameters are expected,
 * however, you can use a '#' offset to scroll to a given dn. The syntax is
 * tree.php#<server_id>_<rawurlencoded dn>, so if I wanted to scroll to
 * dc=example,dc=com for server 3, the URL would be: 
 *	tree.php#3_dc%3Dexample%2Cdc%3Dcom
 */
function draw_server_tree()
{
	global $server_id;
	global $servers;
	global $lang;
	global $tree;
	global $tree_icons;

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
		if( ! pla_ldap_connection_is_error( pla_ldap_connect( $server_id ), false ) ) {
			$schema_href = 'schema.php?server_id=' . $server_id  . '" target="right_frame';
			$search_href = 'search.php?server_id=' . $server_id  . '" target="right_frame';
			$refresh_href = 'refresh.php?server_id=' . $server_id;
			$create_href =  'create_form.php?server_id=' . $server_id . '&amp;container=' .
				rawurlencode( $servers[$server_id]['base'] );
			$logout_href = get_custom_file( $server_id, 'logout.php') . '?server_id=' . $server_id;
			$info_href = 'server_info.php?server_id=' . $server_id;
			$import_href =  'ldif_import_form.php?server_id=' . $server_id;
            $export_href = 'export_form.php?server_id=' . $server_id;
	
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
			echo '<a title="' . $lang['view_server_info'] . '" target="right_frame" '.
				'href="' . $info_href . '">' . $lang['info'] . '</a> | ';
			echo '<a title="' . $lang['import_from_ldif'] . '" target="right_frame" ' .
				'href="' . $import_href .'">' . $lang['import'] . '</a> | ';
            echo '<a href="' . $export_href . '" target="right_frame">' . $lang['export_lcase'] . '</a>';
			if( $servers[ $server_id ][ 'auth_type' ] != 'config'  )
				echo ' | <a title="' . $lang['logout_of_this_server'] . '" href="' . $logout_href . 
					'" target="right_frame">' . $lang['logout'] . '</a>';
			echo ' )</nobr></td></tr>';

			if( $servers[$server_id]['auth_type'] != 'config' ) {
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
			if( null == $servers[ $server_id ]['base'] )
				$base_dn = try_to_get_root_dn( $server_id );
			else
				$base_dn = $servers[ $server_id ]['base'];
	
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
                    // Check if the LDAP server is not yet initialized 
                    // (ie, the base DN configured in config.php does not exist)
                    if( ! dn_exists( $server_id, $base_dn ) ) {
                        $create_base_href = "creation_template.php?template=custom&amp;server_id=$server_id";
                        ?>

                        <tr>
                        <td class="spacer"></td>
                        <td><img src="images/unknown.png" /></td>
                        <td colspan="98"><?php echo pretty_print_dn( $base_dn ); ?></td>
                        </tr>

                        <form name="create_base_form" method="post" action="creation_template.php" target="right_frame">
                        <input type="hidden" name="template" value="custom" />
                        <input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
                        <input type="hidden" name="container" value="" />
                        <input type="hidden" name="rdn" value="<?php echo htmlspecialchars( $base_dn ); ?>" />
                        </form>

                        <tr>
                        <td class="spacer"></td>
                        <td colspan="98">
                        <small><?php echo $lang['base_entry_does_not_exist']; ?>
                        <a href="javascript:document.create_base_form.submit()"><?php echo $lang['create_it']; ?></a></small>
                        </td>
                        </tr>


                        <?php 

                        return;
                    } else {
                        $expand_href =  "expand.php?server_id=$server_id&amp;" .
                            "dn=" . rawurlencode( $base_dn );
                        $expand_img = "images/plus.png";
                        $expand_alt = "+";
                        $limit = isset( $search_result_size_limit ) ? $search_result_size_limit : 50;
                        if( is_server_low_bandwidth( $server_id ) ) {
                            $child_count = null;
                        } else {
                            $child_count = count( get_container_contents( 
                                        $server_id, $base_dn, $limit+1, 
                                        '(objectClass=*)', get_tree_deref_setting() ) );
                            if( $child_count > $limit )
                                $child_count = $limit . '+';
                        }
                    }
				}
	
				$edit_href = "edit.php?server_id=$server_id&amp;dn=" . rawurlencode( $base_dn );

				$icon = isset( $tree_icons[ $server_id ][ $base_dn ] )
					? $tree_icons[ $server_id ][ $base_dn ]
					: get_icon( $server_id, $base_dn );

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

            if( show_create_enabled( $server_id ) && isset( $tree[ $server_id ][ $base_dn ])
                && count( $tree[ $server_id ][ $base_dn ] ) > 10 )
                draw_create_link( $server_id, $base_dn, -1, urlencode( $base_dn ));

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
					return;
				} else {
					// For some unknown reason, we couldn't determine the base dn
					echo "<tr><td class=\"spacer\"></td><td colspan=\"99\"><small><nobr>";
					echo $lang['could_not_determine_root'];
					echo '<br />';
					echo $lang['please_specify_in_config'];
					echo "</small></nobr></td></tr>";
					// Proceed to the next server. We cannot draw anything else for this server.
					return;
				}
			}
	
			flush();
	
			// Is the root of the tree expanded already?
			if( isset( $tree[$server_id][$base_dn] ) && is_array( $tree[$server_id][$base_dn] ) ) {
				foreach( $tree[ $server_id ][ $base_dn ] as $child_dn )
					draw_tree_html( $child_dn, $server_id, 0 );
				if( ! is_server_read_only( $server_id ) ) {
					echo '<tr><td class="spacer"></td>';
					if( show_create_enabled( $server_id ) ) {
						echo '<td class="icon"><a href="' . $create_href .
							'" target="right_frame"><img src="images/star.png" alt="' . 
							$lang['new'] . '" /></a></td>';
						echo '<td class="create" colspan="100"><a href="' . $create_href
							. '" target="right_frame" title="' . $lang['create_new_entry_in']
							. ' ' . $base_dn.'">' . $lang['create_new'] . '</a></td></tr>';
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
				$logout_href = get_custom_file( $server_id, 'logout.php') . '?server_id=' . $server_id;
				echo "<tr>\n";
				echo "<td class=\"spacer\"></td>\n";
				echo "<td class=\"spacer\"></td>\n";
				echo "<td colspan=\"99\"><small>";
				echo "<a target=\"right_frame\" href=\"$logout_href\">" . $lang['logout'] . "</a></small></td>\n";
				echo "</tr>\n";
			}
			// Proceed to the next server in the list. We cannot do anything mroe here.
			return;
		}

	} else { // end if have_auth_info( $server_id )
		// We don't have enough information to login to this server
		// Draw the "login..." link
		$login_href = get_custom_file( $server_id, 'login_form.php' ) . "?server_id=$server_id";
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

?>
