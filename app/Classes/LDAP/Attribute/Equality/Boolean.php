<?php

namespace App\Classes\LDAP\Attribute\Equality;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;
use App\Interfaces\NoAttrTag;

/**
 * Represents an attribute whose values are timestamps
 */
final class Boolean extends Attribute implements NoAttrTag
{
	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		$dotkey = $this->dotkey($attrtag,$index);

		// @note Internal attributes cannot be edited
		if ($this->is_internal)
			return ($view ?: view('components.attribute.value.internal'))
				->with('o',$this)
				->with('value',$this->render_item_new($dotkey));

		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.equality.boolean'),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}
}