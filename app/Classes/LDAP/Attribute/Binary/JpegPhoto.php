<?php

namespace App\Classes\LDAP\Attribute\Binary;

use App\Classes\LDAP\Attribute\Binary;

/**
 * Represents an attribute whose values are jpeg pictures
 */
class JpegPhoto extends Binary
{
	public function __toString(): string
	{
		$result = '';
		$f = new \finfo;

		foreach ($this->values as $value) {
			switch ($x=$f->buffer($value,FILEINFO_MIME_TYPE)) {
				case 'image/jpeg':
				default:
					$result .= sprintf("<img style='display:block; width:100px;height:100px;' src='data:%s;base64, %s' />",$x,base64_encode($value));
			}
		}

		return $result;
	}
}