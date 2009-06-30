<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/HTMLTree.php,v 1.2.2.10 2008/12/13 02:13:13 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 */
class HTMLTree extends Tree {
	protected $javascript = '';
	/**
 	 * Displays the tree in HTML
	 */
	public function draw() {
		if (DEBUG_ENABLED)
			debug_log('Entered with ()',33,__FILE__,__LINE__,__METHOD__);

		$ldapserver = $this->getLdapServer();

		$this->draw_mass_deletion_start_form();

		echo '<table class="tree" border=0>';
		$this->draw_server_name();

		$this->javascript = '';
		$javascript_id = 0;

		/* Do we have what it takes to authenticate here, or do we need to
		 * present the user with a login link (for 'cookie' and 'session' auth_types)?
		 */
		if ($ldapserver->haveAuthInfo()) {
			if ($ldapserver->connect(false)) {
				$this->draw_menu();

				if ($ldapserver->auth_type != 'config')
					$this->draw_logged_in_dn();
				else
					printf('<tr><td class="blank" colspan="%s">&nbsp;</td></tr>',$this->getDepth()+3);

				if ($ldapserver->isReadOnly())
					printf('<tr><td class="spacer"></td><td class="logged_in" colspan="%s">(%s)</td></tr>',$this->getDepth()+3-1,_('read only'));
				else
					printf('<tr><td class="blank" colspan="%s">&nbsp;</td></tr>',$this->getDepth()+3);

				printf('<tr><td>&nbsp;</td><td><div style="overflow: auto; %s%s"><table class="tree" border=0>',
					$_SESSION[APPCONFIG]->GetValue('appearance','tree_width') ? sprintf('width: %spx; ',$_SESSION[APPCONFIG]->GetValue('appearance','tree_width')) : '',
					$_SESSION[APPCONFIG]->GetValue('appearance','tree_height') ? sprintf('height: %spx; ',$_SESSION[APPCONFIG]->GetValue('appearance','tree_height')) : '');

				foreach ($ldapserver->getBaseDN() as $base_dn) {
					# Did we get a base_dn for this server somehow?
					if ($base_dn) {
						/* Check if the LDAP server is not yet initialized
						 * (ie, the base DN configured in config.php does not exist)
						 */
						if (! $ldapserver->dnExists($base_dn)) {
							$javascript_id++;

							printf('<tr><td class="spacer"></td><td class="spacer"></td><td><img src="%s/unknown.png" /></td><td colspan="%s">%s</td></tr>',IMGDIR,$this->getDepth()+3-3,pretty_print_dn($base_dn));

							/* Move this form and add it to the end of the html - otherwise the javascript
							 * doesnt work when isMassDeleteEnabled returning true.
							 */
#@todo: move to new format and test.
							$this->javascript .= sprintf('<form name="create_base_form_%s" method="post" action="cmd.php?cmd=template_engine">',$javascript_id);
							$this->javascript .= sprintf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
							$this->javascript .= sprintf('<input type="hidden" name="container" value="%s" />',htmlspecialchars(get_container($base_dn)));
							$this->javascript .= sprintf('<input type="hidden" name="rdn" value="%s" />',get_rdn($base_dn));
							$this->javascript .= sprintf('</form>');

							printf('<tr><td class="spacer"></td><td class="spacer"></td><td class="spacer"></td><td colspan="%s"><small>%s<a href="javascript:document.create_base_form_%s.submit()">%s</a></small></td></tr>',$this->getDepth()+3-3,_('This base entry does not exist.'),$javascript_id,_('Create it?'));

							continue;

						} else {
							$this->draw_dn($base_dn,-1);
						}

					} else { // end if ($base_dn)
						# The server refuses to give out the base dn
						printf('<tr><td class="spacer"></td><td class="spacer"></td><td colspan="%s"><small>%s<br />%s<br /><b>%s</b></small></td></tr>',
							$this->getDepth()+3-2,
							_('Could not determine the root of your LDAP tree.'),
							_('It appears that the LDAP server has been configured to not reveal its root.'),
							_('Please specify it in config.php'));

						# Proceed to the Base DN. We cannot draw anything else for this Base DN.
						continue;
					}
				}
				echo '</table></div></td></tr>';

			} else { // end if( $ldapserver->connect(false) )
				# @todo: need this message to display the LDAP server name, so we know which one is the problematic one.
				system_message(array(
					'title'=>_('Authenticate to server'),
					'body'=>_('Could not connect to LDAP server'),
					'type'=>'warn'));

				$this->draw_logout_link();

				# Proceed to the next server in the list. We cannot do anything mroe here.
				//return;
			}
		} else { // end if $ldapserver->haveAuthInfo()
			/* We don't have enough information to login to this server
			 * Draw the "login..." link */

			if ($ldapserver->auth_type != 'http')
				$this->draw_login_link();
		}

		$this->draw_mass_deletion_submit_button();

		# Tree Footer.
		# @todo: Need to implement a mechanism to have a footer, but not display it if it is blank.
		#printf('<tr><td class="foot" colspan="%s">%s</td></tr>',$this->getDepth()+3,'&nbsp;');
		echo '</table>';
		$this->draw_mass_deletion_end_form();
		echo "\n\n";

		$this->draw_javascript();
	}

