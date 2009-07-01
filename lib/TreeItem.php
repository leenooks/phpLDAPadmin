<?php
// $Header$

/**
 * Classes and functions for the LDAP tree.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Represents an item in the tree.
 *
 * @package phpLDAPadmin
 * @subpackage Tree
 */
class TreeItem {
	# This entry's DN
	protected $dn;
	# The server this entry belongs to.
	private $server_id;
	# The objectclasses in LDAP, used to deterimine the icon and template
	protected $objectclasses = array();
	# Is this a base entry?
	private $base_entry = false;
	# Array of dn - the children
	private $children = array();
	# An icon file path
	protected $icon;
	# Is the entry a leaf?
	private $leaf = false;
	# Is the node open?
	private $open = false;
	# Is the size of children limited?
	private $size_limited = true;
	# Last template used to edit this entry
	private $template = null;

	public function __construct($server_id,$dn) {
		$this->server_id = $server_id;
		$this->dn = $dn;
	}

	/**
	 * Get the DN of this tree item.
	 *
	 * @return DN The DN of this item.
	 */
	public function getDN() {
		return $this->dn;
	}

	/**
	 * Get the RDN of this tree items DN.
	 *
	 * @return RDN The RDN of this items DN.
	 */
	public function getRDN() {
		return get_rdn($this->getDn(),0,true);
	}

	/**
	 * Set this item as a LDAP base DN item.
	 */
	public function setBase() {
		$this->base_entry = true;
	}

	/**
	 * Return if this item is a base DN item.
	 */
	public function isBaseDN() {
		return $this->base_entry;
	}

	public function setObjectClasses($oc) {
		$this->objectclasses = $oc;
	}

	public function getObjectClasses() {
		return $this->objectclasses;
	}

	public function isInLDAP() {
		return count($this->objectclasses) ? true : false;
	}

	/**
	 * Returns null if the children have never be defined
	 * or an array of the dn of the children
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Add a child to this DN entry.
	 *
	 * @param DN The DN to add.
	 */
	public function addChild($dn) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

		if (in_array($dn,$this->children))
			return;

		array_push($this->children,$dn);
		usort($this->children,'pla_compare_dns');

		if (DEBUG_ENABLED)
			debug_log('Entered with (%s), Leaving ()',1,__FILE__,__LINE__,__METHOD__,$dn);
	}

	/**
	 * Delete a child from this DN entry.
	 *
	 * @param DN The DN to add.
	 */
	public function delChild($dn) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

		if ($this->children) {
			# If the parent hasnt been opened in the tree, then there wont be any children.
			$index = array_search($dn,$this->children);

			if ($index !== false)
				unset($this->children[$index]);
		}
	}

	/**
	 * Rename this DN.
	 *
	 * @param DN The DN to rename to.
	 */
	public function rename($dn) {
		$this->dn = $dn;
	}

	/**
	 * Return if this item has been opened.
	 */
	public function isOpened() {
		return $this->open;
	}

	/**
	 * Mark this node as closed.
	 */
	public function close() {
		$this->open = false;
	}

	/**
	 * Opens the node ; the children of the node must have been defined
	 */
	public function open() {
		$this->open = true;
	}

	/**
	 * Mark this node as a leaf.
	 */
	public function setLeaf() {
		$this->leaf = true;
	}

	/**
	 * Return if this node is a leaf.
	 */
	public function isLeaf() {
		return $this->leaf;
	}

	/**
	 * Returns the path of the icon file used to represent this node ;
	 * If the icon hasnt been set, it will call get_icon()
	 */
	public function getIcon() {
		if (! $this->icon)
			$this->icon = get_icon($this->server_id,$this->dn,$this->objectclasses);

		return $this->icon;
	}

	/**
	 * Mark this node as a size limited (it wont have all its children).
	 */
	public function setSizeLimited() {
		$this->size_limited = true;
	}

	/**
	 * Clear the size limited flag.
	 */
	public function unsetSizeLimited() {
		$this->size_limited = false;
	}

	/**
	 * Return if this node has hit an LDAP size limit (and thus doesnt have all its children).
	 */
	public function isSizeLimited() {
		return $this->size_limited;
	}

	public function setTemplate($template) {
		$this->template = $template;
	}

	public function getTemplate() {
		return $this->template;
	}
}
?>
