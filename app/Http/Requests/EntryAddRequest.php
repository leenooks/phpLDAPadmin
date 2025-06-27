<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

use App\Rules\{DNExists,HasStructuralObjectClass};

class EntryAddRequest extends FormRequest
{
	private const LOGKEY = 'EAR';

	/**
	 * Get the error messages for the defined validation rules.
	 *
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'_rdn' => __('RDN is required.'),
			'_rdn_value' => __('RDN value is required.'),
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
			->merge([
				'_key' => [
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
				'_rdn' => 'required_if:_step,2|string|min:1',
				'_rdn_value' => 'required_if:_step,2|string|min:1',
				'_step' => 'int|min:1|max:2',
				'objectclass'=>[
					'required',
					'array',
					'min:1',
					'max:1',
				],
				'objectclass._null_' => [
					function (string $attribute,mixed $value,\Closure $fail) {
						$oc = collect($value)->dot()->filter();

						// If this is step 1 and there is no objectclass, and no template, then fail
						if ((! $oc->count())
							&& (request()->post('_step') == 1)
							&& (! request()->post('template')))
						{
							$fail(__('Select an objectclass or a template'));
						}

						// Cant have both an objectclass and a template
						if (request()->post('template') && $oc->count())
							$fail(__('You cannot select a template and an objectclass'));
					},
					'array',
					'min:1',
					new HasStructuralObjectClass,
				],
				'template' => [
					function (string $attribute,mixed $value,\Closure $fail) {
						$oc = collect(request()->post('objectclass'))->dot()->filter();

						// If this is step 1 and there is no objectclass, and no template, then fail
						if ((! collect($value)->filter()->count())
							&& (request()->post('_step') == 1)
							&& (! $oc->count()))
						{
							$fail(__('Select an objectclass or a template'));
						}

						// Cant have both an objectclass and a template
						if ($oc->count() && strlen($value))
							$fail(__('You cannot select a template and an objectclass'));
					},
				],
				'_auto_value' => 'nullable|array|min:1',
				'_auto_value.*' => [
					'nullable',
					function (string $attribute,mixed $value,\Closure $fail) {
						$attr = preg_replace('/^_auto_value\./','',$attribute);

						// If the value has been overritten, then our auto_value is invalid
						if (! collect(request()->get($attr))->dot()->contains($value))
							return;

						$cache = Cache::get($attr.':'.Session::id());
						Log::debug(sprintf('%s:Autovalue for Attribute [%s] in Session [%s] Retrieved [%d](%d)',self::LOGKEY,$attr,Session::id(),$cache,$value));

						if ($cache !== (int)$value)
							$fail(__('Lock expired, please re-submit.'));
					}
				]
			])
			->toArray();
	}
}