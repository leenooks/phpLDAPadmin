<?php

namespace App\Classes\LDAP\Attribute\Binary;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Binary;
use App\Classes\Template;

/**
 * Represents an JpegPhoto Attribute
 */
final class JpegPhoto extends Binary
{
	protected(set) bool $render_tables = TRUE;

	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.binary.jpegphoto')
				->with('f',new \finfo),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}
}