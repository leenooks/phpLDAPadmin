<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

use App\Classes\LDAP\Attribute;

/**
 * Represents an attribute whose values are schema related
 */
abstract class Schema extends Attribute
{
	protected bool $internal = TRUE;
	protected(set) bool $no_attr_tags = TRUE;

	protected static function _get(string $filename,string $string,string $key): ?string
	{
		$array = Cache::remember($filename,86400,function() use ($filename) {
			try {
				$f = fopen($filename,'r');

			} catch (\Exception $e) {
				return NULL;
			}

			$result = collect();

			while (! feof($f)) {
				$line = trim(fgets($f));

				if ((! $line) || preg_match('/^#/',$line))
					continue;

				$fields = explode(':',$line);

				$result->put($x=Arr::get($fields,0),[
					'title'=>Arr::get($fields,1,$x),
					'ref'=>Arr::get($fields,2),
					'desc'=>Arr::get($fields,3,__('No description available, can you help with one?')),
				]);
			}

			fclose($f);

			return $result;
		});

		return Arr::get(($array ? $array->get($string) : []),
			$key,
			__('No description available, can you help with one?'));
	}
}