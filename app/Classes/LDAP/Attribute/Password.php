<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;

/**
 * Represents an attribute whose values are passwords
 */
final class Password extends Attribute
{
	public function render(bool $edit=FALSE): View
	{
		return view('components.attribute.password')
			->with('edit',$edit)
			->with('o',$this);
	}
}