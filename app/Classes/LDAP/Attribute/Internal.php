<?php

namespace App\Classes\LDAP\Attribute;

use App\Classes\LDAP\Attribute;

/**
 * Represents an attribute whose values are internal
 */
abstract class Internal extends Attribute
{
	protected bool $is_internal = TRUE;
}