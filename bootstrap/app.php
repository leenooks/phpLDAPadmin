<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\{ApplicationSession,CheckUpdate,SwapinAuthUser};

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__.'/../routes/web.php',
		api: __DIR__.'/../routes/api.php',
		commands: __DIR__.'/../routes/console.php',
		health: '/up',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware->appendToGroup('web', [
			ApplicationSession::class,
			SwapinAuthUser::class,
			CheckUpdate::class,
		]);

		$middleware->prependToGroup('api', [
			EncryptCookies::class,
			ApplicationSession::class,
			SwapinAuthUser::class,
		]);

		$middleware->trustProxies(at: [
			'10.0.0.0/8',
			'172.16.0.0/12',
			'192.168.0.0/12',
		]);
	})
	->withExceptions(function (Exceptions $exceptions) {
		//
	})->create();