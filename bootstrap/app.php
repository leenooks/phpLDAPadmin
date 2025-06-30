<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\{AcceptLanguage,AllowAnonymous,CheckUpdate,SwapinAuthUser,ViewVariables};

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__.'/../routes/web.php',
		commands: __DIR__.'/../routes/console.php',
		health: '/up',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware->appendToGroup(
			group: 'web',
			middleware: [
				AcceptLanguage::class,
				AllowAnonymous::class,
				SwapinAuthUser::class,
				ViewVariables::class,
				CheckUpdate::class,
			]);

		$middleware->trustProxies(at: [
			'10.0.0.0/8',
			'127.0.0.0/8',
			'172.16.0.0/12',
			'192.168.0.0/12',
		]);
	})
	->withExceptions(function (Exceptions $exceptions) {
		//
	})->create();