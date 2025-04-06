<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntryRequest extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		$r = request() ?: collect();

		return config('server')
			->schema('attributetypes')
			->intersectByKeys($r->all())
			->map(fn($item)=>$item->validation($r->get('objectclass',[])))
			->filter()
			->flatMap(fn($item)=>$item)
			->toArray();
	}
}