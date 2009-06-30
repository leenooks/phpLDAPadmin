<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/ShadowAttribute.php,v 1.2 2007/12/15 07:50:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author Xavier Bruyet
 *
 * Represents an shadow attribute
 */
class ShadowAttribute extends Attribute {
	public $shadow_before_today_attrs = array('shadowLastChange','shadowMin');
	public $shadow_after_today_attrs = array('shadowMax','shadowExpire','shadowWarning','shadowInactive');
}
?>
