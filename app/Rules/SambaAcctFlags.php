<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use App\Traits\SetState;
use App\Classes\LDAP\Attribute\Samba\AcctFlags;

class SambaAcctFlags implements ValidationRule
{
	use SetState;

	public function validate(string $attribute,mixed $value,Closure $fail): void
	{
		if (($x=collect($value)->keys()->diff(collect(AcctFlags::values)->keys()))->count())
			$fail(__('The following are invalid items: '.$x->join(',')));
	}
}