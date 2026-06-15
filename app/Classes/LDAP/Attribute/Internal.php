<?php

namespace App\Classes\LDAP\Attribute;

use App\Classes\LDAP\Attribute;
use App\Interfaces\{InternalAttribute,NoAttrTag};

/**
 * Represents an attribute whose values are internal
 */
abstract class Internal extends Attribute implements NoAttrTag,InternalAttribute
{
}