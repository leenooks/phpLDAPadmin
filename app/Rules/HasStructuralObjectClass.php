<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasStructuralObjectClass implements ValidationRule
{
	// Required for artisan optimize
	public static function __set_state(array $array): self
	{
		return new self;
	}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute,mixed $value,Closure $fail): void
    {
		foreach ($value as $item)
			if ($item && config('server')->schema('objectclasses',$item)->isStructural())
				return;

		$fail('There isnt a Structural Objectclass.');
    }
}
