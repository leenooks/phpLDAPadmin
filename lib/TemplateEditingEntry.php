<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/TemplateEditingEntry.php,v 1.3.2.4 2008/11/28 12:50:20 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Represent a tree node
 */
class TemplateEditingEntry extends DefaultEditingEntry {
	protected $templates;
	protected $valid;

	protected $default_template;
	protected $selected_template;

	public function __construct($dn) {
		parent::__construct($dn);
		$this->templates = array();
		$this->valid = false;
		$this->default_template = true;
		$this->selected_template = '';
	}

	protected function readEditingTemplates() {
		global $ldapserver;

		if (DEBUG_ENABLED)
			debug_log('Entered with ()',1,__FILE__,__LINE__,__METHOD__);

		if ($this->valid) {
			if (DEBUG_ENABLED)
				debug_log('All templates [%s] are valid',1,__FILE__,__LINE__,__METHOD__,count($this->templates));
			return;
		}

		$this->templates = array();
		$this->valid = true;

		# read the available templates
		$template_xml = new Templates($ldapserver->server_id);
		$all_templates = $template_xml->getEditingTemplates();
		if (!$all_templates) $all_templates = array();

		foreach ($all_templates as $template_name => $template_attrs) {
			# don't select hidden templates
			if (isset($template_attrs['visible']) && (! $template_attrs['visible'])) {
				if (DEBUG_ENABLED)
					debug_log('The template %s is not visible',1,__FILE__,__LINE__,__METHOD__,$template_name);
				continue;
			}
			# don't select invalid templates
			if (isset($template_attrs['invalid']) && $template_attrs['invalid']) {
				if (DEBUG_ENABLED)
					debug_log('The template %s is invalid [%s]',1,__FILE__,__LINE__,__METHOD__,$template_name,isset($template_attrs['invalid_reason']) ? $template_attrs['invalid_reason'] : '');
				$this->valid = false;
				continue;
			}
			# check the template filter
			if (isset($template_attrs['regexp'])) {
				if (! @preg_match('/'.$template_attrs['regexp'].'/i',$this->getDn())) {
					if (DEBUG_ENABLED)
						debug_log('The entry dn doesn\'t match the template %s regexp',1,__FILE__,__LINE__,__METHOD__,$template_name);
					continue;
				}
			}
			# finally add the template to the list
			if (DEBUG_ENABLED)
				debug_log('The template %s is available for the entry',1,__FILE__,__LINE__,__METHOD__,$template_name);
			$this->templates[$template_name] = $template_attrs;
		}
	}

	public function addDefaultTemplate() {
		$this->default_template = true;
	}

	public function delDefaultTemplate() {
		$this->default_template = false;
	}

	public function hasDefaultTemplate() {
        if ($_SESSION[APPCONFIG]->GetValue('appearance','disable_default_template'))
			return false;
		else
			return $this->default_template;
	}

