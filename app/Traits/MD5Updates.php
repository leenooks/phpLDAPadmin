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
		if (! parent::isDirty())
			return TRUE;

		foreach ($this->values->diff($this->oldValues) as $key => $value)
			if (md5(Arr::get($this->oldValues,$key)) !== $value)
				return TRUE;

		return FALSE;
	}
}