<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/Entry.php,v 1.2.2.2 2008/01/04 14:31:05 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Represent a tree node
 */
abstract class Entry {
	protected $dn;

	# the tree to which the entry belongs
	protected $tree;

	# is the entry a leaf ?
	private $leaf;

	# is the node open ?
	private $open;

	# array of dn
	private $children;

	# allow to test if addChild() is called by readChildren()
	private $reading_children;

	# is the size of children limited ?
	private $size_limited;

	# is the entry modifiable ?
	private $readonly;

	# an icon file path
	protected $icon;

	protected $properties;

	public function __construct($dn) {
		$this->dn = $dn;
		$this->leaf = false;
		$this->open = false;
		$this->children = array();
		$this->reading_children = false;
		$this->size_limited = true;
		$this->readonly = false;
		$this->icon = '';
		$this->properties = array();
	}

	public function getDn() {
		return $this->dn;
	}

	public function getRdn() {
		return get_rdn($this->getDn(), 0, true);
	}

	public function getRdnAttributeName() {
		$attr = '';
		if ($this->dn) {
			$i = strpos($this->dn, '=');
			if ($i !== false) $attr = substr($this->dn, 0, $i);
		}
		return $attr;
	}

	public function setTree($tree) {
		$this->tree = $tree;
	}

	private function readChildren($nolimit=false) {
		if (DEBUG_ENABLED)
			debug_log('Entered with ()',1,__FILE__,__LINE__,__METHOD__);

		$ldapserver = ($this->tree ? $this->tree->getLdapServer() : null);
		if (DEBUG_ENABLED)
			debug_log('LdapServer (%s)',1,__FILE__,__LINE__,__METHOD__, $ldapserver ? $ldapserver->server_id : -1);

		$ldap['child_limit'] = $nolimit ? 0 : $_SESSION[APPCONFIG]->GetValue('search','size_limit');
		$ldap['filter'] = $_SESSION[APPCONFIG]->GetValue('appearance','tree_filter');
		$ldap['deref'] = $_SESSION[APPCONFIG]->GetValue('deref','view');
		$ldap['children'] = $ldapserver->getContainerContents($this->getDn(),$ldap['child_limit'],$ldap['filter'],$ldap['deref']);

		if (DEBUG_ENABLED)
			debug_log('Children of (%s) are (%s)',64,__FILE__,__LINE__,__METHOD__,$this->getDn(),$ldap['children']);

		if ($this->tree) {
			$this->reading_children = true;
			foreach ($ldap['children'] as $dn) {
				if (DEBUG_ENABLED)
					debug_log('Adding (%s)',64,__FILE__,__LINE__,__METHOD__,$dn);

				if (! $this->tree->getEntry($dn))
					$this->tree->addEntry($dn);
			}
			usort($this->children,'pla_compare_dns');
			$this->reading_children = false;
		}
		if (count($this->children) == $ldap['child_limit'])
			$this->size_limited = true;
		else
			$this->size_limited = false;

		if (DEBUG_ENABLED)
			debug_log('Entered with (), Returning ()',1,__FILE__,__LINE__,__METHOD__);
	}

	/**
	 * Returns null if the children have never be defined
	 * or an array of the dn of the children
	 */
	public function getChildren() {
		if (! $this->children)
			$this->readChildren();

		return $this->children;
	}

	public function getChildrenNumber() {
		if (! $this->children)
			$this->readChildren();

		if ($this->children)
			return count($this->children);
		else
			return 0;
	}

	/**
	 * Called by Tree::addEntry() only
	 */
	public function addChild($dn) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

		if (! $this->children) {
			if (DEBUG_ENABLED)
				debug_log('this->children is FALSE',64,__FILE__,__LINE__,__METHOD__);

			if (! $this->reading_children) {
				if (DEBUG_ENABLED)
					debug_log('this->reading_children is FALSE',64,__FILE__,__LINE__,__METHOD__,$dn);

				$this->readChildren();
			}else {
				$this->children = array();
			}
		}

		$index = array_search($dn,$this->children);
		if (DEBUG_ENABLED)
			debug_log('array_search of (%s) in (%s) returned (%s)',64,__FILE__,__LINE__,__METHOD__,$dn,$this->children,$index);

		if ($index === false) {
			$this->children[] = $dn;
			if (! $this->reading_children) usort($this->children,'pla_compare_dns');
		}

		if (DEBUG_ENABLED)
			debug_log('Entered with (%s), Leaving ()',1,__FILE__,__LINE__,__METHOD__,$dn);
	}

	/**
	 * Called by Tree::delEntry() only
	 */
	public function delChild($dn) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

		if ($this->children) {
			# If the parent hasnt been opened in the tree, then there wont be any children.
			$index = array_search($dn,$this->children);
			if ($index !== false) unset($this->children[$index]);
		}
	}

	public function rename($newDn) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$newDn);
		$this->dn = $newDn;
	}

	public function isOpened() {
		return $this->open;
	}

	public function close() {
		if (DEBUG_ENABLED)
			debug_log('Entered with ()',1,__FILE__,__LINE__,__METHOD__);

		$this->open = false;
	}

	/**
	 * Opens the node ; the children of the node must have been defined
	 */
	public function open() {
		if (DEBUG_ENABLED)
			debug_log('Entered with ()',1,__FILE__,__LINE__,__METHOD__);

		$this->open = true;

		if ($this->isSizeLimited()) {
			$this->readChildren(true);
		}
	}

	public function setLeaf($is_leaf) {
		$this->leaf = $is_leaf;
	}

	public function isLeaf() {
		return $this->leaf;
	}

	public function isReadOnly() {
		return $this->readonly;
	}

	public function setReadOnly() {
		$this->readonly = true;
	}

	public function setReadWrite() {
		$this->readonly = false;
	}

	/**
	 * Returns the path of the icon file used to represent this node ;
	 * returns the result of get_icon() function
	 */
	public function getIcon($ldapserver) {
		if ($this->icon) return $this->icon;
		else return get_icon($ldapserver,$this->dn);
	}

	public function isSizeLimited() {
		return $this->size_limited;
	}

	public function setProperty($name, $value) {
		$this->properties[$name] = $value;
	}

	public function delProperty($name) {
		if ($this->hasProperty($name)) unset($this->properties[$name]);
	}

	public function hasProperty($name) {
		return isset($this->properties[$name]);
	}

	public function getProperty($name) {
		if ($this->hasProperty($name)) return $this->properties[$name];
		else return null;
	}

	public function getTemplateName() {
		if (isset($this->selected_template))
			return $this->selected_template;
		else
			return '';
	}

	public function getTemplateTitle() {
		if (isset($this->selected_template['title']))
			return $this->templates[$this->selected_template]['title'];
		else
			return _('No Template');
	}

	/**
	 * Visit the entry and its attributes
	 *
	 * The visitor must implement these methods :
	 * - visit<Entry>Start($entry)
	 * - visit<Entry>End($entry)
	 * where <Entry> is the entry class name.
	 */
	public function accept($visitor) {
		$visitor->visit('Start', $this);
		$attrs = $this->getAttributes();
		foreach ($attrs as $attribute) {
			$attribute->accept($visitor);
		}
		$visitor->visit('End', $this);
	}

	public function getAttribute($name) {
		foreach ($this->getAttributes() as $attr) {
			if ($attr->getName() == $name) return $attr;
		}
		return null;
	}

	/**
	 * Return an array of Attribute objects
	 */
	abstract public function getAttributes();
}
?>
