<?php

namespace App\Classes\LDAP\Attribute;

use App\Classes\LDAP\Attribute;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values are binary
 */
abstract class Binary extends Attribute
{
	use MD5Updates;
}