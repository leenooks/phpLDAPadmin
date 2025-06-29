<?php

namespace App\Classes\LDAP\Attribute\Binary;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Binary;
use App\Classes\Template;
use App\Traits\MD5Updates;

/**
 * Represents an JpegPhoto Attribute
 */
final class JpegPhoto extends Binary
{
	use MD5Updates;

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return view('components.attribute.binary.jpegphoto')
			->with('o',$this)
			->with('edit',$edit)
			->with('old',$old)
			->with('new',$new)
			->with('updated',$updated)
			->with('f',new \finfo);
	}
}