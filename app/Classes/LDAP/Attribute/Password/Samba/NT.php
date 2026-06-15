<?php

namespace App\Classes\LDAP\Attribute\Password\Samba;

use App\Classes\LDAP\Attribute\Password\Base;
use App\Exceptions\InvalidConfiguration;

final class NT extends Base
{
	public const string key = 'NT';

	public function encode(string $password): string
	{
		// Encode the password to UTF-16 Little Endian
		$utf16_password = iconv('UTF-8','UTF-16LE',$password);

		// Generate the MD4 hash
		// PHP's hash() function supports md4, but may not be available on all systems by default
		if (in_array('md4', hash_algos())) {
			$nt_hash = hash('md4',$utf16_password);

			return strtoupper($nt_hash); // Typically stored in uppercase hex format

		} else {
			\Log::error('MD4 algorithm not supported on this PHP installation');

			// Fallback or error handling if md4 is not available
			throw new InvalidConfiguration('MD4 algorithm not supported on this PHP installation');
		}
	}
}