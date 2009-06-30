<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/DefaultEditingEntry.php,v 1.2.2.2 2007/12/29 08:25:24 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Represent a tree node
 */
class DefaultEditingEntry extends Entry {
	public function __construct($dn) {
		parent::__construct($dn);
	}

	public function getAttributes() {
		global $ldapserver;

		static $attrs = array();
		$dn = $this->getDn();

		if (DEBUG_ENABLED)
			debug_log('Entered with () for dn (%s)',1,__FILE__,__LINE__,__METHOD__,$dn);

		if (! isset($attrs[$dn])) {
			$attrs[$dn] = array();

			$attributefactoryclass = $_SESSION[APPCONFIG]->GetValue('appearance','attribute_factory');
			eval('$attribute_factory = new '.$attributefactoryclass.'();');

			$int_attrs_vals = $ldapserver->getDNSysAttrs($this->getDn());
			if (!$int_attrs_vals) $int_attrs_vals = array();
			elseif (!is_array($int_attrs_vals)) $int_attrs_vals = array($int_attrs_vals);

			$attrs_vals = $ldapserver->getDNAttrs($this->getDn(),false,$_SESSION[APPCONFIG]->GetValue('deref','view'));
			if (! $attrs_vals) $attrs_vals = array();
			elseif (! is_array($attrs_vals)) $attrs_vals = array($attrs_vals);

			$attrs_vals = array_merge($attrs_vals, $int_attrs_vals);
			uksort($attrs_vals,'sortAttrs'); # Sort these entries

			$objectclasses = null;

			foreach ($attrs_vals as $attr => $vals) {
				$attribute = $attribute_factory->newAttribute($attr,$vals);
				$attribute->setEntry($this);

				if (isset($int_attrs_vals[$attr])) {
					$attribute->setInternal();
					$attribute->setReadOnly();
				}

				if ($attr == 'objectClass') $objectclasses = $attribute->getValues();

				if ($this->isReadOnly() || $ldapserver->isAttrReadOnly($attr)) {
					$attribute->setReadOnly();
				}
				if ($ldapserver->isAttrHidden($attr)/* || ! strcasecmp($attr,'dn')*/) {
					$attribute->hide();
				}

				$attrs[$dn][] = $attribute;
			}

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
}

?>
