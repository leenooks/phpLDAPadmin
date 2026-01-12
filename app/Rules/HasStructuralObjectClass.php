<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use App\Traits\SetState;

class HasStructuralObjectClass implements ValidationRule
{
	use SetState;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute,mixed $value,Closure $fail): void
    {
		foreach (collect($value)->dot() as $item)
			if ($item && config('server')->schema('objectclasses',$item)->isStructural())
				return;

		if (collect($value)->dot()->filter()->count())
			$fail(__('There isnt a Structural Objectclass.'));
    }
}
