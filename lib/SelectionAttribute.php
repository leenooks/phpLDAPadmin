<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/SelectionAttribute.php,v 1.2 2007/12/15 07:50:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author Xavier Bruyet
 *
 * Represents an attribute whose values are in a predefined list
 */
class SelectionAttribute extends Attribute {
	protected $selection;
	protected $multiple;

	public function __construct($name,$values) {
		parent::__construct($name,$values);

		$this->selection = array();
		$this->multiple = false;
	}

	public function addOption($value, $description) {
		$this->selection["$value"] = $description;
	}

	public function getOptionCount() {
		return count($this->selection);
	}

	public function getSelection() {
		return $this->selection;
	}

	public function isMultiple() {
		return $this->multiple;
	}

	public function setMultiple() {
		$this->multiple = true;
	}
}
?>
