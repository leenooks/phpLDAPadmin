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

	protected(set) bool $base64_values = TRUE;

	public function render_item_old(string $dotkey): ?string
	{
		return base64_encode(parent::render_item_old($dotkey));
	}

	public function render_item_new(string $dotkey): ?string
	{
		return base64_encode(parent::render_item_new($dotkey));
	}
}