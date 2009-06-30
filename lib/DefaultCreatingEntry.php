<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/DefaultCreatingEntry.php,v 1.2.2.2 2007/12/29 08:24:10 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Represent a tree node
 */
class DefaultCreatingEntry extends Entry {
	protected $objectClasses;
	protected $mustattrs;
	protected $mayattrs;
	private $container;
	private $rdn;

	public function __construct() {
		parent::__construct('');

		$this->objectClasses = array();
		$this->mustattrs = array();
		$this->mayattrs = array();
		$this->container = '';
		$this->rdn = null;
	}

	public function addObjectClass($objectClass) {
		global $ldapserver;

		if (!$objectClass || in_array($objectClass,$this->objectClasses)) return;

		$this->objectClasses[] = $objectClass;

		/* add the required and optional attributes of the objectclass */

		$schema_oclass = $ldapserver->getSchemaObjectClass($objectClass);
		assert($schema_oclass);

		// get the required attributes
		$schema_oclasses = $ldapserver->SchemaObjectClasses();
		$schema_attrs = $schema_oclass->getMustAttrs($schema_oclasses);
		if (!$schema_attrs) $schema_attrs = array();
		elseif (!is_array($schema_attrs)) $schema_attrs = array($schema_attrs);

		// for each required attribute
		foreach ($schema_attrs as $schema_attr) {
			$attr_name = $schema_attr->getName();

			if (isset($this->mustattrs[$attr_name])) {
				$this->mustattrs[$attr_name][] = $objectClass;
				continue;
			}

			if (isset($this->mayattrs[$attr_name])) {
				unset($this->mayattrs[$attr_name]);
				$this->mustattrs[$attr_name] = array($objectClass);
				continue;
			}

			// get attribute aliases
			$aliases = ($schema_attr = $ldapserver->getSchemaAttribute($attr_name)) ? $schema_attr->aliases : null;
			if (!$aliases) $aliases = array();
			if (!is_array($aliases)) $aliases = array($aliases);

			// check if we doesn't already add the alias
			$found = false;
			foreach ($aliases as $alias) {
				if (isset($this->mustattrs[$alias])) {
					$this->mustattrs[$alias][] = $objectClass;
					$found = true;
					break;
				} elseif (isset($this->mayattrs[$alias])) {
					unset($this->mayattrs[$alias]);
					$this->mustattrs[$alias] = array($objectClass);
					$found = true;
					break;
				}
			}
			if ($found) continue;

			$this->mustattrs[$attr_name] = array($objectClass);
		}

		// get the optional attributes
		$schema_attrs = $schema_oclass->getMayAttrs($schema_oclasses);
		if (!$schema_attrs) $schema_attrs = array();
		elseif (!is_array($schema_attrs)) $schema_attrs = array($schema_attrs);

		// for each optional attribute
		foreach ($schema_attrs as $schema_attr) {
			$attr_name = $schema_attr->getName();

			if (isset($this->mustattrs[$attr_name])) {
				continue;
			}
			if (isset($this->mayattrs[$attr_name])) {
				$this->mayattrs[$attr_name][] = $objectClass;
				continue;
			}

			// get attribute aliases
			$aliases = ($schema_attr = $ldapserver->getSchemaAttribute($attr_name)) ? $schema_attr->aliases : null;
			if (!$aliases) $aliases = array();
			if (!is_array($aliases)) $aliases = array($aliases);

			// check if we doesn't already add the alias
			$found = false;
			foreach ($aliases as $alias) {
				if (isset($this->mustattrs[$alias])) {
					$found = true;
					break;
				} elseif (isset($this->mayattrs[$alias])) {
					$this->mayattrs[$alias][] = $objectClass;
					$found = true;
					break;
				}
			}
			if ($found) continue;

			$this->mayattrs[$attr_name] = array($objectClass);
		}
	}

	public function setContainer($dn) {
		$this->container = $dn;
	}

	public function getContainer() {
		return $this->container;
	}

	public function setRdnAttributeName($attribute_name) {
		$this->rdn = null;
		if (!$attribute_name) return;

		$attrs = $this->getAttributes();
		foreach ($attrs as $attr) {
			if ($attr->getName() == $attribute_name) {
				$this->rdn = $attr;
				return;
			}
		}
	}

	public function getRdnAttributeName() {
		$attr = $this->getRdnAttribute();
		if ($attr) return $attr->getName();
		else return '';
	}

	public function getRdnAttribute() {
		return $this->rdn;
	}

	public function getDn() {
		if (!$this->container || !$this->rdn) return '';
		$vals = $this->rdn->getValues();
		$val = ($vals && $vals[0]) ? $vals[0] : '';
		if (strlen($val) <= 0) return '';
		return $this->rdn->getName()."=$val,".$this->container;
	}

	public function getAttributes() {
		global $ldapserver;

		# we can use a static variable if there is only one instance of this class
		static $attrs = null;

		if (DEBUG_ENABLED)
			debug_log('Entered with () for dn (%s)',1,__FILE__,__LINE__,__METHOD__,$this->getDn());

		if (! $attrs) {
			$attrs = array();

			$attributefactoryclass = $_SESSION[APPCONFIG]->GetValue('appearance','attribute_factory');
			eval('$attribute_factory = new '.$attributefactoryclass.'();');

			if ($this->objectClasses) {
				$attribute = $attribute_factory->newAttribute('objectClass',$this->objectClasses);
				$attribute->setEntry($this);
				$attribute->setRequired();
				$attribute->hide();
				$attrs[] = $attribute;
			}

			foreach ($this->mustattrs as $attr_name => $objectclasses) {
				if ($attr_name == 'objectClass') continue;

				$attribute = $attribute_factory->newAttribute($attr_name,null);
				$attribute->setEntry($this);
				$attribute->setRequired();
				$attrs[] = $attribute;
			}

			foreach ($this->mayattrs as $attr_name => $objectclasses) {
				if ($attr_name == 'objectClass') continue;

				$attribute = $attribute_factory->newAttribute($attr_name,null);
				$attribute->setEntry($this);
				$attrs[] = $attribute;
			}

			usort($attrs,'attrcmp'); # Sort optional attributes
		}

		return $attrs;
	}
}

?>
