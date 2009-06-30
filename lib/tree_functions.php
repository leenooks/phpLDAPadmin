<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/tree_functions.php,v 1.19.2.1 2005/10/09 09:07:22 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */

/**
 * This function displays the LDAP tree for all the servers that you have
 * in config.php. We read the session variable 'tree' to know which
 * dns are expanded or collapsed. No query string parameters are expected,
 * however, you can use a '#' offset to scroll to a given dn. The syntax is
 * tree.php#<server_id>_<rawurlencoded dn>, so if I wanted to scroll to
 * dc=example,dc=com for server 3, the URL would be:
 *	tree.php#3_dc%3Dexample%2Cdc%3Dcom
 */
function draw_server_tree() {
	if (DEBUG_ENABLED)
		debug_log('draw_server_tree(): Entered with ()',2);

	global $lang;
	global $tree;
	global $tree_icons;
	global $ldapserver;
	global $recently_timed_out_servers;
	global $config;

	$server_id = $ldapserver->server_id;

	// Does this server want mass deletion availble?
	if ($ldapserver->isMassDeleteEnabled()) {
		print '<form name="mass_delete" action="mass_delete.php" method="post" target="right_frame">';
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	}

	print '<tr class="server">';
	printf('<td class="icon"><img src="images/server.png" alt="%s" /></td>',$lang['server']);
	printf('<td colspan="99"><a name="%s"></a>',$ldapserver->server_id);
	printf('<nobr>%s ',htmlspecialchars($ldapserver->name));

	if ($ldapserver->haveAuthInfo() && $ldapserver->session_timeout)
		printf('<acronym title="%s"><img widht=14 height=14 src="images/timeout.png" alt="timeout"><acronym>',
			sprintf($lang['timeout_at'],strftime('%H:%M',time()+($ldapserver->session_timeout*60))));

	print '</nobr></td></tr>';

	// do we have what it takes to authenticate here, or do we need to
	// present the user with a login link (for 'cookie' and 'session' auth_types)?
	if ($ldapserver->haveAuthInfo()) {
		if ($ldapserver->connect(false)) {
			$schema_href = sprintf('schema.php?server_id=%s" target="right_frame',$ldapserver->server_id);
			$search_href = sprintf('search.php?server_id=%s" target="right_frame',$ldapserver->server_id);
			$refresh_href = sprintf('refresh.php?server_id=%s',$ldapserver->server_id);
			$logout_href = get_custom_file($ldapserver->server_id,'logout.php','').'?server_id='.$ldapserver->server_id;
			$info_href = sprintf('server_info.php?server_id=%s',$ldapserver->server_id);
			$import_href = sprintf('ldif_import_form.php?server_id=%s',$ldapserver->server_id);
			$export_href = sprintf('export_form.php?server_id=%s',$ldapserver->server_id);

			// Draw the quick-links below the server name:
			// ( schema | search | refresh | create )
			echo '<tr><td colspan="100" class="links">';
			echo '<nobr>';
			echo '( ';
			echo '<a title="' . $lang['view_schema_for'] . ' ' . $ldapserver->name . '"'.
				' href="' . $schema_href . '">' . $lang['schema'] . '</a> | ';
			echo '<a title="' . $lang['search'] . ' ' . $ldapserver->name . '"' .
				' href="' . $search_href . '">' . $lang['search'] . '</a> | ';
			echo '<a title="' . $lang['refresh_expanded_containers'] . ' ' . $ldapserver->name . '"'.
				' href="' . $refresh_href . '">' . $lang['refresh'] . '</a> | ';

//			if ($ldapserver->isShowCreateEnabled())
//				echo '<a title="' . $lang['create_new_entry_on'] . ' ' . $ldapserver->name . '"'.
//					' href="' . sprintf('create_form.php?server_id=%s',$server_id) . '" target="right_frame">' . $lang['create'] . '</a> | ';

			echo '<a title="' . $lang['view_server_info'] . '" target="right_frame" '.
				'href="' . $info_href . '">' . $lang['info'] . '</a> | ';
			echo '<a title="' . $lang['import_from_ldif'] . '" target="right_frame" ' .
				'href="' . $import_href .'">' . $lang['import'] . '</a> | ';
			echo '<a href="' . $export_href . '" target="right_frame">' . $lang['export_lcase'] . '</a>';

			if( $ldapserver->auth_type != 'config' )
				echo ' | <a title="' . $lang['logout_of_this_server'] . '" href="' . $logout_href .
					'" target="right_frame">' . $lang['logout'] . '</a>';

			echo ' )</nobr></td></tr>';

			if ($ldapserver->auth_type != 'config') {
				$logged_in_dn = get_logged_in_dn( $ldapserver );
				echo "<tr><td class=\"links\" colspan=\"100\"><nobr>" . $lang['logged_in_as'];

				if (dn_get_base($ldapserver,$logged_in_dn) == $logged_in_dn) {
					$logged_in_branch = '';
					$logged_in_dn_array = array();
				} else {
					$logged_in_branch = preg_replace("/,".dn_get_base($ldapserver,$logged_in_dn)."$/","",$logged_in_dn);
					$logged_in_dn_array = explode(',',$logged_in_branch);
				}
				$logged_in_dn_array[] = dn_get_base($ldapserver,$logged_in_dn);
				
				$rdn = $logged_in_dn;
				
				if (strcasecmp("anonymous",$logged_in_dn)) {
					foreach ($logged_in_dn_array as $rdn_piece) {
						printf('<a class="logged_in_dn" href="edit.php?server_id=%s&amp;dn=%s" target="right_frame">%s</a>',
							$server_id,rawurlencode($rdn),pretty_print_dn($rdn_piece));

						if ($rdn_piece != end($logged_in_dn_array))
							echo ',';

						$rdn = substr($rdn,(1 + strpos($rdn,',')));
					}

				} else
					echo "Anonymous";

				echo "</nobr></td></tr>";
			}

			if( $ldapserver->isReadOnly() )
				echo "<tr><td class=\"links\" colspan=\"100\"><nobr>" .
					"(" . $lang['read_only'] . ")</nobr></td></tr>";

			$javascript_forms = '';
			$javascript_id = 0;
			foreach ($ldapserver->getBaseDN() as $base_dn) {
				// Did we get a base_dn for this server somehow?
				if ($base_dn) {
					echo "\n\n<!-- base DN row -->\n<tr>\n";

					// is the root of the tree expanded already?
					if( isset( $tree[$ldapserver->server_id][$base_dn] ) ) {
						$expand_href = sprintf('collapse.php?server_id=%s&amp;dn=%s',
							$ldapserver->server_id,rawurlencode( $base_dn ));
						$expand_img = "images/minus.png";
						$expand_alt = "-";
						$child_count = number_format( count( $tree[$ldapserver->server_id][$base_dn] ) );

					} else {
						// Check if the LDAP server is not yet initialized
						// (ie, the base DN configured in config.php does not exist)
						if( ! dn_exists( $ldapserver, $base_dn ) ) {
							$javascript_id++;
					?>

                        <tr>
                        <td class="spacer"></td>
                        <td><img src="images/unknown.png" /></td>
                        <td colspan="98"><?php echo pretty_print_dn( $base_dn ); ?></td>
                        </tr>

			<?php // Move this form and add it to the end of the html - otherwise the javascript
			// doesnt work when isMassDeleteEnabled returning true.
                        $javascript_forms .= sprintf('<form name="create_base_form_%s" method="post" action="creation_template.php" target="right_frame">',$javascript_id);
                        $javascript_forms .= sprintf('<input type="hidden" name="template" value="custom" />');
                        $javascript_forms .= sprintf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
                        $javascript_forms .= sprintf('<input type="hidden" name="container" value="" />');
                        $javascript_forms .= sprintf('<input type="hidden" name="rdn" value="%s" />',htmlspecialchars($base_dn));
                        $javascript_forms .= sprintf('</form>'); ?>

                        <tr>
                        <td class="spacer"></td>
                        <td colspan="98">
                        <small><?php echo $lang['base_entry_does_not_exist']; ?>
                        <a href="javascript:document.create_base_form_<?php echo $javascript_id ?>.submit()"><?php echo $lang['create_it']; ?></a></small>
                        </td>
                        </tr>

                        <?php

							continue;
						} else {
							$expand_href = sprintf('expand.php?server_id=%s&amp;dn=%s',
								$ldapserver->server_id,rawurlencode( $base_dn ));
							$expand_img = "images/plus.png";
							$expand_alt = "+";
							$size_limit = $config->GetValue('search','size_limit');

							if( $ldapserver->isLowBandwidth() ) {
								$child_count = null;

							} else {
								$child_count = count( get_container_contents(
									$ldapserver, $base_dn, $size_limit+1,
									'(objectClass=*)', $config->GetValue('deref','tree') ) );

                                                               if( $child_count > $size_limit )
									$child_count = $size_limit . '+';
                                                       }
						}
					}

					$create_href = sprintf('create_form.php?server_id=%s&amp;container=%s',$ldapserver->server_id,
						rawurlencode( $base_dn ));

					$edit_href = sprintf("edit.php?server_id=%s&amp;dn=%s",$ldapserver->server_id,rawurlencode( $base_dn ));

					$icon = isset( $tree_icons[ $ldapserver->server_id ][ $base_dn ] )
						? $tree_icons[ $ldapserver->server_id ][ $base_dn ]
						: get_icon( $ldapserver, $base_dn );

					// Shall we draw the "mass-delete" checkbox?
					if( $ldapserver->isMassDeleteEnabled() ) {
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

					if( $ldapserver->isShowCreateEnabled() && isset( $tree[ $ldapserver->server_id ][ $base_dn ])
						&& count( $tree[ $ldapserver->server_id ][ $base_dn ] ) > 10 )
							draw_create_link( $ldapserver->server_id, $base_dn, -1, urlencode( $base_dn ));

				} else { // end if( $base_dn )

					# The server refuses to give out the base dn
					printf('<tr><td class="spacer"></td><td colspan="98"><small>%s<br />%s<br /><b>%s</b></small></td></tr>',
						$lang['could_not_determine_root'],$lang['ldap_refuses_to_give_root'],$lang['please_specify_in_config']);

					# Proceed to the next server. We cannot draw anything else for this server.
					continue;
				}

				flush();

				// Is the root of the tree expanded already?
				if( isset( $tree[$ldapserver->server_id][$base_dn] ) && is_array( $tree[$ldapserver->server_id][$base_dn] ) ) {
					foreach( $tree[ $ldapserver->server_id ][ $base_dn ] as $child_dn )
						draw_tree_html( $child_dn, $ldapserver, 0 );

					if( ! $ldapserver->isReadOnly() ) {
						echo '<tr><td class="spacer"></td>';
						if( $ldapserver->isShowCreateEnabled() ) {
							echo '<td class="icon"><a href="' . $create_href .
								'" target="right_frame"><img src="images/star.png" alt="' .
								$lang['new'] . '" /></a></td>';
							echo '<td class="create" colspan="100"><a href="' . $create_href
								. '" target="right_frame" title="' . $lang['create_new_entry_in']
								. ' ' . $base_dn.'">' . $lang['create_new'] . '</a></td></tr>';
						}
					}
				}
			}

		} else { // end if( $ldapserver->connect(false) )
			// could not connect to LDAP server
			echo "<tr>\n";
			echo "<td class=\"spacer\"></td>\n";
			echo "<td><img src=\"images/warning_small.png\" alt=\"" . $lang['warning'] . "\" /></td>\n";
			echo "<td colspan=\"99\"><small><span style=\"color:red\">" . $lang['could_not_connect'] . "</span></small></td>\n";
			echo "</tr>\n";

			if( $ldapserver->auth_type != 'config' ) {
				$logout_href = get_custom_file( $ldapserver->server_id, 'logout.php','') . '?server_id=' . $ldapserver->server_id;
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

	} else { // end if $ldapserver->haveAuthInfo()
		// We don't have enough information to login to this server
		// Draw the "login..." link
		$login_href = get_custom_file( $ldapserver->server_id, 'login_form.php','' ) . "?server_id=$server_id";
		echo '<tr class="login">';
		echo '<td class="spacer"></td>';
		echo '<td><a href="' . $login_href . '" target="right_frame">';
		echo '<img src="images/uid.png" align="top" alt="' . $lang['login'] . '" /></a></td>';
		echo '<td colspan="99"><a href="' . $login_href . '" target="right_frame">' . $lang['login_link'] . '</a>';
		echo '</td></tr>';
		// If the server recently timed out display the message
		if ( in_array($ldapserver->server_id,$recently_timed_out_servers) )
			echo '<tr><td class="spacer"></td><td colspan="100" class="links">' . $lang['session_timed_out_tree'] . '</td></tr>';
	}

	if( $ldapserver->isMassDeleteEnabled() ) {
		echo "<tr><td colspan=\"99\"><input type=\"submit\" value=\"Delete Checked Entries\" \></td></tr>\n";
		echo "<!-- The end of the mass deletion form -->\n";
		echo "</form>\n";
	}
	if (isset($javascript_forms) && $javascript_forms) {
		echo "<!-- Forms for javascript submit to call to create base_dns -->\n";
		echo $javascript_forms;
		echo "<!-- The end of the forms for javascript submit to call to create base_dns -->\n";
	}
}
?>
