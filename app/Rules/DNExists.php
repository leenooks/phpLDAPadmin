<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Crypt;

class DNExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute,mixed $value,Closure $fail): void
    {
		$dn = Crypt::decryptString($value);

		// Sometimes our key has a command, so we'll ignore it
		if (str_starts_with($dn,'*') && ($x=strpos($dn,'|')))
			$dn = substr($dn,$x+1);

		if (! config('server')->fetch($dn))
			$fail(sprintf('The DN %s doesnt exist.',$dn));
    }
}
