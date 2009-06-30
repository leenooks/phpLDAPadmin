<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/EntryReader.php,v 1.2.2.3 2008/01/27 14:09:14 wurley Exp $

define('ENTRY_READER_CREATION_CONTEXT', '1');
define('ENTRY_READER_EDITING_CONTEXT', '2');

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Visit an entry and its attributes to initialize their values
 */
class EntryReader extends Visitor {
	protected $index;
	protected $context;

	public function __construct($ldapserver) {
		$this->index = $ldapserver->server_id;
		$this->context = 0;
	}

	/**************************/
	/* Visit an Entry         */
	/**************************/

	public function visitEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Enter with (%s) for dn (%s)',1,__FILE__,__LINE__,__METHOD__,$entry,$entry->getDn());
	}

	public function visitEntryEnd($entry) {
		if (DEBUG_ENABLED)
			debug_log('Enter with (%s) for dn (%s)',1,__FILE__,__LINE__,__METHOD__,$entry,$entry->getDn());
	}

	/**************************/
	/* Visit a EditingEntry   */
	/**************************/

	public function visitDefaultEditingEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Enter with (%s) for dn (%s)',1,__FILE__,__LINE__,__METHOD__,$entry,$entry->getDn());

		$this->context = ENTRY_READER_EDITING_CONTEXT;
		$this->visit('Entry::Start', $entry);
	}

	public function visitTemplateEditingEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Enter with (%s) for dn (%s)',1,__FILE__,__LINE__,__METHOD__,$entry,$entry->getDn());

		$this->visit('DefaultEditingEntry::Start', $entry);

		if (isset($_REQUEST['template'])) {
			$entry->setSelectedTemplateName(trim($_REQUEST['template']));
		} elseif (($entry->getTemplatesCount() == 1) && !$entry->hasDefaultTemplate()) {
			$templates = &$entry->getTemplates();
			$template_names = array_keys($templates);
			$entry->setSelectedTemplateName($template_names[0]);
		}
	}

	/**************************/
	/* Visit a CreatingEntry  */
	/**************************/

	public function visitDefaultCreatingEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Enter with (%s) for dn (%s)',1,__FILE__,__LINE__,__METHOD__,$entry,$entry->getDn());

		$this->context = ENTRY_READER_CREATION_CONTEXT;
		$this->visit('Entry::Start', $entry);

		if (isset($_POST['new_values']['objectClass'])) {
			$ocs = $_POST['new_values']['objectClass'];
			if (is_string($ocs) && (strlen($ocs) > 0)) $ocs = array($ocs);
			elseif (!$ocs) $ocs = array();

			foreach ($ocs as $oc) $entry->addObjectClass(trim($oc));
		}

		if (isset($_REQUEST['container'])) {
			$entry->setContainer(trim($_REQUEST['container']));
		}
	}

	public function visitTemplateCreatingEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Enter with (%s) for dn (%s)',1,__FILE__,__LINE__,__METHOD__,$entry,$entry->getDn());

		$this->visit('DefaultCreatingEntry::Start', $entry);

		if (isset($_REQUEST['template'])) {
			$entry->setSelectedTemplateName(trim($_REQUEST['template']));
		} elseif (($entry->getTemplatesCount() == 1) && !$entry->hasDefaultTemplate()) {
			$templates = &$entry->getTemplates();
			$template_names = array_keys($templates);
			$entry->setSelectedTemplateName($template_names[0]);
		}
	}

	/**************************/
	/* Visit an Attribute     */
	/**************************/

	public function visitAttribute($attribute) {
		if (DEBUG_ENABLED)
			debug_log('Enter with (%s) for attribute (%s)',1,__FILE__,__LINE__,__METHOD__,$attribute,$attribute->getName());

		$name = $attribute->getName();

		// @todo editing objectclasses
		if (($this->context == ENTRY_READER_CREATION_CONTEXT) && ($name == 'objectClass')) return;

		$old_vals = $this->get('OldValues', $attribute);
		$new_vals = $this->get('NewValues', $attribute);

		if (isset($_POST['old_values'][$name])) {
			$post_old_vals = $_POST['old_values'][$name];
			if (is_string($post_old_vals) && (strlen($post_old_vals) > 0)) $post_old_vals = array($post_old_vals);
			elseif (!$post_old_vals) $post_old_vals = array();

			// delete last empty values
			for ($i = count($post_old_vals)-1; $i >= 0; $i--) {
				if (! strlen($post_old_vals[$i])) unset($post_old_vals[$i]);
				else break;
			}

			// attribute modified by someone else ?
			if (count($old_vals) != count($post_old_vals)) {
				$attribute->justModified();
			} else {
				foreach ($post_old_vals as $i => $old_val) {
					if (!isset($old_vals[$i]) || ($old_vals[$i] != $old_val)) {
						$attribute->justModified();
						break;
					}
				}
			}
		}

		foreach ($new_vals as $i => $new_val) {
			// if the attribute has not been already modified by a post of a previous page
			if (!$attribute->hasBeenModified()) {
				// if the value has changed (added or modified/deleted)
				if ((!isset($old_vals[$i]) && (strlen($new_val) > 0)) || (isset($old_vals[$i]) && ($old_vals[$i] != $new_val))) {
					$new_val = $this->get('PostValue', $attribute, $i, $new_val);
				}
			}

			if ((!isset($old_vals[$i]) && (strlen($new_val) > 0)) || (isset($old_vals[$i]) && ($old_vals[$i] != $new_val))) {
				$attribute->justModified();
				$attribute->addValue($new_val, $i);
			}
		}

		// old value deletion
		if (isset($_POST['old_values'][$name]) && !$attribute->isInternal()) {
			for ($i = count($new_vals); $i < count($old_vals); $i++) {
				$attribute->addValue('', $i);
			}
		}

		// modified attributes
		$modified_attrs = isset($_REQUEST['modified_attrs']) ? $_REQUEST['modified_attrs'] : false;
		if (is_array($modified_attrs) && in_array($name, $modified_attrs)) {
			$attribute->justModified();
		}
	}

	public function getAttributeOldValues($attribute) {
		$old_vals = $attribute->getValues();
		return $old_vals;
	}

	public function getAttributeNewValues($attribute) {
		$name = $attribute->getName();

		$new_vals = isset($_POST['new_values'][$name]) ? $_POST['new_values'][$name] : null;
		if (is_string($new_vals) && (strlen($new_vals) > 0)) $new_vals = array($new_vals);
		elseif (!$new_vals) $new_vals = array();

		$i = count($new_vals) - 1;
		$j = $attribute->getValueCount();
		while (($i >= 0) && ($i >= $j) && !$new_vals[$i]) {
			if ($i > $j) unset($new_vals[$i]);
			$i--;
		}

		return $new_vals;
	}

	public function getAttributeRequestValue($attribute, $i, $val, $request) {
		if ($request == $attribute->getName()) return $val;

		$val = null;
		$entry = $attribute->getEntry();
		$request_attribute = ($entry ? $entry->getAttribute($request) : null);

		if ($request_attribute) {
			$val = $request_attribute->getValue($i);
		} elseif (isset($_REQUEST[$request][$attribute->getName()][$i])) {
			$val = $_REQUEST[$request][$attribute->getName()][$i];
		}

		if (is_null($val)) {
			pla_error(sprintf(_('Your template is missing variable (%s)'), $request));
		}

		return $val;
	}

	public function getAttributePostValue($attribute, $i, $val) {
		if (!$attribute->hasProperty('post')) return trim($val);

		if (preg_match('/^=php\.(\w+)\((.*)\)$/', $attribute->getProperty('post'), $matches)) {
			switch ($matches[1]) {
				case 'Password' :
					preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$matches[2],$matchall);
					$enc = $this->get('RequestValue', $attribute, $i, $val, $matchall[1][0]);
					$password = $val;
					if ($password) {
						$val = password_hash($password, $enc);
					}
					break;
				case 'SambaPassword' :
					$matchall = explode(',',$matches[2]);

					# If we have no password, then dont hash nothing!
					if (strlen($val) <= 0)
						break;

					$sambapassword = new smbHash;

					switch ($matchall[0]) {
						case 'LM' : $val = $sambapassword->lmhash($val); break;
						case 'NT' : $val = $sambapassword->nthash($val); break;
						default : $val = '';
					}
					break;
				case 'Join' :
					preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$matches[2],$matchall);
					$matchattrs = explode(',',$matches[2]);
					$char = $matchattrs[0];

					$values = array();
					foreach ($matchall[1] as $joinattr) {
						$values[] = $this->get('RequestValue', $attribute, $i, $val, $joinattr);
					}

					$val = implode($char, $values);
					break;
				default :
					if (function_exists($matches[1])) {
						$val = call_user_func($matches[1], $matches[2], $attribute, $i, $val);
					} else {
						pla_error(sprintf(_('Your template has an unknown post function (%s).'), $matches[1]));
					}
			}
		}

		return $val;
	}

	/*******************************/
	/* Visit a BinaryAttribute     */
	/*******************************/

	public function getBinaryAttributeOldValues($attribute) {
		$old_vals = array();
		return $old_vals;
	}

	/**
	 * If there is binary post data, save them in
	 * $_SESSION['submitform'][$attribute_name][$key][$file_name][$file_path]
	 * with key = md5("$file_name|$file_path")
	 *
	 * return binary values
	 */
	public function getBinaryAttributeNewValues($attribute) {
		$name = $attribute->getName();
		$new_vals = $this->get('Attribute::NewValues', $attribute);

		$i = 0;
		$vals = array();
		foreach ($new_vals as $new_val) {
			if (isset($_SESSION['submitform'][$name][$new_val])) {
				$bin = '';
				foreach ($_SESSION['submitform'][$name][$new_val] as $filename => $file) {
					$attribute->addFileName($filename, $i);
					foreach ($file as $filepath => $binaries) {
						$attribute->addFilePath($filepath, $i);
						$bin = $binaries;
					}
				}
				$vals[] = $bin;
				$i++;
			}
		}

		$new_files = isset($_FILES['new_values']['name'][$name]) ? $_FILES['new_values']['name'][$name] : null;
		if (!$new_files) $new_files = array();
		elseif (!is_array($new_files)) $new_files = array($new_files);

		foreach ($new_files as $j => $file_name) {
			$file_path = $_FILES['new_values']['tmp_name'][$name][$j];
			if (is_uploaded_file($file_path)) {
				$f = fopen($file_path, 'r');
				$binary_data = fread($f, filesize($file_path));
				fclose($f);

				$attribute->addFileName($file_name, $i);
				$attribute->addFilePath($file_path, $i);

				$key = md5("$file_name|$file_path");
				$_SESSION['submitform'][$name][$key][$file_name][$file_path] = $binary_data;
				$vals[] = $binary_data;
				$i++;
			}
		}

		return $vals;
	}

	public function getBinaryAttributePostValue($attribute, $i, $val) {
		return $val;
	}

	/*********************************/
	/* Visit a PasswordAttribute     */
	/*********************************/

	public function getPasswordAttributePostValue($attribute, $i, $val) {
		$name = $attribute->getName();

		if ($attribute->hasProperty('verify') && $attribute->getProperty('verify')) {
			$verif_val = isset($_POST['new_values_verify'][$name][$i]) ? $_POST['new_values_verify'][$name][$i] : null;
			if (!$verif_val || ($verif_val != $val)) {
				system_message(array(
					'title'=>_('Checking passwords'),
					'body'=>_('You have specified two different passwords'),
					'type'=>'error'));
				return $attribute->getValue($i);
			}
		}

		if ($attribute->hasProperty('post')) {
			$val = $this->get('Attribute::PostValue', $attribute, $i, $val);

		} elseif (strlen($val) > 0) {
			if (isset($_REQUEST['enc'][$attribute->getName()][$i]))
				$enc = $_REQUEST['enc'][$attribute->getName()][$i];
			else
				$enc = get_default_hash($this->index);

			$val = password_hash($val, $enc);
		}
		return $val;
	}

	public function getSambaPasswordAttributePostValue($attribute, $i, $val) {
		$name = $attribute->getName();

		if ($attribute->hasProperty('verify') && $attribute->getProperty('verify')) {
			$verif_val = isset($_POST['new_values_verify'][$name][$i]) ? $_POST['new_values_verify'][$name][$i] : null;
			if (!$verif_val || ($verif_val != $val)) {
				system_message(array(
					'title'=>_('Checking passwords'),
					'body'=>_('You have specified two different passwords'),
					'type'=>'error'));
				return $attribute->getValue($i);
			}
		}

		if ($attribute->hasProperty('post')) {
			$val = $this->get('Attribute::PostValue', $attribute, $i, $val);
		} elseif (strlen($val) > 0) {
			$sambapassword = new smbHash;

			if ($name == 'sambaLMPassword')
				$val = $sambapassword->lmhash($val);
			elseif ($name == 'sambaNTPassword')
				$val = $sambapassword->nthash($val);
		}
		return $val;
	}
}
?>
