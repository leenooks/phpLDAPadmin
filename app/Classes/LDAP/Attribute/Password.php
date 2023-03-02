<?php

namespace App\Classes\LDAP\Attribute;

use App\Classes\LDAP\Attribute;

/**
 * Represents an attribute whose values are passwords
 */
class Password extends Attribute
{
	public function __toString(): string
	{
		return str_repeat('*',10)
			.sprintf('<br><span class="btn btn-sm btn-outline-dark"><i class="fas fa-user-check"></i> %s</span>',__('Check Password'));
	}
}