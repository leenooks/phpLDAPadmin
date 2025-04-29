<?php

namespace App\Classes\LDAP\Attribute\Schema;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Schema;
use App\Ldap\Entry;

/**
 * Represents a Generic Schema Attribute
 */
class Generic extends Schema
{
	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,string $langtag=Entry::TAG_NOTAG,bool $updated=FALSE): View
	{
		// @note Schema attributes cannot be edited
		return view('components.attribute.schema.generic')
			->with('o',$this);
	}
}