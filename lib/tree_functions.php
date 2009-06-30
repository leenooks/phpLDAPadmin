<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/Attic/tree_functions.php,v 1.20.2.23 2007/03/18 03:21:18 wurley Exp $

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
		debug_log('draw_server_tree(): Entered with ()',33);

	global $ldapserver;
	global $recently_timed_out_servers;
	global $config;

	static $tm = null;

	$server_id = $ldapserver->server_id;
	$tree = get_cached_item($ldapserver->server_id,'tree');

	# Does this server want mass deletion available?
	if ($ldapserver->isMassDeleteEnabled()) {
		echo '<form name="mass_delete" action="mass_delete.php" method="post" target="right_frame">';
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
		echo "\n\n";
	}

	echo '<table class="tree" cellspacing="0">';
	echo '<tr class="server">';
	printf('<td class="icon"><img src="images/server.png" alt="%s" /></td>',_('Server'));
	printf('<td colspan="99"><a name="%s"></a>',$ldapserver->server_id);
	printf('<span style="white-space: nowrap;">%s ',htmlspecialchars($ldapserver->name));

	if ($ldapserver->haveAuthInfo() && $ldapserver->auth_type != 'config')
		printf('<acronym title="%s"><img width=14 height=14 src="images/timeout.png" alt="timeout" /></acronym>',
			sprintf(_('Inactivity will log you off at %s'),strftime('%H:%M',time()+($ldapserver->session_timeout*60))));

	echo '</span></td></tr>';

	/* do we have what it takes to authenticate here, or do we need to
	   present the user with a login link (for 'cookie' and 'session' auth_types)? */
	if ($ldapserver->haveAuthInfo()) {

		if ($ldapserver->connect(false)) {
			$schema_href = sprintf('schema.php?server_id=%s" target="right_frame',$ldapserver->server_id);
			$search_href = sprintf('search.php?server_id=%s" target="right_frame',$ldapserver->server_id);
			$refresh_href = sprintf('refresh.php?server_id=%s',$ldapserver->server_id);
			$logout_href = get_custom_file($ldapserver->server_id,'logout.php','').'?server_id='.$ldapserver->server_id;
			$info_href = sprintf('server_info.php?server_id=%s',$ldapserver->server_id);
			$import_href = sprintf('ldif_import_form.php?server_id=%s',$ldapserver->server_id);
			$export_href = sprintf('export_form.php?server_id=%s',$ldapserver->server_id);

			# Draw the quick-links below the server name:
			echo '<tr><td colspan="100" class="links">';
			echo '<span style="white-space: nowrap;">';
			echo '( ';
			printf('<a title="%s %s" href="%s">%s</a> | ',_('View schema for'),$ldapserver->name,$schema_href,_('schema'));
			printf('<a title="%s %s" href="%s">%s</a> | ',_('search'),$ldapserver->name,$search_href,_('search'));
			printf('<a title="%s %s" href="%s">%s</a> | ',_('Refresh all expanded containers for'),$ldapserver->name,$refresh_href,_('refresh'));
			printf('<a title="%s" target="right_frame" href="%s">%s</a> | ',_('View server-supplied information'),$info_href,_('info'));
			printf('<a title="%s" target="right_frame" href="%s">%s</a> | ',_('Import entries from an LDIF file'),$import_href,_('import'));
			printf('<a href="%s" target="right_frame">%s</a>',$export_href,_('export'));

			if ($ldapserver->auth_type != 'config')
				printf(' | <a title="%s" href="%s" target="right_frame">%s</a>',_('Logout of this server'),$logout_href,_('logout'));

			echo ' )</span></td></tr>';

			if ($ldapserver->auth_type != 'config') {
				$logged_in_dn = $ldapserver->getLoggedInDN();
				echo '<tr><td class="links" colspan="100"><span style="white-space: nowrap;">'._('Logged in as: ');

				if ($ldapserver->getDNBase($logged_in_dn) == $logged_in_dn) {
					$logged_in_branch = '';
					$logged_in_dn_array = array();

				} else {
					$logged_in_branch = preg_replace('/,'.$ldapserver->getDNBase($logged_in_dn).'$/','',$logged_in_dn);
					$logged_in_dn_array = pla_explode_dn($logged_in_branch);
				}

				$bases = $ldapserver->getDNBase($logged_in_dn);
				if (is_array($bases) && count($bases))
					$logged_in_dn_array[] = $bases;

				$rdn = $logged_in_dn;

				if (strcasecmp('anonymous',$logged_in_dn)) {

					foreach ($logged_in_dn_array as $rdn_piece) {
						printf('<a class="logged_in_dn" href="template_engine.php?server_id=%s&amp;dn=%s" target="right_frame">%s</a>',
							$server_id,rawurlencode($rdn),pretty_print_dn($rdn_piece));

						if ($rdn_piece != end($logged_in_dn_array))
							echo ',';

						$rdn = substr($rdn,(1 + strpos($rdn,',')));
					}

				} else
					echo 'Anonymous';

				echo '</span></td></tr>';
			}

			if ($ldapserver->isReadOnly())
				printf('<tr><td class="links" colspan="100"><span style="white-space: nowrap;">(%s)</span></td></tr>',_('read only'));

			$javascript_forms = '';
			$javascript_id = 0;
			$tree_plm = '';

			if ($config->GetValue('appearance','tree_plm') && ! isset($tm)) {
				$tm = new TreeMenu();
				$tm->setDirroot(JSDIR.'phplayersmenu/');
				$tm->setIcondir(HTDOCDIR.'/images/');
				$tm->setIconwww('images/');
				$tm->setImgwww(JSDIR.'phplayersmenu/menuimages/');
			}

			foreach ($ldapserver->getBaseDN() as $base_dn) {

				# Did we get a base_dn for this server somehow?
				if ($base_dn) {
					# is the root of the tree expanded already?
					if (isset($tree['browser'][$base_dn]['open']) && $tree['browser'][$base_dn]['open']) {
						$expand_href = sprintf('collapse.php?server_id=%s&amp;dn=%s',
							$ldapserver->server_id,rawurlencode( $base_dn ));
						$expand_img = 'images/minus.png';
						$expand_alt = '-';
						$child_count = number_format(count($tree['browser'][$base_dn]['children']));

					} else {
						/* Check if the LDAP server is not yet initialized
						   (ie, the base DN configured in config.php does not exist) */
						if (! $ldapserver->dnExists($base_dn)) {
							$javascript_id++;

							printf('<tr><td class="spacer"></td><td><img src="images/unknown.png" /></td><td colspan="98">%s</td></tr>',
								pretty_print_dn($base_dn));

							/* Move this form and add it to the end of the html - otherwise the javascript
							   doesnt work when isMassDeleteEnabled returning true. */
							$javascript_forms .= sprintf('<form name="create_base_form_%s" method="post" action="template_engine.php" target="right_frame">',
								$javascript_id);
							$javascript_forms .= sprintf('<input type="hidden" name="template" value="custom" />');
							$javascript_forms .= sprintf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
							$javascript_forms .= sprintf('<input type="hidden" name="container" value="%s" />',htmlspecialchars($rdn));
							$javascript_forms .= sprintf('<input type="hidden" name="rdn" value="%s" />',get_rdn($base_dn));
							$javascript_forms .= sprintf('</form>');

							printf('<tr><td class="spacer"></td><td colspan="98"><small>%s<a href="javascript:document.create_base_form_%s.submit()">%s</a></small></td></tr>',
								_('This base entry does not exist.'),$javascript_id,_('Create it?'));

							continue;

						} else {
							$expand_href = sprintf('expand.php?server_id=%s&amp;dn=%s',
								$ldapserver->server_id,rawurlencode( $base_dn ));
							$expand_img = 'images/plus.png';
							$expand_alt = '+';
							# $size_limit = $config->GetValue('search','size_limit');
							$size_limit = -1;

							if ($ldapserver->isLowBandwidth()) {
								$child_count = null;

							} else {
								$children = $ldapserver->getContainerContents($base_dn,$size_limit+1,'(objectClass=*)',
									$config->GetValue('deref','tree'));

								$child_count = count($children);

#								if ($child_count > $size_limit)
#									$child_count = $size_limit.'+';
							}
						}
					}

					$create_href = sprintf('create_form.php?server_id=%s&amp;container=%s',$ldapserver->server_id,rawurlencode($base_dn));
					$edit_href = sprintf('template_engine.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,rawurlencode($base_dn));

					$icon = isset($tree['browser'][$base_dn]['icon']) ? $tree['browser'][$base_dn]['icon'] : get_icon($ldapserver,$base_dn);


					if ($config->GetValue('appearance','tree_plm')) {
						$tree_plm .= sprintf(".|%s|%s|%s|%s|%s|%s\n",
							pretty_print_dn($base_dn).($child_count ? ' ('.$child_count.')' : ''),$edit_href,$base_dn,$icon,'right_frame',0);

					} else {
						echo '<tr>';

						# Shall we draw the "mass-delete" checkbox?
						if ($ldapserver->isMassDeleteEnabled())
							printf('<td><input type="checkbox" name="mass_delete[%s]" /></td>',htmlspecialchars($base_dn));

						printf('<td class="expander"><a href="%s"><img src="%s" alt="%s" /></a></td>',$expand_href,$expand_img,$expand_alt);
						printf('<td class="icon"><a href="%s" target="right_frame"><img src="images/%s" alt="img" /></a></td>',$edit_href,$icon);
						printf('<td class="rdn" colspan="98"><span style="white-space: nowrap;"><a href="%s" target="right_frame">%s</a>',$edit_href,pretty_print_dn($base_dn));

						if ($child_count)
							printf(' <span class="count">(%s)</span>',$child_count);

						echo '</span></td>';
						echo '</tr>';
					}

				} else { // end if( $base_dn )

					# The server refuses to give out the base dn
					printf('<tr><td class="spacer"></td><td colspan="98"><small>%s<br />%s<br /><b>%s</b></small></td></tr>',
						_('Could not determine the root of your LDAP tree.'),_('It appears that the LDAP server has been configured to not reveal its root.'),_('Please specify it in config.php'));

					# Proceed to the next server. We cannot draw anything else for this server.
					continue;
				}

				flush();

				if ($config->GetValue('appearance','tree_plm')) {
					foreach ($children as $child_dn)
						$tree_plm .= draw_tree_plm($child_dn,$ldapserver);

				} else {
					# Is the root of the tree expanded already?
					if (isset($tree['browser'][$base_dn]['open'] ) && $tree['browser'][$base_dn]['open']) {

						if ($ldapserver->isShowCreateEnabled() && count($tree['browser'][$base_dn]['children']) > 10 )
							draw_create_link($ldapserver->server_id,$base_dn,-1,urlencode($base_dn));

						foreach ($tree['browser'][$base_dn]['children'] as $child_dn)
							draw_tree_html($child_dn,$ldapserver,0);

						if (! $ldapserver->isReadOnly()) {
							echo '<tr><td class="spacer"></td></tr>';

							if ($ldapserver->isShowCreateEnabled())
								draw_create_link($ldapserver->server_id,$base_dn,-1,urlencode($base_dn));
						}
					}
				}
			} // foreeach

			if ($config->GetValue('appearance','tree_plm')) {
				$tm->setMenuStructureString($tree_plm);
				$tm->parseStructureForMenu('pla_tree_'.$ldapserver->server_id);
				$tm->setTreeMenuTheme('');
				$tm->newTreeMenu('pla_tree_'.$ldapserver->server_id);
				echo '<tr><td colspan=99>'.$tm->getTreeMenu('pla_tree_'.$ldapserver->server_id).'</td></tr>';
			}

		} else { // end if( $ldapserver->connect(false) )

			# could not connect to LDAP server
			printf('<tr><td class="spacer"></td><td><img src="images/warning_small.png" alt="%s" /></td><td colspan="99"><small><span style="color:red">%s</span></small></td></tr>',_('warning'),_('Could not connect to LDAP server.'));

			if ($ldapserver->auth_type != 'config') {
				$logout_href = sprintf('%s?server_id=%s',get_custom_file($ldapserver->server_id,'logout.php',''),$ldapserver->server_id);

				printf('<tr><td class="spacer"></td><td class="spacer"></td><td colspan="99"><small><a target="right_frame" href="%s">%s</a></small></td></tr>',
					$logout_href,_('logout'));
			}

			# Proceed to the next server in the list. We cannot do anything mroe here.
			return;
		}

	} else { // end if $ldapserver->haveAuthInfo()

		/* We don't have enough information to login to this server
		   Draw the "login..." link */
		$login_href = sprintf('%s?server_id=%s',get_custom_file($ldapserver->server_id,'login_form.php',''),$ldapserver->server_id);
		printf('<tr class="login"><td class="spacer"></td><td><a href="%s" target="right_frame"><img src="images/uid.png" align="top" alt="%s" /></a></td><td colspan="99"><a href="%s" target="right_frame">%s</a></td></tr>',$login_href,_('login'),$login_href,_('Login...'));

		# If the server recently timed out display the message
		if (in_array($ldapserver->server_id,$recently_timed_out_servers))
			printf('<tr><td class="spacer"></td><td colspan="100" class="links">%s</td></tr>',_('(Session timed out. Automatically logged out.)'));
	}

	if ($ldapserver->isMassDeleteEnabled() && ! $config->GetValue('appearance','tree_plm')) {
		printf('<tr><td colspan="99"><input type="submit" value="%s" /></td></tr>',_('Delete Checked Entries'));
		echo '<!-- The end of the mass deletion form -->';
		echo '</table>';
		echo '</form>';
	} else {
		echo '</table>';
	}
	echo "\n\n";

	if (isset($javascript_forms) && $javascript_forms) {
		echo "<!-- Forms for javascript submit to call to create base_dns -->\n";
		echo $javascript_forms;
		echo "<!-- The end of the forms for javascript submit to call to create base_dns -->\n";
	}
}

