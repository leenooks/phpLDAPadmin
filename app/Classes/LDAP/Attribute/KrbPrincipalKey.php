<?php

namespace App\Classes\LDAP\Attribute;

use App\Classes\LDAP\Attribute;
use App\Interfaces\MD5Updates as MD5Interface;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values are passwords
 */
final class KrbPrincipalKey extends Attribute implements MD5Interface
{
	use MD5Updates;

	protected(set) bool $no_attr_tags = TRUE;

	public function render_item_old(string $dotkey): ?string
	{
		return parent::render_item_old($dotkey)
			? Password::obfuscate
			: NULL;
	}

	public function render_item_new(string $dotkey): ?string
	{
		return parent::render_item_new($dotkey)
			? Password::obfuscate
			: NULL;
	}
}