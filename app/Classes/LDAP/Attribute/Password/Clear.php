<?php

namespace App\Classes\LDAP\Attribute\Password;

final class Clear extends Base
{
	public const key = '*clear*';

	public function encode(string $password): string
	{
		return $password;
	}
}