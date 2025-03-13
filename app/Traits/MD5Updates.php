<?php

/**
 * Determine if a value has changed by comparing its MD5 value
 */
namespace App\Traits;

use Illuminate\Support\Arr;

trait MD5Updates
{
	public function isDirty(): bool
	{
		foreach ($this->values->diff($this->values_old) as $key => $value)
			if (md5(Arr::get($this->values_old,$key)) !== $value)
				return TRUE;

		return FALSE;
	}
}