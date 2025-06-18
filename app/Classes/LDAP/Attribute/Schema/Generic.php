<?php

namespace App\Classes\LDAP\Attribute\Schema;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Schema;
use App\Classes\Template;

/**
 * Represents a Generic Schema Attribute
 */
class Generic extends Schema
{
	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		// @note Schema attributes cannot be edited
		return view('components.attribute.schema.generic')
			->with('o',$this);
	}
}