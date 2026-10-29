<?php

namespace App\Providers;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Connection;
use LdapRecord\Laravel\LdapRecord;

use App\Ldap\LdapUserRepository;
use App\Ldap\DomainConfiguration;

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

		// Bind Connection so that when LdapServiceProvider calls
		// app(Connection::class, ['config' => $array]), our subclass is used
		// and setConfiguration() instantiates App\Ldap\DomainConfiguration.
		$this->app->bind(Connection::class,function ($app,array $params) {
			$config = $params['config'] ?? [];

			// Build our DomainConfiguration subclass from the raw array
			if (! $config instanceof DomainConfiguration)
				$config = new DomainConfiguration($config);

			$connection = new Connection($config);

			return $connection;
		});
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		$this->loadViewsFrom(__DIR__.'/../../resources/themes/architect/views/','architect');

		// Enabling config setting of trusted proxies
		$this->app->afterResolving(TrustProxies::class, function(TrustProxies $middleware) {
			$middleware->at(config('app.trust_proxies',[]));
		});
	}
}