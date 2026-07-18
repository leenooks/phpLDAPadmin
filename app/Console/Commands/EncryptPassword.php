<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

// @note If your APP_KEY changes, then you need to re-encrypt the password
class EncryptPassword extends Command
{
	protected $signature = 'encrypt:password {password? : Password to encrypt}';

	protected $description = 'This will treat passwords used in config/ldap.php as encrypted with the APP_KEY.';

	public function handle()
	{
		if (! config('app.key')) {
			$this->error('Please set the APP_KEY');

			return FALSE;
		}

		// @note If the configuration is cached, then env('LDAP_PASSWORD') will not return the password in the
		// environment variable or .env file
		if ((! env('LDAP_PASSWORD')) && (! $this->argument('password'))) {
			$this->error('No password supplied to encrypt');

			return FALSE;
		}

		if ($this->argument('password')) {
			$this->info('Encrypting LDAP password on command line:');
			$this->line(Crypt::encryptString($this->argument('password')));

		} else {
			$this->info('Encrypting LDAP password in LDAP_PASSWORD variable:');
			$this->line(Crypt::encryptString(env('LDAP_PASSWORD')));
		}
	}
}