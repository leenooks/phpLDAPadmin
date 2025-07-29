<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;

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
			$key === 'title' ? $string : __('No description available, can you help with one?'));
	}

	public function render(string $attrtag,int $index,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		// @note Schema attributes cannot be edited
		return view('components.attribute.schema.generic')
			->with('o',$this)
			->with('dotkey',$dotkey=$this->dotkey($attrtag,$index))
			->with('value',$this->render_item_new($dotkey));
	}
}