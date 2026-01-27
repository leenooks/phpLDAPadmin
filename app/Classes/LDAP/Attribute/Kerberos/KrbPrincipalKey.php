<?php

namespace App\Classes\LDAP\Attribute\Kerberos;

use App\Classes\LDAP\Attribute;
use App\Interfaces\{MD5Update,NoAttrTag};
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values are passwords
 */
final class KrbPrincipalKey extends Attribute implements MD5Update,NoAttrTag
{
	use MD5Updates;

	public function render_item_old(string $dotkey): ?string
	{
		return parent::render_item_old($dotkey)
			? Attribute\Password::obfuscate
			: NULL;
	}

	public function render_item_new(string $dotkey): ?string
	{
		return parent::render_item_new($dotkey)
			? Attribute\Password::obfuscate
			: NULL;
	}
}