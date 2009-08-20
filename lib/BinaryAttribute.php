<?php
/**
 * Classes and functions for the template engine.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Represents an attribute whose values are binary
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
class BinaryAttribute extends Attribute {
	protected $filepaths;
	protected $filenames;

	public function __construct($name,$values,$server_id,$source=null) {
		parent::__construct($name,$values,$server_id,$source);

		$this->filepaths = array();
		$this->filenames = array();
	}

	public function getFileNames() {
		return $this->filenames;
	}

	public function getFileName($i) {
		if (isset($this->filenames[$i])) return $this->filenames[$i];
		else return null;
	}

	public function addFileName($name, $i = -1) {
		if ($i < 0) {
			$this->filenames[] = $name;
		} else {
			$this->filenames[$i] = $name;
		}
	}

	public function getFilePaths() {
		return $this->filepaths;
	}

	public function getFilePath($i) {
		if (isset($this->filepaths[$i])) return $this->filepaths[$i];
		else return null;
	}

	public function addFilePath($path, $i = -1) {
		if ($i < 0) {
			$this->filepaths[] = $path;
		} else {
			$this->filepaths[$i] = $path;
		}
	}
}
?>
