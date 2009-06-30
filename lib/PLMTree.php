<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/PLMTree.php,v 1.2 2007/12/15 07:50:32 wurley Exp $

require HTDOCDIR.JSDIR.'phplayersmenu/lib/PHPLIB.php';
require HTDOCDIR.JSDIR.'phplayersmenu/lib/layersmenu-common.inc.php';
require HTDOCDIR.JSDIR.'phplayersmenu/lib/treemenu.inc.php';

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 */
class PLMTree extends HTMLTree {

	// no support of mass deletion form
	protected function draw_mass_deletion_start_form() {
	}

	protected function draw_mass_deletion_submit_button() {
	}
	
	protected function draw_mass_deletion_end_form() {
	}

	/**
	 * Recursively descend on the given dn and draw the tree in plm
	 *
	 * @param dn $dn Current dn.
	 * @param int $level Level to start drawing
	 * @todo: Currently draw PLM only shows the first 50 entries of the base children -
	 * possibly the childrens children too. Have disabed the size_limit on the base -
	 * need to check that it doesnt affect non PLM tree viewer and children where
	 * size > size_limit.
	 */
	protected function draw_dn($dn,$level) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s)',33,__FILE__,__LINE__,__METHOD__,$dn,$level);

		static $tm = null;

		if (! isset($tm)) {
			$tm = new TreeMenu();
			$tm->setDirroot(JSDIR.'phplayersmenu/');
			$tm->setIcondir(HTDOCDIR.'/images/');
			$tm->setIconwww('images/');
			$tm->setImgwww(JSDIR.'phplayersmenu/menuimages/');
		}

		$tree_plm = $this->to_plm($dn,$level);

		$tm->setMenuStructureString($tree_plm);
		$tm->parseStructureForMenu('pla_tree_'.$this->server_id);
		$tm->setTreeMenuTheme('');
		$tm->newTreeMenu('pla_tree_'.$this->server_id);

		echo sprintf('<tr><td class="spacer"></td><td colspan="%s">%s</td></tr>',$this->getDepth()+3-1,$tm->getTreeMenu('pla_tree_'.$this->server_id));
	}

	protected function to_plm($dn,$level) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s)',33,__FILE__,__LINE__,__METHOD__,$dn,$level);

		$ldapserver = $this->getLdapServer();

		$dnEntry = $this->getEntry($dn);
		if (!$dnEntry) {
			$this->addEntry($dn);
			$dnEntry = $this->getEntry($dn);
		}
		if (!$dnEntry) {
			if (DEBUG_ENABLED)
				debug_log('Returning (%s)',33,__FILE__,__LINE__,__METHOD__,'');
			return '';
		}

		$encoded_dn = rawurlencode($dn);
		$edit_href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',$this->server_id,$encoded_dn);
		$rdn = get_rdn($dn);

		$dots = '';
		for ($i=0;$i<=$level+1;$i++) $dots .= '.';

		# Have we tranversed this part of the tree yet?
		if ($dnEntry->isOpened()) {
			$child_count = $this->get_children_number($dnEntry);
			$tree_plm = sprintf("%s|%s|%s|%s|%s|%s|%s\n",
				$dots,
				($this->get_formatted_dn($dnEntry, $level)).($child_count ? ' ('.$child_count.')' : ''),
				$edit_href, $this->get_formatted_title($dnEntry, $level), $dnEntry->getIcon($ldapserver), '',
				($dnEntry->isOpened() ? 1 : 0));

			$tree_plm .= $this->get_plm_before_first_child($dnEntry, $level);

			foreach ($dnEntry->getChildren() as $dn) {
				$tree_plm .= $this->to_plm($dn,$level+1);
			}

			$tree_plm .= $this->get_plm_after_last_child($dnEntry, $level);
		} else {
			$child_count = $this->get_children_number($dnEntry);

			if ($child_count) {
				$tree_plm = sprintf("%s|%s|%s|%s|%s|%s|%s|%s\n",
					$dots,
					($this->get_formatted_dn($dnEntry, $level)).($child_count ? ' ('.$child_count.')' : ''),
					$edit_href, $this->get_formatted_title($dnEntry, $level), $dnEntry->getIcon($ldapserver), '',
					($dnEntry->isOpened() ? 1 : 0),
					$child_count);
			} else {
				$tree_plm = sprintf("%s|%s|%s|%s|%s|%s|%s|%s\n",
					$dots,
					($this->get_formatted_dn($dnEntry, $level)),
					$edit_href, $this->get_formatted_title($dnEntry, $level), $dnEntry->getIcon($ldapserver), '',
					($dnEntry->isOpened() ? 1 : 0),
					$child_count === false ? 1 : 0);
			}
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',33,__FILE__,__LINE__,__METHOD__,$tree_plm);

		return $tree_plm;
	}

	protected function get_formatted_title($entry, $level) {
		return $entry->getDn();
	}

	protected function get_plm_before_first_child($entry, $level) {
		$ldapserver = $this->getLdapServer();

		$plm = '';

		if (!$ldapserver->isReadOnly() && ($entry->getChildrenNumber() > 10) && ($ldapserver->isShowCreateEnabled())) {
			$encoded_dn = rawurlencode($entry->getDn());
			$create_href = sprintf('cmd.php?cmd=template_engine&server_id=%s&container=%s', $ldapserver->server_id, $encoded_dn);

			$dots = '.';
			for ($i=0;$i<=$level+1;$i++) $dots .= '.';

			$plm = sprintf("%s|%s|%s|%s|%s|%s|%s\n",
					$dots, _('Create new entry here'),
					$create_href, $entry->getDn(), 'star.png', '', 0);
		}

		return $plm;
	}

	protected function get_plm_after_last_child($entry, $level) {
		$ldapserver = $this->getLdapServer();

		$plm = '';

		if (!$ldapserver->isReadOnly() && !$entry->isLeaf() && $ldapserver->isShowCreateEnabled()) {
			$encoded_dn = rawurlencode($entry->getDn());
			$create_href = sprintf('cmd.php?cmd=template_engine&server_id=%s&container=%s', $ldapserver->server_id, $encoded_dn);

			$dots = '.';
			for ($i=0;$i<=$level+1;$i++) $dots .= '.';

			$plm = sprintf("%s|%s|%s|%s|%s|%s|%s\n",
					$dots, _('Create new entry here'),
					$create_href, $entry->getDn(), 'star.png', '', 0);
		}

		return $plm;
	}
}
?>
