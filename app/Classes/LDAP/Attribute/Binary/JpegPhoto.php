<?php

namespace App\Classes\LDAP\Attribute\Binary;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Binary;
use App\Traits\MD5Updates;

/**
 * Represents an JpegPhoto Attribute
 */
final class JpegPhoto extends Binary
{
	use MD5Updates;

	public function __construct(string $name,array $values)
	{
		parent::__construct($name,$values);

		$this->internal = FALSE;
	}

	public function render(bool $edit=FALSE,bool $blank=FALSE): View
	{
		return view('components.attribute.binary.jpegphoto')
			->with('edit',$edit)
			->with('blank',$blank)
			->with('o',$this)
			->with('f',new \finfo);
	}
}