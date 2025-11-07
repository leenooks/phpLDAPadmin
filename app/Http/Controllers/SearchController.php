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
			$ao = $so
				->schema('attributetypes',$attr);

			$result = collect();

			foreach ($so->baseDNs(FALSE) as $base) {
				$search = (new Entry)
					->in($base);

				$wildcard = FALSE;

				// If the attribute supports substring queries
				if ($ao->sub_str_rule) {
					if (Str::startsWith($value,'*')) {
						$wildcard = TRUE;
						$search = $search->whereEndsWith($attr,substr($value,1));

					} elseif ($wildcard=Str::endsWith($value,'*')) {
						$search = $search->whereStartsWith($attr,substr($value,0,-1));

					} else
						$search = $search->whereStartsWith($attr,$value);

				} else {
					$search = $search->where($attr,'*');
				}

				$result = $result->merge($search->get($attr));
			}

			return $result
				->map(fn($item)=>[
					'name'=>$item->getDN(),
					'value'=>Crypt::encryptString($item->getDN()),
					'category'=>sprintf('%s%s: [%s=%s%s]',
						__('Result'),
						$ao->sub_str_rule ? '' : sprintf('(%s)', __('No Sub Search')),
						$attr,
						$ao->sub_str_rule ? $value : '',
						($wildcard ? '' : '*'))
				]);

		// We are searching for an attribute
		} else {
			$attrs = $so
				->schema('attributetypes')
				->sortBy('names_lc')
				->filter(fn($item)=>Str::contains($item->names_lc,strtolower($request->term)));

			return $attrs
				->map(fn($item)=>[
					'name'=>$item->names->first(),
					'value'=>'',
					'category'=>__('Select attribute...')
				])
				->values();
		}
	}
}