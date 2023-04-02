<?php

namespace App\Classes\LDAP\Attribute\Binary;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Binary;

/**
 * Represents an JpegPhoto Attribute
 */
final class JpegPhoto extends Binary
{
	public function __construct(string $name,array $values)
	{
		parent::__construct($name,$values);

		$this->internal = FALSE;
	}

	public function render(bool $edit=FALSE): View
	{
		return view('components.attribute.binary.jpegphoto')
			->with('edit',$edit)
			->with('o',$this)
			->with('f',new \finfo);
	}
}