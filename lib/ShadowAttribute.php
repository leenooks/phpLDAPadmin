<?php
/**
 * Classes and functions for the template engine.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Represents a shadow date attribute
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
class ShadowAttribute extends Attribute {
	public $shadow_before_today_attrs = array('shadowLastChange','shadowMin');
	public $shadow_after_today_attrs = array('shadowMax','shadowExpire','shadowWarning','shadowInactive');
}
?>
