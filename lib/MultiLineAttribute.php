<?php
/**
 * Classes and functions for the template engine.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Represents a attribute whose values are multiline text
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
class MultiLineAttribute extends Attribute {
	protected $rows = 0;
	protected $cols = 0;

	public function getRows() {
		return $this->rows;
	}

	public function setRows($rows) {
		$this->rows = $rows;
	}

	public function getCols() {
		return $this->cols;
	}

	public function setCols($cols) {
		$this->cols = $cols;
	}
}
?>
