<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LdapRecord\Configuration\DomainConfiguration;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
		// Add a new option available to be set in the configuration:
		DomainConfiguration::extend('name', $default = null);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/themes/architect/views/','architect');
    }
}