	protected function draw_mass_deletion_start_form() {
		$ldapserver = $this->getLdapServer();

		# Does this server want mass deletion available?
		if ($ldapserver->isMassDeleteEnabled()) {
			echo '<form name="mass_delete" action="cmd.php?cmd=mass_delete" method="post">';
			printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
			echo "\n\n";
		}
	}

	protected function draw_mass_deletion_submit_button() {
		$ldapserver = $this->getLdapServer();

		if ($ldapserver->isMassDeleteEnabled()) {
			printf('<tr><td colspan="%s"><input type="submit" value="%s" /></td></tr>',
				$this->getDepth()+3,_('Delete Checked Entries'));
		}
	}

	protected function draw_mass_deletion_end_form() {
		$ldapserver = $this->getLdapServer();

		if ($ldapserver->isMassDeleteEnabled()) {
			echo '<!-- The end of the mass deletion form -->';
			echo '</form>';
		}
	}

	protected function draw_server_name() {
		$ldapserver = $this->getLdapServer();

		echo '<tr class="server">';
		printf('<td class="icon"><img src="%s/server.png" alt="%s" /></td>',IMGDIR,_('Server'));
		printf('<td class="name" colspan="%s">',$this->getDepth()+3-1);
		printf('%s',htmlspecialchars($ldapserver->name));

		if ($ldapserver->haveAuthInfo() && ! in_array($ldapserver->auth_type,array('config','http'))) {
			$m = sprintf(_('Inactivity will log you off at %s'),
				strftime('%H:%M',time() + ($ldapserver->session_timeout*60)));
			printf(' <img width=14 height=14 src="%s/timeout.png" title="%s" alt="%s"/>',IMGDIR,$m,$m);
		}
		echo '</td></tr>';
	}

	protected function draw_menu() {
		$links = '';
		$link = '';
		$i = 0;

		while (($link = $this->get_menu_item($i)) !== false) {
			if ($link) {
				//if ($links) $links .= ' | ';
				$links .= '<td class="server_links">'.$link.'</td>';
			}
			$i++;
		}

		# Draw the quick-links below the server name:
		if ($links) {
			printf('<tr><td class="spacer"></td><td colspan="%s" class="links">',$this->getDepth()+3-1);
			printf('<table><tr>%s</tr></table>',$links);
			echo '</td></tr>';
		}
	}

	protected function get_menu_item($i) {
		$ldapserver = $this->getLdapServer();

		switch($i) {
			case 0 :
				if ($_SESSION[APPCONFIG]->isCommandAvailable('schema')) return $this->get_schema_menu_item();
				else return '';
			case 1 :
				if ($_SESSION[APPCONFIG]->isCommandAvailable('search')) return $this->get_search_menu_item();
				else return '';
			case 2 :
				if ($_SESSION[APPCONFIG]->isCommandAvailable('server_refresh')) return $this->get_refresh_menu_item();
				else return '';
			case 3 :
				if ($_SESSION[APPCONFIG]->isCommandAvailable('server_info')) return $this->get_info_menu_item();
				else return '';
			case 4 :
				if (!$ldapserver->isReadOnly() && $_SESSION[APPCONFIG]->isCommandAvailable('import')) return $this->get_import_menu_item();
				else return '';
			case 5 :
				if ($_SESSION[APPCONFIG]->isCommandAvailable('export')) return $this->get_export_menu_item();
				else return '';
			case 6 :
				if (! in_array($ldapserver->auth_type,array('config','http'))) return $this->get_logout_menu_item();
				else return '';
			default :
				return false;
		}
	}

