<?php

namespace App\Classes\LDAP\Attribute\Internal;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Internal;
use App\Classes\Template;

/**
 * Represents an attribute whose values are timestamps
 */
final class Timestamp extends Internal
{
	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.internal.timestamp'));
	}
}