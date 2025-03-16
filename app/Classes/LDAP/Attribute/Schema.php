<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

use App\Classes\LDAP\Attribute;

/**
 * Represents an attribute whose values are schema related
 */
abstract class Schema extends Attribute
{
	protected bool $internal = TRUE;

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

	public function __get(string $key): mixed
	{
		return match ($key) {
			// Schema items shouldnt have language tags, so our values should only have 1 key
			'values'=>collect($this->values->first()),
			default => parent::__get($key),
		};
	}

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		// @note Schema attributes cannot be edited
		return view('components.attribute.internal')
			->with('o',$this);
	}
}