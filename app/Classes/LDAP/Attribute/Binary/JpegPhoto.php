<?php

namespace App\Classes\LDAP\Attribute\Binary;

use App\Classes\LDAP\Attribute\Binary;

/**
 * Represents an attribute whose values are jpeg pictures
 */
final class JpegPhoto extends Binary
{
	public function __construct(string $name,array $values)
	{
		parent::__construct($name,$values);

		$this->internal = FALSE;
	}

	public function __toString(): string
	{
		// We'll use finfo to try and figure out what type of image is stored
		$f = new \finfo;

		$result = '<table class="table table-borderless p-0 m-0"><tr>';

		foreach ($this->values as $value) {
			switch ($x=$f->buffer($value,FILEINFO_MIME_TYPE)) {
				case 'image/jpeg':
				default:
					$result .= sprintf('<td><img class="jpegphoto" src="data:%s;base64, %s" />%s</td>',
						$x,
						base64_encode($value),
						$this->deletable ? sprintf('<br><span class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> %s</span>',__('Delete')) : '');
			}
		}

		$result .= '</tr></table>';

		return $result;
	}
}