<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/Tree.php,v 1.2.2.1 2007/12/26 09:26:33 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Abstract class which represents the LDAP tree view ; the draw() method
 * must be implemented by subclasses
 * @see HTMLTree PLMTree
 */
abstract class Tree {
	// list of entries in the tree view cache
	// array : dn -> Entry
	protected $entries = array();

	// list of entries which are not visible in the tree view
	// array : dn -> (true|false)
	protected $misses = array();

	// ldap server id represented by this tree
	protected $server_id = -1;

	protected function __construct($server_id) {
		$this->server_id = $server_id;
	}

	static public function getInstance($server_id) {
		$tree = get_cached_item($server_id,'tree');
		if (!$tree) {
			$ldapserver = $_SESSION[APPCONFIG]->ldapservers->Instance($server_id);
			if (!$ldapserver) return null;

			$treeclass = $_SESSION[APPCONFIG]->GetValue('appearance','tree');
			eval('$tree = new '.$treeclass.'($server_id);');

			foreach ($ldapserver->getBaseDN() as $baseDn)
				if ($baseDn)
					$tree->addEntry($baseDn);

			set_cached_item($server_id,'tree','null',$tree);
		}
		return $tree;
	}

	public function getLdapServer() {
		return $_SESSION[APPCONFIG]->ldapservers->Instance($this->server_id);
	}

	/**
	 * This function will take the DN, convert it to lowercase and strip unnessary
	 * commas. This result will be used as the index for the tree object.
	 * Any display of a DN should use the object->dn entry, not the index.
	 * The reason we need to do this is because:
	 * uid=User A,ou=People,c=AU and
	 * uid=User B, ou=PeOpLe, c=au
	 * are infact in the same branch, but PLA will show them inconsistently.
	 */
	public function indexDN($dn) {
		$index = strtolower(join(',',pla_explode_dn($dn)));

		if (DEBUG_ENABLED)
			debug_log('Entered with (%s), Result (%s)',1,__FILE__,__LINE__,__METHOD__,$dn,$index);

		return $index;
	}

	/**
	 * Add an entry in the tree view ; the entry is added in the
	 * children array of its parent
	 *
	 * The added entry is created using the factory class defined
	 * in $_SESSION[APPCONFIG]->custom->appearance['entry_factory']
	 *
	 * @param $dn the dn of the entry to create
	 */
	public function addEntry($dn) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

		$ldapserver = $_SESSION[APPCONFIG]->ldapservers->Instance($this->server_id);

		# We need to convert the DN to lower case, to avoid any case problems and strip any unnessary spaces after commas.
		$dnlower = $this->indexDN($dn);

		# If the parent entry is not in the tree, we add it
		$bases = $ldapserver->getBaseDN();

		if (! $bases)
			$bases = array('');
		elseif (! is_array($bases))
			$bases = array($bases);

		if (DEBUG_ENABLED)
			debug_log('Got BaseDNs (%s)',64,__FILE__,__LINE__,__METHOD__,$bases);

		$parent_entry = null;
		if (! in_array_ignore_case($dn,$bases)) {
			$parent_dn = get_container($dn);

			if (DEBUG_ENABLED)
				debug_log('Parent DNs (%s)',64,__FILE__,__LINE__,__METHOD__,$parent_dn);

			if ($parent_dn) {
				$parent_entry = $this->getEntry($parent_dn);
				if (! $parent_entry) {
					$this->addEntry($parent_dn);
					$parent_entry = $this->getEntry($parent_dn);
				}
			} else {
				if (DEBUG_ENABLED)
					debug_log('NO parent, entry (%s) ignored.',64,__FILE__,__LINE__,__METHOD__,$dn);
			}
		}

		if (isset($this->entries[$dnlower]))
			unset($this->entries[$dnlower]);

		# If this DN is in our miss list, we can remove it now.
		if (isset($this->misses[$dnlower]))
			unset($this->misses[$dnlower]);

		$entryfactoryclass = $_SESSION[APPCONFIG]->GetValue('appearance','entry_factory');
		eval('$entry_factory = new '.$entryfactoryclass.'();');
		if (DEBUG_ENABLED)
			debug_log('New ENTRY (%s) for (%s).',64,__FILE__,__LINE__,__METHOD__,$dnlower,$dn);
		$this->entries[$dnlower] = $entry_factory->newEditingEntry($dn);

		$this->entries[$dnlower]->setTree($this);
		if ($ldapserver->isReadOnly())
			$this->entries[$dnlower]->setReadOnly();

		# Update this DN's parent's children list as well.
		if ($parent_entry)
			$parent_entry->addChild($dn);

		if (DEBUG_ENABLED)
			debug_log('Leaving (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);
	}

	/**
	 * Delete an entry from the tree view ; the entry is deleted from the
	 * children array of its parent
	 */
	public function delEntry($dn) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

		$dnlower = $this->indexDN($dn);
		if (isset($this->entries[$dnlower])) unset($this->entries[$dnlower]);

		# Delete entry from parent's children as well.
		$parent_dn = get_container($dn);
		$parent_entry = $this->getEntry($parent_dn);
		if ($parent_entry) $parent_entry->delChild($dn);

		# Might be worthwhile adding it to our miss list, while we are here.
		$this->misses[$dnlower] = true;
	}

	public function renameEntry($oldDn, $newDn) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,$oldDn,$newDn);

		$olddnlower = $this->indexDN($oldDn);
		$newdnlower = $this->indexDN($newDn);

		$this->entries[$newdnlower] = $this->entries[$olddnlower];
		unset($this->entries[$olddnlower]);
		$this->entries[$newdnlower]->rename($newDn);

		# Might be worthwhile adding it to our miss list, while we are here.
		$this->misses[$olddnlower] = true;
		if (isset($this->misses[$newdnlower])) unset($this->misses[$newdnlower]);

		# Update the parent's children
		$parent_dn = get_container($newDn);
		$parent_entry = $this->getEntry($parent_dn);
		if ($parent_entry) {
			$parent_entry->delChild($oldDn);
			$parent_entry->addChild($newDn);
		}
	}

	public function getEntry($dn) {
		$dnlower = $this->indexDN($dn);

		if (isset($this->entries[$dnlower]))
			return $this->entries[$dnlower];
		else
			return null;
	}

	public function isMissed($dn) {
		$dnlower = $this->indexDN($dn);

		return isset($this->misses[$dnlower]) && $this->misses[$dnlower];
	}

	/**
	 * Displays the LDAP tree
	 */
	abstract public function draw();
}
?>
