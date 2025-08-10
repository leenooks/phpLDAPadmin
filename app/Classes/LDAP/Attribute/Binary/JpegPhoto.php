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
	protected(set) bool $base64_values = TRUE;

	public function render(string $attrtag,int $index,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return view('components.attribute.value.binary.jpegphoto')
			->with('o',$this)
			->with('dotkey',$dotkey=$this->dotkey($attrtag,$index))
			->with('value',$this->values->dot()->get($dotkey))
			->with('edit',$edit)
			->with('editable',$editable)
			->with('new',$new)
			->with('attrtag',$attrtag)
			->with('index',$index)
			->with('updated',$updated)
			->with('template',$template)
			->with('f',new \finfo);
	}
}