	protected function get_schema_menu_item() {
		$ldapserver = $this->getLdapServer();
		$href = sprintf('cmd.php?cmd=schema&server_id=%s',$ldapserver->server_id);

		return sprintf('<a title="%s %s" href="%s"><img src="%s/%s" alt="%s" /><br />%s</a>',
			_('View schema for'),$ldapserver->name,htmlspecialchars($href),IMGDIR,'schema.png',_('schema'),_('schema'));
	}

	protected function get_search_menu_item() {
		$ldapserver = $this->getLdapServer();
		$href = sprintf('cmd.php?cmd=search&server_id=%s&form=undefined',$ldapserver->server_id);

		return sprintf('<a title="%s %s" href="%s"><img src="%s/%s" alt="%s" /><br />%s</a>',
			_('search'),$ldapserver->name,htmlspecialchars($href),IMGDIR,'search.png',_('search'),_('search'));
	}

	protected function get_refresh_menu_item() {
		$ldapserver = $this->getLdapServer();
		$href = sprintf('cmd.php?cmd=refresh&server_id=%s',$ldapserver->server_id);

		return sprintf('<a title="%s %s" href="%s"><img src="%s/%s" alt="%s" /><br />%s</a>',
			_('Refresh all expanded containers for'),$ldapserver->name,htmlspecialchars($href),IMGDIR,'refresh-big.png',_('refresh'),_('refresh'));
	}

	protected function get_info_menu_item() {
		$ldapserver = $this->getLdapServer();
		$href = sprintf('cmd.php?cmd=server_info&server_id=%s',$ldapserver->server_id);

		return sprintf('<a title="%s" href="%s"><img src="%s/%s" alt="%s" /><br />%s</a>',
			_('View server-supplied information'),htmlspecialchars($href),IMGDIR,'info.png',_('info'),_('info'));
	}

	protected function get_import_menu_item() {
		$ldapserver = $this->getLdapServer();
		$href = sprintf('cmd.php?cmd=ldif_import_form&server_id=%s',$ldapserver->server_id);

		return sprintf('<a title="%s" href="%s"><img src="%s/%s" alt="%s" /><br />%s</a>',
			_('Import entries from an LDIF file'),htmlspecialchars($href),IMGDIR,'import.png',_('import'),_('import'));
	}

	protected function get_export_menu_item() {
		$ldapserver = $this->getLdapServer();
		$href = sprintf('cmd.php?cmd=export_form&server_id=%s',$ldapserver->server_id);

		return sprintf('<a title="%s" href="%s"><img src="%s/%s" alt="%s" /><br />%s</a>',
			_('Export entries'),htmlspecialchars($href),IMGDIR,'export.png',_('export'),_('export'));
	}

	protected function get_logout_menu_item() {
		$ldapserver = $this->getLdapServer();
		$href = sprintf('cmd.php?cmd=logout&server_id=%s',$ldapserver->server_id);

		return sprintf('<a title="%s" href="%s"><img src="%s/%s" alt="%s" /><br />%s</a>',
			_('Logout of this server'),htmlspecialchars($href),IMGDIR,'logout.png',_('logout'),_('logout'));
	}

	protected function draw_logged_in_dn() {
		$ldapserver = $this->getLdapServer();

		$logged_in_dn = $ldapserver->getLoggedInDN();
		printf('<tr><td class="spacer"></td><td class="logged_in" colspan="%s">%s%s ',$this->getDepth()+3-1,_('Logged in as'),_(':'));

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

		# Some sanity checking here, in case our DN doesnt look like a DN
		if (! is_array($logged_in_dn_array))
			$logged_in_dn_array = array($logged_in_dn);

		if (strcasecmp('anonymous',$logged_in_dn)) {
			foreach ($logged_in_dn_array as $rdn_piece) {
				$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',$ldapserver->server_id,rawurlencode($rdn));
				printf('<a href="%s">%s</a>',htmlspecialchars($href),pretty_print_dn($rdn_piece));

				if ($rdn_piece != end($logged_in_dn_array))
					echo ',';

				$rdn = substr($rdn,(1 + strpos($rdn,',')));
			}

		} else {
			echo 'Anonymous';
		}

		echo '</td></tr>';
	}

