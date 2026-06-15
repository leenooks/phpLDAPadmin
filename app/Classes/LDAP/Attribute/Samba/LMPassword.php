<?php

namespace App\Classes\LDAP\Attribute\Samba;

use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;
use App\Interfaces\{ForceSingleValue,MD5Update,NoAttrTag};
use App\Traits\{MD5Updates,SambaPassword};

/**
 * Represents an attribute whose values are Samba LM Passwords
 */
final class LMPassword extends Attribute implements MD5Update,ForceSingleValue,NoAttrTag
{
	use MD5Updates,SambaPassword;

	public const encoding = 'LM';

	protected static function helpers(): Collection
	{
		return collect(['LM'=>Attribute\Password\Samba\LM::class]);
	}

	public static function hash(string $password): ?Attribute\Password\Base
	{
		return new Attribute\Password\Samba\LM;
	}
}