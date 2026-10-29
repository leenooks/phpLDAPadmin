<?php

namespace App\Ldap;

use Illuminate\Support\Facades\Crypt;
use LdapRecord\Configuration\DomainConfiguration as DomainConfigurationBase;

/**
 * Extends the vendor DomainConfiguration to transparently decrypt the password
 * before it is used by LdapRecord. The encrypted ciphertext lives in config/ldap.php
 * (or the .env) and is decrypted here at read-time with Laravel's APP_KEY.
 */
class DomainConfiguration extends DomainConfigurationBase
{
	/**
	 * {@inheritdoc}
	 *
	 * Intercepts reads of the 'password' key to decrypt the stored ciphertext.
	 */
	public function get(string $key): mixed
	{
		$value = parent::get($key);

		// See if we have an encrypted password
		return (($key === 'password') && is_string($value) && strlen($value) && config('ldap.password_enc'))
			? Crypt::decryptString($value)
			: $value;
	}
}