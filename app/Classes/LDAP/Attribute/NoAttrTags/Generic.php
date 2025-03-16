<?php

namespace App\Classes\LDAP\Attribute\NoAttrTags;

use App\Classes\LDAP\Attribute;

/**
 * Represents an Attribute that doesnt have Lang Tags
 */
class Generic extends Attribute
{
	protected(set) bool $no_attr_tags = TRUE;
}