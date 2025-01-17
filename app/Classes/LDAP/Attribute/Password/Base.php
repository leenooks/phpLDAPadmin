<?php

namespace App\Classes\LDAP\Attribute\Password;

abstract class Base
{
	abstract public function compare(string $source,string $compare): bool;
	abstract public function encode(string $password): string;

	public static function id(): string
	{
		return static::key;
	}
}