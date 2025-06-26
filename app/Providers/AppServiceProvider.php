<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LdapRecord\Configuration\DomainConfiguration;
use LdapRecord\Laravel\LdapRecord;

use App\Ldap\LdapUserRepository;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		// Add a new option available to be set in the configuration:
		DomainConfiguration::extend('name', $default = null);

		// Use our LdapUserRepository to support multiple baseDN querying
		LdapRecord::locateUsersUsing(LdapUserRepository::class);
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		$this->loadViewsFrom(__DIR__.'/../../resources/themes/architect/views/','architect');
	}
}