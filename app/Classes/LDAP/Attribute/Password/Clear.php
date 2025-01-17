<?php

namespace App\Classes\LDAP\Attribute\Password;

final class Clear extends Base
{
	public const key = 'Clear';

	public function compare(string $source,string $compare): bool
	{
		return $source === $compare;
	}

	public function encode(string $password): string
	{
		return $password;
	}
}