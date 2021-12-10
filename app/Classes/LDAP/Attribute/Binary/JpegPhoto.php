<?php

namespace App\Classes\LDAP\Attribute\Binary;

use App\Classes\LDAP\Attribute\Binary;

/**
 * Represents an attribute whose values are jpeg pictures
 */
final class JpegPhoto extends Binary
{
	public function __toString(): string
	{
		$result = '';
		// We'll use finfo to try and figure out what type of image is stored
		$f = new \finfo;

		foreach ($this->values as $value) {
			switch ($x=$f->buffer($value,FILEINFO_MIME_TYPE)) {
				case 'image/jpeg':
				default:
					$result .= sprintf('<img class="jpegphoto" src="data:%s;base64, %s" />',$x,base64_encode($value));
			}
		}

		return $result;
	}
}