	/**
	 * Recursively descend on the given dn and draw the tree in html
	 *
	 * @param dn $dn Current dn.
	 * @param int $level Level to start drawing (start to -1)
	 */
	protected function draw_dn($dn,$level) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s)',33,__FILE__,__LINE__,__METHOD__,$dn,$level);

		$ldapserver = $this->getLdapServer();

		$dnEntry = $this->getEntry($dn);
		if (!$dnEntry) {
			$this->addEntry($dn);
			$dnEntry = $this->getEntry($dn);
		}
		if (!$dnEntry)
			return;

		$encoded_dn = rawurlencode($dn);
		$href['expand'] = sprintf('cmd.php?cmd=expand&server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);
		$href['collapse'] = sprintf('cmd.php?cmd=collapse&server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);
		$href['edit'] = sprintf('cmd.php?cmd=template_engine&server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);
		$img_src = sprintf('%s/%s',IMGDIR,$dnEntry->getIcon($ldapserver));
		$rdn = get_rdn($dn);

		echo '<tr class="option">';
		$colspan = $this->getDepth()+3+$level+1;

		for ($i=0;$i<=$level;$i++) {
			echo '<td class="spacer"></td>';
			$colspan--;
		}

		# Shall we draw the "mass-delete" checkbox?
		if ($ldapserver->isMassDeleteEnabled()) {
			printf('<td><input type="checkbox" name="mass_delete[%s]" /></td>',htmlspecialchars($dn));
		} else {
			echo '<td class="spacer"></td>';
		}
		$colspan--;

		$child_count = $this->get_children_number($dnEntry);

		# Is this node expanded? (deciding whether to draw "+" or "-")
		if ($dnEntry->isOpened()) {
			if (!$child_count && !$ldapserver->isShowCreateEnabled()) {
				printf('<td class="expander"><img src="%s/minus.png" alt="-" /></td>',IMGDIR);
			} else {
				printf('<td class="expander"><a href="%s"><img src="%s/minus.png" alt="-" /></a></td>',$href['collapse'],IMGDIR);
			}
		} else {
			if (($child_count !== false) && (!$child_count) && (!$ldapserver->isShowCreateEnabled())) {
				printf('<td class="expander"><img src="%s/minus.png" alt="-" /></td>',IMGDIR);
			} else {
				printf('<td class="expander"><a href="%s"><img src="%s/plus.png" alt="+" /></a></td>',$href['expand'],IMGDIR);
			}
		}
		$colspan--;

		printf('<td class="icon"><a href="%s" name="%s_%s"><img src="%s" alt="img" /></a></td>',$href['edit'],$ldapserver->server_id,$encoded_dn,$img_src);
		$colspan--;

		printf('<td class="logged_in" colspan="%s" width=100%%><span style="white-space: nowrap;">',$colspan);
		printf('<a href="%s">%s</a>',$href['edit'],$this->get_formatted_dn($dnEntry,$level));

		if ($child_count)
			printf(' <span class="count">(%s)</span>',$child_count);

		echo '</span></td></tr>';

		if ($dnEntry->isOpened()) {
			/* Draw the "create new" link at the top of the tree list if there are more than 10
			 * entries in the listing for this node.
			 */
			if (!$ldapserver->isReadOnly() && ($dnEntry->getChildrenNumber() > 10)
				&& ($ldapserver->isShowCreateEnabled())) {

				$this->draw_create_link($ldapserver->server_id,$rdn,$level,$encoded_dn);
			}

			foreach ($dnEntry->getChildren() as $dnChildEntry)
				$this->draw_dn($dnChildEntry,$level+1);

			# Always draw the "create new" link at the bottom of the listing
			if (!$ldapserver->isReadOnly() && !$dnEntry->isLeaf() && $ldapserver->isShowCreateEnabled()) {
				$this->draw_create_link($ldapserver->server_id,$rdn,$level,$encoded_dn);
			}
		}

		if (DEBUG_ENABLED)
			debug_log('Leaving (%s,%s)',33,__FILE__,__LINE__,__METHOD__,$dn,$level);
	}

	protected function get_formatted_dn($entry,$level) {
		if ($level < 0) return pretty_print_dn($entry->getDn());
		else return draw_formatted_dn($this->getLdapServer(),$entry);
	}

	protected function get_children_number($entry) {
		if ($entry->isOpened()) {
			$child_count = $entry->getChildrenNumber(true);

			if ($entry->isSizeLimited()) {
				$child_count .= '...';
			}

			return $child_count;

		} else {
			if ($this->getLdapServer()->isLowBandwidth()) {
				return false;

			} else {
				$child_count = $entry->getChildrenNumber();

				if ($entry->isSizeLimited()) {
					$child_count .= '+';
				}

				return $child_count;
			}
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
	protected function draw_create_link($server_id,$rdn,$level,$encoded_dn) {
		# print the "Create New object" link.
		$href = htmlspecialchars(sprintf('cmd.php?cmd=template_engine&server_id=%s&container=%s',$server_id,$encoded_dn));

		echo '<tr>';
		for ($i=0;$i<=$level;$i++)
			echo '<td class="spacer"></td>';

		echo '<td class="spacer"></td>';
		echo '<td class="spacer"></td>';
		printf('<td class="icon"><a href="%s"><img src="%s/star.png" alt="%s" /></a></td>',$href,IMGDIR,_('new'));
		printf('<td class="link" colspan="%s"><a href="%s" title="%s %s">%s</a></td>',
			$this->getDepth()+3-$level-1-3,$href,_('Create a new entry in'),$rdn,_('Create new entry here'));
		echo '</tr>';
	}

	protected function draw_login_link() {
		global $recently_timed_out_servers;

		$ldapserver = $this->getLdapServer();

		$href = htmlspecialchars(
			sprintf('cmd.php?cmd=%s&server_id=%s',get_custom_file($ldapserver->server_id,'login_form',''),$ldapserver->server_id));

		echo '<tr class="option"><td class="spacer"></td>';
		printf('<td class="icon"><a href="%s"><img src="%s/uid.png" alt="%s" /></a></td>',$href,IMGDIR,_('login'));
		printf('<td class="logged_in" colspan="%s"><a href="%s">%s</a></td>',$this->getDepth()+3-2,$href,_('Login').'...');
		echo '</tr>';

		printf('<tr><td class="blank" colspan="%s">&nbsp;</td>',$this->getDepth()+3);
		printf('<tr><td class="blank" colspan="%s">&nbsp;</td>',$this->getDepth()+3);

		# If the server recently timed out display the message
		if (is_array($recently_timed_out_servers) && in_array($ldapserver->server_id,$recently_timed_out_servers))
			printf('<tr><td class="spacer"></td><td colspan="%s" class="links">%s</td></tr>',
				$this->getDepth()+3-1,_('(Session timed out. Automatically logged out.)'));
	}

	protected function draw_logout_link() {
		$ldapserver = $this->getLdapServer();

		if (! in_array($ldapserver->auth_type,array('config','http'))) {
			printf('<tr><td class="spacer"></td><td colspan="%s"><small><a href="cmd.php?cmd=%s&server_id=%s">%s</a></small></td></tr>',
				$this->getDepth()+3-1,get_custom_file($ldapserver->server_id,'logout',''),$ldapserver->server_id,_('logout'));
		}
	}

	protected function draw_javascript() {
		if ($this->javascript) {
			echo "<!-- Forms for javascript submit to call to create base_dns -->\n";
			echo $this->javascript;
			echo "<!-- The end of the forms for javascript submit to call to create base_dns -->\n";
		}
	}

	/*
	 * Work out how deep the "opened" tree is.
	 */
	public function getDepth() {
		$ldapserver = $this->getLdapServer();

		static $depths = array();

		if (! isset($depths[$ldapserver->server_id])) {
			$max = 0; # BaseDN are open, so we start at 1.

			foreach ($this->entries as $dn) {
				$basedepth = count(pla_explode_dn($ldapserver->getContainerParent($dn->getDn(),'/')));
				$depth = 0;

				//if ($dn->isOpened())
				$depth = count(pla_explode_dn($dn->getDn()))+1-$basedepth;

				if ($depth > $max)
					$max = $depth;
			}

			$depths[$ldapserver->server_id] = $max;
		}

		return $depths[$ldapserver->server_id];
	}
}
?>
