<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntryRequest extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function rules(): array
	{
		$r = request() ?: collect();
		$rk = array_keys($r->all());

		return config('server')
			->schema('attributetypes')
			->filter(fn($item)=>$item->names_lc->intersect($rk)->count())
			->transform(function($item) use ($rk) {
				// Set the attributetype name
				if (($x=$item->names_lc->intersect($rk))->count() === 1)
					$item->setName($x->pop());

				return $item;
			})
			->map(fn($item)=>$item->validation($r->get('objectclass',[])))
			->filter()
			->flatMap(fn($item)=>$item)
			->toArray();
	}
}