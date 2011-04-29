<?php
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
	# Do we need to sort the children
	private $childsort = true;
	# Are we reading the children
	private $reading_children = false;

	public function __construct($server_id,$dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->server_id = $server_id;
		$this->dn = $dn;
	}

	/**
	 * Get the DN of this tree item.
	 *
	 * @return DN The DN of this item.
	 */
	public function getDN() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->dn);

		return $this->dn;
	}

	public function getDNEncode() {
		return urlencode(preg_replace('/%([0-9a-fA-F]+)/',"%25\\1",$this->dn));
	}

	/**
	 * Get the RDN of this tree items DN.
	 *
	 * @return RDN The RDN of this items DN.
	 */
	public function getRDN() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return get_rdn($this->getDn(),0,true);
	}

	/**
	 * Set this item as a LDAP base DN item.
	 */
	public function setBase() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->base_entry = true;
	}

	/**
	 * Return if this item is a base DN item.
	 */
	public function isBaseDN() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->base_entry);

		return $this->base_entry;
	}

	public function setObjectClasses($oc) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->objectclasses = $oc;
	}

	public function getObjectClasses() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->objectclasses);

		return $this->objectclasses;
	}

	public function isInLDAP() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return count($this->objectclasses) ? true : false;
	}

	/**
	 * Returns null if the children have never be defined
	 * or an array of the dn of the children
	 */
	public function getChildren() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->children);

		if ($this->childsort && ! $this->reading_children) {
			usort($this->children,'pla_compare_dns');
			$this->childsort = false;
		}

		return $this->children;
	}

	public function readingChildren($bool) {
		$this->reading_children = $bool;
	}

	/**
	 * Do the children require resorting
	 */
	public function isChildSorted() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->childsort);

		return $this->childsort;
	}

	/**
	 * Mark the children as sorted
	 */
	public function childSorted() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->childsort = false;
	}

	/**
	 * Add a child to this DN entry.
	 *
	 * @param DN The DN to add.
	 */
	public function addChild($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (in_array($dn,$this->children))
			return;

		array_push($this->children,$dn);
		$this->childsort = true;
	}

	/**
	 * Delete a child from this DN entry.
	 *
	 * @param DN The DN to add.
	 */
	public function delChild($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->dn = $dn;
	}

	/**
	 * Return if this item has been opened.
	 */
	public function isOpened() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->open);

		return $this->open;
	}

	/**
	 * Mark this node as closed.
	 */
	public function close() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->open = false;
	}

	/**
	 * Opens the node ; the children of the node must have been defined
	 */
	public function open() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->open = true;
	}

	/**
	 * Mark this node as a leaf.
	 */
	public function setLeaf() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->leaf = true;
	}

	/**
	 * Return if this node is a leaf.
	 */
	public function isLeaf() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->leaf);

		return $this->leaf;
	}

	/**
	 * Returns the path of the icon file used to represent this node ;
	 * If the icon hasnt been set, it will call get_icon()
	 */
	public function getIcon() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->icon);

		if (! $this->icon)
			$this->icon = get_icon($this->server_id,$this->dn,$this->objectclasses);

		return $this->icon;
	}

	/**
	 * Mark this node as a size limited (it wont have all its children).
	 */
	public function setSizeLimited() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->size_limited = true;
	}

	/**
	 * Clear the size limited flag.
	 */
	public function unsetSizeLimited() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->size_limited = false;
	}

	/**
	 * Return if this node has hit an LDAP size limit (and thus doesnt have all its children).
	 */
	public function isSizeLimited() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->size_limited;
	}

	public function setTemplate($template) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->template = $template;
	}

	public function getTemplate() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->template;
	}
}
?>
