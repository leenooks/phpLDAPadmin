<?php

namespace App\Classes\LDAP\Attribute\Password\Samba;

use phpseclib3\Crypt\DES;

use App\Classes\LDAP\Attribute\Password\Base;

final class LM extends Base
{
	public const string key = 'LM';

	public const string magic = 'KGS!@#$%';

	public function encode(string $password): string
	{
		$password = str_pad(substr(strtoupper($password),0,14),14,"\0");

		$des = new DES('ecb');
		$des->disablePadding();

		// Part 1
		$des->setKey($this->strToKey(substr($password,0,7)));
		$hash1 = $des->encrypt(self::magic);

		// Part 2
		$des->setKey($this->strToKey(substr($password,7,7)));
		$hash2 = $des->encrypt(self::magic);

		return bin2hex($hash1.$hash2);
	}

	private function strToKey(string $part): string
	{
		$tmp = [];

		// Ensure we have 7 bytes even if padding was missing
		for ($i = 0; $i < 7; $i++)
			$tmp[] = isset($part[$i]) ? ord($part[$i]) : 0;

		$key = [];
		// Standard LM bit-shifting logic to insert parity bits
		$key[] = $tmp[0] & 254;
		$key[] = (($tmp[0] << 7) & 255) | ($tmp[1] >> 1);
		$key[] = (($tmp[1] << 6) & 255) | ($tmp[2] >> 2);
		$key[] = (($tmp[2] << 5) & 255) | ($tmp[3] >> 3);
		$key[] = (($tmp[3] << 4) & 255) | ($tmp[4] >> 4);
		$key[] = (($tmp[4] << 3) & 255) | ($tmp[5] >> 5);
		$key[] = (($tmp[5] << 2) & 255) | ($tmp[6] >> 6);
		$key[] = ($tmp[6] << 1) & 255;

		$keyStr = '';
		foreach ($key as $k)
			$keyStr .= chr($k);

		return $keyStr;
	}
}