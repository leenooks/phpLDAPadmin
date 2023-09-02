<?php

namespace App\Classes\LDAP\Attribute\Internal;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Internal;

/**
 * Represents an attribute whose values are timestamps
 */
final class Timestamp extends Internal
{
	public function render(bool $edit=FALSE,bool $blank=FALSE): View
	{
		// @note Internal attributes cannot be edited
		return view('components.attribute.internal.timestamp')
			->with('o',$this);
	}
}