<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;

/**
 * Represents an attribute whose values are internal
 */
abstract class Internal extends Attribute
{
	protected bool $is_internal = TRUE;

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		// @note Internal attributes cannot be edited
		return view('components.attribute.internal')
			->with('o',$this);
	}
}