<?php

namespace App\Classes\LDAP\Attribute\Password;

final class Bcrypt extends Base
{
	public const key = 'BCRYPT';

	private const options = [
		'cost' => 8,
	];

	public function compare(string $source,string $compare): bool
	{
		return password_verify($compare,base64_decode($this->password($source)));
	}

	public function encode(string $password): string
	{
		return sprintf('{%s}%s',self::key,base64_encode(password_hash($password,PASSWORD_BCRYPT,self::options)));
	}
}