<?php

namespace App\Classes\LDAP\Attribute\Password;

final class Yescrypt extends Base
{
	public const key = 'YESCRYPT';
	protected const subkey = 'yescrypt';
	protected const identifier = '$y$';

	public static function subid(string $password): bool
	{
		return str_starts_with(self::password($password),self::identifier);
	}

	public function compare(string $source,string $compare): bool
	{
		return password_verify($compare,$this->password($source));
	}

	public function encode(string $password): string
	{
		if (defined('PASSWORD_YESCRYPT')) {
			return sprintf('{%s}%s',self::key,password_hash($password,PASSWORD_YESCRYPT));

		} else {
			\Log::error(__('It appears that YESCRYPT is not supported by your PHP engine'));

			return '';
		}
	}
}