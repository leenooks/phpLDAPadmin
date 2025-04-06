<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

use App\Rules\{DNExists,HasStructuralObjectClass};

class EntryAddRequest extends FormRequest
{
	/**
	 * Get the error messages for the defined validation rules.
	 *
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'rdn' => __('RDN is required.'),
			'rdn_value' => __('RDN value is required.'),
		];
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function rules(): array
	{
		if (request()->method() === 'GET')
			return [];

		$r = request() ?: collect();
		return config('server')
			->schema('attributetypes')
			->intersectByKeys($r->all())
			->map(fn($item)=>$item->validation($r->get('objectclass',[])))
			->filter()
			->flatMap(fn($item)=>$item)
			->merge([
				'key' => [
					'required',
					new DNExists,
					function (string $attribute,mixed $value,\Closure $fail) {
						$cmd = Crypt::decryptString($value);

						// Sometimes our key has a command, so we'll ignore it
						if (str_starts_with($cmd,'*') && ($x=strpos($cmd,'|')))
							$cmd = substr($cmd,1,$x-1);

						if ($cmd !== 'create') {
							$fail(sprintf('Invalid command: %s',$cmd));
						}
					},
				],
				'rdn' => 'required_if:step,2|string|min:1',
				'rdn_value' => 'required_if:step,2|string|min:1',
				'step' => 'int|min:1|max:2',
				'objectclass'=>[
					'required',
					'array',
					'min:1',
					'max:1',
				],
				'objectclass._null_'=>[
					'required',
					'array',
					'min:1',
					new HasStructuralObjectClass,
				]
			])
			->toArray();
	}
}