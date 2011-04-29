<?php
/**
 * Allows to create new attributes
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * AttributeFactory Class
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
class AttributeFactory {
	public function newAttribute($name,$values,$server_id,$source=null) {
		global $app;

		# Check to see if the value is auto generated, our attribute type is dependant on the function called.
		if (isset($values['post']) && ! is_array($values['post'])) {
			if (preg_match('/^=php\.(\w+)\((.*)\)$/',$values['post'],$matches)) {
				switch ($matches[1]) {
					case 'Join':
					case 'PasswordEncrypt':
						break;

					default:
						if (! $_SESSION[APPCONFIG]->getValue('appearance','hide_template_warning'))
							system_message(array(
								'title'=>sprintf('%s [<i>%s</i>]',_('Unknown template [post] function'),$matches[1]),
								'body'=>sprintf('%s <small>[%s]</small>',_('The template function is not known and will be ignored.'),$values['post']),
								'type'=>'warn'));

						unset($values['post']);
				}
			}
		}

		# Check our helper functions exists
		if (isset($values['helper']['value']) && ! is_array($values['helper']['value']))
			if (preg_match('/^=php\.(\w+)\((.*)\)$/',$values['helper']['value'],$matches))
				if (! in_array($matches[1],array('GetNextNumber','PasswordEncryptionTypes'))) {
					if (! $_SESSION[APPCONFIG]->getValue('appearance','hide_template_warning'))
						system_message(array(
							'title'=>sprintf('%s [<i>%s</i>]',_('Unknown template helper function'),$matches[1]),
							'body'=>sprintf('%s <small>[%s]</small>',_('The template helper function is not known and will be ignored.'),$values['helper']['value']),
							'type'=>'warn'));

					unset($values['helper']['value']);
				}

		# Check to see if the value is auto generated, our attribute type is dependant on the function called.
		if (isset($values['value']) && ! is_array($values['value'])) {
			if (preg_match('/^=php\.(\w+)\((.*)\)$/',$values['value'],$matches)) {
				switch ($matches[1]) {
					case 'MultiList':
						if (! isset($values['type']))
							$values['type'] = 'multiselect';

					case 'PickList':
						return $this->newSelectionAttribute($name,$values,$server_id,$source);

					case 'RandomPassword':
						return $this->newRandomPasswordAttribute($name,$values,$server_id,$source);

					# Fall through and determine the attribute using other methods.
					case 'GetNextNumber':
					case 'Function' :
						break;

					default:
						if (! $_SESSION[APPCONFIG]->getValue('appearance','hide_template_warning'))
							system_message(array(
								'title'=>sprintf('%s [<i>%s</i>]',_('Unknown template function'),$matches[1]),
								'body'=>sprintf('%s <small>[%s]</small>',_('The template function is not known and will be ignored.'),$values['value']),
								'type'=>'warn'));

						unset($values['value']);
				}
			}
		}

		if (isset($values['type']))
			switch ($values['type']) {
				case 'password':
					if (! strcasecmp($name,'sambaLMPassword') || ! strcasecmp($name,'sambaNTPassword'))
						return $this->newSambaPasswordAttribute($name,$values,$server_id,$source);
					else
						return $this->newPasswordAttribute($name,$values,$server_id,$source);

				case 'multiselect':
				case 'select':
					return $this->newSelectionAttribute($name,$values,$server_id,$source);

				case 'textarea':
					return $this->newMultiLineAttribute($name,$values,$server_id,$source);
			}

		if (! strcasecmp($name,'objectClass')) {
			return $this->newObjectClassAttribute($name,$values,$server_id,$source);

		} elseif ($app['server']->isJpegPhoto($name) || in_array($name,$app['server']->getValue('server','jpeg_attributes'))) {
			return $this->newJpegAttribute($name,$values,$server_id,$source);

		} elseif ($app['server']->isAttrBinary($name)) {
			return $this->newBinaryAttribute($name,$values,$server_id,$source);

		} elseif (! strcasecmp($name,'userPassword')) {
			return $this->newPasswordAttribute($name,$values,$server_id,$source);

		} elseif (! strcasecmp($name,'sambaLMPassword') || ! strcasecmp($name,'sambaNTPassword')) {
			return $this->newSambaPasswordAttribute($name,$values,$server_id,$source);

		} elseif (in_array(strtolower($name),array_keys(array_change_key_case($_SESSION[APPCONFIG]->getValue('appearance','date_attrs'))))) {
			return $this->newDateAttribute($name,$values,$server_id,$source);

		} elseif (in_array(strtolower($name),array('shadowlastchange','shadowmin','shadowmax','shadowexpire','shadowwarning','shadowinactive'))) {
			return $this->newShadowAttribute($name,$values,$server_id,$source);

		} elseif ($app['server']->isAttrBoolean($name)) {
			$attribute = $this->newSelectionAttribute($name,$values,$server_id,$source);
			$attribute->addOption('TRUE',_('true'));
			$attribute->addOption('FALSE',_('false'));
			return $attribute;

		} elseif ($app['server']->isDNAttr($name)) {
			return $this->newDnAttribute($name,$values,$server_id,$source);

		} elseif ($app['server']->isMultiLineAttr($name)) {
			return $this->newMultiLineAttribute($name,$values,$server_id,$source);

		} elseif (! strcasecmp($name,'gidNumber')) {
			return $this->newGidAttribute($name,$values,$server_id,$source);

		} else {
			return new Attribute($name,$values,$server_id,$source);
		}
	}

	private function newJpegAttribute($name,$values,$server_id,$source) {
		return new JpegAttribute($name,$values,$server_id,$source);
	}

	private function newBinaryAttribute($name,$values,$server_id,$source) {
		return new BinaryAttribute($name,$values,$server_id,$source);
	}

	private function newPasswordAttribute($name,$values,$server_id,$source) {
		return new PasswordAttribute($name,$values,$server_id,$source);
	}

	private function newSambaPasswordAttribute($name,$values,$server_id,$source) {
		return new SambaPasswordAttribute($name,$values,$server_id,$source);
	}

	private function newRandomPasswordAttribute($name,$values,$server_id,$source) {
		return new RandomPasswordAttribute($name,$values,$server_id,$source);
	}

	private function newShadowAttribute($name,$values,$server_id,$source) {
		return new ShadowAttribute($name,$values,$server_id,$source);
	}

	private function newSelectionAttribute($name,$values,$server_id,$source) {
		return new SelectionAttribute($name,$values,$server_id,$source);
	}

	private function newMultiLineAttribute($name,$values,$server_id,$source) {
		return new MultiLineAttribute($name,$values,$server_id,$source);
	}

	private function newDateAttribute($name,$values,$server_id,$source) {
		return new DateAttribute($name,$values,$server_id,$source);
	}

	private function newObjectClassAttribute($name,$values,$server_id,$source) {
		return new ObjectClassAttribute($name,$values,$server_id,$source);
	}

	private function newDnAttribute($name,$values,$server_id,$source) {
		return new DnAttribute($name,$values,$server_id,$source);
	}

	private function newGidAttribute($name,$values,$server_id,$source) {
		return new GidAttribute($name,$values,$server_id,$source);
	}
}
?>
