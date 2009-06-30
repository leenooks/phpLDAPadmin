<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/AttributeFactory.php,v 1.2 2007/12/15 07:50:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author Xavier Bruyet
 *
 * Allows to create new attributes
 */
class AttributeFactory {
	public function newAttribute($name,$values) {
		global $ldapserver;

		if (! strcasecmp($name,'objectClass')) {
			return $this->newObjectClassAttribute($name,$values);

		} elseif ($ldapserver->isJpegPhoto($name)) {
			return $this->newJpegAttribute($name,$values);

		} else if ($ldapserver->isAttrBinary($name)) {
			return $this->newBinaryAttribute($name,$values);

		} else if (! strcasecmp($name,'userPassword')) {
			return $this->newPasswordAttribute($name,$values);

		} else if (! strcasecmp($name,'sambaLMPassword') || ! strcasecmp($name,'sambaNTPassword')) {
			return $this->newSambaPasswordAttribute($name,$values);

		} elseif (in_array_ignore_case($name,array_keys($_SESSION['plaConfig']->GetValue('appearance','date_attrs')))) {
			return $this->newDateAttribute($name,$values);

		} elseif (in_array(strtolower($name),array('shadowlastchange','shadowmin',
				'shadowmax','shadowexpire','shadowwarning','shadowinactive'))) {
			return $this->newShadowAttribute($name,$values);

		} elseif ($ldapserver->isAttrBoolean($name)) {
			$attribute = $this->newSelectionAttribute($name,$values);
			$attribute->addOption('TRUE',_('true'));
			$attribute->addOption('FALSE',_('false'));
			return $attribute;

		} elseif ($ldapserver->isDNAttr($name)) {
			return $this->newDnAttribute($name,$values);

		} elseif ($ldapserver->isMultiLineAttr($name)) {
			return $this->newMultiLineAttribute($name,$values);

		} elseif (! strcasecmp($name,'gidNumber')) {
			return $this->newGidAttribute($name,$values);

		} else {
			return new Attribute($name,$values);
		}
	}

	public function newJpegAttribute($name,$values) {
		return new JpegAttribute($name,$values);
	}

	public function newBinaryAttribute($name,$values) {
		return new BinaryAttribute($name,$values);
	}

	public function newPasswordAttribute($name,$values) {
		return new PasswordAttribute($name,$values);
	}

	public function newSambaPasswordAttribute($name,$values) {
		return new SambaPasswordAttribute($name,$values);
	}

	public function newRandomPasswordAttribute($name,$values) {
		return new RandomPasswordAttribute($name,$values);
	}

	public function newShadowAttribute($name,$values) {
		return new ShadowAttribute($name,$values);
	}

	public function newSelectionAttribute($name,$values) {
		return new SelectionAttribute($name,$values);
	}

	public function newMultiLineAttribute($name,$values) {
		return new MultiLineAttribute($name,$values);
	}

	public function newDateAttribute($name,$values) {
		return new DateAttribute($name,$values);
	}

	public function newObjectClassAttribute($name,$values) {
		return new ObjectClassAttribute($name,$values);
	}

	public function newDnAttribute($name,$values) {
		return new DnAttribute($name,$values);
	}

	public function newGidAttribute($name,$values) {
		return new GidAttribute($name,$values);
	}
}
?>
