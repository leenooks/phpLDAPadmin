<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/TemplateCreatingEntry.php,v 1.3 2007/12/15 11:27:04 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Represent a entry which is being created
 */
class TemplateCreatingEntry extends DefaultCreatingEntry {
	protected $templates;

	protected $default_template;
	protected $selected_template;

	public function __construct() {
		parent::__construct();
		$this->templates = array();
		$this->default_template = true;
		$this->selected_template = '';
	}

	protected function readCreationTemplates() {
		global $ldapserver;

		if ($this->templates) return;
		if (DEBUG_ENABLED)
			debug_log('Entered',1,__FILE__,__LINE__,__METHOD__,1);

		$this->templates = array();

		# read the available templates
		$template_xml = new Templates($ldapserver->server_id);
		$all_templates = $template_xml->getCreationTemplates();
		if (!$all_templates) $all_templates = array();

		foreach ($all_templates as $template_name => $template_attrs) {
			# don't select hidden templates
			if (isset($template_attrs['visible']) && (! $template_attrs['visible'])) {
				if (DEBUG_ENABLED)
					debug_log('The template %s is not visible.',1,__FILE__,__LINE__,__METHOD__,1,$template_name);
				continue;
			}
			# don't select invalid templates
			if (isset($template_attrs['invalid']) && $template_attrs['invalid']) {
				if (DEBUG_ENABLED)
					debug_log('The template %s is invalid [%s].',1,__FILE__,__LINE__,__METHOD__,1,
						$template_name,isset($template_attrs['invalid_reason']) ? $template_attrs['invalid_reason'] : '');
				continue;
			}
			# finally add the template to the list
			if (DEBUG_ENABLED)
				debug_log('The template %s is available for the entry.',1,__FILE__,__LINE__,__METHOD__,1,
					$template_name);
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
		return $this->default_template;
	}

	public function getAttributes() {
		global $ldapserver;

		# we can use a static variable if there is only one instance of this class
		static $attrs = null;
		static $tmpl = '';

		if (DEBUG_ENABLED)
			debug_log('Entered',1,__FILE__,__LINE__,__METHOD__,1);

		if (!$this->selected_template) {
			return parent::getAttributes();
		} elseif (!$attrs || ($this->selected_template != $tmpl)) {
			$attrs = array();
			$tmpl = $this->selected_template;

			# The selected template
			$selected_tmpl = isset($this->templates[$this->selected_template])
				? $this->templates[$this->selected_template]
				: array();

			# The objectclasses of the entry to create
		 	if (isset($selected_tmpl['objectclass'])) {
				$ocs = $selected_tmpl['objectclass'];
				if (is_string($ocs) && (strlen($ocs) > 0)) $ocs = array($ocs);
				elseif (!$ocs) $ocs = array();

				foreach ($ocs as $oc) $this->addObjectClass($oc);
			}

			$template_attrs = isset($selected_tmpl['empty_attrs'])
				? $selected_tmpl['empty_attrs']
				: array();
			masort($template_attrs,'page,order',1);

			$attributefactoryclass = $_SESSION['plaConfig']->GetValue('appearance','attribute_factory');
			eval('$attribute_factory = new '.$attributefactoryclass.'();');

			if ($this->objectClasses) {
				$attribute = $attribute_factory->newAttribute('objectClass',$this->objectClasses);
				$attribute->setEntry($this);
				$attribute->setRequired();
				$attribute->hide();
				$attrs[] = $attribute;
			}

			# Template attributes
			foreach ($template_attrs as $attr => $params) {
				if ($attr == 'objectClass') continue;
				if (! is_array($params)) continue;
				$vals = array();

				if (isset($params['value'])) {
					if (! is_array($params['value']))
						$params['value'] = array($params['value']);
					$arr1 = array();
					foreach ($params['value'] as $id_parval => $parval) {
						$arr2 = Templates::EvaluateDefault($ldapserver,$parval,
							$this->getContainer(),null,null);
						if (is_array($arr2)) $arr1 = array_merge($arr1,$arr2);
						else $arr1[$id_parval] = $arr2;
					}
					$params['value'] = $arr1;
					foreach ($arr1 as $default_value) {
						$vals[] = $default_value;
					}
				}

				if (isset($params['option'])
					|| ( isset($params['type']) && (($params['type'] == 'select') || ($params['type'] == 'multiselect')) )) {

					if (! isset($params['option'])) $params['option'] = array();
					elseif (! is_array($params['option'])) $params['option'] = array($params['option']);
					$arr1 = array();
					foreach ($params['option'] as $id_parval => $parval) {
						$arr2 = Templates::EvaluateDefault($ldapserver,$parval,
							$this->getContainer(),null,null);

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

				$attribute->setEntry($this);

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
							// page, post, spacer, onchange
							$attribute->setProperty($param_name, $param_value);
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

				if (isset($this->mustattrs[$attr])) {
					$attribute->setRequired();
				}

				$attrs[] = $attribute;
			}

			// hide attributes not in template
			foreach ($this->mustattrs as $attr_name => $objectclasses) {
				if (isset($template_attrs[$attr_name])) continue;
				if ($attr_name == 'objectClass') continue;
				$vals = array();

				$attribute = $attribute_factory->newAttribute($attr_name,$vals);
				$attribute->setEntry($this);
				$attribute->setReadOnly();
				$attribute->setRequired();
				$attribute->hide(); // should not be hidden because it is required
				$attrs[] = $attribute;
			}
			foreach ($this->mayattrs as $attr_name => $objectclasses) {
				if (isset($template_attrs[$attr_name])) continue;
				if ($attr_name == 'objectClass') continue;
				$vals = array();

				$attribute = $attribute_factory->newAttribute($attr_name,$vals);
				$attribute->setEntry($this);
				$attribute->setReadOnly();
				$attribute->hide();
				$attrs[] = $attribute;
			}
		}

		return $attrs;
	}

	public function &getTemplates() {
		$this->readCreationTemplates();
		return $this->templates;
	}

	public function getTemplatesCount() {
		$this->readCreationTemplates();
		return count($this->templates);
	}

	public function setSelectedTemplateName($name) {
		$this->readCreationTemplates();
		$this->setLeaf(false);
		if (!$name || isset($this->templates[$name])) {
			$this->selected_template = $name;
			if (isset($this->templates[$name]['leaf']) && $this->templates[$name]['leaf'])
				$this->setLeaf(true);
			if (isset($this->templates[$name]['rdn']) && $this->templates[$name]['rdn'])
				$this->setRdnAttributeName($this->templates[$name]['rdn']);
			if (isset($this->templates[$name]['handler']) && $this->templates[$name]['handler'])
				$this->setProperty('handler',$this->templates[$name]['handler']);
			if (isset($this->templates[$name]['action']) && $this->templates[$name]['action'])
				$this->setProperty('action',$this->templates[$name]['action']);
			if (isset($this->templates[$name]['description']) && $this->templates[$name]['description'])
				$this->setProperty('description',$this->templates[$name]['description']);
			if (isset($this->templates[$name]['destinationcontainer']))
				$this->setContainer($this->templates[$name]['destinationcontainer']);
		}
	}

	public function getSelectedTemplateName() {
		return $this->selected_template;
	}

	public function setContainer($dn) {
		parent::setContainer($dn);

		$this->readCreationTemplates();
		foreach ($this->templates as $template_name => $template_attrs) {
			# check the template filter
			if (isset($template_attrs['regexp'])) {
				if (! @preg_match('/'.$template_attrs['regexp'].'/i',$dn)) {
					if (DEBUG_ENABLED)
						debug_log('The container %s doesn\'t match the template %s regexp',1,__FILE__,__LINE__,__METHOD__,
							$dn,$template_name);
					$this->templates[$template_name]['invalid'] = true;
				}
			}
		}
	}
}
?>
