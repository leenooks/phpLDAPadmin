<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntryRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return TRUE;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules()
	{
		return config('server')
			->schema('attributetypes')
			->intersectByKeys($this->request)
			->transform(function($item) { return $item->validation; })
			->filter()
			->flatMap(function($item) { return $item; })
			->toArray();
	}
}