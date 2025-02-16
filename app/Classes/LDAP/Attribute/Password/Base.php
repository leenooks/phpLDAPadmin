<?php

namespace App\Classes\LDAP\Attribute\Password;

abstract class Base
{
	protected const subkey = '';

	abstract public function encode(string $password): string;

	public static function id(): string
	{
		return static::subkey ? strtoupper(static::subkey) : static::key;
	}

	/**
	 * Remove the hash {TEXT}xxxx from the password
	 *
	 * @param string $password
	 * @return string
	 */
	protected static function password(string $password): string
	{
		return preg_replace('/^{'.static::key.'}/','',$password);
	}

	public static function shortid(): string
	{
		return static::key;
	}

	/**
	 * When multiple passwords share the same ID, this determines which hash is responsible for the presented password
	 *
	 * @param string $password
	 * @return bool
	 */
	public static function subid(string $password): bool
	{
		return FALSE;
	}

	/**
	 * Compare our password to see if it is the same as that stored
	 *
	 * @param string $source Encoded source password
	 * @param string $compare Password entered by user
	 * @return bool
	 */
	public function compare(string $source,string $compare): bool
	{
		return $source === $this->encode($compare);
	}

	protected function salted_hash(string $password,string $algo,int $salt_size=8,?string $salt=NULL): string
	{
		if (is_null($salt))
			$salt = hex2bin(random_salt($salt_size));

		return base64_encode(hash($algo,$password.$salt,true).$salt);
	}

	protected function salted_salt(string $source): string
	{
		$hash = base64_decode(substr($source,strlen(static::key)+2));
		return substr($hash,strlen($hash)-static::salt/2);
	}
}