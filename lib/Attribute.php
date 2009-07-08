<?php
// $Header$

/**
 * Classes and functions for the template engine.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Represents an attribute of a template.
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
class Attribute {
	# Attribute Name
	public $name;
	# Source of this attribute definition
	protected $source;

	# Current and Old Values
	protected $oldvalues = array();
	protected $values = array();

	# MIN/MAX number of values
	protected $min_value_count = -1;
	protected $max_value_count = -1;

	# Is the attribute internal
	protected $internal = false;
	# Has the attribute been modified
	protected $modified = false;
	# Is the attribute being deleted because of an object class removal
	protected $forcedelete = false;
	# Is the attribute visible
	protected $visible = false;
	protected $forcehide = false;
	# Is the attribute modifiable
	protected $readonly = false;
	# LDAP attribute type MUST/MAY
	protected $ldaptype = null;
	# Attribute property type (eg password, select, multiselect)
	protected $type = '';
	# Attribute value to keep unique
	protected $unique = false;

	# Display parameters
	protected $display = '';
	protected $icon = '';
	protected $hint = '';
	# Helper details
	protected $helper = array();
	protected $helpervalue = array();
	# Onchange details
	protected $onchange = array();
	# Show spacer after this attribute is rendered
	protected $spacer = false;
	protected $verify = false;

	# Component size
	protected $size = 0;
	# Value max length
	protected $maxlength = 0;
	# Text Area sizings
	protected $cols = 0;
	protected $rows = 0;

	# Public for sorting
	public $page = 1;
	public $order = 255;
	public $ordersort = 255;
	public $rdn = false;

	# Schema Aliases for this attribute (stored in lowercase)
	protected $aliases = array();

	# Configuration for automatically generated values
	protected $autovalue = array();
	protected $postvalue = array();

	public function __construct($name,$values,$server_id,$source=null) {
		$server = $_SESSION[APPCONFIG]->getServer($server_id);

		$sattr = $server->getSchemaAttribute($name);
		if ($sattr) {
			$this->name = $sattr->getName(false);
			$this->setLDAPdetails($sattr);

		} else
			$this->name = $name;

		$this->source = $source;

		# XML attributes are shown by default
		switch ($source) {
			case 'XML': $this->show();
				$this->setXML($values);

				break;

			default:
				if (! isset($values['values']))
					debug_dump_backtrace('no index "values"',1);

				$this->initValue($values['values']);
		}

		# Should this attribute be hidden
		if ($server->isAttrHidden($this->name))
			$this->forcehide = true;

		# Should this attribute value be read only
		if ($server->isAttrReadOnly($this->name))
			$this->readonly = true;

		# Should this attribute value be unique
		if ($server->isAttrUnique($this->name))
			$this->unique = true;
	}

	/**
	 * Return the name of the attribute.
	 *
	 * @param boolean $lower - Return the attribute in normal or lower case (default lower)
	 * @param boolean $real - Return the real attribute name (with ;binary, or just the name)
	 * @return string Attribute name
	 */
	public function getName($lower=true,$real=false) {
		if ($real)
			return $lower ? strtolower($this->name) : $this->name;
		else
			return $lower ? strtolower($this->real_attr_name()) : $this->real_attr_name();
	}

	public function getValues() {
		return $this->values;
	}

	public function getOldValues() {
		return $this->oldvalues;
	}

	public function getValueCount() {
		return count($this->values);
	}

	public function getSource() {
		return $this->source;
	}

	/**
	 * Autovalue is called after the attribute is initialised, and thus the values from the ldap server will be set.
	 */
	public function autoValue($new_val) {
		if ($this->values)
			return;

		$this->values = $new_val;
	}

	public function initValue($new_val) {
		if ($this->values || $this->oldvalues) {
			debug_dump(array('new_val'=>$new_val,'this'=>$this));
			debug_dump_backtrace('new and/or old values are set',1);
		}

		$this->values = $new_val;
	}

	public function clearValue() {
		$this->values = array();
	}

	public function setOldValue($val) {
		$this->oldvalues = $val;
	}

	public function setValue($new_val) {
		if ($this->values) {
			if ($this->values == $new_val)
				return;

			if ($this->oldvalues) {
				debug_dump($this);
				debug_dump_backtrace('old values are set',1);
			} else
				$this->oldvalues = $this->values;
		}

		if ($new_val == $this->values)
			return;

		$this->values = $new_val;
		$this->justModified();
	}

	public function addValue($new_val,$i=-1) {
		if ($i < 0)
			$i = $this->getValueCount();

		$old_val = $this->getValue($i);
		if (is_null($old_val) || ($old_val != $new_val))
			$this->justModified();

		$this->values[$i] = $new_val;
	}

	public function delValue($i=-1) {
		if ($i < 0)
			$this->setValue(array());

		if (! $this->hasBeenModified())
			$this->oldvalues = $this->values;

		if (isset($this->values[$i])) {
			unset($this->values[$i]);
			$this->values = array_values($this->values);
			$this->justModified();
		}
	}

	public function getValue($i) {
		if (isset($this->values[$i]))
			return $this->values[$i];
		else
			return null;
	}

	public function getOldValue($i) {
		if (isset($this->oldvalues[$i]))
			return $this->oldvalues[$i];
		else
			return null;
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

	public function haveMoreValues() {
		if ($this->getMaxValueCount() < 0 || ($this->getValueCount() < $this->getMaxValueCount()))
			return true;
		else
			return false;
	}

	public function justModified() {
		$this->modified = true;
	}

	public function hasBeenModified() {
		return $this->modified;
	}

	public function isForceDelete() {
		return $this->forcedelete;
	}

	public function setForceDelete() {
		$this->forcedelete = true;
		$this->oldvalues = $this->values;
		$this->values = array();
		$this->justModified();
	}

	public function isInternal() {
		return $this->internal;
	}

	public function setInternal() {
		$this->internal = true;
	}

	public function isRequired() {
		if ($this->getMinValueCount() > 0)
			return true;
		elseif ($this->ldaptype == 'must')
			return true;
		elseif ($this->isRDN())
			return true;
		else
			return false;
	}

	public function isMay() {
		if (($this->ldaptype == 'may') && ! $this->isRequired())
			return true;
		else
			return false;
	}

	public function setType($type) {
		$this->type = strtolower($type);
	}

	public function getType() {
		return $this->type;
	}

	public function setLDAPtype($type) {
		$this->ldaptype = strtolower($type);
	}

	public function getLDAPtype() {
		return $this->ldaptype;
	}

	public function setProperties($properties) {
		foreach ($properties as $index => $value) {
			if ($index == 'maxvalnb') {
				$this->setMaxValueCount($value);
				continue;

			} elseif ($index == 'minvalnb') {
				$this->setMinValueCount($value);
				continue;

			} elseif ($index == 'maxlength') {
				$this->setMinValueCount($value);
				continue;

			} elseif ($index == 'hidden') {
				$this->visible = $value;
				continue;

			} elseif (in_array($index,array('cols','rows'))) {
				# @todo To be implemented
				continue;
			}

			if (isset($this->$index))
				$this->$index = $value;
			else {
				debug_dump($this);
				debug_dump_backtrace(sprintf('Unknown property (%s) with value (%s) for (%s)',$index,$value,$this->getName()),1);
			}
		}
	}

	public function setRequired() {
		if ($this->getMinValueCount() <= 0)
			$this->setMinValueCount(1);
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
		return $this->visible && (! $this->forcehide);
	}

	public function hide() {
		$this->visible = false;
	}

	public function show() {
		$this->visible = true;
	}

	public function haveFriendlyName() {
		return $_SESSION[APPCONFIG]->haveFriendlyName($this);
	}

	public function getFriendlyName() {
		if ($this->display)
			return $this->display;
		else
			return $_SESSION[APPCONFIG]->getFriendlyName($this);
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
		return $this->icon ? sprintf('%s/%s',IMGDIR,$this->icon) : '';
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

	public function getSpacer() {
		return $this->spacer;
	}

	public function getPage() {
		return $this->page;
	}
	public function setPage($page) {
		$this->page = $page;
	}

	public function getOnChange() {
		return $this->onchange;
	}

	public function getHelper() {
		return $this->helper;
	}

	public function getHelperValue() {
		return $this->helpervalue;
	}

	public function getVerify() {
		return $this->verify;
	}

	public function setRDN($rdn) {
		$this->rdn = $rdn;
	}

	/**
	 * Return if this attribute is an RDN attribute
	 *
	 * @return boolean
	 */
	public function isRDN() {
		return $this->rdn;
	}

	/**
	 * Capture all the LDAP details we are interested in
	 *
	 * @param sattr Schema Attribute
	 */
	private function setLDAPdetails($sattr) {
		# By default, set this as a MAY attribute, later processing should make it a MUST attribute if it is.
		if (! $this->ldaptype)
			$this->ldaptype = 'may';

		# Store our Aliases
		foreach ($sattr->getAliases() as $alias)
			array_push($this->aliases,strtolower($alias));

		if ($sattr->getIsSingleValue())
			$this->setMaxValueCount(1);
	}

	/**
	 * Return a list of aliases for this Attribute (as defined by the schema)
	 * This list will be lowercase.
	 */
	public function getAliases() {
		return $this->aliases;
	}

	public function getAutoValue() {
		return $this->autovalue;
	}

	public function getPostValue() {
		return $this->postvalue;
	}

	public function setPostValue($postvalue) {
		$this->postvalue = $postvalue;
	}

	public function setXML($values) {
		# Mostly all the time, this should be an array
		if (is_array($values))
			foreach ($values as $index => $value)
				switch ($index) {
					# Helpers should be accompanied with a <post> attribute.
					case 'helper':
						if (! isset($values['post']))
							system_message(array(
								'title'=>sprintf('%s [<i>%s</i>]',_('Missing [post] setting in XML file'),$index),
								'body'=>_('[helper] needs an accompanying [post] action.'),
								'type'=>'warn'));

						if (isset($value['value']) && ! is_array($value['value']) && preg_match('/^=php\.(\w+)\((.*)\)$/',$value['value'],$matches)) {
							$this->helpervalue['function'] = $matches[1];
							$this->helpervalue['args'] = $matches[2];

							unset ($value['value']);
						}

						foreach ($value as $i => $detail) {
							if (! in_array($i,array('default','display','id','value'))) {
								system_message(array(
									'title'=>sprintf('%s [<i>%s</i>]',_('Unknown XML setting'),$i),
									'body'=>sprintf('%s <small>[%s]</small>',_('Unknown XML type setting for helper will be ignored.'),$detail),
									'type'=>'warn'));

								unset($value[$i]);
							}
						}

						$this->$index = $value;

						break;

					case 'hidden': $value ? $this->visible = false : $this->visible = true;
						break;

					case 'spacer': $value ? $this->$index = true : $this->$index = false;
						break;

					# Essentially, we ignore type, it is used to select an Attribute type in the Factory. But we'll generated a warning if there is an unknown type.
					case 'type':
						if (! in_array($value,array('password','multiselect','select','textarea')))
							system_message(array(
								'title'=>sprintf('%s [<i>%s</i>]',_('Unknown XML setting'),$index),
								'body'=>sprintf('%s <small>[%s]</small>',_('Unknown XML type setting will be ignored.'),$value),
								'type'=>'warn'));

						break;

					case 'post':
						if (preg_match('/^=php\.(\w+)\((.*)\)$/',$value,$matches)) {
							$this->postvalue['function'] = $matches[1];
							$this->postvalue['args'] = $matches[2];

						} else
							system_message(array(
								'title'=>sprintf('%s [<i>%s</i>]',_('Unknown XML setting'),$index),
								'body'=>sprintf('%s <small>[%s]</small>',_('Unknown XML type setting will be ignored.'),$value),
								'type'=>'warn'));

					case 'value':
						if (is_array($value))
							$this->values = $value;

						else
							# Check to see if the value is auto generated.
							if (preg_match('/^=php\.(\w+)\((.*)\)$/',$value,$matches)) {
								$this->autovalue['function'] = $matches[1];
								$this->autovalue['args'] = $matches[2];

								# We'll add a hint too
								if (! $this->hint)
									$this->hint = _('Automatically determined');

							} else
								$this->values = array($value);

						break;

					# Queries
					case 'ordersort':

					# Creation/Editing Templates
					case 'cols':
					case 'default':
					case 'display':
					case 'hint':
					case 'icon':
					case 'maxlength':
					case 'onchange':
					case 'order':
					case 'page':
					case 'readonly':
					case 'rows':
					case 'size':
					case 'values':
					case 'verify': $this->$index = $value;
						break;

					default:
						system_message(array(
							'title'=>sprintf('%s [<i>%s</i>]',_('Unknown XML setting'),$index),
							'body'=>sprintf('%s <small>[%s]</small>',_('Unknown attribute setting will be ignored.'),serialize($value)),
							'type'=>'warn'));
				}

		elseif (is_string($values) && (strlen($values) > 0))
			$this->values = array($values);
	}

	/**
	 * Display the values removed in an attribute.
	 */
	public function getRemovedValues() {
		$return = array();

		foreach ($this->getOldValues() as $value)
			if (! in_array($value,$this->getValues()))
				array_push($return,$value);

		return $return;
	}

	/**
	 * Display the values removed in an attribute.
	 */
	public function getAddedValues() {
		$return = array();

		foreach ($this->getValues() as $value)
			if (! in_array($value,$this->getOldValues()))
				array_push($return,$value);

		return $return;
	}

	/**
	 * Prunes off anything after the ";" in an attr name. This is useful for
	 * attributes that may have ";binary" appended to their names. With
	 * real_attr_name(), you can more easily fetch these attributes' schema
	 * with their "real" attribute name.
	 *
	 * @param string $attr_name The name of the attribute to examine.
	 * @return string
	 */
	private function real_attr_name() {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$attr_name);

		return preg_replace('/;.*$/U','',$this->name);
	}

	/**
	 * Does this attribute need supporting JS
	 */
	public function needJS($type=null) {
		if (is_null($type)) {
			foreach (array('focus','blur','validate') as $type)
				if ($this->needJS($type))
					return true;

			return false;

		} elseif ($type == 'focus') {
			# We dont have any focus javascript routines.
			return false;

		} elseif ($type == 'blur') {
			if ($this->onchange || $this->isRequired())
				return true;
			else
				return false;

		} elseif ($type == 'validate') {
			if ($this->isRequired())
				return true;
			else
				return false;

		} else
			debug_dump_backtrace(sprintf('Unknown JS request %s',$type),1);
	}
}
?>
