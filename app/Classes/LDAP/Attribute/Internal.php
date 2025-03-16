<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;

/**
 * Represents an attribute whose values are internal
 */
abstract class Internal extends Attribute
{
	protected(set) bool $is_internal = TRUE;

	public function __get(string $key): mixed
	{
		return match ($key) {
			// Internal items shouldnt have language tags, so our values should only have 1 key
			'values'=>collect($this->values->first()),
			default => parent::__get($key),
		};
	}

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		// @note Internal attributes cannot be edited
		return view('components.attribute.internal')
			->with('o',$this);
	}
}