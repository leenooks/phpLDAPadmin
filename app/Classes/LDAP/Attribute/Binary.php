<?php

namespace App\Classes\LDAP\Attribute;

use App\Classes\LDAP\Attribute;
use App\Interfaces\{Base64Value,MD5Update};
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values are binary
 */
abstract class Binary extends Attribute implements Base64Value,MD5Update
{
	use MD5Updates;

	protected const CERTIFICATE_ENCODE_LENGTH = 76;

	public function __get(string $key): mixed
	{
		return match ($key) {
			'binarytags' => $this->values
				->keys()
				->filter(fn($item)=>$item==='binary'),

			default => parent::__get($key)
		};
	}

	public function render_item_old(string $dotkey): ?string
	{
		return base64_encode(parent::render_item_old($dotkey));
	}

	public function render_item_new(string $dotkey): ?string
	{
		return base64_encode(parent::render_item_new($dotkey));
	}
}