	public function getAttributes() {
		global $ldapserver;

		static $tmpl = array();
		static $attrs = array();
		$dn = $this->getDn();

		if (DEBUG_ENABLED)
			debug_log('Entered with () for dn [%s]',1,__FILE__,__LINE__,__METHOD__,$dn);

		if (!$this->selected_template) {
			return parent::getAttributes();
		} elseif (!isset($attrs[$dn]) || !isset($tmpl[$dn]) || ($this->selected_template != $tmpl[$dn])) {
			$attrs[$dn] = array();
			$tmpl[$dn] = $this->selected_template;

			$attributefactoryclass = $_SESSION[APPCONFIG]->GetValue('appearance','attribute_factory');
			eval('$attribute_factory = new '.$attributefactoryclass.'();');

			$int_attrs_vals = $ldapserver->getDNSysAttrs($this->getDn());
			if (! $int_attrs_vals) $attrs_vals = array();
			elseif (! is_array($int_attrs_vals)) $int_attrs_vals = array($int_attrs_vals);

			$custom_int_attrs_vals = $ldapserver->getCustomDNSysAttrs($this->getDn());
			if (! $custom_int_attrs_vals) $attrs_vals = array();
			elseif (! is_array($custom_int_attrs_vals)) $custom_int_attrs_vals = array($custom_int_attrs_vals);

			$attrs_vals = $ldapserver->getDNAttrs($this->getDn(),false,$_SESSION[APPCONFIG]->GetValue('deref','view'));
			if (! $attrs_vals) $attrs_vals = array();
			elseif (! is_array($attrs_vals)) $attrs_vals = array($attrs_vals);

			$custom_attrs_vals = $ldapserver->getCustomDNAttrs($this->getDn(),false,$_SESSION[APPCONFIG]->GetValue('deref','view'));
			if (! $custom_attrs_vals) $attrs_vals = array();
			elseif (! is_array($custom_attrs_vals)) $custom_attrs_vals = array($custom_attrs_vals);

			$int_attrs_vals = array_merge($int_attrs_vals,$custom_int_attrs_vals);
			$attrs_vals = array_merge($attrs_vals,$custom_attrs_vals);
			$attrs_vals = array_merge($attrs_vals,$int_attrs_vals);

			$selected_tmpl = isset($this->templates[$this->selected_template])
					? $this->templates[$this->selected_template]
					: array();
			$template_attrs = isset($selected_tmpl['empty_attrs'])
					? $selected_tmpl['empty_attrs']
					: array();
			masort($template_attrs,'page,order',1);

			$objectclasses = null;

			// template attributes
			foreach ($template_attrs as $attr => $params) {
				$vals = (isset($attrs_vals[$attr]) && $attrs_vals[$attr]) ? $attrs_vals[$attr] : array();
				if (! is_array($vals)) $vals = array($vals);

				if (isset($params['option'])
					|| ( isset($params['type']) && (($params['type'] == 'select') || ($params['type'] == 'multiselect')) )) {

					if (! isset($params['option'])) $params['option'] = array();
					elseif (! is_array($params['option'])) $params['option'] = array($params['option']);

					$arr1 = array();
					foreach ($params['option'] as $id_parval => $parval) {
						$arr2 = Templates::EvaluateDefault($ldapserver,$parval,$this->getDn(),null,null);
						if (is_array($arr2)) $arr1 = array_merge($arr1,$arr2);
						else $arr1[$id_parval] = $arr2;
					}
					$params['option'] = $arr1;
				}

				if (!isset($params['type'])) $params['type'] = 'text';

				if ($params['type'] != 'text' && $params['type'] != 'password' && $params['type'] != 'textarea'
					&& $params['type'] != 'multiselect' && $params['type'] != 'select') {

					eval('$attribute = $attribute_factory->new'.$params['type'].'Attribute($attr,$vals);');

				} else if ($params['type'] == 'password') {
					$attribute = $attribute_factory->newPasswordAttribute($attr,$vals);

				} elseif ($params['type'] == 'textarea') {
					$attribute = $attribute_factory->newMultiLineAttribute($attr,$vals);

					if (isset($params['rows']) && $params['rows']) {
						$attribute->setRows($params['rows']);
					}

					if (isset($params['cols']) && $params['cols']) {
						$attribute->setCols($params['cols']);
					}

				} elseif (isset($params['option']) && is_array($params['option'])) {
					$attribute = $attribute_factory->newSelectionAttribute($attr,$vals);

					if ($params['type'] == 'multiselect') {
						$attribute->setMultiple();
					}

					foreach ($params['option'] as $key => $value) {
						if (preg_match('/^_KEY:/',$key)) {
							$key = preg_replace('/^_KEY:/','',$key);
						} else {
							$key = $value;
						}
						$attribute->addOption($key,$value);
					}

				} else {
					$attribute = $attribute_factory->newAttribute($attr,$vals);
				}

				if ($attr == 'objectClass') $objectclasses = $attribute->getValues();
				$attribute->setEntry($this);

				if (isset($int_attrs_vals[$attr])) {
					$attribute->setInternal();
				}

				foreach ($params as $param_name => $param_value) {
					switch ($param_name) {
						case 'minvalnb':
							$attribute->setMinValueCount($param_value);
							break;
						case 'maxvalnb':
							$attribute->setMaxValueCount($param_value);
							break;
						case 'icon':
							if ($param_value) $attribute->setIcon($param_value);
							break;
						case 'description':
							if ($param_value) $attribute->setDescription($param_value);
							break;
						case 'display':
							if ($param_value) $attribute->setFriendlyName($param_value);
							break;
						case 'hint':
							if ($param_value) $attribute->setHint($param_value);
							break;
						case 'size':
							if ($param_value) $attribute->setSize($param_value);
							break;
						case 'maxlength':
							if ($param_value) $attribute->setMaxLength($param_value);
							break;
						case 'option':
						case 'type':
						case 'rows':
						case 'cols':
						case 'readonly':
						case 'disable':
						case 'hidden':
							break;
						default:
							# page, post, spacer, onchange
							$attribute->setProperty($param_name,$param_value);
							break;
					}
				}

				if ($this->isReadOnly()
					|| (isset($params['readonly']) && $params['readonly'])
					|| (!isset($params['readonly']) && $ldapserver->isAttrReadOnly($attr))) {
					$attribute->setReadOnly();
				}

				# has the config.php or the template specified
				# that this attribute is to be hidden or shown ?
				if ((isset($params['disable']) && $params['disable'])
					|| (isset($params['hidden']) && $params['hidden'])
					|| (!isset($params['hidden']) && $ldapserver->isAttrHidden($attr))) {
					$attribute->hide();
				}

				$attrs[$dn][] = $attribute;
			}

			# Hidden attributes
			foreach ($attrs_vals as $attr => $vals) {
				if (isset($template_attrs[$attr])) continue;

				$attribute = $attribute_factory->newAttribute($attr,$vals);
				$attribute->setEntry($this);

				if (isset($int_attrs_vals[$attr])) {
					$attribute->setInternal();
				}
				if ($attr == 'objectClass') {
					$objectclasses = $attribute->getValues();
				}

				$attribute->setReadOnly();
				if (!$attribute->isInternal()) { # internal attributes are visible by default
					$attribute->hide();
				}
				$attrs[$dn][] = $attribute;
			}

			# Required attributes
			if ($objectclasses) {
				$schema_oclasses = $ldapserver->SchemaObjectClasses();
				foreach ($objectclasses as $oclass) {
					$schema_oclass = $ldapserver->getSchemaObjectClass($oclass);
					assert($schema_oclass);

					$mustattrs = $schema_oclass->getMustAttrs($schema_oclasses);
					if (!$mustattrs) $mustattrs = array();
					if (!is_array($mustattrs)) $mustattrs = array($mustattrs);

					foreach ($mustattrs as $mustattr) {
						foreach ($attrs[$dn] as $attr) {
							if ($attr->getName() == $mustattr->getName()) {
								$attr->setRequired();
								break;
							}
						}
					}
				}
			}
		}

		return $attrs[$dn];
	}

	public function &getTemplates() {
		$this->readEditingTemplates();
		return $this->templates;
	}

	public function getTemplatesCount() {
		$this->readEditingTemplates();
		return count($this->templates);
	}

	public function setSelectedTemplateName($name) {
		$this->readEditingTemplates();
		$this->setLeaf(false);
		if (!$name || isset($this->templates[$name])) {
			$this->selected_template = $name;
			if (isset($this->templates[$name]['leaf']) && $this->templates[$name]['leaf']) $this->setLeaf(true);
		}
	}

	public function getSelectedTemplateName() {
		return $this->selected_template;
	}
}
?>