/**
 * Recursively descend on the given dn and draw the tree in html
 *
 * @param dn $dn Current dn.
 * @param object $LDAPServer LDAPServer object
 * @param int $level Level to start drawing (defaults to 0)
 */
function draw_tree_html($dn,$ldapserver,$level=0) {
	global $config;

	$tree = get_cached_item($ldapserver->server_id,'tree');
	$encoded_dn = rawurlencode($dn);
	$expand_href = sprintf('expand.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);
	$collapse_href = sprintf('collapse.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);
	$edit_href = sprintf('template_engine.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);
	$img_src = sprintf('images/%s',$tree['browser'][$dn]['icon']);
	$rdn = get_rdn($dn);

	echo '<tr>';

	for ($i=0;$i<=$level;$i++)
		echo '<td class="spacer"></td>';

	# Shall we draw the "mass-delete" checkbox?
	if ($ldapserver->isMassDeleteEnabled())
		printf('<td><input type="checkbox" name="mass_delete[%s]" /></td>',htmlspecialchars($dn));

	# Is this node expanded? (deciding whether to draw "+" or "-")
	if (isset($tree['browser'][$dn]['open']) && $tree['browser'][$dn]['open']) {
		$child_count = number_format(count($tree['browser'][$dn]['children']));

		if ((! $child_count) && (! $ldapserver->isShowCreateEnabled()))
			echo '<td class="expander"><span style="white-space: nowrap;"><img src="images/minus.png" alt="-" /></span></td>';
		else
			printf('<td class="expander"><span style="white-space: nowrap;"><a href="%s"><img src="images/minus.png" alt="-" /></a></span></td>',$collapse_href);

	} else {
		$size_limit = $config->GetValue('search','size_limit');

		if ($ldapserver->isLowBandwidth()) {
			$child_count = null;

		} else {
			$child_count = count($ldapserver->getContainerContents($dn,$size_limit+1,'(objectClass=*)',$config->GetValue('deref','tree')));

			if ($child_count > $size_limit)
				$child_count = $size_limit.'+';
		}

		if ((! $child_count) && (! $ldapserver->isShowCreateEnabled()))
			echo '<td class="expander"><span style="white-space: nowrap;"><img src="images/minus.png" alt="-" /></span></td>';
		else
			printf('<td class="expander"><span style="white-space: nowrap;"><a href="%s"><img src="images/plus.png" alt="+" /></a></span></td>',$expand_href);
	}

	printf('<td class="icon"><a href="%s" target="right_frame" name="%s_%s"><img src="%s" alt="img" /></a></td>',
		$edit_href,$ldapserver->server_id,$encoded_dn,$img_src);

	printf('<td class="rdn" colspan="%s"><span style="white-space: nowrap;">',97-$level);
	printf('<a href="%s" target="right_frame">%s</a>',$edit_href,draw_formatted_dn($ldapserver,$dn));

	if ($child_count)
		printf(' <span class="count">(%s)</span>',$child_count);

	echo '</span></td></tr>';

	if (isset($tree['browser'][$dn]['open']) && $tree['browser'][$dn]['open']) {
		/* Draw the "create new" link at the top of the tree list if there are more than 10
		   entries in the listing for this node. */

		if ((count($tree['browser'][$dn]['children']) > 10) && ($ldapserver->isShowCreateEnabled()))
			draw_create_link($ldapserver->server_id,$rdn,$level,$encoded_dn);

		foreach ($tree['browser'][$dn]['children'] as $dn)
			draw_tree_html($dn,$ldapserver,$level+1);

		# Always draw the "create new" link at the bottom of the listing
		if ($ldapserver->isShowCreateEnabled())
			draw_create_link($ldapserver->server_id,$rdn,$level,$encoded_dn);
	}
}

/**
 * Recursively descend on the given dn and draw the tree in plm
 *
 * @param dn $dn Current dn.
 * @param object $LDAPServer LDAPServer object
 * @param int $level Level to start drawing (defaults to 2)
 * @todo: Currently draw PLM only shows the first 50 entries of the base children - possibly the childrens children too. Have disabed the size_limit on the base - need to check that it doesnt affect non PLM tree viewer and children where size > size_limit.
 */
function draw_tree_plm($dn,$ldapserver,$level=2) {
	if (DEBUG_ENABLED)
		debug_log('draw_tree_plm(): Entered with (%s,%s,%s)',33,
			$dn,$ldapserver,$level);

	global $config;

	$tree = get_cached_item($ldapserver->server_id,'tree');
	$encoded_dn = rawurlencode($dn);
	#$expand_href = sprintf('expand.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);
	$edit_href = sprintf('template_engine.php?server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);
	#$img_src = sprintf('images/%s',$tree['browser'][$dn]['icon']);
	$rdn = get_rdn($dn);

	$dots = '';
	for ($i=1;$i<=$level;$i++)
		$dots .= '.';

	# Have we tranversed this part of the tree yet?
	if (isset($tree['browser'][$dn]['open'])) {
		$tree_plm = sprintf("%s|%s|%s|%s|%s|%s|%s\n",
			$dots,
			$rdn.' ('.number_format(count($tree['browser'][$dn]['children'])).')',
			$edit_href,$dn,$tree['browser'][$dn]['icon'],'right_frame',
			(isset($tree['browser'][$dn]['open']) ? $tree['browser'][$dn]['open'] : 0));

		foreach ($tree['browser'][$dn]['children'] as $dn)
			$tree_plm .= draw_tree_plm($dn,$ldapserver,$level+1);

	} else {
		$size_limit = $config->GetValue('search','size_limit');
		$child_count = count($ldapserver->getContainerContents($dn,$size_limit+1,'(objectClass=*)',$config->GetValue('deref','tree')));
		if ($child_count > $size_limit)
			$child_count = $size_limit.'+';

		if ($child_count) {
			$tree_plm = sprintf("%s|%s|%s|%s|%s|%s|%s|%s\n",
				$dots,
				$rdn.' ('.$child_count.')',
				$edit_href,$dn,$tree['browser'][$dn]['icon'],
				'right_frame',
				(isset($tree['browser'][$dn]['open']) ? $tree['browser'][$dn]['open'] : 0),
				$child_count);
		} else {
			$tree_plm = sprintf("%s|%s|%s|%s|%s|%s|%s|%s\n",
				$dots,
				$rdn.' (0)',
				$edit_href,$dn,$tree['browser'][$dn]['icon'],
				'right_frame',
				(isset($tree['browser'][$dn]['open']) ? $tree['browser'][$dn]['open'] : 0),
				$child_count);
		}
	}

	if (DEBUG_ENABLED)
		debug_log('draw_tree_plm(): Returning (%s)',33,$tree_plm);

	return $tree_plm;
}

/**
 * Print the HTML to show the "create new entry here".
 *
 * @param int $server_id
 * @param dn $rdn
 * @param int $level
 * @param dn $encoded_dn
 */
function draw_create_link($server_id,$rdn,$level,$encoded_dn) {
	# print the "Create New object" link.
	$create_href = sprintf('create_form.php?server_id=%s&amp;container=%s',$server_id,$encoded_dn);

	echo '<tr>';
	for ($i=0;$i<=$level;$i++)
		echo '<td class="spacer"></td>';

	echo '<td class="spacer"></td>';
	printf('<td class="icon"><a href="%s" target="right_frame"><img src="images/star.png" alt="%s" /></a></td>',
		$create_href,_('new'));
	printf('<td class="create" colspan="%s"><a href="%s" target="right_frame" title="%s %s">%s</a></td>',
		97-$level,$create_href,_('Create a new entry in'),$rdn,_('Create new entry here'));
	echo '</tr>';
}
?>
