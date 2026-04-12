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
		$term = preg_replace('/[;\'"()]+.*$/','',$request->term);

		// We are searching for a value
		if (strpos($term,'=')) {
			list($attr,$value) = explode('=',$term,2);
			$ao = $so
				->schema('attributetypes',$attr);

			$value = trim($value);
			if (! Str::contains($value,'*'))
				$value .= '*';

			$result = collect();
			foreach ($so->baseDNs(FALSE) as $base) {
				$search = (new Entry)
					->in($base)
					->whereRaw($attr,'=',$value);

				$result = $result->merge($search->get($attr));
			}

			return $result
				->map(fn($item)=>[
					'name'=>$item->getDN(),
					'value'=>Crypt::encryptString($item->getDN()),
					'category'=>sprintf('%s%s: [%s=%s]',
						__('Result'),
						$ao->sub_str_rule ? '' : sprintf('(%s)', __('No Sub Search')),
						$attr,
						$value)
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