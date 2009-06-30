<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/MultiLineAttribute.php,v 1.2 2007/12/15 07:50:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author Xavier Bruyet
 *
 * Represents a attribute whose values are multiline text
 */
class MultiLineAttribute extends Attribute {
	protected $rows;
	protected $cols;

	public function __construct($name,$values) {
		parent::__construct($name,$values);

		$this->rows = 0;
		$this->cols = 0;
	}

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
