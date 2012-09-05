<?php
/**
 * Classes and functions for the template engine.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Represents an attribute whose values are in a predefined list
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
class SelectionAttribute extends Attribute {
	protected $selection = array();
	protected $multiple;
	protected $default;

	public function __construct($name,$values,$server_id,$source=null) {
		# Call our parent constructor
		parent::__construct($name,$values,$server_id,$source);

		# Our values are set by parent(). If we do have values, and the source was XML, move them to our selection.
		if ($this->source == 'XML' && $this->values) {
			$this->selection = $this->values;
			$this->values = array();
		}

		if (isset($values['type']) && $values['type'] == 'multiselect')
			$this->multiple = true;
		else
			$this->multiple = false;
	}

	public function addOption($value,$description) {
		$this->selection[$value] = $description;
	}

	public function addValue($new_val,$i=-1) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->addOption($new_val,$i);
	}

	public function getOptionCount() {
		return count($this->selection);
	}

	public function getSelection() {
		return $this->selection;
	}

	public function autoValue($value) {
		$this->selection = $value;
	}

	public function getDefault() {
		return $this->default;
	}

	public function isMultiple() {
		return $this->multiple;
	}

	public function setMultiple() {
		$this->multiple = true;
	}
}
?>
