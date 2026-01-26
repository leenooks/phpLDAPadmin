<?php

namespace App\Classes\LDAP\Attribute\Samba;

use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;
use App\Interfaces\MD5Updates as MD5Interface;
use App\Traits\{MD5Updates,SambaPassword};

/**
 * Represents an attribute whose values are Samba NT Passwords
 */
final class NTPassword extends Attribute implements MD5Interface
{
	use MD5Updates,SambaPassword;

	protected(set) string $encoding = 'NT';

	protected static function helpers(): Collection
	{
		return collect(['NT'=>Attribute\Password\Samba\NT::class]);
	}

	public static function hash(string $password): ?Attribute\Password\Base
	{
		return new Attribute\Password\Samba\NT;
	}
}