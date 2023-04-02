<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;

/**
 * Represents an ObjectClass Attribute
 */
final class ObjectClass extends Attribute
{
	public function __get(string $key): mixed
	{
		switch ($key) {
			case 'is_structural': return FALSE;	// @todo - need to determine which of the values is the structural objectclass value(s)
			default:
				return parent::__get($key);
		}
	}

	public function render(bool $edit=FALSE): View
	{
		return view('components.attribute.objectclass')
			->with('edit',$edit)
			->with('o',$this);
	}
}