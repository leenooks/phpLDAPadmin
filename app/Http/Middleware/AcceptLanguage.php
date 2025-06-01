<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AcceptLanguage
{
	public function handle(Request $request,Closure $next): mixed
	{
		if ($locale=$this->parseHttpLocale($request)) {
			Log::debug(sprintf('Accept Language changed from [%s] to [%s] from Browser (%s)',app()->getLocale(),$locale,$request->header('Accept-Language')));

			app()->setLocale($locale);
		}

		return $next($request);
	}

	private function parseHttpLocale(Request $request): string
	{
		$list = explode(',',$request->server('HTTP_ACCEPT_LANGUAGE',''));

		$locales = Collection::make($list)
			->map(function ($locale) {
				$parts = explode(';',$locale);
				$mapping = [];

				$mapping['locale'] = trim($parts[0]);
				$mapping['factor'] = isset($parts[1])
					? Arr::get(explode('=',$parts[1]),1)
					: 1;

				return $mapping;
			})
			->sortByDesc(fn($locale)=>$locale['factor']);

		return Arr::get($locales->first(),'locale');
	}
}