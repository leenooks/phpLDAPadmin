<?php

namespace App\Classes\LDAP\Attribute\Internal;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Internal;
use App\Ldap\Entry;

/**
 * Represents an attribute whose values are timestamps
 */
final class Timestamp extends Internal
{
	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,string $langtag=Entry::TAG_NOTAG,bool $updated=FALSE): View
	{
		// @note Internal attributes cannot be edited
		return view('components.attribute.internal.timestamp')
			->with('o',$this);
	}
}