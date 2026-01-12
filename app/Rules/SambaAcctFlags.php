<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use App\Traits\SetState;

class SambaAcctFlags implements ValidationRule
{
	use SetState;

	public function validate(string $attribute,mixed $value,Closure $fail): void
	{
		if (($x=collect($value)->keys()->diff(collect(\App\Classes\LDAP\Attribute\SambaAcctFlags::values)->keys()))->count())
			$fail(__('The following are invalid items: '.$x->join(',')));
	}
}