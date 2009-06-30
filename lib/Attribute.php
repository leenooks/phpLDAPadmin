<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/Attribute.php,v 1.2.2.2 2007/12/26 09:26:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author Xavier Bruyet
 *
 * Represents an attribute of a entry
 */
class Attribute {
	private $name;
	private $values;

	# min/max number of values
	protected $min_value_count;
	protected $max_value_count;

	# The entry in which the attribute is
	protected $entry;

	# Is the attribute internal
	protected $internal;

	# Has the attribute been modified
	protected $modified;

	# Is the attribute visible
	protected $visible;

	# Is the attribute modifiable
	protected $readonly;

	# Display parameters
	protected $friendly_name;
	protected $description;
	protected $icon;
	protected $hint;

	protected $size;	# Component size
	protected $maxlength;	# Value max length

	protected $properties;
	
	public function __construct($name, $values) {
		$this->name = $name;

		if (is_string($values) && (strlen($values) > 0)) $this->values = array($values);
		elseif (is_array($values)) $this->values = $values;
		else $this->values = array();

		$this->min_value_count = -1;
		$this->max_value_count = -1;

		$this->entry = null;
		$this->internal = false;
		$this->modified = false;
		$this->visible = true;
		$this->readonly = false;

		$this->friendly_name = '';
		$this->description = '';
		$this->icon = '';
		$this->hint = '';

		$this->size = 0;
		$this->maxlength = 0;

		$this->properties = array();
	}

	public function getName() {
		return $this->name;
	}

	public function getValues() {
		return $this->values;
	}

	public function getValueCount() {
		return count($this->values);
	}

	public function addValue($new_val, $i = -1) {
		if ($i < 0) $i = $this->getValueCount();
		$old_val = $this->getValue($i);
		if (is_null($old_val) || ($old_val != $new_val)) $this->justModified();
		$this->values[$i] = $new_val;
	}

	public function getValue($i) {
		if (isset($this->values[$i])) return ''.$this->values[$i];
		else return null;
	}

	public function getMinValueCount() {
		return $this->min_value_count;
	}

	public function setMinValueCount($min) {
		$this->min_value_count = $min;
	}

	public function getMaxValueCount() {
		return $this->max_value_count;
	}

	public function setMaxValueCount($max) {
		$this->max_value_count = $max;
	}

	public function getEntry() {
		return $this->entry;
	}

	public function setEntry($entry) {
		$this->entry = $entry;

		global $ldapserver;
		$schema_attr = null;
		if ($entry) {
			$schema_attr = $ldapserver->getSchemaAttribute($this->getName(), $entry->getDn());
		}
		if ($schema_attr && $schema_attr->getIsSingleValue()) {
			$this->setMaxValueCount(1);
		}
	}

	public function justModified() {
		$this->modified = true;
	}

	public function hasBeenModified() {
		return $this->modified;
	}

	public function isInternal() {
		return $this->internal;
	}

	public function setInternal() {
		$this->internal = true;
	}

	public function isRequired() {
		if ($this->getMinValueCount() > 0) {
			return true;
		} elseif ($this->isRdn()) {
			return true;
		} else {
			return false;
		}
	}

	public function setRequired() {
		if ($this->getMinValueCount() <= 0) {
			$this->setMinValueCount(1);
		}
	}

	public function setOptional() {
		$this->setMinValueCount(0);
	}

	public function isReadOnly() {
		return $this->readonly;
	}

	public function setReadOnly() {
		$this->readonly = true;
	}

	public function isVisible() {
		return $this->visible;
	}

	public function hide() {
		$this->visible = false;
	}

	public function show() {
		$this->visible = true;
	}

	public function setFriendlyName($name) {
		if ($name != $this->name) {
			$this->friendly_name = $name;
		}
	}

	public function getFriendlyName() {
		if ($this->friendly_name)
			return $this->friendly_name;
		else
			return $_SESSION[APPCONFIG]->getFriendlyName(real_attr_name($this->name));
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setIcon($icon) {
		$this->icon = $icon;
	}

	public function getIcon() {
		return $this->icon;
	}

	public function getHint() {
		return $this->hint;
	}

	public function setHint($hint) {
		$this->hint = $hint;
	}

	public function getMaxLength() {
		return $this->maxlength;
	}

	public function setMaxLength($maxlength) {
		$this->maxlength = $maxlength;
	}

	public function getSize() {
		return $this->size;
	}

	public function setSize($size) {
		$this->size = $size;
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

	public function isRdn() {
		if ($this->entry) {
			//$rdn = get_rdn($this->entry->getDn());
    			//$attr = $this->name;
			//return preg_match("/^${attr}=/", $rdn);
			return ($this->name == $this->entry->getRdnAttributeName());
		} else {
			return false;
		}
	}

	/**
	 * Visit the attribute
	 */
	public function accept($visitor) {
		$visitor->visit('', $this);
	}
}
?>
