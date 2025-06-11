<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

use App\Ldap\Entry;

class SearchController extends Controller
{
	public function search(Request $request): Collection
	{
		$so = config('server');

		// We are searching for a value
		if (strpos($request->term,'=')) {
			list($attr,$value) = explode('=',$request->term,2);
			$value = trim($value);

			$result = collect();

			foreach ($so->baseDNs(FALSE) as $base) {
				$search = (new Entry)
					->in($base);

				$search = ($x=Str::startsWith($value,'*'))
					? $search->whereEndsWith($attr,substr($value,1))
					: $search->whereStartsWith($attr,$value);

				$result = $result->merge($search->get());
			}

			return $result
				->map(fn($item)=>[
					'name'=>$item->getDN(),
					'value'=>Crypt::encryptString($item->getDN()),
					'category'=>sprintf('%s: [%s=%s%s]',__('Result'),$attr,$value,($x ? '' : '*'))
				]);

		// We are searching for an attribute
		} else {
			$attrs = $so
				->schema('attributetypes')
				->sortBy('name')
				->filter(fn($item)=>Str::contains($item->name_lc,strtolower($request->term)));

			return $attrs
				->map(fn($item)=>[
					'name'=>$item->name,
					'value'=>'',
					'category'=>__('Select attribute...')
				])
				->values();
		}
	}
}